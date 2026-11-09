# MarketPulse COGS & Profit for WooCommerce

[![GPLv2 License](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%20--%209.4+-96588a.svg)](https://woocommerce.com)
[![HPOS Ready](https://img.shields.io/badge/HPOS-100%25%20Compatible-brightgreen.svg)](https://developer.woocommerce.com/docs/hpos-order-storage/)
[![PHP](https://img.shields.io/badge/PHP-7.4%20--%208.3+-777bb4.svg)](https://php.net)

**A clean, zero-bloat Cost of Goods Sold (COGS) and true net profit margin tracker for WooCommerce.**

Revenue is vanity; **profit is sanity**. Most Cost of Goods plugins for WooCommerce charge $79 to $199/year or bundle bloated ERP systems that drag down database performance. **Simple Cost of Goods** does one job with mathematical accuracy, elegant UX, and lightning-fast speed.

---

## ✨ Features

- **Unified Profit Analytics Hub (`WooCommerce > MarketPulse Profit Analytics`):** Complete executive overview with 6 core financial KPIs, date filtering (Today, Yesterday, 7D, This Month, Last Month, Custom), and product profit leaderboards.
- **Interactive Daily Trend SVG Chart:** Pure SVG daily performance chart visualizing Gross Revenue vs. Total COGS vs. True Net Profit with zero external JavaScript dependencies.
- **Payment Gateway Fee Auto-Detection:** Automatically detect transaction fees across payment processors (bKash, Nagad, Rocket, SSLCommerz, Stripe, PayPal, etc.) and deduct them from gross profit.
- **Operating Expense Manager (Overhead Ledger):** Record non-product operational expenses (Meta/Google Ads, domain/hosting, salaries, office rent, packaging) to reveal true business take-home profit.
- **Actual Shipping Courier Cost & Deficit Tracking:** Record actual courier invoices (Pathao, Steadfast, RedX, DHL, FedEx) inside orders to automatically calculate out-of-pocket shipping deficit.
- **Product Cost Price Fields:** Seamlessly add wholesale unit costs to Simple and Variable products with real-time live margin calculation.
- **Order Profit Snapshotting:** Automatically locks in the unit cost at checkout (`_cogs_item_cost`) so future supplier price fluctuations never distort historical sales analytics.
- **Visual Color-Coded Badges:**
  - 🟢 **Green (30%+):** Healthy, high-margin orders.
  - 🟡 **Amber (15% - 30%):** Standard margin orders.
  - 🔴 **Red (<15% / Loss):** Low-margin or loss-making transactions.
- **Quick Edit & Bulk Edit:** Adjust product wholesale costs and assign suppliers in seconds without leaving the WooCommerce Products list.
- **Supplier Directory & Filtering:** Associate products with vendors and filter catalog items by supplier with one click.
- **Native WooCommerce CSV Import & Export:** Bulk upload and download COGS and Suppliers via native WooCommerce product CSV tools.
- **Executive Dashboard Widget:** Real-time Today's and This Month's Net Profit summary cached intelligently to ensure zero database bottlenecks.
- **100% HPOS Ready:** Full native support for WooCommerce High-Performance Order Storage (custom order tables) and legacy post-meta.
- **Zero Frontend Overhead:** Less than 50 KB in total size. Zero scripts loaded on customer-facing pages.

---

## 🛠️ Installation

1. Clone or download this repository into your WordPress plugin directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/NexForge-AI/marketpulse-cogs-profit.git
   ```
2. Activate the plugin via **WP Admin &rarr; Plugins**.
3. Edit any product and fill in the **Cost Price (COGS)** field.
4. Access your central profit hub under **WooCommerce &rarr; MarketPulse Profit Analytics**.
5. View real-time profit tracking under **WooCommerce &rarr; Orders** and your **WordPress Dashboard**.

---

## ⚙️ Configuration

- **Profit Analytics Hub:** Navigate to `WooCommerce > MarketPulse Profit Analytics` to view charts, record operating expenses, and customize gateway fee deduction rules.
- **Settings:** Head over to `WooCommerce > Settings > Products > Cost of Goods (COGS)`:
  - **Include Shipping in Profit:** Toggle whether customer-paid shipping is factored into order net revenue.
  - **Include Tax in Profit:** Toggle whether customer-paid taxes are factored into order net revenue.

---

## 🔒 Performance & Privacy

- **No External Cloud Calls:** Operates 100% locally on your WordPress server.
- **Zero Database Bloat:** Stores costs cleanly inside standard WooCommerce meta and snapshots them per order line item. Operating expenses use a single lightweight indexed table (`wp_scogs_expenses`).
- **Smart Transients:** Dashboard profit queries are cached for 10 minutes, avoiding repeated heavy database aggregate queries.

---

## 👨‍💻 Developed by NexForge AI

Crafted with ❤️ by **[NexForge AI](https://nexforgehq.com)**. **MarketPulse** (https://usemarketpulse.com) is our flagship brand and product suite dedicated to automated profit tracking, competitor price intelligence, and dynamic margin optimization for modern e-commerce merchants.

---

## 📄 License

This plugin is open-source software licensed under the **[GNU General Public License v2 or later (GPL-2.0-or-later)](https://www.gnu.org/licenses/gpl-2.0.html)**.
