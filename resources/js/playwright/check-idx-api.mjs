import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage();

// Monitor all XHR/fetch requests
const apiCalls = [];
page.on('request', req => {
  const url = req.url();
  if (url.includes('idx.co.id') && (url.includes('api') || url.includes('json') || url.includes('data'))) {
    apiCalls.push({ url, method: req.method() });
  }
});

await page.goto('https://www.idx.co.id/id/data-pasar/data-saham/daftar-saham', { waitUntil: 'networkidle', timeout: 30000 });
await page.waitForTimeout(2000);

// Also try to find sector data from rendered table
const tableData = await page.evaluate(() => {
  // Try to find any table in the page
  const tables = document.querySelectorAll('table');
  const results = [];
  tables.forEach((t, ti) => {
    const headers = Array.from(t.querySelectorAll('th')).map(th => th.textContent.trim());
    if (headers.length > 0) {
      const rows = Array.from(t.querySelectorAll('tbody tr')).slice(0, 5).map(tr => {
        return Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
      });
      results.push({ tableIndex: ti, headers, sampleRows: rows });
    }
  });
  return results;
});

console.log(JSON.stringify({ apiCalls: apiCalls.slice(0, 20), tableData }, null, 2));
await browser.close();
