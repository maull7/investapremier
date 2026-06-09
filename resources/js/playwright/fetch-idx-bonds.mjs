import { chromium } from 'playwright';

// IDX exposes an internal API at /secondary/get/BondSukuk/bond which is what the
// UI dropdown uses internally. We hit it directly via Playwright so the request
// inherits the browser's Cloudflare clearance cookie from the bootstrap page load.
//
// Discovered bondType values:
//   1 = corporate bonds (~1419 total)
//   2/3 = government FR series (~189)
//   4 = government ritel ORI (~189)
//
// Args: node fetch-idx-bonds.mjs [bondType=1] [pageSize=2000]

const BOOTSTRAP_URL = 'https://www.idx.co.id/id/data-pasar/obligasi-sukuk/obligasi-sukuk-korporasi/';
const PAGE_TIMEOUT = 60000;

const bondType = parseInt(process.argv[2] || '1', 10);
const pageSize = parseInt(process.argv[3] || '2000', 10);
const maxRetries = 5;

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

    // Step 1: bootstrap — visit the public page once so Cloudflare grants a cookie.
    await page.goto(BOOTSTRAP_URL, { waitUntil: 'networkidle', timeout: PAGE_TIMEOUT });
    await page.waitForTimeout(3000);

    // Step 2: fetch via the bond list API. Retry on transient 5xx (rate limit / WAF).
    let lastError = null;
    let payload = null;
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        const result = await page.evaluate(async ({ size, type }) => {
            try {
                const r = await fetch(`/secondary/get/BondSukuk/bond?pageSize=${size}&indexFrom=1&bondType=${type}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!r.ok) return { ok: false, status: r.status };
                const j = await r.json();
                return { ok: true, data: j };
            } catch (e) {
                return { ok: false, error: String(e) };
            }
        }, { size: pageSize, type: bondType });

        if (result.ok && result.data) {
            payload = result.data;
            break;
        }
        lastError = result.status || result.error || 'unknown';
        // Backoff: 2s, 4s, 8s, 16s, 32s (capped)
        const backoff = Math.min(2000 * Math.pow(2, attempt - 1), 32000);
        await page.waitForTimeout(backoff);
    }

    if (!payload) {
        console.log(JSON.stringify({ success: false, error: `API failed after ${maxRetries} retries: ${lastError}` }));
        await browser.close();
        process.exit(0);
    }

    const total = payload.ResultCount || (Array.isArray(payload.Results) ? payload.Results.length : 0);
    const results = Array.isArray(payload.Results) ? payload.Results : [];

    // Trim whitespace padding present in IDX API responses
    const trim = (v) => (typeof v === 'string' ? v.replace(/\s+/g, ' ').trim() : v);
    const rows = results.map(r => {
        const o = {};
        for (const k of Object.keys(r)) o[k] = trim(r[k]);
        return o;
    });

    console.log(JSON.stringify({
        success: true,
        bond_type: bondType,
        total_count: total,
        returned_count: rows.length,
        page_size: pageSize,
        rows,
    }));
} catch (err) {
    console.log(JSON.stringify({ success: false, error: err.message || String(err) }));
} finally {
    if (browser) await browser.close();
}
