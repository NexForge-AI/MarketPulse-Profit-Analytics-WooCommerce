=== Simple Cost of Goods & Profit Tracker for WooCommerce ===
Contributors: nexforge, usemarketpulse
Donate link: https://usemarketpulse.com
Tags: cost of goods, cogs, profit, profit margin, profit tracker, woocommerce profit, net profit, gateway fee, expense tracker
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
WC requires at least: 7.0
WC tested up to: 9.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, high-performance Cost of Goods Sold (COGS) and true net profit margin tracker for WooCommerce. Track real profit per product and order with operating expenses, gateway fee auto-detection, interactive SVG charts, and 100% HPOS compatibility.

== Description ==

**Track your actual net profits and profit margins in WooCommerce — without the bloat, without high annual fees, and without slowing down your store.**

WooCommerce tells you how much revenue you made, but revenue isn't what goes into your bank account. **Profit is.**

Most financial plugins charge $79 to $199 every single year, or load hundreds of kilobytes of complex accounting scripts that slow down your store. **Simple Cost of Goods & Profit Tracker by NexForge AI** is built on a clean philosophy: **Zero bloat, zero sidebar clutter, and 100% mathematical accuracy.**

### 🌟 Key Features

* **Unified Profit Analytics Hub (`WooCommerce > MarketPulse Profit Analytics`):** Consolidate all financial reporting, operating expenses, payment gateway rules, and supplier tracking in one clean, tabbed interface.
* **Interactive Daily Trend SVG Chart:** Pure SVG daily performance chart visualizing Revenue vs. COGS vs. Net Profit across custom dates with zero external JavaScript library overhead.
* **Payment Gateway Fee Auto-Detection:** Automatically detect transaction fees across payment processors (bKash, Nagad, Rocket, SSLCommerz, Stripe, PayPal, etc.) and deduct them from gross revenue.
* **Operating Expense Manager (Overhead Ledger):** Record non-product operational expenses (Meta/Google Ads, domain/hosting, salaries, office rent, packaging) to reveal true business take-home profit.
* **Actual Shipping Courier Cost & Deficit Tracking:** Record actual courier invoices (Pathao, Steadfast, RedX, DHL, FedEx) inside orders to automatically calculate out-of-pocket shipping deficit.
* **Line-Item Cost Snapshotting:** Captures unit cost at the exact moment an order is placed, ensuring historical profit records remain 100% accurate even if suppliers raise prices later.
* **Simple & Variable Product Support:** Seamlessly enter wholesale costs for simple products and individual variations with live real-time gross margin preview.
* **Color-Coded Profit Badges:**
  * 🟢 **Green (30%+ Margin):** Healthy, high-profit products and orders.
  * 🟡 **Amber (15% - 30% Margin):** Standard margin transactions.
  * 🔴 **Red (<15% Margin / Loss):** Low-margin or loss-making orders requiring attention.
* **Quick Edit & Bulk Edit:** Instant inline cost editing and supplier assignment right from the products table.
* **Supplier Directory & Filtering:** Associate products with vendors and filter catalog items by supplier with one click.
* **Native WooCommerce CSV Import & Export:** Bulk upload and download COGS and Suppliers via native WooCommerce product CSV tools.
* **Executive Profit Dashboard Widget:** High-performance dashboard widget showing Today's and This Month's Net Profit, Revenue, and Margin percentage (cached to eliminate database overhead).
* **100% HPOS Ready:** Future-proof compatibility with High-Performance Order Storage (custom order tables) and legacy post-meta.
* **Zero Frontend Script Footprint:** Absolutely zero frontend CSS or JS is loaded on the storefront.

### 🚀 Built by NexForge AI

Crafted with care by the engineering team at **[NexForge AI](https://usemarketpulse.com)** — creators of **MarketPulse**, the automated 24/7 competitor price intelligence and dynamic repricing platform.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/simple-cost-of-goods-for-woocommerce` directory, or install directly through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Edit any product in **WooCommerce &rarr; Products** and enter your unit purchase cost in the **Cost Price (COGS)** field.
4. Access your central profit hub under **WooCommerce &rarr; MarketPulse Profit Analytics**.
5. View real-time order breakdowns under **WooCommerce &rarr; Orders** and on your WordPress **Dashboard**.

== Frequently Asked Questions ==

= Does this plugin slow down my store frontend? =
**No, absolutely not.** This plugin only registers lightweight hooks in the WordPress admin panel. It enqueues zero scripts or stylesheets on your customer-facing storefront.

= Is it compatible with WooCommerce High-Performance Order Storage (HPOS)? =
**Yes.** The plugin fully declares and supports HPOS (`custom_order_tables`), ensuring future-proof reliability as WooCommerce migrates away from `wp_posts`.

= What happens if my supplier increases product cost next month? =
The plugin snapshots line-item costs at checkout. Your historical orders will always preserve the exact cost at the time they were purchased, so historical profit analytics will never be distorted.

= Can I configure payment gateway fees for local gateways like bKash or SSLCommerz? =
Yes! Navigate to **WooCommerce &rarr; Profit Analytics &rarr; Payment Gateway Fees** to configure exact percentage and fixed fees per gateway.

= Can I track non-product overhead expenses like Facebook Ads and hosting? =
Yes! Use the **Operating Expenses** tab inside Profit Analytics to record overhead expenses and deduct them from your store's gross profit.

= Can I bulk update costs with CSV? =
Yes! The plugin hooks into WooCommerce's native Product CSV Importer and Exporter, adding `Cost of Goods (COGS)` and `Supplier` columns.

= Is this plugin completely free? =
Yes, 100% free and open-source under the GPL-2.0 license.

== Screenshots ==

1. Unified Profit Analytics Hub: Real-time KPI summary cards and daily SVG trend chart.
2. Operating Expense Manager: Overhead expense ledger for ads, rent, and server costs.
3. Payment Gateway Fee Rules: Automated transaction fee rules per payment method.
4. Order Details Screen: Financial Waterfall breakdown of Revenue, COGS, Gateway Fees, and Net Profit.
5. Product Edit Screen: Simple and variation Cost Price fields with live estimated margin percentage.
6. Orders Table: Color-coded Net Profit and Margin % badges.
7. Dashboard Widget: Real-time Today's and This Month's net profit analytics.

== Changelog ==

= 1.0.0 =
* Initial public open-source release.
* Unified Profit Analytics central hub under `WooCommerce > Profit Analytics`.
* Interactive pure SVG daily performance chart (Revenue vs COGS vs Net Profit).
* Core mathematical calculation engine for True Net Profit and Net Margin %.
* Payment Gateway Fee auto-detection engine with configurable percentage and fixed fee rules.
* Business Operating Expenses ledger for marketing, hosting, salaries, and overhead.
* Actual shipping courier cost tracking and shipping deficit calculation.
* Simple & Variable product Cost Price (COGS) and Supplier fields.
* Real-time live margin preview for simple products and variations.
* Order profit snapshotting and Financial Waterfall breakdown sidebar box.
* Product and Order list table columns with color-coded profit badges.
* Quick Edit and Bulk Edit support for Cost Price and Supplier.
* Native WooCommerce Product CSV Importer and Exporter integration.
* High-Performance WordPress Dashboard widget with transient caching.
* Full WooCommerce HPOS (custom order tables) compatibility.
