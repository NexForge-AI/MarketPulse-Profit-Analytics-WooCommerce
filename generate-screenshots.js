const { chromium } = require('D:/Project/MarketPulse/node_modules/playwright');
const path = require('path');
const fs = require('fs');

async function generateScreenshots() {
  console.log('Launching browser to render WordPress.org screenshots (1280x720)...');
  const browser = await chromium.launch({ channel: 'msedge', headless: true });

  const logoPath = path.join(__dirname, 'assets', 'images', 'logo.png');
  let logoDataUri = '';
  if (fs.existsSync(logoPath)) {
    const logoBase64 = fs.readFileSync(logoPath).toString('base64');
    logoDataUri = `data:image/png;base64,${logoBase64}`;
  }

  const targetDir = path.join(__dirname, '.wordpress-org');
  if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
  }

  // Common WordPress Admin Wrapper styling
  const wpWrapper = (title, activeSubmenu, contentHtml) => `
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>${title}</title>
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
          width: 1280px;
          height: 720px;
          background: #f0f0f1;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
          color: #3c434a;
          display: flex;
          flex-direction: column;
          overflow: hidden;
        }
        /* Top WP Admin Bar */
        .wp-admin-bar {
          height: 32px;
          background: #1d2327;
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 16px;
          color: #c3c4c7;
          font-size: 13px;
          flex-shrink: 0;
        }
        .wp-bar-left { display: flex; align-items: center; gap: 16px; }
        .wp-bar-site { font-weight: 600; color: #fff; display: flex; align-items: center; gap: 6px; }
        .wp-bar-badge { background: #2271b1; color: #fff; padding: 1px 6px; border-radius: 10px; font-size: 11px; }
        .wp-bar-right { display: flex; align-items: center; gap: 12px; font-size: 12px; }

        /* Main Admin Layout */
        .admin-layout {
          display: flex;
          flex: 1;
          height: calc(720px - 32px);
          overflow: hidden;
        }
        /* WP Left Sidebar */
        .wp-sidebar {
          width: 175px;
          background: #1d2327;
          flex-shrink: 0;
          display: flex;
          flex-direction: column;
          padding-top: 8px;
        }
        .wp-menu-item {
          padding: 8px 14px;
          color: #c3c4c7;
          font-size: 13px;
          display: flex;
          align-items: center;
          gap: 10px;
          text-decoration: none;
        }
        .wp-menu-item.active {
          background: #2271b1;
          color: #fff;
          font-weight: 600;
        }
        .wp-submenu-item {
          padding: 5px 14px 5px 34px;
          font-size: 12px;
          color: #c3c4c7;
        }
        .wp-submenu-item.active-sub {
          color: #72aee6;
          font-weight: 600;
          background: #13171b;
        }

        /* Content Area */
        .admin-content {
          flex: 1;
          padding: 16px 20px;
          overflow-y: auto;
          background: #f0f0f1;
        }
        .admin-header-bar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 12px;
          padding-bottom: 10px;
          border-bottom: 1px solid #dcdcde;
        }
        .admin-header-left {
          display: flex;
          align-items: center;
          gap: 12px;
        }
        .admin-logo-img {
          width: 38px;
          height: 38px;
          object-fit: contain;
          background: #fff;
          padding: 2px;
          border-radius: 8px;
          border: 1px solid #cbd5e1;
          box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .admin-title {
          font-size: 20px;
          font-weight: 700;
          color: #1e293b;
          display: flex;
          align-items: center;
          gap: 8px;
        }
        .admin-ver {
          background: #e2e8f0;
          color: #475569;
          font-size: 11px;
          padding: 2px 6px;
          border-radius: 4px;
          font-weight: 600;
        }
        .admin-brand-pill {
          background: #fff;
          border: 1px solid #cbd5e1;
          border-radius: 20px;
          padding: 4px 12px;
          font-size: 11px;
          color: #64748b;
          display: flex;
          align-items: center;
          gap: 6px;
          box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .admin-brand-pill img { width: 14px; height: 14px; object-fit: contain; }
        .admin-brand-pill strong { color: #2563eb; }

        /* Nav Tabs */
        .scogs-tabs {
          display: flex;
          border-bottom: 1px solid #c3c4c7;
          margin-bottom: 14px;
          gap: 4px;
        }
        .scogs-tab {
          padding: 7px 14px;
          background: #dcdcde;
          color: #50575e;
          font-size: 13px;
          font-weight: 600;
          border-radius: 4px 4px 0 0;
          text-decoration: none;
          display: flex;
          align-items: center;
          gap: 6px;
        }
        .scogs-tab.active-tab {
          background: #f0f0f1;
          color: #1d2327;
          border: 1px solid #c3c4c7;
          border-bottom: 1px solid #f0f0f1;
          margin-bottom: -1px;
        }
      </style>
    </head>
    <body>
      <!-- WP Admin Top Bar -->
      <div class="wp-admin-bar">
        <div class="wp-bar-left">
          <div class="wp-bar-site">
            <span>MarketPulse Store</span>
            <span class="wp-bar-badge">Live</span>
          </div>
          <span>🛒 WooCommerce</span>
          <span>⚡ Updates (0)</span>
        </div>
        <div class="wp-bar-right">
          <span>Howdy, Administrator</span>
          <span style="width:20px;height:20px;background:#64748b;border-radius:50%;display:inline-block;"></span>
        </div>
      </div>

      <!-- Admin Layout -->
      <div class="admin-layout">
        <!-- Sidebar -->
        <div class="wp-sidebar">
          <div class="wp-menu-item">📊 Dashboard</div>
          <div class="wp-menu-item">📦 Products</div>
          <div class="wp-menu-item active">🛒 WooCommerce</div>
          <div class="wp-submenu-item">Orders <span style="background:#2271b1;color:#fff;border-radius:8px;padding:0 5px;font-size:10px;">3</span></div>
          <div class="wp-submenu-item ${activeSubmenu === 'scogs' ? 'active-sub' : ''}">Profit Analytics</div>
          <div class="wp-submenu-item">Customers</div>
          <div class="wp-submenu-item">Coupons</div>
          <div class="wp-submenu-item">Settings</div>
          <div class="wp-menu-item">📈 Analytics</div>
          <div class="wp-menu-item">⚙️ Settings</div>
        </div>

        <!-- Main Content Viewport -->
        <div class="admin-content">
          ${contentHtml}
        </div>
      </div>
    </body>
    </html>
  `;

  // --- SCREENSHOT DEFINITIONS ---
  const screenshots = [
    // 1. screenshot-1.png: Overview Dashboard & Real-Time KPI Cards + SVG Trendline
    {
      filename: 'screenshot-1.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div class="admin-header-bar">
          <div class="admin-header-left">
            <img class="admin-logo-img" src="${logoDataUri}" alt="Logo" />
            <div class="admin-title">
              MarketPulse Profit Analytics & Financial Intelligence
              <span class="admin-ver">v1.0.0</span>
            </div>
          </div>
          <div class="admin-brand-pill">
            <img src="${logoDataUri}" alt="" /> Powered by <strong>NexForge AI</strong>
          </div>
        </div>

        <div class="scogs-tabs">
          <div class="scogs-tab active-tab">📈 Overview & Charts</div>
          <div class="scogs-tab">💼 Operating Expenses (OpEx)</div>
          <div class="scogs-tab">💳 Payment Gateways & Costs</div>
          <div class="scogs-tab">📦 Suppliers & Bulk Tools</div>
          <div class="scogs-tab">📖 User Guide & FAQs</div>
        </div>

        <!-- 6 KPI Stat Cards -->
        <div style="display:grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 14px;">
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Gross Revenue</div>
            <div style="font-size:18px; font-weight:800; color:#0f172a; margin-top:4px;">$48,650.00</div>
            <div style="font-size:10px; color:#10b981; margin-top:2px; font-weight:600;">↑ +14.2% vs last mo</div>
          </div>
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Wholesale COGS</div>
            <div style="font-size:18px; font-weight:800; color:#ef4444; margin-top:4px;">-$22,140.00</div>
            <div style="font-size:10px; color:#64748b; margin-top:2px;">45.5% of Revenue</div>
          </div>
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Gateway Fees</div>
            <div style="font-size:18px; font-weight:800; color:#f59e0b; margin-top:4px;">-$1,410.85</div>
            <div style="font-size:10px; color:#64748b; margin-top:2px;">Stripe & PayPal Auto</div>
          </div>
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Courier Deficit</div>
            <div style="font-size:18px; font-weight:800; color:#f59e0b; margin-top:4px;">-$640.00</div>
            <div style="font-size:10px; color:#ef4444; margin-top:2px; font-weight:600;">Out-of-Pocket Loss</div>
          </div>
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">OpEx Overheads</div>
            <div style="font-size:18px; font-weight:800; color:#64748b; margin-top:4px;">-$2,408.00</div>
            <div style="font-size:10px; color:#64748b; margin-top:2px;">Ads, Server & Rent</div>
          </div>
          <div style="background:linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border:1.5px solid #10b981; border-radius:8px; padding:12px; box-shadow:0 2px 4px rgba(16,185,129,0.15);">
            <div style="font-size:11px; color:#047857; text-transform:uppercase; font-weight:800;">True Net Profit</div>
            <div style="font-size:19px; font-weight:900; color:#065f46; margin-top:4px;">+$22,051.15</div>
            <div style="display:inline-block; background:#10b981; color:#fff; border-radius:10px; padding:1px 6px; font-size:10px; font-weight:700; margin-top:2px;">45.3% Net Margin</div>
          </div>
        </div>

        <!-- 30-Day Pure SVG Trendline Chart -->
        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:14px; margin-bottom:12px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div>
              <strong style="font-size:14px; color:#0f172a;">30-Day Daily Performance Trend (Zero-JS Pure SVG)</strong>
              <div style="font-size:11px; color:#64748b;">Daily Revenue vs Wholesale Costs vs True Take-Home Profit</div>
            </div>
            <div style="display:flex; gap:12px; font-size:11px; font-weight:600;">
              <span style="color:#2563eb; display:flex; align-items:center; gap:4px;"><span style="width:8px;height:8px;background:#2563eb;border-radius:50%;display:inline-block;"></span> Revenue</span>
              <span style="color:#ef4444; display:flex; align-items:center; gap:4px;"><span style="width:8px;height:8px;background:#ef4444;border-radius:50%;display:inline-block;"></span> Total COGS</span>
              <span style="color:#10b981; display:flex; align-items:center; gap:4px;"><span style="width:8px;height:8px;background:#10b981;border-radius:50%;display:inline-block;"></span> Net Profit</span>
            </div>
          </div>
          <svg viewBox="0 0 1000 120" style="width:100%; height:120px; overflow:visible;">
            <defs>
              <linearGradient id="profitGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#10b981" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
              </linearGradient>
            </defs>
            <path d="M0,80 Q100,50 200,65 T400,40 T600,30 T800,20 T1000,15 L1000,120 L0,120 Z" fill="url(#profitGrad)"/>
            <path d="M0,50 Q100,30 200,45 T400,20 T600,15 T800,10 T1000,5" fill="none" stroke="#2563eb" stroke-width="2.5"/>
            <path d="M0,90 Q100,75 200,85 T400,65 T600,55 T800,45 T1000,40" fill="none" stroke="#ef4444" stroke-width="2"/>
            <path d="M0,80 Q100,50 200,65 T400,40 T600,30 T800,20 T1000,15" fill="none" stroke="#10b981" stroke-width="3"/>
          </svg>
        </div>

        <!-- 2 Column: Left Top Profit Products, Right Loss Notice -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:12px;">
            <strong style="font-size:13px; color:#0f172a;">Top Profit-Generating Products</strong>
            <div style="margin-top:8px; font-size:12px; display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9;">
              <span>Zebra Fashionable Laptop Bag (SKU: ZB-01)</span>
              <span style="font-weight:700; color:#10b981;">+$4,250.00 (45.3%)</span>
            </div>
            <div style="font-size:12px; display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9;">
              <span>Wireless Bluetooth Headphones Pro</span>
              <span style="font-weight:700; color:#10b981;">+$3,120.00 (52.5%)</span>
            </div>
            <div style="font-size:12px; display:flex; justify-content:space-between; padding:4px 0;">
              <span>Ergonomic Mesh Office Chair Deluxe</span>
              <span style="font-weight:700; color:#10b981;">+$2,890.00 (38.2%)</span>
            </div>
          </div>
          <div style="background:#fff1f2; border:1px solid #fecdd3; border-radius:8px; padding:12px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <span style="color:#b91c1c; font-weight:700; font-size:13px;">⚠️ Critical Margin Floor Protection Alert</span>
              <p style="font-size:12px; color:#7f1d1d; margin-top:4px; line-height:1.4;">2 catalog items currently sell below 15% margin floor. After 2.9% Stripe processing and courier delivery deficits, your store is operating at a net loss on these items.</p>
            </div>
            <div style="display:flex; justify-content:flex-end;">
              <span style="background:#ef4444; color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:4px;">Review 2 At-Risk SKUs &rarr;</span>
            </div>
          </div>
        </div>
      `
    },

    // 2. screenshot-2.png: Product Edit Screen (Cost Price, Live Margin & Alert)
    {
      filename: 'screenshot-2.png',
      activeSubmenu: 'products',
      renderHtml: () => `
        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px; max-width:860px; margin:0 auto; box-shadow:0 2px 4px rgba(0,0,0,0.04);">
          <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:14px;">
            <div style="font-size:16px; font-weight:700; color:#0f172a;">Product Data &mdash; Simple product</div>
            <span style="font-size:12px; color:#64748b;">SKU: AC-NET-BRA-01</span>
          </div>

          <div style="display:grid; grid-template-columns: 160px 1fr; gap:16px;">
            <div style="background:#f8fafc; border-right:1px solid #e2e8f0; padding-right:12px; font-size:13px; color:#475569; display:flex; flex-direction:column; gap:8px;">
              <div style="font-weight:700; color:#2563eb; background:#eff6ff; padding:6px 10px; border-radius:4px;">⚙️ General (Pricing)</div>
              <div style="padding:4px 10px;">📦 Inventory</div>
              <div style="padding:4px 10px;">🚚 Shipping</div>
              <div style="padding:4px 10px;">🔗 Linked Products</div>
              <div style="padding:4px 10px;">⚡ Advanced</div>
            </div>

            <div style="display:flex; flex-direction:column; gap:12px;">
              <div style="display:flex; align-items:center;">
                <label style="width:170px; font-size:13px; font-weight:600; color:#334155;">Regular price ($)</label>
                <input type="text" value="66.00" style="width:140px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;" readonly />
              </div>
              <div style="display:flex; align-items:center;">
                <label style="width:170px; font-size:13px; font-weight:600; color:#334155;">Sale price ($)</label>
                <input type="text" value="60.00" style="width:140px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;" readonly />
              </div>

              <!-- Cost Price with Red Warning Border and Inline Chip -->
              <div style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                <label style="width:170px; font-size:13px; font-weight:700; color:#0f172a;">Cost Price (COGS) ($)</label>
                <input type="text" value="55.00" style="width:140px; padding:6px 10px; border:2px solid #ef4444; background:#fff1f2; color:#991b1b; font-weight:700; border-radius:4px; font-size:13px; box-shadow:0 0 0 3px rgba(239,68,68,0.2);" readonly />
                <span style="display:inline-flex; align-items:center; gap:5px; background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:4px 10px; border-radius:14px; font-size:11px; font-weight:700;">
                  <span style="width:6px;height:6px;background:#ef4444;border-radius:50%;display:inline-block;"></span> ⚠️ 8.3% Critical Margin
                </span>
              </div>

              <!-- Live Margin Preview Row -->
              <div style="display:flex; align-items:center; margin-top:-2px;">
                <label style="width:170px; font-size:13px; color:#64748b;">Est. Gross Margin</label>
                <div style="display:flex; align-items:center; gap:10px;">
                  <span style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; font-size:11px; font-weight:700; padding:3px 8px; border-radius:10px;">$5.00 (8.3%)</span>
                  <span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; color:#2563eb; font-weight:600; text-decoration:none;">
                    <img src="${logoDataUri}" style="width:13px;height:13px;" alt="" /> Boost margin with MarketPulse &rarr;
                  </span>
                </div>
              </div>

              <!-- Dangerously Low Margin Alert Box -->
              <div style="background:#fff1f2; border:1px solid #fecdd3; border-left:4px solid #ef4444; border-radius:6px; padding:12px 14px; display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div>
                  <div style="font-size:12.5px; font-weight:700; color:#991b1b;">⚠️ Dangerously Low Margin Alert (8.3%):</div>
                  <div style="font-size:11.5px; color:#7f1d1d; margin-top:2px;">After payment gateway fees (2.9% + $0.30) and courier deficits, your net take-home profit is nearly zero.</div>
                </div>
                <div style="background:#2563eb; color:#fff; font-size:11.5px; font-weight:700; padding:6px 12px; border-radius:4px; white-space:nowrap; display:flex; align-items:center; gap:6px;">
                  <img src="${logoDataUri}" style="width:14px;height:14px;filter:brightness(0) invert(1);" alt="" /> Protect & Reprice with MarketPulse AI &rarr;
                </div>
              </div>

              <div style="display:flex; align-items:center;">
                <label style="width:170px; font-size:13px; color:#475569;">Supplier / Vendor</label>
                <input type="text" value="Acme Wholesale Apparel Ltd." style="width:280px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;" readonly />
              </div>
            </div>
          </div>
        </div>
      `
    },

    // 3. screenshot-3.png: Order Financial Waterfall Breakdown
    {
      filename: 'screenshot-3.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <div>
            <span style="font-size:20px; font-weight:700; color:#0f172a;">Order #1042 details</span>
            <span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px; margin-left:8px;">Completed</span>
          </div>
          <span style="font-size:12px; color:#64748b;">Payment via Stripe Credit Card. Customer IP: 192.168.1.1</span>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 320px; gap:16px;">
          <!-- Left: Order Items with Line-Item Snapshots -->
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
            <strong style="font-size:14px; color:#0f172a;">Purchased Items & Line-Item Cost Snapshots</strong>
            <table style="width:100%; border-collapse:collapse; margin-top:12px; font-size:12.5px;">
              <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b;">
                  <th style="padding:8px 10px;">Item</th>
                  <th style="padding:8px 10px;">Cost (COGS)</th>
                  <th style="padding:8px 10px;">Qty</th>
                  <th style="padding:8px 10px;">Total</th>
                  <th style="padding:8px 10px;">Profit</th>
                </tr>
              </thead>
              <tbody>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:10px;">
                    <strong>Zebra Fashionable Laptop Bag</strong><br>
                    <span style="font-size:11px; color:#64748b;">SKU: ZB-01 • Supplier: Acme Wholesale</span>
                  </td>
                  <td style="padding:10px; color:#ef4444; font-weight:600;">$45.00</td>
                  <td style="padding:10px;">2</td>
                  <td style="padding:10px; font-weight:700;">$130.00</td>
                  <td style="padding:10px;"><span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 6px; border-radius:6px;">+$40.00 (30.8%)</span></td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:10px;">
                    <strong>Wireless Bluetooth Headphones Pro</strong><br>
                    <span style="font-size:11px; color:#64748b;">SKU: HP-PRO • Supplier: SoundMax Audio</span>
                  </td>
                  <td style="padding:10px; color:#ef4444; font-weight:600;">$28.00</td>
                  <td style="padding:10px;">1</td>
                  <td style="padding:10px; font-weight:700;">$59.00</td>
                  <td style="padding:10px;"><span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 6px; border-radius:6px;">+$31.00 (52.5%)</span></td>
                </tr>
              </tbody>
            </table>

            <!-- Shipping Deficit Reconciliation Box -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-top:16px;">
              <strong style="font-size:12.5px; color:#1e293b;">🚚 Shipping & Delivery Courier Reconciliation</strong>
              <div style="font-size:12px; color:#475569; margin-top:4px;">Customer Paid Shipping: <strong>$5.00 (Flat Rate)</strong> | Actual Courier Invoice: <strong>$8.50 (FedEx Ground)</strong></div>
              <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; margin-top:6px; display:inline-block;">
                ⚠️ Courier Shipping Deficit: -$3.50 Out-of-Pocket Loss
              </div>
            </div>
          </div>

          <!-- Right Sidebar: Financial Waterfall Meta Box -->
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
            <div style="font-size:14px; font-weight:700; color:#0f172a; border-bottom:1px solid #e2e8f0; padding-bottom:8px; margin-bottom:10px;">
              Financial Waterfall Breakdown
            </div>
            <div style="display:flex; flex-direction:column; gap:6px; font-size:12.5px;">
              <div style="display:flex; justify-content:space-between;"><span>Gross Revenue</span><strong style="color:#0f172a;">$189.00</strong></div>
              <div style="display:flex; justify-content:space-between;"><span>Discounts & Coupons</span><span style="color:#64748b;">$0.00</span></div>
              <div style="display:flex; justify-content:space-between;"><span>Total Wholesale COGS</span><strong style="color:#ef4444;">-$118.00</strong></div>
              <div style="border-bottom:1px solid #f1f5f9; padding-bottom:4px; display:flex; justify-content:space-between;"><span>Gross Markup Profit</span><strong style="color:#10b981;">+$71.00</strong></div>
              <div style="display:flex; justify-content:space-between; margin-top:4px;"><span>Gateway Fee (Stripe 2.9% + $0.30)</span><strong style="color:#f59e0b;">-$5.78</strong></div>
              <div style="display:flex; justify-content:space-between;"><span>Courier Shipping Deficit</span><strong style="color:#ef4444;">-$3.50</strong></div>
            </div>

            <!-- Big True Net Profit Result -->
            <div style="background:linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border:1.5px solid #10b981; border-radius:8px; padding:12px; margin-top:14px; text-align:center;">
              <div style="font-size:11px; font-weight:700; color:#047857; text-transform:uppercase;">True Take-Home Net Profit</div>
              <div style="font-size:22px; font-weight:900; color:#065f46; margin:4px 0;">+$61.72</div>
              <span style="background:#10b981; color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">32.7% Net Margin</span>
            </div>

            <div style="font-size:11px; color:#64748b; line-height:1.4; margin-top:12px; text-align:center;">
              🔒 Line-item costs permanently snapshotted at checkout. Changing future product prices will not alter this order.
            </div>
          </div>
        </div>
      `
    },

    // 4. screenshot-4.png: Operating Expenses (OpEx) Overhead Ledger
    {
      filename: 'screenshot-4.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div class="admin-header-bar">
          <div class="admin-header-left">
            <img class="admin-logo-img" src="${logoDataUri}" alt="Logo" />
            <div class="admin-title">
              Operating Expenses (OpEx) Overhead Ledger
              <span class="admin-ver">v1.0.0</span>
            </div>
          </div>
        </div>

        <div class="scogs-tabs">
          <div class="scogs-tab">📈 Overview & Charts</div>
          <div class="scogs-tab active-tab">💼 Operating Expenses (OpEx)</div>
          <div class="scogs-tab">💳 Payment Gateways & Costs</div>
          <div class="scogs-tab">📦 Suppliers & Bulk Tools</div>
          <div class="scogs-tab">📖 User Guide & FAQs</div>
        </div>

        <div style="display:grid; grid-template-columns: 320px 1fr; gap:16px;">
          <!-- Left: Add Expense Form -->
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
            <strong style="font-size:14px; color:#0f172a;">+ Add Operating Expense</strong>
            <p style="font-size:11.5px; color:#64748b; margin-top:4px;">Record overhead costs like Meta ads, server hosting, staff salaries or rent to deduct from monthly gross profit.</p>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:12px; font-size:12px;">
              <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Expense Name</label>
                <input type="text" value="Meta / Facebook Retargeting Ads" style="width:100%; padding:6px; border:1px solid #cbd5e1; border-radius:4px;" readonly />
              </div>
              <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                <div>
                  <label style="font-weight:600; display:block; margin-bottom:4px;">Amount ($)</label>
                  <input type="text" value="1250.00" style="width:100%; padding:6px; border:1px solid #cbd5e1; border-radius:4px;" readonly />
                </div>
                <div>
                  <label style="font-weight:600; display:block; margin-bottom:4px;">Category</label>
                  <select style="width:100%; padding:6px; border:1px solid #cbd5e1; border-radius:4px;"><option>Marketing & Ads</option></select>
                </div>
              </div>
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px;">
                <label style="font-weight:600; display:flex; align-items:center; gap:6px;">
                  <input type="checkbox" checked readonly /> Monthly Recurring Expense
                </label>
              </div>
              <button style="background:#2563eb; color:#fff; border:none; padding:8px; border-radius:4px; font-weight:700; cursor:pointer;">+ Record Expense Entry</button>
            </div>
          </div>

          <!-- Right: Active Expenses Table -->
          <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
              <strong style="font-size:14px; color:#0f172a;">Active Business Overheads Ledger</strong>
              <span style="font-size:12px; background:#eff6ff; color:#2563eb; font-weight:700; padding:3px 8px; border-radius:6px;">Total OpEx: -$2,408.00/mo</span>
            </div>
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
              <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b;">
                  <th style="padding:8px;">Expense Item</th>
                  <th style="padding:8px;">Category</th>
                  <th style="padding:8px;">Frequency</th>
                  <th style="padding:8px; text-align:right;">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:8px;"><strong>Meta / Facebook Retargeting Ads</strong></td>
                  <td style="padding:8px;"><span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-size:10px;">Marketing</span></td>
                  <td style="padding:8px;">Monthly Recurring</td>
                  <td style="padding:8px; text-align:right; font-weight:700; color:#ef4444;">-$1,250.00</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:8px;"><strong>AWS & Hostinger Cloud Server</strong></td>
                  <td style="padding:8px;"><span style="background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:4px; font-size:10px;">Hosting</span></td>
                  <td style="padding:8px;">Monthly Recurring</td>
                  <td style="padding:8px; text-align:right; font-weight:700; color:#ef4444;">-$145.00</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:8px;"><strong>Packaging Materials & Bubble Wrap</strong></td>
                  <td style="padding:8px;"><span style="background:#fef3c7; color:#b45309; padding:2px 6px; border-radius:4px; font-size:10px;">Operations</span></td>
                  <td style="padding:8px;">Monthly Recurring</td>
                  <td style="padding:8px; text-align:right; font-weight:700; color:#ef4444;">-$213.00</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:8px;"><strong>Customer Support Specialist Salary</strong></td>
                  <td style="padding:8px;"><span style="background:#f3e8ff; color:#7e22ce; padding:2px 6px; border-radius:4px; font-size:10px;">Payroll</span></td>
                  <td style="padding:8px;">Monthly Recurring</td>
                  <td style="padding:8px; text-align:right; font-weight:700; color:#ef4444;">-$800.00</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      `
    },

    // 5. screenshot-5.png: Payment Gateway Fee Rules Auto-Detection
    {
      filename: 'screenshot-5.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div class="admin-header-bar">
          <div class="admin-header-left">
            <img class="admin-logo-img" src="${logoDataUri}" alt="Logo" />
            <div class="admin-title">
              Payment Gateway Fee Rules & Courier Cost Setup
              <span class="admin-ver">v1.0.0</span>
            </div>
          </div>
        </div>

        <div class="scogs-tabs">
          <div class="scogs-tab">📈 Overview & Charts</div>
          <div class="scogs-tab">💼 Operating Expenses (OpEx)</div>
          <div class="scogs-tab active-tab">💳 Payment Gateways & Costs</div>
          <div class="scogs-tab">📦 Suppliers & Bulk Tools</div>
          <div class="scogs-tab">📖 User Guide & FAQs</div>
        </div>

        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px; max-width:880px;">
          <strong style="font-size:14px; color:#0f172a;">Zero-Config Payment Gateway Processing Fee Auto-Detection</strong>
          <p style="font-size:12px; color:#64748b; margin-top:4px;">When orders are placed, the plugin automatically detects the payment gateway and deducts transaction fees to reveal true net profit.</p>

          <table style="width:100%; border-collapse:collapse; margin-top:12px; font-size:12.5px;">
            <thead>
              <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b;">
                <th style="padding:8px 12px;">Payment Method</th>
                <th style="padding:8px 12px;">Detection Status</th>
                <th style="padding:8px 12px;">Percentage Fee (%)</th>
                <th style="padding:8px 12px;">Fixed Fee per Order</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 12px;"><strong>Stripe (Credit / Debit Cards)</strong></td>
                <td style="padding:10px 12px;"><span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">✓ Auto-Configured</span></td>
                <td style="padding:10px 12px;"><input type="text" value="2.90" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /> %</td>
                <td style="padding:10px 12px;">$ <input type="text" value="0.30" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /></td>
              </tr>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 12px;"><strong>PayPal Payments Standard</strong></td>
                <td style="padding:10px 12px;"><span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">✓ Auto-Configured</span></td>
                <td style="padding:10px 12px;"><input type="text" value="3.49" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /> %</td>
                <td style="padding:10px 12px;">$ <input type="text" value="0.49" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /></td>
              </tr>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 12px;"><strong>bKash Merchant Payment (Bangladesh)</strong></td>
                <td style="padding:10px 12px;"><span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">✓ Auto-Configured</span></td>
                <td style="padding:10px 12px;"><input type="text" value="1.50" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /> %</td>
                <td style="padding:10px 12px;">৳ <input type="text" value="0.00" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /></td>
              </tr>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 12px;"><strong>Cash on Delivery (COD)</strong></td>
                <td style="padding:10px 12px;"><span style="background:#f1f5f9; color:#475569; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">0% Gateway Fee</span></td>
                <td style="padding:10px 12px;"><input type="text" value="0.00" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /> %</td>
                <td style="padding:10px 12px;">$ <input type="text" value="0.00" style="width:65px; padding:4px 6px; border:1px solid #cbd5e1; border-radius:4px; text-align:right;" readonly /></td>
              </tr>
            </tbody>
          </table>
        </div>
      `
    },

    // 6. screenshot-6.png: Suppliers & Bulk Restocking Sheet
    {
      filename: 'screenshot-6.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div class="admin-header-bar">
          <div class="admin-header-left">
            <img class="admin-logo-img" src="${logoDataUri}" alt="Logo" />
            <div class="admin-title">
              Suppliers Directory & Bulk Restocking Tools
              <span class="admin-ver">v1.0.0</span>
            </div>
          </div>
        </div>

        <div class="scogs-tabs">
          <div class="scogs-tab">📈 Overview & Charts</div>
          <div class="scogs-tab">💼 Operating Expenses (OpEx)</div>
          <div class="scogs-tab">💳 Payment Gateways & Costs</div>
          <div class="scogs-tab active-tab">📦 Suppliers & Bulk Tools</div>
          <div class="scogs-tab">📖 User Guide & FAQs</div>
        </div>

        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
              <strong style="font-size:14px; color:#0f172a;">Active Suppliers & Profit Margin Analysis</strong>
              <div style="font-size:12px; color:#64748b;">Compare wholesale distributor pricing, margins and generate inventory reorder sheets.</div>
            </div>
            <button style="background:#2563eb; color:#fff; border:none; padding:6px 12px; border-radius:4px; font-size:12px; font-weight:700;">+ Export Restocking Sheet (CSV)</button>
          </div>

          <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
              <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left; color:#64748b;">
                <th style="padding:8px 10px;">Supplier / Vendor</th>
                <th style="padding:8px 10px;">Linked SKUs</th>
                <th style="padding:8px 10px;">Total Stock</th>
                <th style="padding:8px 10px;">Avg Wholesale Margin</th>
                <th style="padding:8px 10px;">Restock Status</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px;"><strong>Acme Wholesale Apparel Ltd.</strong></td>
                <td style="padding:10px;">14 Products</td>
                <td style="padding:10px;">420 units</td>
                <td style="padding:10px;"><span style="color:#10b981; font-weight:700;">46.2% Margin</span></td>
                <td style="padding:10px;"><span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; padding:2px 6px; border-radius:6px;">Healthy Stock</span></td>
              </tr>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px;"><strong>SoundMax Audio Imports</strong></td>
                <td style="padding:10px;">8 Products</td>
                <td style="padding:10px;">85 units</td>
                <td style="padding:10px;"><span style="color:#10b981; font-weight:700;">52.1% Margin</span></td>
                <td style="padding:10px;"><span style="background:#fef3c7; color:#b45309; font-size:11px; font-weight:700; padding:2px 6px; border-radius:6px;">Reorder Soon</span></td>
              </tr>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px;"><strong>Apex Leather & Bags Co.</strong></td>
                <td style="padding:10px;">6 Products</td>
                <td style="padding:10px;">18 units</td>
                <td style="padding:10px;"><span style="color:#f59e0b; font-weight:700;">28.5% Margin</span></td>
                <td style="padding:10px;"><span style="background:#fee2e2; color:#b91c1c; font-size:11px; font-weight:700; padding:2px 6px; border-radius:6px;">Low Stock (18 left)</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      `
    },

    // 7. screenshot-7.png: Catalog Profit Leak Radar & Dynamic Repricing Lift
    {
      filename: 'screenshot-7.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:12px; padding:18px 24px; position:relative; overflow:hidden;">
          <div style="position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg, #ef4444 0%, #f59e0b 50%, #10b981 100%);"></div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="display:inline-flex; align-items:center; gap:8px; background:#fef2f2; border:1px solid #fecaca; padding:4px 10px; border-radius:20px;">
              <img src="${logoDataUri}" style="width:15px;height:15px;" alt="" />
              <span style="width:6px;height:6px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
              <strong style="color:#991b1b; font-size:11px; letter-spacing:0.04em;">LIVE STORE AUDIT: ESTIMATED PROFIT LEAK</strong>
            </div>
            <span style="font-size:11px; color:#64748b;">Based on standard eCommerce static vs dynamic pricing benchmarks</span>
          </div>

          <div style="display:grid; grid-template-columns: 1fr 260px; gap:20px; align-items:center; padding-bottom:16px; border-bottom:1px solid #f1f5f9;">
            <div>
              <h2 style="font-size:20px; font-weight:700; color:#0f172a; margin-bottom:6px;">
                You Left an Estimated <span style="color:#dc2626; font-weight:800;">-$426.72</span> on the Table This Period
              </h2>
              <p style="font-size:12.5px; color:#475569; line-height:1.45;">
                By keeping product prices static while rivals adjust prices 24/7, your store routinely misses high-margin surges during competitor stockouts and loses checkout conversions to competitor undercutting.
              </p>
            </div>
            <div style="background:#fff1f2; border:1px solid #fecdd3; border-radius:8px; padding:12px; text-align:center;">
              <span style="font-size:10px; color:#64748b; font-weight:700; text-transform:uppercase;">Estimated Uncaptured Margin</span>
              <div style="font-size:22px; font-weight:900; color:#dc2626; margin:4px 0;">-$426.72</div>
              <span style="font-size:10px; color:#991b1b;">Pure cash lost to manual static pricing</span>
            </div>
          </div>

          <!-- Dynamic Lift Simulation Bars -->
          <div style="margin:14px 0;">
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
              <span style="color:#475569;">Your Actual Net Profit:</span>
              <strong style="color:#0f172a;">$3,556.00</strong>
            </div>
            <div style="height:20px; background:#334155; border-radius:4px; display:flex; overflow:hidden; font-size:10px; color:#fff; font-weight:700; text-align:center; line-height:20px;">
              <div style="width:89%; background:#334155;">89% Captured</div>
              <div style="width:11%; background:#dc2626;">-11% Leaking</div>
            </div>

            <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:10px; margin-bottom:4px;">
              <span style="color:#059669; font-weight:700;">Potential Profit with MarketPulse AI Repricer:</span>
              <strong style="color:#059669;">$3,982.72 (+$426.72 more cash in bank)</strong>
            </div>
            <div style="height:20px; background:#10b981; border-radius:4px; font-size:10px; color:#fff; font-weight:700; text-align:center; line-height:20px;">
              100% Fully Optimized (+12% repricing margin lift)
            </div>
          </div>

          <!-- 3-Column Battle Card -->
          <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-top:14px; font-size:12px;">
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
              <span style="font-weight:700; color:#475569;">1. The Competitor Stockout Trap</span>
              <p style="color:#64748b; font-size:11px; margin-top:2px;">When rivals run out of stock, AI safely lifts prices by +15% with zero lost sales.</p>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
              <span style="font-weight:700; color:#475569;">2. Wasted Ad Spend on Undercuts</span>
              <p style="color:#64748b; font-size:11px; margin-top:2px;">When rivals drop prices by $1, your ad ROAS drops 40%. AI matches prices instantly.</p>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
              <span style="font-weight:700; color:#475569;">3. Zero Profit Margin Floor Guard</span>
              <p style="color:#64748b; font-size:11px; margin-top:2px;">Automated AI guardrails strictly protect your COGS floor so you never sell at a loss.</p>
            </div>
          </div>
        </div>
      `
    },

    // 8. screenshot-8.png: In-Dashboard User Guide & Knowledgebase
    {
      filename: 'screenshot-8.png',
      activeSubmenu: 'scogs',
      renderHtml: () => `
        <div class="admin-header-bar">
          <div class="admin-header-left">
            <img class="admin-logo-img" src="${logoDataUri}" alt="Logo" />
            <div class="admin-title">
              Official Documentation & User Knowledgebase
              <span class="admin-ver">v1.0.0</span>
            </div>
          </div>
        </div>

        <div class="scogs-tabs">
          <div class="scogs-tab">📈 Overview & Charts</div>
          <div class="scogs-tab">💼 Operating Expenses (OpEx)</div>
          <div class="scogs-tab">💳 Payment Gateways & Costs</div>
          <div class="scogs-tab">📦 Suppliers & Bulk Tools</div>
          <div class="scogs-tab active-tab">📖 User Guide & FAQs</div>
        </div>

        <!-- 60-Second Quick Start 3 Steps -->
        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:14px; margin-bottom:12px;">
          <strong style="font-size:14px; color:#0f172a;">⏱️ 60-Second Quick Start Guide</strong>
          <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-top:10px; font-size:12px;">
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
              <div style="width:24px;height:24px;background:#2563eb;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;margin-bottom:6px;">1</div>
              <strong style="color:#1e293b;">Enter Product Costs (COGS)</strong>
              <p style="color:#64748b; font-size:11.5px; margin-top:4px;">Edit products and enter wholesale purchase cost in the Cost Price field.</p>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
              <div style="width:24px;height:24px;background:#2563eb;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;margin-bottom:6px;">2</div>
              <strong style="color:#1e293b;">Automation Takes Over</strong>
              <p style="color:#64748b; font-size:11.5px; margin-top:4px;">Orders automatically snapshot costs, gateway fees, and courier losses.</p>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
              <div style="width:24px;height:24px;background:#2563eb;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;margin-bottom:6px;">3</div>
              <strong style="color:#1e293b;">Track True Net Profit</strong>
              <p style="color:#64748b; font-size:11.5px; margin-top:4px;">Review real take-home profit on interactive charts and export P&L reports.</p>
            </div>
          </div>
        </div>

        <!-- 5 Formulas Demystified -->
        <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:14px; font-size:12px;">
          <strong style="font-size:14px; color:#0f172a;">🧮 5 Accounting Formulas Demystified</strong>
          <div style="display:flex; flex-direction:column; gap:6px; margin-top:10px;">
            <div style="background:#f8fafc; padding:6px 10px; border-radius:4px; display:flex; justify-content:space-between;">
              <span><strong>1. Gross Revenue</strong> = Item Retail Price × Quantity Sold</span>
              <span style="color:#64748b;">Total money received</span>
            </div>
            <div style="background:#f8fafc; padding:6px 10px; border-radius:4px; display:flex; justify-content:space-between;">
              <span><strong>2. Total COGS</strong> = Wholesale Unit Cost × Quantity Sold</span>
              <span style="color:#ef4444;">Supplier purchase cost</span>
            </div>
            <div style="background:#f8fafc; padding:6px 10px; border-radius:4px; display:flex; justify-content:space-between;">
              <span><strong>3. Gross Profit</strong> = Gross Revenue &minus; Discounts &minus; Total COGS</span>
              <span style="color:#10b981;">Markup profit</span>
            </div>
            <div style="background:#f8fafc; padding:6px 10px; border-radius:4px; display:flex; justify-content:space-between;">
              <span><strong>4. Courier Deficit</strong> = Actual Courier Fee &minus; Shipping Charged to Customer</span>
              <span style="color:#ef4444;">Out-of-pocket loss</span>
            </div>
            <div style="background:#ecfdf5; border:1px solid #10b981; padding:6px 10px; border-radius:4px; display:flex; justify-content:space-between;">
              <span><strong style="color:#065f46;">5. True Net Profit</strong> = Gross Profit &minus; Gateway Fees &minus; Courier Deficit &minus; OpEx</span>
              <strong style="color:#047857;">Actual Take-Home Cash in Bank</strong>
            </div>
          </div>
        </div>
      `
    }
  ];

  for (const s of screenshots) {
    console.log(`Rendering ${s.filename}...`);
    const page = await browser.newPage({ viewport: { width: 1280, height: 720 } });
    const fullHtml = wpWrapper(
      'WooCommerce Profit Analytics &bull; WordPress',
      s.activeSubmenu,
      s.renderHtml()
    );
    await page.setContent(fullHtml, { waitUntil: 'load' });
    await page.screenshot({ path: path.join(targetDir, s.filename) });
    await page.close();
  }

  console.log('All 8 high-res WordPress.org screenshots rendered successfully!');
  await browser.close();
}

generateScreenshots().catch(console.error);

