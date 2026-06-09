import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage();
await page.goto('https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham', {
  waitUntil: 'domcontentloaded', timeout: 45000
});
await page.waitForTimeout(8000);

const result = await page.evaluate(() => {
  const nuxt = window.__NUXT__;
  if (!nuxt) {
    // Try alternative: check how script captures __NUXT__
    const scripts = document.querySelectorAll('script');
    for (const s of scripts) {
      if (s.textContent && s.textContent.includes('__NUXT__')) {
        return { found: false, hasNuxtInScript: true };
      }
    }
    return { found: false, hasNuxtInScript: false, html: document.body.innerHTML.substring(0, 2000) };
  }
  
  const dataObj = nuxt.data || {};
  const dataKeys = Object.keys(dataObj);
  
  const allData = {};
  for (const key of dataKeys) {
    const val = dataObj[key];
    if (Array.isArray(val)) {
      allData[key] = { type: 'array', length: val.length, sample: JSON.stringify(val[0]).substring(0, 300) };
    } else if (typeof val === 'object' && val !== null) {
      const subKeys = Object.keys(val);
      const sample = {};
      for (const sk of subKeys.slice(0, 10)) {
        const sv = val[sk];
        if (Array.isArray(sv)) {
          sample[sk] = { type: 'array', length: sv.length, sampleKeys: typeof sv[0] === 'object' ? Object.keys(sv[0]).slice(0, 20) : [] };
        } else {
          sample[sk] = { type: typeof sv };
        }
      }
      allData[key] = { type: 'object', keys: subKeys.slice(0, 20), sample };
    } else {
      allData[key] = { type: typeof val, value: String(val).substring(0, 100) };
    }
  }
  
  return { found: true, dataKeys, allData };
});

console.log(JSON.stringify(result, null, 2));
await browser.close();
