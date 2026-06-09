import { chromium } from 'playwright';
import process from 'node:process';

if(process.env.APP_ENV == 'production') {
    process.env.PLAYWRIGHT_BROWSERS_PATH =
        process.env.PLAYWRIGHT_BROWSERS_PATH ||
        '/var/www/.cache/ms-playwright';
}

const url = process.argv[2];
if (!url) {
    console.error('Usage: node render-page.mjs <url>');
    process.exit(1);
}

const launchOpts = {
    headless: true,
    args: [
        '--disable-blink-features=AutomationControlled',
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-web-security',
        '--disable-features=IsolateOrigins,site-per-process',
        '--window-size=1920,1080',
    ],
};

const contextOpts = {
    userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    viewport: { width: 1920, height: 1080 },
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
    ignoreHTTPSErrors: true,
};

let browser;
try {
    browser = await chromium.launch(launchOpts);
    const context = await browser.newContext(contextOpts);

    await context.addInitScript(() => {
        Object.defineProperty(navigator, 'webdriver', { get: () => false });
    });

    const page = await context.newPage();
    page.setDefaultTimeout(60000);
    page.setDefaultNavigationTimeout(60000);

    // Block heavy third-party resources that aren't needed (analytics, ads,
    // RUM, fonts, images). Speeds up loading dramatically on slow VPS links.
    await page.route('**/*', (route) => {
        const req = route.request();
        const u = req.url();
        const t = req.resourceType();
        if (['image', 'font', 'media'].includes(t)) {
            return route.abort();
        }
        if (/(googletagmanager|google-analytics|googleadservices|doubleclick|sentry|cloudflareinsights|cdn-cgi\/rum|facebook|hotjar|clarity\.ms|segment\.com|amplitude)/i.test(u)) {
            return route.abort();
        }
        return route.continue();
    });

    // `networkidle` is unreliable on IDX (third-party RUM/analytics keep the
    // network busy indefinitely on some VPS networks). Use `domcontentloaded`
    // which fires as soon as the HTML parser is done, then wait for content/
    // network to settle separately with shorter individual timeouts.
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });

    // Wait for body text to appear (real content rendered)
    try {
        await page.waitForFunction(() => (document.body?.innerText?.length || 0) > 200, { timeout: 15000 });
    } catch { /* continue */ }

    // Best-effort wait for network to quiet down (don't fail if it never does)
    try {
        await page.waitForLoadState('networkidle', { timeout: 8000 });
    } catch { /* continue with whatever we have */ }

    await page.waitForTimeout(3000);

    // Strategy 1: Extract structured data from page state (__NUXT__, __INITIAL_STATE__, etc.)
    let extractedData = null;
    let extractionSource = null;

    const stateData = await page.evaluate(() => {
        const candidates = ['__NUXT__', '__INITIAL_STATE__', '__NEXT_DATA__', '__APOLLO_STATE__', '__REACT_QUERY_STATE__'];
        for (const key of candidates) {
            const val = window[key];
            if (val) return { source: key, data: JSON.parse(JSON.stringify(val)) };
        }
        return null;
    });

    if (stateData) {
        extractionSource = stateData.source;
        const data = stateData.data;

        // Find the largest array of objects in the state - that's likely our table data
        let bestMatch = null;
        let bestSize = 0;
        let allArrays = [];

        function findArrays(obj, path) {
            if (!obj || typeof obj !== 'object') return;
            if (Array.isArray(obj)) {
                const objCount = obj.filter(i => i && typeof i === 'object' && !Array.isArray(i)).length;
                allArrays.push({ path, data: obj, count: objCount });
                if (objCount >= 3 && objCount > bestSize) {
                    bestSize = objCount;
                    bestMatch = { path, data: obj };
                }
                return;
            }
            for (const [k, v] of Object.entries(obj)) {
                findArrays(v, path ? `${path}.${k}` : k);
            }
        }

        findArrays(data, '');

        // Prefer array with "stock-like" keys (Code, Price, etc.) over smaller arrays
        if (allArrays.length > 1) {
            const stockLike = allArrays.filter(a => {
                if (a.data.length === 0) return false;
                const keys = Object.keys(a.data[0]).map(k => k.toLowerCase());
                return keys.some(k => ['code', 'price', 'change', 'volume', 'frequency', 'value'].includes(k));
            });
            if (stockLike.length > 0) {
                stockLike.sort((a, b) => b.count - a.count);
                bestMatch = { path: stockLike[0].path, data: stockLike[0].data };
            }
        }

        if (bestMatch) {
            extractedData = bestMatch.data;
        }
    }

    // Strategy 2: Extract visible table data from DOM (always, even if NUXT found)
    let tableData = await page.evaluate(() => {
        const tables = document.querySelectorAll('table');
        const results = [];
        tables.forEach((table, ti) => {
            const headers = [];
            const thead = table.querySelector('thead');
            if (thead) {
                thead.querySelectorAll('th, td').forEach(th => headers.push(th.textContent.trim()));
            }
            const rows = [];
            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = [];
                tr.querySelectorAll('td, th').forEach(td => cells.push(td.textContent.trim()));
                if (cells.length > 0) rows.push(cells);
            });
            if (rows.length >= 3) {
                results.push({ tableIndex: ti, headers, rows, rowCount: rows.length });
            }
        });
        return results.length > 0 ? results : null;
    });

    // Strategy 2b: If table found and has pagination controls, try to get more rows
    if (tableData && tableData.length > 0) {
        try {
            const maxRowsBefore = tableData.reduce((sum, t) => sum + t.rows.length, 0);
            // Try to increase DataTable page length to max (or "All" if available)
            await page.evaluate(() => {
                const selects = document.querySelectorAll('select');
                for (const sel of selects) {
                    const allOption = Array.from(sel.options).find(o => {
                        const label = (o.text || '').trim().toLowerCase();
                        return o.value === '-1' || label === 'all' || label === 'semua';
                    });
                    if (allOption) {
                        sel.value = allOption.value;
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        sel.dispatchEvent(new Event('input', { bubbles: true }));
                        return;
                    }
                    const opts = Array.from(sel.options).map(o => parseInt(o.value)).filter(v => !isNaN(v) && v > 0);
                    if (opts.length > 0) {
                        const max = Math.max(...opts);
                        if (max > 10) {
                            sel.value = String(max);
                            sel.dispatchEvent(new Event('change', { bubbles: true }));
                            sel.dispatchEvent(new Event('input', { bubbles: true }));
                            return;
                        }
                    }
                }
            });
            await page.waitForTimeout(6000);
            // Re-extract table after increasing page length
            const expandedTable = await page.evaluate(() => {
                const tables = document.querySelectorAll('table');
                const results = [];
                tables.forEach((table, ti) => {
                    const headers = [];
                    const thead = table.querySelector('thead');
                    if (thead) {
                        thead.querySelectorAll('th, td').forEach(th => headers.push(th.textContent.trim()));
                    }
                    const rows = [];
                    table.querySelectorAll('tbody tr').forEach(tr => {
                        const cells = [];
                        tr.querySelectorAll('td, th').forEach(td => cells.push(td.textContent.trim()));
                        if (cells.length > 0) rows.push(cells);
                    });
                    if (rows.length >= 3) {
                        results.push({ tableIndex: ti, headers, rows, rowCount: rows.length });
                    }
                });
                return results.length > 0 ? results : null;
            });
            const maxRowsAfter = expandedTable ? expandedTable.reduce((sum, t) => sum + t.rows.length, 0) : 0;
            if (maxRowsAfter > maxRowsBefore) {
                tableData = expandedTable;
            }
        } catch { /* continue with original tableData */ }
    }

    // Strategy 3: If no table and no NUXT, try visible table-like data from text
    // Try to find table-like structures in plain text
    let textTable = null;
    if (!tableData && !extractedData) {
        textTable = await page.evaluate(() => {
            const text = document.body?.innerText || '';
            const lines = text.split('\n').filter(l => l.trim());
            // Look for lines with tab/space separated values that look tabular
            const scored = lines.map((line, i) => {
                const parts = line.split(/\s{2,}|\t/).filter(Boolean);
                return parts.length >= 3 ? { line: i, parts, count: parts.length } : null;
            }).filter(Boolean);
            // Return first block of consecutive tabular lines
            return scored.length >= 5 ? { sampleCount: scored.length, data: scored } : null;
        });
    }

    // Fallback to visible text
    const visibleText = await page.evaluate(() => document.body?.innerText || '');

    const response = {
        success: true,
        text_size: visibleText.length,
        text_content: (!extractedData && !tableData) ? visibleText.slice(0, 30000) : null,
    };

    if (extractedData) {
        response.extracted = extractedData;
        response.extracted_count = extractedData.length;
        response.extraction_source = extractionSource;
    }

    if (tableData) {
        // Convert table data to array-of-objects using headers as keys
        const converted = [];
        for (const tbl of tableData) {
            if (tbl.headers.length > 0) {
                for (const row of tbl.rows) {
                    const obj = {};
                    tbl.headers.forEach((h, i) => {
                        // Clean header: strip DataTable sort button text (everything after \n)
                        const clean = h.replace(/\n.*$/s, '').trim();
                        obj[clean] = row[i] || '';
                    });
                    converted.push(obj);
                }
            } else {
                // No headers: use positional keys
                for (const row of tbl.rows) {
                    const obj = {};
                    row.forEach((cell, i) => {
                        obj[`col${i}`] = cell;
                    });
                    converted.push(obj);
                }
            }
        }
        if (converted.length > 0) {
            response.table_data = converted;
            response.table_data_count = converted.length;
        }
    }

    console.log(JSON.stringify(response));
} catch (err) {
    console.log(JSON.stringify({ success: false, error: err.message || String(err) }));
} finally {
    if (browser) await browser.close();
}
