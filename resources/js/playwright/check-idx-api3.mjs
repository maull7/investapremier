import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage();
await page.goto('https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham', { 
  waitUntil: 'domcontentloaded', timeout: 45000 
});
await page.waitForTimeout(5000);

const result = await page.evaluate(() => {
  const nuxt = window.__NUXT__;
  if (!nuxt) return { error: 'no nuxt' };
  
  // Dump data section
  const dataSection = nuxt.data || {};
  const dataKeys = Object.keys(dataSection);
  
  const arrays = {};
  for (const key of dataKeys) {
    const val = dataSection[key];
    if (Array.isArray(val) && val.length > 0) {
      const sampleKeys = typeof val[0] === 'object' && val[0] !== null ? Object.keys(val[0]) : [];
      arrays[key] = { count: val.length, sampleKeys: sampleKeys.slice(0, 30) };
    } else if (typeof val === 'object' && val !== null) {
      // Check nested arrays
      for (const subKey of Object.keys(val)) {
        const subVal = val[subKey];
        if (Array.isArray(subVal) && subVal.length > 0) {
          const sampleKeys = typeof subVal[0] === 'object' && subVal[0] !== null ? Object.keys(subVal[0]) : [];
          arrays[`${key}.${subKey}`] = { count: subVal.length, sampleKeys: sampleKeys.slice(0, 30) };
        }
      }
    }
  }
  
  return {
    dataKeys,
    arrays,
    sample: dataKeys.length > 0 ? JSON.stringify(dataSection[dataKeys[0]]).substring(0, 500) : null
  };
});

console.log(JSON.stringify(result, null, 2));
await browser.close();
