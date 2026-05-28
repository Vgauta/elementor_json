#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const pixelmatch = require('pixelmatch');
const { PNG } = require('pngjs');

async function render(url, outPath, viewport) {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport });
  await page.goto(url, { waitUntil: 'networkidle' });
  await page.screenshot({ path: outPath, fullPage: true });
  await browser.close();
}

function diffImages(originalPath, generatedPath, diffPath, threshold = 0.1) {
  const img1 = PNG.sync.read(fs.readFileSync(originalPath));
  const img2 = PNG.sync.read(fs.readFileSync(generatedPath));
  const width = Math.min(img1.width, img2.width);
  const height = Math.min(img1.height, img2.height);

  const a = new PNG({ width, height });
  const b = new PNG({ width, height });
  PNG.bitblt(img1, a, 0, 0, width, height, 0, 0);
  PNG.bitblt(img2, b, 0, 0, width, height, 0, 0);

  const diff = new PNG({ width, height });
  const mismatch = pixelmatch(a.data, b.data, diff.data, width, height, { threshold });
  fs.writeFileSync(diffPath, PNG.sync.write(diff));
  const ratio = mismatch / (width * height);
  return { mismatch_pixels: mismatch, total_pixels: width * height, mismatch_ratio: ratio };
}

(async () => {
  const input = JSON.parse(fs.readFileSync(0, 'utf8'));
  const renderPath = path.resolve(input.generated_screenshot_path);
  await render(input.generated_url, renderPath, input.viewport || { width: 1440, height: 2200 });
  const result = diffImages(input.original_screenshot_path, renderPath, input.diff_output_path, input.threshold || 0.1);
  process.stdout.write(JSON.stringify({ ok: true, ...result }));
})();
