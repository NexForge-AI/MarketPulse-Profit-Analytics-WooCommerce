# Simple Cost of Goods & Profit Tracker for WooCommerce
### The Merchant's Complete User Guide & Operating Manual
*Authored by NexForge AI ([usemarketpulse.com](https://usemarketpulse.com))*

---

## 📖 Welcome & Overview

Most WooCommerce store owners make a critical mistake: they treat **Gross Revenue** as **Take-Home Profit**. 

In reality, your true profit is constantly eroded by four hidden leaks:
1. **Wholesale Product Costs (COGS)**
2. **Payment Gateway Processing Fees** (Stripe, PayPal, bKash, Square, COD fees)
3. **Courier Shipping Deficits** (charging a flat $5 or ৳60 while paying the courier $8 or ৳120)
4. **Operating Expenses (OpEx)** (store hosting, employee salaries, warehouse rent, ad spend)

**Simple Cost of Goods & Profit Tracker** calculates your **True Net Profit** automatically, in real-time, with zero code and zero impact on storefront page speed.

---

## ⚡ 60-Second Quick Start Guide

Getting started takes less than one minute:

### Step 1: Add Cost Price to Your Products
1. Go to **Products > All Products** in your WordPress dashboard.
2. Click **Edit** on any product.
3. In the **Product Data** box (under the *General* tab), you will see two new fields:
   - **Cost Price (COGS):** Enter what you paid your supplier or manufacturer per unit (e.g., `55`).
   - **Supplier / Vendor (Optional):** Enter where you bought the item (e.g., `Acme Wholesale Ltd.`).
4. Click **Update** to save.

> 💡 **Instant Margin Feedback:** The moment you type a cost price, the plugin instantly calculates your **Est. Gross Margin %** in real-time right below the field. If your margin falls below 15%, a high-visibility warning box alerts you immediately before you save.

---

### Step 2: Automation Takes Over
You don't need to do anything else for sales tracking!
- When a customer places an order, the plugin **permanently freezes** the cost price and calculates gateway fees and shipping deficits.
- If your wholesale supplier raises prices next month, your past order historical records will **never be altered or corrupted**.

---

### Step 3: View Your True Net Profit
1. Go to **WooCommerce > Profit Analytics** in the sidebar.
2. View your real-time **True Net Profit**, **Gross Margin**, **Gateway Losses**, and **Courier Deficits**.
3. Toggle date ranges: *Today*, *Yesterday*, *This Month*, *Last Month*, *Year to Date*, or *Custom Range*.
4. Click **Export P&L (CSV)** anytime to download your financial statement for your accountant or tax advisor.

---

## 🛠️ Feature Deep Dive

### 1. Simple Products & Real-Time Margin Alerts
When editing a simple product:
- **Cost Price (COGS):** Enter your unit cost in your store's default currency.
- **Supplier / Vendor:** Tag your distributor for supply chain reporting.
- **Dangerously Low Margin Alert:** If `Sale Price - COGS` yields a margin under 15%, the field highlights in red with a live warning chip reminding you to protect your profit floor against gateway cuts.

### 2. Variable Products & Variation COGS
For products with sizes, colors, or materials:
- Go to the **Variations** tab in the Product Data box.
- Expand any individual variation to set a custom **Cost Price (COGS)** and **Supplier**.
- **Bulk Set Variation Costs:** Need to apply the same cost to 50 variations at once? Click the Variation dropdown menu &rarr; select **"Set Cost Price (COGS)"** under *Cost of Goods* &rarr; enter the amount once, and all variations update instantly.

### 3. Bulk & Quick Edit from Product Table
Save hours when managing hundreds of products:
- **Quick Edit:** Hover over any item in **Products > All Products** and click **Quick Edit**. Change the Cost Price right inside the table row without opening the full editor.
- **Bulk Edit:** Check multiple products &rarr; select **Bulk Actions > Edit** &rarr; update Suppliers or adjust prices en masse.

### 4. Zero-Config Payment Gateway Fee Auto-Detection
Most plugins force you to manually calculate percentages and fixed transaction fees. Our plugin comes pre-configured with standard merchant rates for:
- **Stripe:** 2.9% + $0.30 (or regional equivalent)
- **PayPal:** 3.49% + $0.49
- **Square:** 2.6% + $0.10
- **bKash / Nagad (Bangladesh):** 1.5% merchant transaction fee
- **Cash on Delivery (COD):** 0% (or courier handling fee)

To customize or adjust rates for negotiated corporate discounts, go to:
**WooCommerce > Profit Analytics > Payment Gateways & Costs**.

### 5. Courier Shipping Deficit Tracking
If you offer free shipping or subsidized flat-rate shipping (e.g., charging customer ৳60 while Pathao / Steadfast / FedEx charges you ৳120), you are losing money on every single parcel.
- Under **WooCommerce > Profit Analytics > Payment Gateways & Costs**, input your standard courier rates or flat out-of-pocket costs.
- The plugin tracks **Shipping Deficit = Actual Courier Cost &minus; Shipping Fee Charged to Customer** and deducts it directly from your Net Profit.

### 6. Operating Expenses (OpEx) Ledger
Wholesale costs are only half the picture. To know if your business is truly profitable:
1. Open **WooCommerce > Profit Analytics > Operating Expenses (OpEx)**.
2. Click **+ Add Expense Entry**.
3. Record monthly rent, Facebook/Google ad spend, software subscriptions (Canva, Shopify, hosting), or team payroll.
4. Choose whether the cost is **One-time** or **Monthly Recurring**.
5. The analytics engine amortizes these costs across your reporting periods.

---

## 🧮 5 Accounting Formulas Demystified

Here is the exact mathematical model used by the analytics engine:

| # | Metric | Accounting Formula | Meaning |
|---|---|---|---|
| **1** | **Gross Revenue** | `Item Retail Price × Quantity Sold` | Total money received before any costs or deductions. |
| **2** | **Total COGS** | `Unit Cost Price × Quantity Sold` | What you paid your suppliers or factories to source the items. |
| **3** | **Gross Profit** | `Gross Revenue − Total COGS` | Margin left over solely from the product markup. |
| **4** | **Courier Deficit** | `Actual Shipping Cost − Shipping Fee Charged` | Out-of-pocket losses from subsidized customer delivery. |
| **5** | **True Net Profit** | `Gross Profit − Gateway Fees − Courier Deficit − OpEx` | **Actual cash remaining in your bank account.** |

**Net Margin % Formula:**
$$\text{Net Profit Margin} = \left(\frac{\text{True Net Profit}}{\text{Gross Revenue}}\right) \times 100\%$$

---

## 📊 Analytics Dashboard & Reports Hub

Navigate to **WooCommerce > Profit Analytics** to access 5 dedicated management screens:

1. **Overview & Charts:**
   - 6 Instant KPI Stat Cards (Revenue, COGS, Gateway Cuts, Shipping Deficit, True Net Profit, Net Margin %).
   - SVG Trend Chart showing daily profit trajectories.
   - Visual Waterfall breakdown (Gross Sales &rarr; COGS &rarr; Fees &rarr; True Take-Home).
   - Top 5 Most Profitable Products & Danger Low Margin Table.
   - Live Catalog Sample Vulnerability Radar with Repricing Lift Simulator.
   - 1-Click CSV Export.

2. **Operating Expenses (OpEx):**
   - Complete expense ledger with search, categorization, and monthly recurring automation.

3. **Payment Gateways & Costs:**
   - Gateway fee rate manager with one-click restore defaults.

4. **Suppliers & Bulk Tools:**
   - Directory of wholesale suppliers, supplier-specific profit margins, and inventory restocking sheets.

5. **User Guide & FAQs:**
   - Embedded interactive documentation and troubleshooting right inside your WordPress admin.

---

## ❓ Frequently Asked Questions (FAQ)

#### Q: Does this plugin slow down my website or checkout page?
**A:** Absolutely not. The plugin loads **zero JavaScript and zero CSS** on your customer-facing frontend. It runs exclusively in the WordPress admin panel and saves order metrics quietly during checkout background processes.

#### Q: If I edit a product's Cost Price today, will my past months' sales reports change?
**A:** Never. When an order is completed, its COGS, gateway fees, and shipping costs are permanently frozen into that order's metadata. Changing costs today only impacts sales made from this moment forward.

#### Q: Can I track profits for orders placed before installing this plugin?
**A:** Yes! If you had existing orders before installing, simply fill in the Cost Price for your products, then go to **WooCommerce > Profit Analytics > Suppliers & Bulk Tools** and use the **"Retroactive Order COGS Recalculation"** tool to populate past orders.

#### Q: How do I export my P&L report for tax filing or my CPA?
**A:** In **WooCommerce > Profit Analytics > Overview**, select your tax period (e.g. *Last Month* or *Custom Date Range*), then click the **Export P&L (CSV)** button. The resulting spreadsheet is formatted according to standard GAAP/IFRS accounting conventions.

#### Q: What is MarketPulse AI Dynamic Repricing?
**A:** Static pricing costs merchants thousands in lost sales and uncaptured margin. When competitors drop their price, your sales plummet; when competitors go out of stock, you sell at standard low prices instead of capturing surge profits. MarketPulse AI monitors competitors 24/7 and automatically adjusts your WooCommerce prices while strictly guarding your COGS floor. You can claim a free audit anytime at [usemarketpulse.com](https://usemarketpulse.com/signup?plan=cmu76n3c80005n2el6itwbile).

---

## 🇧🇩 সহজ ইউজার গাইড (বাংলা সংস্করণ)

আমাদের সাধারণ বাংলাদেশি ই-কমার্স উদ্যোক্তাদের জন্য সহজ নির্দেশিকা:

### ১. প্রোডাক্টের পাইকারি কেনা খরচ (COGS) কীভাবে বসাবেন?
- আপনার ওয়ার্ডপ্রেস ড্যাশবোর্ড থেকে **Products > All Products**-এ যান।
- যেকোনো প্রোডাক্ট **Edit** করুন।
- নিচের **Product Data** বক্সে **Cost Price (COGS) (৳)** নামের নতুন ঘরে প্রোডাক্টটির কেনা দাম বা পাইকারি খরচ লিখুন (যেমন: ৫০ টাকা)।
- সাথে সাথে নিচে আপনার **Est. Gross Margin** দেখাবে। মার্জিন ১৫% এর কম হলে একটি লাল সতর্কবার্তা দেখাবে।
- প্রোডাক্টটি **Update** করুন।

### ২. অর্ডার আসলে কী হবে?
- কাস্টমার যখন অর্ডার করবে, প্লাগইন নিজে থেকেই প্রোডাক্টের কেনা খরচ, গেটওয়ে ফি (bKash/Nagad/Cards) এবং কুরিয়ার খরচ হিসাব করে নিবে।
- আপনাকে কোনো ক্যালকুলেটর নিয়ে বসতে হবে না!

### ৩. আসল নিট লাভ (True Net Profit) কীভাবে দেখবেন?
- ড্যাশবোর্ডের বাঁ দিকের মেনু থেকে **WooCommerce > Profit Analytics**-এ যান।
- আপনি দেখতে পাবেন মোট বিক্রি কত হয়েছে, কেনা খরচ কত গেছে, বিকাশ বা গেটওয়ে কত টাকা কেটেছে, এবং আপনার পকেটে **আসল নিট লাভ কত টাকা থাকলো**।

### ৪. এক্সেল বা CSV ফাইল ডাউনলোড
- যেকোনো মাসের রিপোর্ট এক্সেল ফাইলে সেভ করতে **Overview** ট্যাবের **Export P&L (CSV)** বাটনে ক্লিক করলেই সম্পূর্ণ হিসাব ডাউনলোড হয়ে যাবে।

---

## 💬 Support & Contact

Need help or want to suggest a new feature?
- **Website:** [usemarketpulse.com](https://usemarketpulse.com)
- **Email:** support@usemarketpulse.com
- **Plugin Author:** NexForge AI
