const { chromium } = require('D:/Project/MarketPulse/node_modules/playwright');
const path = require('path');
const fs = require('fs');

async function generate() {
  console.log('Launching Edge via Playwright to generate WordPress.org banners and icons...');
  const browser = await chromium.launch({ channel: 'msedge', headless: true });

  const logoPath = path.join(__dirname, 'assets', 'images', 'logo.png');
  const logoBase64 = fs.readFileSync(logoPath).toString('base64');
  const logoDataUri = `data:image/png;base64,${logoBase64}`;

  const targetDir = path.join(__dirname, '.wordpress-org');
  if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
  }

  // 1. Icon HTML (256x256 or 128x128)
  const iconHtml = (size) => `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
          width: ${size}px;
          height: ${size}px;
          background: #060D1D;
          display: flex;
          align-items: center;
          justify-content: center;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
          overflow: hidden;
        }
        .container {
          width: 100%;
          height: 100%;
          position: relative;
          background: radial-gradient(circle at 35% 25%, #132752 0%, #081229 60%, #040814 100%);
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .glow-mesh {
          position: absolute;
          width: ${size * 0.9}px;
          height: ${size * 0.9}px;
          background: radial-gradient(circle, rgba(37, 99, 235, 0.4) 0%, rgba(16, 185, 129, 0.2) 50%, transparent 75%);
          filter: blur(${size * 0.08}px);
        }
        .pulse-ring {
          position: absolute;
          width: ${size * 0.72}px;
          height: ${size * 0.72}px;
          border-radius: 50%;
          border: 1px dashed rgba(52, 211, 153, 0.35);
          pointer-events: none;
        }
        .card-frame {
          width: ${size * 0.84}px;
          height: ${size * 0.84}px;
          background: rgba(15, 27, 60, 0.82);
          border: ${size > 200 ? '2px' : '1.5px'} solid rgba(255, 255, 255, 0.18);
          border-radius: ${size * 0.22}px;
          display: flex;
          align-items: center;
          justify-content: center;
          box-shadow: 0 ${size * 0.06}px ${size * 0.18}px rgba(0, 0, 0, 0.7), 
                      inset 0 0 ${size * 0.06}px rgba(37, 99, 235, 0.35);
          position: relative;
        }
        .logo-img {
          width: ${size * 0.58}px;
          height: ${size * 0.58}px;
          object-fit: contain;
          filter: drop-shadow(0 ${size * 0.03}px ${size * 0.08}px rgba(37, 99, 235, 0.7));
        }
      </style>
    </head>
    <body>
      <div class="container">
        <div class="glow-mesh"></div>
        <div class="card-frame">
          <div class="pulse-ring"></div>
          <img class="logo-img" src="${logoDataUri}" alt="Logo" />
        </div>
      </div>
    </body>
    </html>
  `;

  // 2. Banner HTML (772x250 or 1544x500)
  const bannerHtml = (scale = 1) => {
    const w = 772 * scale;
    const h = 250 * scale;
    return `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
          width: ${w}px;
          height: ${h}px;
          background: #060C1B;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          color: #FFFFFF;
          overflow: hidden;
          position: relative;
          -webkit-font-smoothing: antialiased;
        }
        .banner-root {
          width: 100%;
          height: 100%;
          background: linear-gradient(135deg, #050B17 0%, #091328 45%, #0E1F42 100%);
          display: flex;
          position: relative;
          overflow: hidden;
        }
        /* Digital grid pattern */
        .grid-overlay {
          position: absolute;
          top: 0; left: 0; right: 0; bottom: 0;
          background-image: 
            radial-gradient(rgba(255, 255, 255, 0.09) 1px, transparent 1px);
          background-size: ${20 * scale}px ${20 * scale}px;
          opacity: 0.75;
        }
        /* Ambient Lighting */
        .ambient-glow-left {
          position: absolute;
          top: ${-60 * scale}px;
          left: ${-60 * scale}px;
          width: ${400 * scale}px;
          height: ${400 * scale}px;
          background: radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, transparent 65%);
          filter: blur(${45 * scale}px);
        }
        .ambient-glow-right {
          position: absolute;
          bottom: ${-80 * scale}px;
          right: ${-40 * scale}px;
          width: ${460 * scale}px;
          height: ${460 * scale}px;
          background: radial-gradient(circle, rgba(37, 99, 235, 0.38) 0%, transparent 70%);
          filter: blur(${50 * scale}px);
        }

        /* Left Content Column */
        .left-col {
          flex: 1.15;
          padding: ${24 * scale}px ${32 * scale}px;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          z-index: 10;
        }

        .brand-header {
          display: flex;
          align-items: center;
          gap: ${12 * scale}px;
        }
        .logo-box {
          width: ${44 * scale}px;
          height: ${44 * scale}px;
          background: rgba(15, 27, 60, 0.92);
          border: 1.5px solid rgba(59, 130, 246, 0.5);
          border-radius: ${10 * scale}px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: ${5 * scale}px;
          box-shadow: 0 ${4 * scale}px ${14 * scale}px rgba(37, 99, 235, 0.4);
        }
        .logo-box img {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
        .brand-title {
          font-size: ${24 * scale}px;
          font-weight: 800;
          letter-spacing: -0.02em;
          color: #FFFFFF;
          display: flex;
          align-items: baseline;
          gap: ${8 * scale}px;
        }
        .brand-accent {
          color: #38BDF8;
        }
        .badge-wc {
          background: rgba(16, 185, 129, 0.16);
          color: #34D399;
          border: 1px solid rgba(52, 211, 153, 0.4);
          font-size: ${10 * scale}px;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.05em;
          padding: ${2.5 * scale}px ${8 * scale}px;
          border-radius: ${4 * scale}px;
        }

        .headline-group {
          margin-top: ${8 * scale}px;
        }
        .main-headline {
          font-size: ${21 * scale}px;
          font-weight: 800;
          line-height: 1.25;
          letter-spacing: -0.02em;
          color: #F8FAFC;
        }
        .main-headline span {
          background: linear-gradient(135deg, #38BDF8 0%, #34D399 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }
        .sub-headline {
          font-size: ${12 * scale}px;
          font-weight: 400;
          color: #94A3B8;
          line-height: 1.45;
          margin-top: ${6 * scale}px;
          max-width: ${420 * scale}px;
        }

        .pills-row {
          display: flex;
          gap: ${8 * scale}px;
          flex-wrap: wrap;
          margin-top: ${12 * scale}px;
        }
        .pill {
          background: rgba(15, 27, 60, 0.85);
          border: 1px solid rgba(255, 255, 255, 0.12);
          border-radius: ${6 * scale}px;
          padding: ${4.5 * scale}px ${10 * scale}px;
          font-size: ${11 * scale}px;
          font-weight: 600;
          display: flex;
          align-items: center;
          gap: ${5 * scale}px;
          color: #E2E8F0;
        }
        .pill-highlight {
          background: rgba(16, 185, 129, 0.18);
          border-color: rgba(52, 211, 153, 0.45);
          color: #6EE7B7;
          font-weight: 700;
          box-shadow: 0 0 ${12 * scale}px rgba(16, 185, 129, 0.25);
        }

        /* Right Content Column (Card Mockup) */
        .right-col {
          flex: 0.88;
          padding: ${18 * scale}px ${26 * scale}px ${18 * scale}px 0;
          display: flex;
          align-items: center;
          justify-content: flex-end;
          z-index: 10;
        }
        .demo-card {
          width: ${310 * scale}px;
          background: rgba(12, 23, 50, 0.92);
          backdrop-filter: blur(16px);
          border: 1px solid rgba(255, 255, 255, 0.18);
          border-radius: ${12 * scale}px;
          padding: ${14 * scale}px ${16 * scale}px;
          box-shadow: 0 ${14 * scale}px ${32 * scale}px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        .card-top {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: ${10 * scale}px;
          border-bottom: 1px solid rgba(255, 255, 255, 0.08);
          padding-bottom: ${8 * scale}px;
        }
        .card-top-title {
          font-size: ${11 * scale}px;
          font-weight: 700;
          color: #94A3B8;
          text-transform: uppercase;
          letter-spacing: 0.05em;
          display: flex;
          align-items: center;
          gap: ${6 * scale}px;
        }
        .status-dot {
          display: flex;
          align-items: center;
          gap: ${5 * scale}px;
          font-size: ${11 * scale}px;
          font-weight: 600;
          color: #34D399;
        }
        .status-dot::before {
          content: "";
          width: ${6 * scale}px;
          height: ${6 * scale}px;
          background: #34D399;
          border-radius: 50%;
          box-shadow: 0 0 ${8 * scale}px #34D399;
        }

        .product-name {
          font-size: ${12 * scale}px;
          font-weight: 600;
          color: #F8FAFC;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          margin-bottom: ${10 * scale}px;
        }

        .metric-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: ${8 * scale}px;
          margin-bottom: ${10 * scale}px;
        }
        .metric-box {
          background: rgba(5, 12, 28, 0.65);
          border: 1px solid rgba(255, 255, 255, 0.07);
          border-radius: ${7 * scale}px;
          padding: ${8 * scale}px ${10 * scale}px;
        }
        .metric-lbl {
          font-size: ${10 * scale}px;
          color: #94A3B8;
          text-transform: uppercase;
          letter-spacing: 0.03em;
          margin-bottom: ${2 * scale}px;
        }
        .metric-val {
          font-size: ${14 * scale}px;
          font-weight: 700;
          color: #F8FAFC;
        }
        .metric-val.cogs { color: #F87171; }
        .metric-val.fees { color: #FBBF24; }

        .profit-banner {
          background: linear-gradient(135deg, rgba(6, 78, 59, 0.8) 0%, rgba(6, 95, 70, 0.9) 100%);
          border: 1px solid rgba(52, 211, 153, 0.45);
          border-radius: ${8 * scale}px;
          padding: ${9 * scale}px ${12 * scale}px;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        .profit-info {
          display: flex;
          flex-direction: column;
        }
        .profit-lbl {
          font-size: ${9.5 * scale}px;
          font-weight: 700;
          color: #A7F3D0;
          text-transform: uppercase;
          letter-spacing: 0.05em;
        }
        .profit-val {
          font-size: ${17 * scale}px;
          font-weight: 800;
          color: #FFFFFF;
        }
        .margin-badge {
          background: #059669;
          color: #FFFFFF;
          font-size: ${11 * scale}px;
          font-weight: 700;
          padding: ${3 * scale}px ${8 * scale}px;
          border-radius: ${6 * scale}px;
        }
      </style>
    </head>
    <body>
      <div class="banner-root">
        <div class="grid-overlay"></div>
        <div class="ambient-glow-left"></div>
        <div class="ambient-glow-right"></div>

        <!-- Left Column -->
        <div class="left-col">
          <div class="brand-header">
            <div class="logo-box">
              <img src="${logoDataUri}" alt="MarketPulse" />
            </div>
            <div class="brand-title">
              MarketPulse <span class="brand-accent">COGS</span>
            </div>
            <span class="badge-wc">WooCommerce</span>
          </div>

          <div class="headline-group">
            <div class="main-headline">
              Simple Cost of Goods &<br><span>True Net Profit Tracker</span>
            </div>
            <div class="sub-headline">
              Automated wholesale COGS, payment gateway fees, courier deficits & overhead expenses. Replace vanity revenue with real bank profit.
            </div>
          </div>

          <div class="pills-row">
            <div class="pill pill-highlight">🛡️ 100% HPOS Ready</div>
            <div class="pill">⚡ 0ms Storefront Bloat</div>
            <div class="pill">💳 Gateway Fee Detection</div>
            <div class="pill">🚚 Courier Loss Tracking</div>
          </div>
        </div>

        <!-- Right Column (Card Mockup) -->
        <div class="right-col">
          <div class="demo-card">
            <div class="card-top">
              <div class="card-top-title">
                <span>Executive Profit Hub</span>
              </div>
              <div class="status-dot">Live Audit</div>
            </div>
            <div class="product-name">Zebra Fashionable Laptop Bag (SKU: ZB-01)</div>

            <div class="metric-grid">
              <div class="metric-box">
                <div class="metric-lbl">Retail Price</div>
                <div class="metric-val">$130.00</div>
              </div>
              <div class="metric-box">
                <div class="metric-lbl">Wholesale COGS</div>
                <div class="metric-val cogs">-$75.00</div>
              </div>
              <div class="metric-box">
                <div class="metric-lbl">Stripe Fee</div>
                <div class="metric-val fees">-$4.07</div>
              </div>
              <div class="metric-box">
                <div class="metric-lbl">Courier Deficit</div>
                <div class="metric-val fees">-$3.50</div>
              </div>
            </div>

            <div class="profit-banner">
              <div class="profit-info">
                <div class="profit-lbl">True Net Profit</div>
                <div class="profit-val">+$47.43</div>
              </div>
              <div class="margin-badge">36.5% Net Margin</div>
            </div>
          </div>
        </div>
      </div>
    </body>
    </html>
    `;
  };

  // Render 1. icon-256x256.png
  console.log('Rendering icon-256x256.png...');
  const pageIcon256 = await browser.newPage({ viewport: { width: 256, height: 256 } });
  await pageIcon256.setContent(iconHtml(256));
  await pageIcon256.screenshot({ path: path.join(targetDir, 'icon-256x256.png') });
  await pageIcon256.close();

  // Render 2. icon-128x128.png
  console.log('Rendering icon-128x128.png...');
  const pageIcon128 = await browser.newPage({ viewport: { width: 128, height: 128 } });
  await pageIcon128.setContent(iconHtml(128));
  await pageIcon128.screenshot({ path: path.join(targetDir, 'icon-128x128.png') });
  await pageIcon128.close();

  // Render 3. banner-1544x500.png (Retina 2x scale)
  console.log('Rendering banner-1544x500.png...');
  const pageBanner1544 = await browser.newPage({ viewport: { width: 1544, height: 500 } });
  await pageBanner1544.setContent(bannerHtml(2));
  await pageBanner1544.screenshot({ path: path.join(targetDir, 'banner-1544x500.png') });
  await pageBanner1544.close();

  // Render 4. banner-772x250.png (Standard 1x scale)
  console.log('Rendering banner-772x250.png...');
  const pageBanner772 = await browser.newPage({ viewport: { width: 772, height: 250 } });
  await pageBanner772.setContent(bannerHtml(1));
  await pageBanner772.screenshot({ path: path.join(targetDir, 'banner-772x250.png') });
  await pageBanner772.close();

  console.log('All WordPress.org brand assets generated successfully via Playwright!');
  await browser.close();
}

generate().catch(console.error);
