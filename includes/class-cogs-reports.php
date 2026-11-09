<?php
/**
 * Unified Profit Analytics & Reporting Central Hub
 * Renders the clean, single-surface administrative interface under WooCommerce > Profit Analytics.
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Reports {

    /**
     * Initialize reports hooks
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ), 25 );
        add_action( 'admin_post_scogs_save_gateway_rules', array( __CLASS__, 'handle_save_gateways' ) );
    }

    /**
     * Register single submenu under WooCommerce
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Profit Analytics', 'simple-cost-of-goods-for-woocommerce' ),
            __( 'Profit Analytics', 'simple-cost-of-goods-for-woocommerce' ),
            'manage_woocommerce',
            'scogs-analytics',
            array( __CLASS__, 'render_page' )
        );
    }

    /**
     * Render the unified page
     */
    public static function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
        ?>
        <div class="wrap scogs-analytics-wrap">
            <!-- Header -->
            <div class="scogs-header-bar">
                <div class="scogs-header-left">
                    <h1 class="wp-heading-inline">
                        <?php esc_html_e( 'Profit Analytics & Financial Intelligence', 'simple-cost-of-goods-for-woocommerce' ); ?>
                    </h1>
                    <span class="scogs-version-tag">v<?php echo esc_html( SCOGS_VERSION ); ?></span>
                </div>
                <div class="scogs-header-right">
                    <span class="scogs-brand-pill">
                        <?php esc_html_e( 'Powered by', 'simple-cost-of-goods-for-woocommerce' ); ?> 
                        <a href="https://usemarketpulse.com?utm_source=cogs_plugin&utm_medium=header_link" target="_blank">NexForge AI</a>
                    </span>
                </div>
            </div>

            <!-- Tab Navigation -->
            <nav class="nav-tab-wrapper scogs-nav-tabs">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=overview' ) ); ?>" class="nav-tab <?php echo 'overview' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-chart-area"></span> <?php esc_html_e( 'Overview & Charts', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=expenses' ) ); ?>" class="nav-tab <?php echo 'expenses' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-money-alt"></span> <?php esc_html_e( 'Operating Expenses', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=gateways' ) ); ?>" class="nav-tab <?php echo 'gateways' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Payment Gateway Fees', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=suppliers' ) ); ?>" class="nav-tab <?php echo 'suppliers' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Suppliers & Bulk Tools', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </a>
            </nav>

            <!-- Tab Contents -->
            <div class="scogs-tab-content">
                <?php
                switch ( $active_tab ) {
                    case 'expenses':
                        self::render_expenses_tab();
                        break;
                    case 'gateways':
                        self::render_gateways_tab();
                        break;
                    case 'suppliers':
                        self::render_suppliers_tab();
                        break;
                    case 'overview':
                    default:
                        self::render_overview_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * TAB 1: Overview & Interactive Charts
     */
    private static function render_overview_tab() {
        // Parse date filter
        $range = isset( $_GET['range'] ) ? sanitize_key( $_GET['range'] ) : 'this_month';
        $today = current_time( 'Y-m-d' );

        switch ( $range ) {
            case 'today':
                $start_date = $today;
                $end_date   = $today;
                break;
            case 'yesterday':
                $start_date = date( 'Y-m-d', strtotime( '-1 day', strtotime( $today ) ) );
                $end_date   = $start_date;
                break;
            case 'last_7_days':
                $start_date = date( 'Y-m-d', strtotime( '-6 days', strtotime( $today ) ) );
                $end_date   = $today;
                break;
            case 'last_month':
                $start_date = date( 'Y-m-01', strtotime( 'first day of last month', strtotime( $today ) ) );
                $end_date   = date( 'Y-m-t', strtotime( 'last day of last month', strtotime( $today ) ) );
                break;
            case 'custom':
                $start_date = ! empty( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : date( 'Y-m-01', strtotime( $today ) );
                $end_date   = ! empty( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : $today;
                break;
            case 'this_month':
            default:
                $start_date = date( 'Y-m-01', strtotime( $today ) );
                $end_date   = $today;
                break;
        }

        // Fetch order financials
        $order_stats = self::get_aggregated_financials( $start_date, $end_date );

        // Fetch operating expenses for period
        $operating_expenses = SCOGS_Expenses::get_total_expenses( $start_date, $end_date );

        // 5. True Net Profit = Gross Profit - Gateway Fees - Shipping Deficit - Operating Expenses
        $true_net_profit = $order_stats['gross_profit'] - $order_stats['gateway_fees'] - $order_stats['shipping_deficit'] - $operating_expenses;
        $true_net_margin = $order_stats['gross_revenue'] > 0 ? ( $true_net_profit / $order_stats['gross_revenue'] ) * 100 : 0.0;
        $net_badge_class = $true_net_margin >= 30 ? 'scogs-badge-green' : ( $true_net_margin >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );
        ?>
        <!-- Date Filter Toolbar -->
        <div class="scogs-toolbar-card">
            <div class="scogs-filter-presets">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=overview&range=today' ) ); ?>" class="button <?php echo 'today' === $range ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Today', 'simple-cost-of-goods-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=overview&range=yesterday' ) ); ?>" class="button <?php echo 'yesterday' === $range ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Yesterday', 'simple-cost-of-goods-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=overview&range=last_7_days' ) ); ?>" class="button <?php echo 'last_7_days' === $range ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Last 7 Days', 'simple-cost-of-goods-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=overview&range=this_month' ) ); ?>" class="button <?php echo 'this_month' === $range ? 'button-primary' : ''; ?>"><?php esc_html_e( 'This Month', 'simple-cost-of-goods-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=overview&range=last_month' ) ); ?>" class="button <?php echo 'last_month' === $range ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Last Month', 'simple-cost-of-goods-for-woocommerce' ); ?></a>
            </div>
            <form method="get" class="scogs-custom-date-form">
                <input type="hidden" name="page" value="scogs-analytics">
                <input type="hidden" name="tab" value="overview">
                <input type="hidden" name="range" value="custom">
                <input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" required>
                <span>&rarr;</span>
                <input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" required>
                <button type="submit" class="button"><?php esc_html_e( 'Filter', 'simple-cost-of-goods-for-woocommerce' ); ?></button>
            </form>
        </div>

        <!-- 6 Core Financial Metric KPI Cards -->
        <div class="scogs-kpi-grid">
            <!-- 1. Gross Revenue -->
            <div class="scogs-kpi-card">
                <span class="scogs-kpi-title"><?php esc_html_e( '1. Gross Revenue', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-kpi-num"><?php echo wc_price( $order_stats['gross_revenue'] ); ?></span>
                <span class="scogs-kpi-sub"><?php echo esc_html( $order_stats['order_count'] ); ?> <?php esc_html_e( 'orders placed', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
            </div>

            <!-- 2. Total COGS -->
            <div class="scogs-kpi-card">
                <span class="scogs-kpi-title"><?php esc_html_e( '2. Total COGS', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-kpi-num scogs-cost-text">&minus;<?php echo wc_price( $order_stats['total_cogs'] ); ?></span>
                <span class="scogs-kpi-sub"><?php esc_html_e( 'Product acquisition cost', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
            </div>

            <!-- 3. Gross Profit -->
            <div class="scogs-kpi-card">
                <span class="scogs-kpi-title"><?php esc_html_e( '3. Gross Profit', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-kpi-num scogs-subtotal-text"><?php echo wc_price( $order_stats['gross_profit'] ); ?></span>
                <span class="scogs-badge scogs-badge-blue"><?php echo esc_html( round( $order_stats['gross_margin'], 1 ) ); ?>% <?php esc_html_e( 'Gross Margin', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
            </div>

            <!-- 4. Fees & Deficit -->
            <div class="scogs-kpi-card">
                <span class="scogs-kpi-title"><?php esc_html_e( '4. Gateway & Shipping Fees', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-kpi-num scogs-cost-text">&minus;<?php echo wc_price( $order_stats['gateway_fees'] + $order_stats['shipping_deficit'] ); ?></span>
                <span class="scogs-kpi-sub"><?php esc_html_e( 'Gateways:', 'simple-cost-of-goods-for-woocommerce' ); ?> <?php echo wc_price( $order_stats['gateway_fees'] ); ?> | <?php esc_html_e( 'Deficit:', 'simple-cost-of-goods-for-woocommerce' ); ?> <?php echo wc_price( $order_stats['shipping_deficit'] ); ?></span>
            </div>

            <!-- 5. Operating Expenses -->
            <div class="scogs-kpi-card">
                <span class="scogs-kpi-title"><?php esc_html_e( '5. Operating Expenses', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-kpi-num scogs-cost-text">&minus;<?php echo wc_price( $operating_expenses ); ?></span>
                <span class="scogs-kpi-sub"><a href="<?php echo esc_url( admin_url( 'admin.php?page=scogs-analytics&tab=expenses' ) ); ?>"><?php esc_html_e( 'Manage Ads, Rent, Server &rarr;', 'simple-cost-of-goods-for-woocommerce' ); ?></a></span>
            </div>

            <!-- 6. True Net Profit -->
            <div class="scogs-kpi-card scogs-kpi-highlight">
                <span class="scogs-kpi-title"><?php esc_html_e( '6. True Net Profit', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-kpi-num scogs-profit-val"><?php echo wc_price( $true_net_profit ); ?></span>
                <span class="scogs-badge <?php echo esc_attr( $net_badge_class ); ?>"><?php echo esc_html( round( $true_net_margin, 1 ) ); ?>% <?php esc_html_e( 'Net Margin', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
            </div>
        </div>

        <!-- Daily Financial Trend Chart -->
        <?php self::render_profit_chart( $start_date, $end_date ); ?>

        <!-- Product Profit Leaderboards -->
        <div class="scogs-leaderboard-grid">
            <!-- Top 5 Profit-Makers -->
            <div class="scogs-table-card">
                <h3 class="scogs-card-heading">
                    <span class="dashicons dashicons-awards" style="color:#10b981;"></span> 
                    <?php esc_html_e( 'Top 5 Most Profitable Products', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </h3>
                <?php self::render_top_products_table( $start_date, $end_date, 'DESC' ); ?>
            </div>

            <!-- Top 5 Margin-Losers / Dead Stock -->
            <div class="scogs-table-card">
                <h3 class="scogs-card-heading">
                    <span class="dashicons dashicons-warning" style="color:#ef4444;"></span> 
                    <?php esc_html_e( 'Lowest Margin Products (Requires Attention)', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </h3>
                <?php self::render_top_products_table( $start_date, $end_date, 'ASC' ); ?>
            </div>
        </div>

        <!-- Strategic NexForge / MarketPulse Cross-Promotion -->
        <div class="scogs-cross-promo-card">
            <div class="scogs-promo-content">
                <div class="scogs-promo-badge"><?php esc_html_e( 'PRO PROFIT STRATEGY', 'simple-cost-of-goods-for-woocommerce' ); ?></div>
                <h3><?php esc_html_e( 'Protect Your Profit Margin Floor from Competitor Price Wars', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                <p>
                    <?php esc_html_e( 'Knowing your COGS is the first step. The next step is ensuring you never underprice yourself or lose sales to competitors. MarketPulse tracks prices across 100+ marketplaces 24/7 and automatically adjusts your WooCommerce prices while strictly guarding your minimum profit floor.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </p>
                <a href="https://usemarketpulse.com?utm_source=cogs_plugin&utm_medium=analytics_dashboard&utm_campaign=overview_promo" target="_blank" class="button button-primary">
                    <?php esc_html_e( 'Explore MarketPulse AI Dynamic Repricer &rarr;', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * TAB 2: Operating Expenses Manager
     */
    private static function render_expenses_tab() {
        $categories = SCOGS_Expenses::get_categories();
        $expenses   = SCOGS_Expenses::get_expenses();
        $total      = SCOGS_Expenses::get_total_expenses();
        ?>
        <div class="scogs-expenses-wrap">
            <div class="scogs-two-col-grid">
                <!-- Add Expense Form -->
                <div class="scogs-form-card">
                    <h3><?php esc_html_e( 'Add Operating Expense', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                    <p class="description"><?php esc_html_e( 'Record overhead costs like Meta ads, server bills, salaries or packaging to deduct from monthly gross profit.', 'simple-cost-of-goods-for-woocommerce' ); ?></p>
                    
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'scogs_add_expense_nonce' ); ?>
                        <input type="hidden" name="action" value="scogs_add_expense">

                        <p>
                            <label for="expense_date"><strong><?php esc_html_e( 'Expense Date:', 'simple-cost-of-goods-for-woocommerce' ); ?></strong></label><br>
                            <input type="date" id="expense_date" name="expense_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" class="widefat" required>
                        </p>

                        <p>
                            <label for="category"><strong><?php esc_html_e( 'Category:', 'simple-cost-of-goods-for-woocommerce' ); ?></strong></label><br>
                            <select id="category" name="category" class="widefat">
                                <?php foreach ( $categories as $key => $label ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>

                        <p>
                            <label for="title"><strong><?php esc_html_e( 'Expense Title:', 'simple-cost-of-goods-for-woocommerce' ); ?></strong></label><br>
                            <input type="text" id="title" name="title" placeholder="<?php esc_attr_e( 'e.g. Facebook Ads Campaign #12', 'simple-cost-of-goods-for-woocommerce' ); ?>" class="widefat" required>
                        </p>

                        <p>
                            <label for="amount"><strong><?php esc_html_e( 'Amount:', 'simple-cost-of-goods-for-woocommerce' ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)</strong></label><br>
                            <input type="number" step="0.01" min="0.01" id="amount" name="amount" placeholder="0.00" class="widefat" required>
                        </p>

                        <p>
                            <label for="notes"><strong><?php esc_html_e( 'Notes / Reference (Optional):', 'simple-cost-of-goods-for-woocommerce' ); ?></strong></label><br>
                            <textarea id="notes" name="notes" rows="2" class="widefat" placeholder="<?php esc_attr_e( 'Invoice number, transaction ID, or details...', 'simple-cost-of-goods-for-woocommerce' ); ?>"></textarea>
                        </p>

                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Expense', 'simple-cost-of-goods-for-woocommerce' ); ?></button>
                    </form>
                </div>

                <!-- Expense History Table -->
                <div class="scogs-table-card">
                    <div class="scogs-table-header-flex">
                        <h3><?php esc_html_e( 'Expense Ledger', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                        <span class="scogs-badge scogs-badge-blue"><?php esc_html_e( 'Total Logged:', 'simple-cost-of-goods-for-woocommerce' ); ?> <?php echo wc_price( $total ); ?></span>
                    </div>

                    <?php if ( empty( $expenses ) ) : ?>
                        <p class="scogs-muted" style="padding: 20px; text-align:center;">
                            <?php esc_html_e( 'No operating expenses logged yet. Add your first marketing or overhead expense using the form on the left.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 100px;"><?php esc_html_e( 'Date', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Category', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                    <th><?php esc_html_e( 'Title', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                    <th style="width: 100px; text-align:right;"><?php esc_html_e( 'Amount', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                    <th style="width: 60px; text-align:center;"><?php esc_html_e( 'Action', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $expenses as $row ) : 
                                    $cat_label = isset( $categories[ $row->category ] ) ? $categories[ $row->category ] : ucfirst( $row->category );
                                    $delete_url = wp_nonce_url( admin_url( 'admin-post.php?action=scogs_delete_expense&id=' . $row->id ), 'scogs_delete_expense_' . $row->id );
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $row->expense_date ); ?></td>
                                    <td><small><?php echo esc_html( $cat_label ); ?></small></td>
                                    <td><strong><?php echo esc_html( $row->title ); ?></strong></td>
                                    <td style="text-align:right; font-weight:600; color:#b91c1c;">&minus;<?php echo wc_price( $row->amount ); ?></td>
                                    <td style="text-align:center;">
                                        <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete this expense entry?', 'simple-cost-of-goods-for-woocommerce' ); ?>');" style="color:#ef4444;">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * TAB 3: Payment Gateway Fee Rules
     */
    private static function render_gateways_tab() {
        $gateways = SCOGS_Gateways::get_available_gateways();
        $rules    = SCOGS_Gateways::get_rules();
        ?>
        <div class="scogs-gateways-wrap">
            <div class="scogs-form-card" style="max-width: 900px;">
                <h3><?php esc_html_e( 'Payment Gateway Fee Auto-Detection', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                <p class="description">
                    <?php esc_html_e( 'Configure the percentage and fixed transaction fees deducted by your payment processors (e.g. bKash, SSLCommerz, Stripe, PayPal). The plugin automatically subtracts these transaction fees to reveal True Net Profit.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                </p>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'scogs_save_gateways_nonce' ); ?>
                    <input type="hidden" name="action" value="scogs_save_gateway_rules">

                    <table class="wp-list-table widefat fixed striped" style="margin-top:14px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Payment Gateway', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                <th style="width: 140px;"><?php esc_html_e( 'Percentage Fee (%)', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                <th style="width: 140px;"><?php esc_html_e( 'Fixed Fee', 'simple-cost-of-goods-for-woocommerce' ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $gateways as $gateway_id => $data ) : 
                                $rule_percent = isset( $rules[ $gateway_id ]['percent'] ) ? $rules[ $gateway_id ]['percent'] : 0.0;
                                $rule_fixed   = isset( $rules[ $gateway_id ]['fixed'] ) ? $rules[ $gateway_id ]['fixed'] : 0.0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $data['title'] ); ?></strong>
                                    <code><?php echo esc_html( $gateway_id ); ?></code>
                                    <?php if ( ! $data['enabled'] ) : ?>
                                        <span class="scogs-badge" style="background:#f1f5f9; color:#94a3b8;"><?php esc_html_e( 'Inactive', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="gateways[<?php echo esc_attr( $gateway_id ); ?>][percent]" value="<?php echo esc_attr( $rule_percent ); ?>" style="width:100px;"> %
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="gateways[<?php echo esc_attr( $gateway_id ); ?>][fixed]" value="<?php echo esc_attr( $rule_fixed ); ?>" style="width:100px;">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p style="margin-top: 16px;">
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Gateway Rules', 'simple-cost-of-goods-for-woocommerce' ); ?></button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * TAB 4: Suppliers & Bulk CSV Tools
     */
    private static function render_suppliers_tab() {
        global $wpdb;

        $suppliers = $wpdb->get_results( $wpdb->prepare(
            "SELECT meta_value as supplier_name, COUNT(post_id) as product_count 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = %s AND meta_value != '' 
             GROUP BY meta_value 
             ORDER BY product_count DESC",
            SCOGS_Product::META_KEY_SUPPLIER
        ) );
        ?>
        <div class="scogs-suppliers-wrap">
            <div class="scogs-two-col-grid">
                <!-- Supplier List -->
                <div class="scogs-table-card">
                    <h3><?php esc_html_e( 'Active Suppliers & Distributors', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                    <p class="description"><?php esc_html_e( 'Suppliers currently linked to your product catalog.', 'simple-cost-of-goods-for-woocommerce' ); ?></p>

                    <?php if ( empty( $suppliers ) ) : ?>
                        <p class="scogs-muted" style="padding: 20px; text-align:center;">
                            <?php esc_html_e( 'No suppliers assigned yet. You can assign suppliers on product edit screens or via CSV import.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Supplier Name', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                    <th style="width: 120px; text-align:center;"><?php esc_html_e( 'Products Linked', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                    <th style="width: 100px; text-align:center;"><?php esc_html_e( 'Action', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $suppliers as $sup ) : 
                                    $filter_url = admin_url( 'edit.php?post_type=product&filter_supplier=' . urlencode( $sup->supplier_name ) );
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $sup->supplier_name ); ?></strong></td>
                                    <td style="text-align:center;"><span class="scogs-badge scogs-badge-blue"><?php echo esc_html( $sup->product_count ); ?></span></td>
                                    <td style="text-align:center;">
                                        <a href="<?php echo esc_url( $filter_url ); ?>" class="button button-small"><?php esc_html_e( 'View Products', 'simple-cost-of-goods-for-woocommerce' ); ?></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Bulk CSV Import / Export Card -->
                <div class="scogs-form-card">
                    <h3><?php esc_html_e( 'Bulk CSV Inventory Tools', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                    <p class="description">
                        <?php esc_html_e( 'Simple Cost of Goods hooks seamlessly into WooCommerce’s native CSV Importer and Exporter. You can bulk update wholesale costs and supplier names for thousands of products in Excel or Google Sheets.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                    </p>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin: 16px 0;">
                        <h4><?php esc_html_e( '1. Export Your Catalog to CSV', 'simple-cost-of-goods-for-woocommerce' ); ?></h4>
                        <p><?php esc_html_e( 'Exports your entire catalog with dedicated "Cost of Goods (COGS)" and "Supplier" columns ready for editing.', 'simple-cost-of-goods-for-woocommerce' ); ?></p>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ); ?>" class="button button-secondary">
                            <?php esc_html_e( 'Export Products to CSV &rarr;', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </a>
                    </div>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom: 16px;">
                        <h4><?php esc_html_e( '2. Import Updated CSV File', 'simple-cost-of-goods-for-woocommerce' ); ?></h4>
                        <p><?php esc_html_e( 'Upload your modified CSV. Existing products will have their cost prices and suppliers updated automatically.', 'simple-cost-of-goods-for-woocommerce' ); ?></p>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_importer' ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'Import Products from CSV &rarr;', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Flush all report, chart and dashboard transient caches
     */
    public static function flush_cache() {
        global $wpdb;
        delete_transient( 'scogs_dashboard_stats' );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_scogs_%' OR option_name LIKE '_transient_timeout_scogs_%'" );
    }

    /**
     * Handle saving gateway fee rules POST
     */
    public static function handle_save_gateways() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Unauthorized user.', 'simple-cost-of-goods-for-woocommerce' ) );
        }

        check_admin_referer( 'scogs_save_gateways_nonce' );

        if ( isset( $_POST['gateways'] ) && is_array( $_POST['gateways'] ) ) {
            SCOGS_Gateways::save_rules( $_POST['gateways'] );
            self::flush_cache();
        }

        wp_safe_redirect( admin_url( 'admin.php?page=scogs-analytics&tab=gateways&saved=1' ) );
        exit;
    }

    /**
     * Aggregate orders financial metrics between two dates
     */
    private static function get_aggregated_financials( $start_date, $end_date ) {
        $cache_key = 'scogs_agg_' . md5( $start_date . '_' . $end_date );
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $orders = wc_get_orders( array(
            'date_created' => $start_date . ' 00:00:00...' . $end_date . ' 23:59:59',
            'status'       => array( 'wc-processing', 'wc-completed' ),
            'limit'        => 1000,
        ) );

        $gross_revenue    = 0.0;
        $total_cogs       = 0.0;
        $gross_profit     = 0.0;
        $gateway_fees     = 0.0;
        $shipping_deficit = 0.0;

        foreach ( $orders as $order ) {
            // Read-only pass: $save = false guarantees zero database writes during reporting
            $stats = SCOGS_Calculations::calculate_order_financials( $order, false );
            $gross_revenue    += $stats['gross_revenue'];
            $total_cogs       += $stats['total_cogs'];
            $gross_profit     += $stats['gross_profit'];
            $gateway_fees     += $stats['gateway_fee'];
            $shipping_deficit += $stats['shipping_deficit'];
        }

        $gross_margin = $gross_revenue > 0 ? ( $gross_profit / $gross_revenue ) * 100 : 0.0;

        $result = array(
            'order_count'      => count( $orders ),
            'gross_revenue'    => $gross_revenue,
            'total_cogs'       => $total_cogs,
            'gross_profit'     => $gross_profit,
            'gross_margin'     => $gross_margin,
            'gateway_fees'     => $gateway_fees,
            'shipping_deficit' => $shipping_deficit,
        );

        set_transient( $cache_key, $result, 15 * MINUTE_IN_SECONDS );
        return $result;
    }

    /**
     * Render Top / Lowest products table
     */
    private static function render_top_products_table( $start_date, $end_date, $order_dir = 'DESC' ) {
        $stats_cache_key = 'scogs_pstats_' . md5( $start_date . '_' . $end_date );
        $product_stats   = get_transient( $stats_cache_key );

        if ( false === $product_stats || ! is_array( $product_stats ) ) {
            $orders = wc_get_orders( array(
                'date_created' => $start_date . ' 00:00:00...' . $end_date . ' 23:59:59',
                'status'       => array( 'wc-processing', 'wc-completed' ),
                'limit'        => 500,
            ) );

            $product_stats = array();

            foreach ( $orders as $order ) {
                foreach ( $order->get_items() as $item ) {
                    if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
                        continue;
                    }

                    $product_id = $item->get_product_id();
                    $name       = $item->get_name();
                    $qty        = (int) $item->get_quantity();
                    $line_total = (float) $item->get_total();

                    $unit_cost  = (float) $item->get_meta( SCOGS_Order::META_ITEM_COST, true );
                    $line_cost  = $unit_cost * $qty;
                    $profit     = $line_total - $line_cost;

                    if ( ! isset( $product_stats[ $product_id ] ) ) {
                        $product_stats[ $product_id ] = array(
                            'name'    => $name,
                            'qty'     => 0,
                            'revenue' => 0.0,
                            'cost'    => 0.0,
                            'profit'  => 0.0,
                        );
                    }

                    $product_stats[ $product_id ]['qty']     += $qty;
                    $product_stats[ $product_id ]['revenue'] += $line_total;
                    $product_stats[ $product_id ]['cost']    += $line_cost;
                    $product_stats[ $product_id ]['profit']  += $profit;
                }
            }

            set_transient( $stats_cache_key, $product_stats, 15 * MINUTE_IN_SECONDS );
        }

        if ( empty( $product_stats ) ) {
            echo '<p class="scogs-muted" style="padding:16px; text-align:center;">' . esc_html__( 'No product sales recorded in this date range.', 'simple-cost-of-goods-for-woocommerce' ) . '</p>';
            return;
        }

        // Sort by profit
        uasort( $product_stats, function( $a, $b ) use ( $order_dir ) {
            return 'DESC' === $order_dir ? ( $b['profit'] <=> $a['profit'] ) : ( $a['profit'] <=> $b['profit'] );
        } );

        // Slice top 5
        $product_stats = array_slice( $product_stats, 0, 5, true );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                    <th style="width: 50px; text-align:center;"><?php esc_html_e( 'Sold', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                    <th style="width: 80px; text-align:right;"><?php esc_html_e( 'Revenue', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                    <th style="width: 80px; text-align:right;"><?php esc_html_e( 'Profit', 'simple-cost-of-goods-for-woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $product_stats as $pid => $item ) : 
                    $margin = $item['revenue'] > 0 ? ( $item['profit'] / $item['revenue'] ) * 100 : 0.0;
                    $badge  = $margin >= 30 ? 'scogs-badge-green' : ( $margin >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html( wp_trim_words( $item['name'], 5 ) ); ?></strong><br>
                        <span class="scogs-badge <?php echo esc_attr( $badge ); ?>" style="font-size:10px; padding:1px 5px;">
                            <?php echo esc_html( round( $margin, 1 ) ); ?>% <?php esc_html_e( 'margin', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </span>
                    </td>
                    <td style="text-align:center;"><?php echo esc_html( $item['qty'] ); ?></td>
                    <td style="text-align:right;"><?php echo wc_price( $item['revenue'] ); ?></td>
                    <td style="text-align:right; font-weight:700; color:<?php echo $item['profit'] >= 0 ? '#059669' : '#dc2626'; ?>;">
                        <?php echo wc_price( $item['profit'] ); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render Interactive Daily Trend SVG Chart (Revenue vs COGS vs Net Profit)
     */
    private static function render_profit_chart( $start_date, $end_date ) {
        $chart_cache_key = 'scogs_chart_' . md5( $start_date . '_' . $end_date );
        $cached_chart    = get_transient( $chart_cache_key );

        if ( false !== $cached_chart && is_array( $cached_chart ) ) {
            $daily_data = $cached_chart['data'];
            $has_data   = $cached_chart['has_data'];
            $max_val    = $cached_chart['max_val'];
        } else {
            // Build daily timeline array
            $start = new DateTime( $start_date );
            $end   = new DateTime( $end_date );
            $end->modify( '+1 day' );
            $interval = new DateInterval( 'P1D' );
            $period   = new DatePeriod( $start, $interval, $end );

            $daily_data = array();
            foreach ( $period as $dt ) {
                $date_str = $dt->format( 'Y-m-d' );
                $daily_data[ $date_str ] = array(
                    'date'    => $date_str,
                    'label'   => $dt->format( 'M j' ),
                    'revenue' => 0.0,
                    'cogs'    => 0.0,
                    'profit'  => 0.0,
                );
            }

            // Fetch orders in date range
            $orders = wc_get_orders( array(
                'date_created' => $start_date . ' 00:00:00...' . $end_date . ' 23:59:59',
                'status'       => array( 'wc-processing', 'wc-completed' ),
                'limit'        => 1000,
            ) );

            $has_data = false;
            foreach ( $orders as $order ) {
                $order_date = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d' ) : '';
                if ( isset( $daily_data[ $order_date ] ) ) {
                    // Read-only calculation: $save = false guarantees zero order saves
                    $stats = SCOGS_Calculations::calculate_order_financials( $order, false );
                    $daily_data[ $order_date ]['revenue'] += (float) $stats['gross_revenue'];
                    $daily_data[ $order_date ]['cogs']    += (float) $stats['total_cogs'];
                    $daily_data[ $order_date ]['profit']  += (float) $stats['net_profit'];
                    if ( $stats['gross_revenue'] > 0 || $stats['total_cogs'] > 0 || $stats['net_profit'] > 0 ) {
                        $has_data = true;
                    }
                }
            }

            // Calculate max scale value
            $max_val = 0.0;
            foreach ( $daily_data as $row ) {
                if ( $row['revenue'] > $max_val ) {
                    $max_val = $row['revenue'];
                }
                if ( $row['cogs'] > $max_val ) {
                    $max_val = $row['cogs'];
                }
                if ( $row['profit'] > $max_val ) {
                    $max_val = $row['profit'];
                }
            }
            if ( $max_val <= 0 ) {
                $max_val = 100;
            }
            $max_val = ceil( $max_val * 1.15 ); // 15% top headroom

            set_transient( $chart_cache_key, array(
                'data'     => $daily_data,
                'has_data' => $has_data,
                'max_val'  => $max_val,
            ), 15 * MINUTE_IN_SECONDS );
        }

        // Chart SVG dimensions
        $svg_w       = 1000;
        $svg_h       = 320;
        $pad_left    = 70;
        $pad_right   = 25;
        $pad_top     = 30;
        $pad_bottom  = 45;
        $plot_w      = $svg_w - $pad_left - $pad_right;
        $plot_h      = $svg_h - $pad_top - $pad_bottom;
        $baseline_y  = $pad_top + $plot_h;

        $day_count   = count( $daily_data );
        $slot_w      = $day_count > 0 ? ( $plot_w / $day_count ) : $plot_w;
        $bar_w       = max( 2, min( 16, ( $slot_w - 6 ) / 3 ) );
        $currency    = get_woocommerce_currency_symbol();
        $label_step  = $day_count > 16 ? ceil( $day_count / 12 ) : 1;
        ?>
        <div class="scogs-chart-card">
            <div class="scogs-chart-header">
                <div class="scogs-chart-title">
                    <span class="dashicons dashicons-chart-bar" style="color:#2563eb;"></span>
                    <h3><?php esc_html_e( 'Sales, Cost & Profit Daily Trend', 'simple-cost-of-goods-for-woocommerce' ); ?></h3>
                </div>
                <div class="scogs-chart-legend">
                    <span class="scogs-legend-item"><span class="scogs-legend-color" style="background:#3b82f6;"></span> <?php esc_html_e( 'Gross Revenue', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                    <span class="scogs-legend-item"><span class="scogs-legend-color" style="background:#f59e0b;"></span> <?php esc_html_e( 'Total COGS', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                    <span class="scogs-legend-item"><span class="scogs-legend-color" style="background:#10b981;"></span> <?php esc_html_e( 'Net Profit', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                </div>
            </div>

            <div class="scogs-chart-svg-container">
                <svg viewBox="0 0 <?php echo esc_attr( $svg_w ); ?> <?php echo esc_attr( $svg_h ); ?>" preserveAspectRatio="xMidYMid meet" class="scogs-svg-chart">
                    <!-- Horizontal Gridlines & Y-Axis Labels -->
                    <?php
                    $steps = 4;
                    for ( $i = 0; $i <= $steps; $i++ ) {
                        $ratio = $i / $steps;
                        $y_val = $baseline_y - ( $ratio * $plot_h );
                        $amt   = round( $max_val * $ratio );
                        ?>
                        <line x1="<?php echo esc_attr( $pad_left ); ?>" y1="<?php echo esc_attr( $y_val ); ?>" x2="<?php echo esc_attr( $svg_w - $pad_right ); ?>" y2="<?php echo esc_attr( $y_val ); ?>" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="<?php echo 0 === $i ? 'none' : '4,4'; ?>"></line>
                        <text x="<?php echo esc_attr( $pad_left - 10 ); ?>" y="<?php echo esc_attr( $y_val + 4 ); ?>" text-anchor="end" font-size="11" fill="#94a3b8">
                            <?php echo esc_html( $currency . number_format_i18n( $amt ) ); ?>
                        </text>
                    <?php } ?>

                    <!-- Baseline X axis -->
                    <line x1="<?php echo esc_attr( $pad_left ); ?>" y1="<?php echo esc_attr( $baseline_y ); ?>" x2="<?php echo esc_attr( $svg_w - $pad_right ); ?>" y2="<?php echo esc_attr( $baseline_y ); ?>" stroke="#cbd5e1" stroke-width="1.5"></line>

                    <!-- Daily Bars -->
                    <?php
                    $idx = 0;
                    foreach ( $daily_data as $d_key => $day ) {
                        $group_w   = ( $bar_w * 3 ) + 4;
                        $slot_x    = $pad_left + ( $idx * $slot_w );
                        $start_x   = $slot_x + ( ( $slot_w - $group_w ) / 2 );

                        // Bar heights
                        $h_rev     = $max_val > 0 ? ( $day['revenue'] / $max_val ) * $plot_h : 0;
                        $h_cogs    = $max_val > 0 ? ( $day['cogs'] / $max_val ) * $plot_h : 0;
                        $h_prof    = $max_val > 0 ? ( max( 0, $day['profit'] ) / $max_val ) * $plot_h : 0;

                        $y_rev     = $baseline_y - $h_rev;
                        $y_cogs    = $baseline_y - $h_cogs;
                        $y_prof    = $baseline_y - $h_prof;

                        $prof_color = $day['profit'] >= 0 ? '#10b981' : '#ef4444';
                        $tip_text   = sprintf(
                            "%s\nRevenue: %s%s\nCOGS: -%s%s\nNet Profit: %s%s",
                            $day['label'],
                            $currency, number_format( $day['revenue'], 2 ),
                            $currency, number_format( $day['cogs'], 2 ),
                            $currency, number_format( $day['profit'], 2 )
                        );
                        ?>
                        <g class="scogs-chart-day-group" data-date="<?php echo esc_attr( $d_key ); ?>">
                            <!-- Revenue Bar -->
                            <rect x="<?php echo esc_attr( $start_x ); ?>" y="<?php echo esc_attr( $y_rev ); ?>" width="<?php echo esc_attr( $bar_w ); ?>" height="<?php echo esc_attr( max( 0, $h_rev ) ); ?>" rx="2" fill="#3b82f6" class="scogs-chart-bar">
                                <title><?php echo esc_attr( $tip_text ); ?></title>
                            </rect>

                            <!-- COGS Bar -->
                            <rect x="<?php echo esc_attr( $start_x + $bar_w + 2 ); ?>" y="<?php echo esc_attr( $y_cogs ); ?>" width="<?php echo esc_attr( $bar_w ); ?>" height="<?php echo esc_attr( max( 0, $h_cogs ) ); ?>" rx="2" fill="#f59e0b" class="scogs-chart-bar">
                                <title><?php echo esc_attr( $tip_text ); ?></title>
                            </rect>

                            <!-- Profit Bar -->
                            <rect x="<?php echo esc_attr( $start_x + ( $bar_w * 2 ) + 4 ); ?>" y="<?php echo esc_attr( $y_prof ); ?>" width="<?php echo esc_attr( $bar_w ); ?>" height="<?php echo esc_attr( max( 0, $h_prof ) ); ?>" rx="2" fill="<?php echo esc_attr( $prof_color ); ?>" class="scogs-chart-bar">
                                <title><?php echo esc_attr( $tip_text ); ?></title>
                            </rect>

                            <!-- Date Label -->
                            <?php if ( 0 === ( $idx % $label_step ) || $idx === ( $day_count - 1 ) ) : ?>
                                <text x="<?php echo esc_attr( $slot_x + ( $slot_w / 2 ) ); ?>" y="<?php echo esc_attr( $baseline_y + 18 ); ?>" text-anchor="middle" font-size="10" fill="#64748b">
                                    <?php echo esc_html( $day['label'] ); ?>
                                </text>
                            <?php endif; ?>
                        </g>
                        <?php
                        $idx++;
                    }
                    ?>

                    <?php if ( ! $has_data ) : ?>
                        <rect x="<?php echo esc_attr( $pad_left + 40 ); ?>" y="<?php echo esc_attr( $pad_top + 60 ); ?>" width="<?php echo esc_attr( $plot_w - 80 ); ?>" height="70" rx="8" fill="#ffffff" stroke="#e2e8f0" stroke-width="1" opacity="0.95"></rect>
                        <text x="<?php echo esc_attr( $svg_w / 2 ); ?>" y="<?php echo esc_attr( $pad_top + 100 ); ?>" text-anchor="middle" font-size="13" font-weight="600" fill="#64748b">
                            <?php esc_html_e( 'No sales recorded in this period yet. Placed orders will automatically appear here.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </text>
                    <?php endif; ?>
                </svg>
            </div>
        </div>
        <?php
    }
}

