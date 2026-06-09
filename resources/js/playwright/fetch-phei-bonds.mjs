import { chromium } from 'playwright';

const URL = 'https://www.phei.co.id/Data/Informasi-Efek';
const PAGE_TIMEOUT = 60000;
const MAX_PAGES = 200;
const POSTBACK_WAIT = 1500;

const tabArg = (process.argv[2] || 'all').toLowerCase();
const tabs = tabArg === 'all'
    ? ['pemerintah', 'korporasi']
    : [tabArg].filter(t => ['pemerintah', 'korporasi'].includes(t));

if (tabs.length === 0) {
    console.log(JSON.stringify({ success: false, error: `Invalid tab arg "${tabArg}". Use: pemerintah | korporasi | all` }));
    process.exit(1);
}

const TABLE_CONFIG = {
    pemerintah: {
        gridName: 'gvObligasiPemerintah',
        postbackPrefix: 'dnn$ctr1481$SecuritiesInformation$idObligasiPemerintah$gvObligasiPemerintah',
    },
    korporasi: {
        gridName: 'gvObligasiKorporasi',
        postbackPrefix: 'dnn$ctr1481$SecuritiesInformation$idObligasiKorporasi$gvObligasiKorporasi',
    },
};

let browser;
try {
    browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });
    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        viewport: { width: 1920, height: 1080 },
    });
    const page = await context.newPage();
    page.setDefaultTimeout(PAGE_TIMEOUT);

    await page.goto(URL, { waitUntil: 'networkidle', timeout: PAGE_TIMEOUT });
    await page.waitForTimeout(3000);

    const allResults = {};

    for (const tabKey of tabs) {
        const cfg = TABLE_CONFIG[tabKey];
        const accumulated = [];
        const seenCodes = new Set();
        let pageNum = 1;
        let stoppedReason = null;

        while (pageNum <= MAX_PAGES) {
            const rows = await page.evaluate((gridName) => {
                const tables = Array.from(document.querySelectorAll('table'));
                const target = tables.find(t => {
                    return t.id && t.id.includes(gridName) || t.outerHTML.includes(gridName);
                });
                if (!target) return null;
                const headerCells = Array.from(target.querySelectorAll('tr')[0]?.querySelectorAll('th, td') || [])
                    .map(c => c.textContent.trim());
                const dataRows = Array.from(target.querySelectorAll('tr')).slice(1);
                const out = [];
                for (const tr of dataRows) {
                    const cells = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
                    if (cells.length === 0) continue;
                    if (cells.length < headerCells.length / 2) continue;
                    const obj = {};
                    headerCells.forEach((h, i) => {
                        if (h) obj[h] = cells[i] || '';
                    });
                    if (Object.keys(obj).length > 0) out.push(obj);
                }
                return out;
            }, cfg.gridName);

            if (!rows || rows.length === 0) {
                stoppedReason = `no_rows_on_page_${pageNum}`;
                break;
            }

            const newAdded = [];
            for (const r of rows) {
                const code = (r['Bond Code'] || r['BondCode'] || r['Code'] || '').trim();
                if (!code) continue;
                if (seenCodes.has(code)) continue;
                seenCodes.add(code);
                newAdded.push(r);
            }
            accumulated.push(...newAdded);

            if (newAdded.length === 0) {
                stoppedReason = 'no_new_codes';
                break;
            }

            const navResult = await page.evaluate(({ prefix, nextPage }) => {
                const target = `Page$${nextPage}`;
                const anchors = Array.from(document.querySelectorAll('a'));
                const link = anchors.find(a => {
                    const href = a.getAttribute('href') || '';
                    return href.includes(prefix) && href.includes(target);
                });
                if (!link) return { found: false };
                link.click();
                return { found: true };
            }, { prefix: cfg.postbackPrefix, nextPage: pageNum + 1 });

            if (!navResult.found) {
                stoppedReason = 'no_next_page_link';
                break;
            }

            await page.waitForTimeout(POSTBACK_WAIT);
            try {
                await page.waitForLoadState('networkidle', { timeout: 8000 });
            } catch { /* continue regardless */ }

            pageNum++;
        }

        allResults[tabKey] = {
            count: accumulated.length,
            pages_traversed: pageNum,
            stopped_reason: stoppedReason,
            rows: accumulated,
        };
    }

    const totalCount = Object.values(allResults).reduce((sum, t) => sum + t.count, 0);
    console.log(JSON.stringify({
        success: true,
        total_count: totalCount,
        tabs: allResults,
    }));
} catch (err) {
    console.log(JSON.stringify({ success: false, error: err.message || String(err) }));
} finally {
    if (browser) await browser.close();
}
