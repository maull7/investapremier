import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const context = await browser.newContext({
  userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
});
const page = await context.newPage();

// Collect network responses
const responses = [];
page.on('response', resp => {
  const url = resp.url();
  const ct = resp.headers()['content-type'] || '';
  if (ct.includes('json') || ct.includes('javascript')) {
    responses.push({ url, status: resp.status(), contentType: ct });
  }
});

// Collect navigation/console errors
page.on('pageerror', err => console.log('PAGE ERROR:', err.message));

await page.goto('https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham', { 
  waitUntil: 'domcontentloaded', 
  timeout: 45000 
});
await page.waitForTimeout(5000);

// Try to get window.__NUXT__
const nuxtCheck = await page.evaluate(() => {
  const nuxt = window.__NUXT__;
  if (!nuxt) return { hasNuxt: false };
  const keys = Object.keys(nuxt);
  return { hasNuxt: true, topKeys: keys };
}).catch(() => ({ hasNuxt: false, error: 'evaluate failed' }));

console.log(JSON.stringify({
  nuxt: nuxtCheck,
  apiResponses: responses.slice(0, 30),
  url: page.url()
}, null, 2));

await browser.close();
