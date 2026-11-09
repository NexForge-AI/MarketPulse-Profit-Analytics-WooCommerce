=== Simple Cost of Goods & Profit Tracker for WooCommerce ===
Contributors: nexforge, usemarketpulse
Donate link: https://usemarketpulse.com
Tags: cost of goods, cogs, profit, profit margin, profit tracker, woocommerce profit, net profit
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, high-performance Cost of Goods Sold (COGS) and net profit margin tracker for WooCommerce. 100% HPOS compatible with zero server bloat.

== Description ==

**Track your actual net profits and profit margins in WooCommerce — without the bloat, without high annual fees, and without slowing down your site.**

WooCommerce tells you how much revenue you made, but revenue isn't what goes into your bank account. **Profit is.**

Most Cost of Goods plugins charge $79 to $199 every single year, or load hundreds of kilobytes of complex accounting scripts that slow down your store. **Simple Cost of Goods & Profit Tracker by NexForge AI** is built on a clean philosophy: **Do one job with absolute perfection.**

### 🌟 Key Features

* **Simple & Variable Product Cost Fields:** Easily input wholesale cost price right beside your regular and sale prices.
* **Inline Live Margin Preview:** See your estimated profit and margin percentage in real-time while editing your products.
* **Order Profit Snapshotting:** Captures unit cost at the exact moment an order is placed, ensuring historical profit numbers remain 100% accurate even if suppliers raise prices later.
* **Color-Coded Profit Badges:**
  * 🟢 **Green (30%+ Margin):** High-profit healthy orders.
  * 🟡 **Amber (15% - 30% Margin):** Standard margins.
  * 🔴 **Red (<15% Margin / Loss):** Low-margin or loss-making orders requiring attention.
* **Product & Order List Columns:** View Cost, Profit, and Margin % right from the native WooCommerce Products and Orders admin tables.
* **Quick Edit Support:** Update product cost prices in seconds directly from the product table without opening individual edit pages.
* **Executive Profit Dashboard Widget:** High-performance dashboard widget showing Today's and This Month's Net Profit, Revenue, and Margin percentage (cached to eliminate database overhead).
* **100% HPOS Ready:** Built from the ground up to support WooCommerce High-Performance Order Storage (HPOS) and legacy post-meta seamlessly.
* **Zero Bloat & Lightweight:** Less than 40 KB in size. Loads admin scripts ONLY on WooCommerce pages. Never slows down your frontend storefront.

### 🚀 Built by NexForge AI

Crafted with care by the engineering team at **[NexForge AI](https://usemarketpulse.com)** — creators of **MarketPulse**, the automated 24/7 competitor price intelligence and dynamic repricing platform.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/simple-cost-of-goods-for-woocommerce` directory, or install directly through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Edit any product in **WooCommerce &rarr; Products** and enter your unit purchase cost in the **Cost Price (COGS)** field.
4. View real-time profit and margin calculations under **WooCommerce &rarr; Orders** and on your WordPress **Dashboard**.

== Frequently Asked Questions ==

= Does this plugin slow down my store frontend? =
**No, absolutely not.** This plugin only registers lightweight hooks in the WordPress admin panel. It enqueues zero scripts or stylesheets on your customer-facing storefront.

= Is it compatible with WooCommerce High-Performance Order Storage (HPOS)? =
**Yes.** The plugin fully declares and supports HPOS (`custom_order_tables`), ensuring future-proof reliability as WooCommerce migrates away from `wp_posts`.

= What happens if my supplier increases product cost next month? =
The plugin snapshots line-item costs at checkout. Your historical orders will always preserve the exact cost at the time they were purchased, so your historical profit analytics will never be skewed.

= Can I include or exclude shipping charges and taxes? =
Yes! Navigate to **WooCommerce &rarr; Settings &rarr; Products &rarr; Cost of Goods (COGS)** to toggle whether customer-paid shipping and taxes are included in your gross revenue calculations.

= Is this plugin completely free? =
Yes, 100% free and open-source under the GPL-2.0 license.

== Screenshots ==

1. Product Edit Screen: Simple Cost Price field with live estimated margin percentage.
2. Orders Table: Color-coded Net Profit and Margin % badges.
3. Order Details Screen: Complete breakdown of Revenue, Cost of Goods, and Net Profit.
4. Dashboard Widget: Real-time Today's and This Month's net profit analytics.

== Changelog ==

= 1.0.0 =
* Initial public open-source release.
* Added Simple & Variable product Cost Price (COGS) fields.
* Added live margin preview in product editor.
* Added Order profit snapshotting and breakdown meta box.
* Added Color-coded profit columns to Products and Orders admin tables.
* Added Quick Edit cost price support.
* Added High-Performance Dashboard Profit Widget with caching.
* Full WooCommerce HPOS (custom order tables) compatibility.
