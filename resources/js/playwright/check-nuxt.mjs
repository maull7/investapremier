import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage();
await page.goto('https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham', { waitUntil: 'networkidle', timeout: 30000 });
await page.waitForTimeout(3000);

const result = await page.evaluate(() => {
  const nuxt = window.__NUXT__;
  if (!nuxt) return { error: 'no nuxt' };
  
  const topKeys = Object.keys(nuxt);
  const foundArrays = {};
  
  function searchData(obj, path, depth = 0) {
    if (depth > 8 || !obj || typeof obj !== 'object') return;
    if (Array.isArray(obj)) {
      if (obj.length > 0 && typeof obj[0] === 'object' && obj[0] !== null) {
        const keys = Object.keys(obj[0]).filter(k => !k.startsWith('_'));
        foundArrays[path] = { count: obj.length, keys };
      }
      return;
    }
    for (const key of Object.keys(obj).slice(0, 20)) {
      searchData(obj[key], path ? `${path}.${key}` : key, depth + 1);
    }
  }
  
  searchData(nuxt, '');
  
  return { topKeys, foundArrays };
});

console.log(JSON.stringify(result, null, 2));
await browser.close();
