=== Simple Cost of Goods & Profit Tracker for WooCommerce – Real Net Profit, COGS, Gateway Fees & Analytics ===
Contributors: nexforge, usemarketpulse
Donate link: https://usemarketpulse.com
Tags: cost of goods, cogs, profit tracker, profit margin, woocommerce profit
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
WC requires at least: 7.0
WC tested up to: 9.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track real net profit in WooCommerce. Accurate Cost of Goods (COGS), profit margins, gateway fee auto-detection, shipping deficits & analytics.

== Description ==

**Stop treating Gross Revenue as Take-Home Profit. Simple Cost of Goods & Profit Tracker calculates your True Net Profit and profit margins in WooCommerce automatically — with zero bloat, zero frontend speed impact, and 100% mathematical accuracy.**

WooCommerce by default shows you how much gross revenue you generated. But revenue is a vanity metric that hides your real business health. 

Every single month, your actual profit is quietly eroded by four major profit leaks:
1. **Wholesale Product Costs (COGS)**
2. **Payment Gateway Processing Fees** (Stripe, PayPal, bKash, Nagad, Square, COD)
3. **Courier Shipping Deficits** (subsidizing flat-rate delivery while couriers bill you higher rates)
4. **Operating Expenses (OpEx)** (Facebook/Google ad spend, hosting, salaries, software, packaging)

Most other WooCommerce Cost of Goods plugins charge **$79 to $199 every year**, or bog down your store database with heavy external libraries. 

**Simple Cost of Goods & Profit Tracker by NexForge AI** gives you an enterprise-grade financial intelligence engine 100% free, lightweight, and completely compatible with WooCommerce High-Performance Order Storage (HPOS).

---

### 🌟 Why This Plugin Ranks #1 for Profit Analytics

* **Accurate Cost of Goods Sold (COGS) Tracking:** Easily assign unit wholesale or manufacturing costs to simple products and variable product variations.
* **Live Margin % Preview & Floor Protection:** Instant margin calculation as you type your cost price, with high-visibility warning alerts when margins drop below 15% to protect your profit floor.
* **Automatic Payment Gateway Fee Detection:** Built-in auto-detection for Stripe, PayPal, Square, bKash, Nagad, Rocket, SSLCommerz, and Cash on Delivery (COD). No manual spreadsheet math required.
* **Courier Shipping Deficit Accounting:** Automatically tracks out-of-pocket losses when shipping charged to the customer is less than actual courier invoices (Pathao, Steadfast, RedX, FedEx, DHL).
* **Operating Expenses (OpEx) Overhead Ledger:** Record non-product operational costs (Meta ads, Google Ads, server hosting, salaries, office rent, packaging) to determine true company take-home profit.
* **Line-Item Cost Snapshotting:** Permanently locks wholesale costs and gateway fees the moment an order is placed. Changing product prices in the future will never alter or corrupt your historical sales reports.
* **Unified Profit Analytics Hub (`WooCommerce > Profit Analytics`):** 5 dedicated tabs consolidating real-time KPI cards, interactive SVG daily trendlines, operating expenses, gateway fee configurations, supplier management, and in-dashboard user documentation.
* **Interactive SVG Trend Charts (Zero JS Bloat):** Fast, beautiful pure-SVG trajectory charts displaying Daily Revenue vs. Total COGS vs. True Net Profit without loading Chart.js or heavy 500KB third-party scripts.
* **Financial Waterfall Order Breakdown:** Complete transparency on every single order screen showing Gross Sales &rarr; COGS &rarr; Payment Gateway Cuts &rarr; Courier Deficits &rarr; Net Profit.
* **Color-Coded Profit Badges:**
  * 🟢 **Green (30%+ Margin):** High-margin healthy transactions.
  * 🟡 **Amber (15% - 30% Margin):** Standard margin transactions.
  * 🔴 **Red (<15% Margin / Loss):** Dangerously low-margin or loss-making sales.
* **Native WooCommerce CSV Import & Export:** Bulk import and export Cost Price and Supplier columns using standard WooCommerce Product CSV tools.
* **Quick Edit & Bulk Edit Support:** Update product wholesale costs right from the WooCommerce product list table in seconds without loading the full editor.
* **Supplier & Vendor Directory:** Group catalog items by wholesale vendor, compare profit margins across distributors, and generate instant restocking sheets.
* **Executive Dashboard Widget:** High-speed cached WordPress dashboard widget showing Today's and This Month's Net Profit, Revenue, and Margin percentage.
* **100% HPOS Ready:** Full native support for High-Performance Order Storage (`custom_order_tables`) and traditional post-meta fallback.
* **Zero Millisecond Storefront Footprint:** Exactly 0 frontend JavaScript and 0 frontend CSS loaded on customer checkout or catalog pages.

---

### 🧮 5 Accounting Formulas Demystified

The plugin follows standard GAAP / IFRS e-commerce accounting principles:

1. **Gross Revenue** = `Retail Price × Quantity Sold`
2. **Total COGS** = `Wholesale Unit Cost × Quantity Sold`
3. **Gross Profit** = `Gross Revenue − Discounts − Total COGS`
4. **Courier Deficit** = `Actual Courier Fee − Shipping Paid by Customer`
5. **True Net Profit** = `Gross Profit − Payment Gateway Fees − Courier Deficit − Operating Expenses`

**Net Profit Margin %** = `(True Net Profit ÷ Gross Revenue) × 100%`

---

### 🚀 Powered by NexForge AI & MarketPulse

Developed and maintained by the engineering team at **[NexForge AI](https://usemarketpulse.com)** — creators of **MarketPulse**, the automated 24/7 competitor price intelligence and dynamic AI repricing engine for WooCommerce merchants.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/simple-cost-of-goods-for-woocommerce/`, or search for **Simple Cost of Goods & Profit Tracker** directly inside your WordPress admin (**Plugins > Add New**).
2. Click **Install Now** and activate the plugin.
3. Go to **Products > All Products**, edit any product, and enter your wholesale purchase cost in the **Cost Price (COGS)** field.
4. Access your analytics dashboard under **WooCommerce > Profit Analytics**.
5. View detailed order profit breakdowns under **WooCommerce > Orders**.

== Frequently Asked Questions ==

= Does this plugin slow down my store or affect customer checkout speed? =
No. The plugin is built with high-performance standards. It registers zero customer-facing frontend scripts or stylesheets. All mathematical calculations run exclusively in the WordPress admin dashboard and during backend order processing.

= If I change a product cost price today, will it alter past order profits? =
Never. When an order is completed, its financials (wholesale cost, gateway fee, courier deficit, net profit) are permanently snapshotted into that specific order's metadata. Updating costs today will only apply to future orders.

= Is this plugin compatible with WooCommerce High-Performance Order Storage (HPOS)? =
Yes! The plugin declares full compatibility with HPOS (`custom_order_tables`) and works seamlessly whether your WooCommerce store runs in HPOS mode or legacy post-meta mode.

= Do I need to manually configure payment gateway fees? =
No. The plugin includes pre-configured standard transaction fees for major processors including Stripe, PayPal, Square, bKash, Nagad, and Cash on Delivery (COD). You can easily customize them under **WooCommerce > Profit Analytics > Payment Gateways & Costs** if you have negotiated custom volume discounts.

= How does Courier Shipping Deficit tracking work? =
If you charge your customer a flat $5.00 for delivery, but FedEx, DHL, or your local courier bills you $8.50, you have suffered a $3.50 shipping deficit. The plugin calculates this difference and deducts it directly from your Net Profit.

= Can I track non-product overhead expenses like Facebook Ads and store hosting? =
Yes! The built-in **Operating Expenses (OpEx)** tab lets you record one-time and recurring monthly business overheads (ad spend, staff salaries, software subscriptions, rent) to calculate true company take-home profit.

= Can I bulk update cost prices with CSV spreadsheets? =
Yes. The plugin integrates directly into WooCommerce's native Product CSV Importer and Exporter, adding `Cost of Goods (COGS)` and `Supplier` columns so you can manage thousands of SKUs in Microsoft Excel or Google Sheets.

= How do I export financial reports for my accountant or tax filing? =
In **WooCommerce > Profit Analytics > Overview**, select your desired date range (e.g., This Month, Last Month, or Custom), then click **Export P&L (CSV)**. The resulting spreadsheet is formatted for accountants and tax auditors.

= Is this plugin free? =
Yes, 100% free and open-source under the GNU General Public License v2 (GPL-2.0).

== Screenshots ==

1. **MarketPulse Profit Analytics Dashboard:** Real-time financial KPI cards (Net Sales, COGS, Gateway Fees, Shipping Deficit, True Net Profit), interactive SVG trend chart, and P&L waterfall breakdown.
2. **Product Edit Screen (COGS & Live Margin):** Wholesale purchase cost input, real-time gross margin preview, and danger floor alerts (<15% margin).
3. **Order Financial Waterfall Breakdown:** Complete line-item cost snapshot, payment processor deduction, courier deficit, and True Net Profit sidebar widget.
4. **Operating Expenses (OpEx) Overhead Ledger:** Track recurring business expenses including Facebook/Google ads, software subscriptions, and staff salaries.
5. **Payment Gateway Fee Rules:** Built-in auto-detection and customizable fee percentages for Stripe, PayPal, bKash, Nagad, Square, and COD.
6. **Suppliers & Bulk Restocking Sheet:** Wholesale vendor directory with catalog filtering, supplier margin tracking, and inventory restocking sheets.
7. **Live Profit Leak Radar & Dynamic Repricing Lift:** Interactive catalog audit identifying uncaptured surge margins and undercut risks.

== Changelog ==

= 1.0.0 =
* Official public open-source release on WordPress.org.
* Unified MarketPulse Profit Analytics Hub (`WooCommerce > Profit Analytics`).
* Real-time True Net Profit mathematical calculation engine.
* Line-item wholesale cost snapshotting on completed orders.
* Payment Gateway Fee auto-detection (Stripe, PayPal, bKash, Nagad, Square, COD).
* Courier shipping deficit tracking and reconciliation.
* Operating Expenses (OpEx) ledger for overhead amortization.
* Pure SVG daily trendline chart (Revenue vs COGS vs Net Profit) with 0 JS bloat.
* Color-coded profit margin badges (Green, Amber, Red).
* Simple & Variable product Cost Price (COGS) and Supplier fields.
* Native WooCommerce Product CSV Importer & Exporter integration.
* Quick Edit and Bulk Edit catalog cost management.
* High-Performance WordPress Dashboard executive summary widget.
* Full WooCommerce HPOS (custom order tables) support.
* Interactive in-dashboard User Guide & Knowledgebase tab.
