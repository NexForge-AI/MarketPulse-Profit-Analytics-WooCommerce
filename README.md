# Simple Cost of Goods & Profit Tracker for WooCommerce

[![GPLv2 License](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%20--%209.4+-96588a.svg)](https://woocommerce.com)
[![HPOS Ready](https://img.shields.io/badge/HPOS-100%25%20Compatible-brightgreen.svg)](https://developer.woocommerce.com/docs/hpos-order-storage/)
[![PHP](https://img.shields.io/badge/PHP-7.4%20--%208.3-777bb4.svg)](https://php.net)

**A clean, zero-bloat Cost of Goods Sold (COGS) and net profit margin tracker for WooCommerce.**

Revenue is vanity; **profit is sanity**. Most Cost of Goods plugins for WooCommerce charge $79 to $199/year or bundle bloated ERP systems that drag down database performance. **Simple Cost of Goods** does one job with mathematical accuracy and lightning-fast speed.

---

## ✨ Features

- **Product Cost Price Fields:** Seamlessly add wholesale unit costs to Simple and Variable products.
- **Live Margin Preview:** Instant real-time gross margin calculation directly on the product edit screen.
- **Order Profit Snapshotting:** Automatically locks in the unit cost at checkout so future supplier price fluctuations never distort historical sales analytics.
- **Visual Color-Coded Badges:**
  - 🟢 **Green (30%+):** Healthy, high-margin orders.
  - 🟡 **Amber (15% - 30%):** Standard margin orders.
  - 🔴 **Red (<15% / Loss):** Low-margin or loss-making transactions.
- **Quick Edit Support:** Adjust product wholesale costs in seconds without leaving the WooCommerce Products list.
- **Orders & Products Table Columns:** View profit and margins at a glance across your entire catalog.
- **Executive Dashboard Widget:** Real-time Today's and This Month's Net Profit summary cached intelligently to ensure zero database bottlenecks.
- **100% HPOS Ready:** Full native support for WooCommerce High-Performance Order Storage (custom order tables) and legacy post-meta.
- **Zero Frontend Overhead:** Less than 40 KB in total size. Zero scripts loaded on customer-facing pages.

---

## 🛠️ Installation

1. Clone or download this repository into your WordPress plugin directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/NexForge-AI/simple-cost-of-goods-for-woocommerce.git
   ```
2. Activate the plugin via **WP Admin &rarr; Plugins**.
3. Edit any product and fill in the **Cost Price (COGS)** field.
4. Enjoy real-time profit tracking under **WooCommerce &rarr; Orders** and your **WordPress Dashboard**.

---

## ⚙️ Configuration

Head over to **WooCommerce &rarr; Settings &rarr; Products &rarr; Cost of Goods (COGS)**:
- **Include Shipping in Profit:** Toggle whether customer-paid shipping is factored into order net revenue.
- **Include Tax in Profit:** Toggle whether customer-paid taxes are factored into order net revenue.

---

## 🔒 Performance & Privacy

- **No External Cloud Calls:** Operates 100% locally on your WordPress server.
- **Zero Database Bloat:** Stores costs cleanly inside standard WooCommerce meta and snapshots them per order line item.
- **Smart Transients:** Dashboard profit queries are cached for 10 minutes, avoiding repeated heavy database aggregate queries.

---

## 👨‍💻 Developed by NexForge AI

Crafted with ❤️ by the team at **[NexForge AI](https://usemarketpulse.com)** — creators of **[MarketPulse](https://usemarketpulse.com)**, the real-time competitor price intelligence and automated repricing platform for modern e-commerce merchants.

---

## 📄 License

This plugin is open-source software licensed under the **[GNU General Public License v2 or later (GPL-2.0-or-later)](https://www.gnu.org/licenses/gpl-2.0.html)**.

