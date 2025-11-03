import { chromium } from 'playwright-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';
import fs from 'fs';
import path from 'path';
import process from 'process';
import { fileURLToPath } from 'url';

// Inject stealth plugin
chromium.use(StealthPlugin());

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const url = process.argv[2];
const outFile = process.argv[3] || path.join(__dirname, 'costco-output.html');
const screenshotFile = outFile.replace('.html', '.png');

if (!url) {
  console.error('URL required. Usage: node puppeteer-costco.mjs <url> [outputFile]');
  process.exit(1);
}

const isProduction = process.env.NODE_ENV === 'production';

(async () => {
  const browser = await chromium.launch({
    headless: true,
    // executablePath: isProduction
    //         ? '/home/debian/.cache/puppeteer/chrome/linux-136.0.7103.49/chrome-linux64/chrome'
    //         : undefined,

    executablePath: '/home/maxxmitchy/.cache/ms-playwright/chromium-1194/chrome-linux/chrome',

    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-blink-features=AutomationControlled',
      '--ignore-certificate-errors',
      '--disable-dev-shm-usage',
      '--window-size=1280,800',
    ],
    timeout: 60000,
  });

  const context = await browser.newContext({
    userAgent:
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    viewport: { width: 1280, height: 800 },
    locale: 'en-US',
  });

  const page = await context.newPage();

  try {
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });

    const title = await page.title();
    if (/Access Denied|Robot Check|Service Unavailable/.test(title)) {
      throw new Error(`Blocked by Akamai. Title: "${title}"`);
    }

    // 👇 Only if the URL has purelife in it
    if (url.includes("purelife")) {
        // Wait until at least one product image is fully loaded
        await page.waitForSelector('.flex.w-full.flex-col.rounded-xl img[data-loaded="true"]', {
            timeout: 30000
        });
    }

    await page.mouse.move(300, 300);
    await page.keyboard.press('ArrowDown');
    await page.waitForTimeout(2000);

    let lastHeight = await page.evaluate('document.body.scrollHeight');
    let attempts = 0;

    while (attempts < 10) {
      await page.evaluate(() => window.scrollBy(0, window.innerHeight));
      await page.waitForTimeout(1500);

      const newHeight = await page.evaluate('document.body.scrollHeight');
      if (newHeight === lastHeight) break;

      lastHeight = newHeight;
      attempts++;
    }

    await page.screenshot({ path: screenshotFile, fullPage: true });

    const html = await page.content();
    fs.writeFileSync(outFile, html);

  } catch (err) {
    console.error('❌ Error:', err.message);
  } finally {
    await new Promise(res => setTimeout(res, 30000)); // Wait 30 seconds
    await browser.close();
  }
})();
