/**
 * Diagnostic script: explore window.__NUXT__ shape on any IDX Nuxt page.
 *
 * Usage:
 *   node resources/js/playwright/debug-nuxt.mjs [url]
 *
 * Example:
 *   node resources/js/playwright/debug-nuxt.mjs 'https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham'
 */

import { chromium } from 'playwright';

const url = process.argv[2] || 'https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham';

const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

const context = await browser.newContext({
    // Mimic a real desktop browser to reduce Cloudflare friction
    userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36',
    viewport: { width: 1366, height: 768 },
});

const page = await context.newPage();

// Block heavy 3rd-party assets that often keep networkidle hanging on VPS
await page.route('**/*', (route) => {
    const u = route.request().url();
    if (/google-analytics|googletagmanager|doubleclick|facebook|hotjar|cdn-cgi\/rum|fonts\.googleapis|fonts\.gstatic/.test(u)) {
        return route.abort();
    }
    return route.continue();
});

try {
    await page.goto(url, {
        waitUntil: 'domcontentloaded',
        timeout: 45000,
    });

    await page.waitForFunction(() => !!window.__NUXT__, { timeout: 15000 }).catch(() => {});
    await page.waitForTimeout(2500); // small grace period for Nuxt hydration

    const result = await page.evaluate(() => {
        const nuxt = window.__NUXT__;
        if (!nuxt) return { error: 'no __NUXT__', htmlSnippet: document.body.innerText.slice(0, 500) };

        const topKeys = Object.keys(nuxt);
        const foundArrays = {};

        function searchData(obj, path, depth = 0) {
            if (depth > 8 || !obj || typeof obj !== 'object') return;
            if (Array.isArray(obj)) {
                if (obj.length > 0 && typeof obj[0] === 'object' && obj[0] !== null) {
                    const keys = Object.keys(obj[0]).filter(k => !k.startsWith('_'));
                    foundArrays[path] = { count: obj.length, keys, sample: obj[0] };
                }
                return;
            }
            for (const key of Object.keys(obj).slice(0, 30)) {
                searchData(obj[key], path ? `${path}.${key}` : key, depth + 1);
            }
        }

        searchData(nuxt, '');
        return { topKeys, foundArrays };
    });

    console.log(JSON.stringify(result, null, 2));
} catch (e) {
    console.error('ERROR:', e.message);
    process.exit(1);
} finally {
    await browser.close();
}
