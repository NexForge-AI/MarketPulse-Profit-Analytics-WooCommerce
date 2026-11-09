<?php
/**
 * Admin Dashboard Profit Summary Widget
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Dashboard {

    /**
     * Initialize dashboard widget
     */
    public static function init() {
        add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_dashboard_widget' ) );
    }

    /**
     * Register widget on WordPress Dashboard
     */
    public static function add_dashboard_widget() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        wp_add_dashboard_widget(
            'scogs_profit_summary_widget',
            __( 'Profit & Margin Tracker (by NexForge AI)', 'simple-cost-of-goods-for-woocommerce' ),
            array( __CLASS__, 'render_dashboard_widget' )
        );
    }

    /**
     * Fetch cached or fresh stats for today and current month
     *
     * @return array
     */
    public static function get_profit_stats() {
        $transient_key = 'scogs_dashboard_stats';
        $stats         = get_transient( $transient_key );

        if ( false !== $stats ) {
            return $stats;
        }

        // Today start/end
        $today_start = date( 'Y-m-d 00:00:00' );
        $today_end   = date( 'Y-m-d 23:59:59' );

        // Month start
        $month_start = date( 'Y-m-01 00:00:00' );

        $statuses = array( 'wc-processing', 'wc-completed' );

        // Orders today
        $today_orders = wc_get_orders( array(
            'date_created' => $today_start . '...' . $today_end,
            'status'       => $statuses,
            'limit'        => 100,
        ) );

        $today_revenue = 0.0;
        $today_cost    = 0.0;
        $today_profit  = 0.0;

        foreach ( $today_orders as $order ) {
            $profit = $order->get_meta( SCOGS_Order::META_ORDER_PROFIT, true );
            $cogs   = $order->get_meta( SCOGS_Order::META_ORDER_COST, true );

            if ( '' === $profit || '' === $cogs ) {
                $calc    = SCOGS_Order::calculate_order_profit( $order );
                $today_revenue += (float) $calc['gross_revenue'];
                $today_cost    += (float) $calc['total_cogs'];
                $today_profit  += (float) $calc['net_profit'];
            } else {
                $today_revenue += (float) $order->get_total();
                $today_cost    += (float) $cogs;
                $today_profit  += (float) $profit;
            }
        }
        $today_margin = $today_revenue > 0 ? ( $today_profit / $today_revenue ) * 100 : 0.0;

        // Orders this month
        $month_orders = wc_get_orders( array(
            'date_created' => '>=' . $month_start,
            'status'       => $statuses,
            'limit'        => 500,
        ) );

        $month_revenue = 0.0;
        $month_cost    = 0.0;
        $month_profit  = 0.0;

        foreach ( $month_orders as $order ) {
            $profit = $order->get_meta( SCOGS_Order::META_ORDER_PROFIT, true );
            $cogs   = $order->get_meta( SCOGS_Order::META_ORDER_COST, true );

            if ( '' === $profit || '' === $cogs ) {
                $calc     = SCOGS_Order::calculate_order_profit( $order );
                $month_revenue += (float) $calc['gross_revenue'];
                $month_cost    += (float) $calc['total_cogs'];
                $month_profit  += (float) $calc['net_profit'];
            } else {
                $month_revenue += (float) $order->get_total();
                $month_cost    += (float) $cogs;
                $month_profit  += (float) $profit;
            }
        }
        $month_margin = $month_revenue > 0 ? ( $month_profit / $month_revenue ) * 100 : 0.0;

        $stats = array(
            'today_revenue' => $today_revenue,
            'today_cost'    => $today_cost,
            'today_profit'  => $today_profit,
            'today_margin'  => $today_margin,
            'today_count'   => count( $today_orders ),
            'month_revenue' => $month_revenue,
            'month_cost'    => $month_cost,
            'month_profit'  => $month_profit,
            'month_margin'  => $month_margin,
            'month_count'   => count( $month_orders ),
        );

        // Cache for 10 minutes to guarantee zero database bottleneck
        set_transient( $transient_key, $stats, 10 * MINUTE_IN_SECONDS );

        return $stats;
    }

    /**
     * Render the admin dashboard widget HTML
     */
    public static function render_dashboard_widget() {
        $stats = self::get_profit_stats();
        ?>
        <div class="scogs-dashboard-widget-wrap">
            <div class="scogs-dashboard-grid">
                <!-- Today Card -->
                <div class="scogs-dash-card">
                    <span class="scogs-card-title"><?php esc_html_e( "Today's Net Profit", 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                    <span class="scogs-card-value scogs-profit-val"><?php echo wc_price( $stats['today_profit'] ); ?></span>
                    <div class="scogs-card-meta">
                        <span><?php esc_html_e( 'Revenue:', 'simple-cost-of-goods-for-woocommerce' ); ?> <?php echo wc_price( $stats['today_revenue'] ); ?></span>
                        <span class="scogs-badge scogs-badge-blue"><?php echo esc_html( round( $stats['today_margin'], 1 ) ); ?>% <?php esc_html_e( 'Margin', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                    </div>
                </div>

                <!-- Month Card -->
                <div class="scogs-dash-card">
                    <span class="scogs-card-title"><?php esc_html_e( 'This Month Net Profit', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                    <span class="scogs-card-value scogs-profit-val"><?php echo wc_price( $stats['month_profit'] ); ?></span>
                    <div class="scogs-card-meta">
                        <span><?php esc_html_e( 'Revenue:', 'simple-cost-of-goods-for-woocommerce' ); ?> <?php echo wc_price( $stats['month_revenue'] ); ?></span>
                        <span class="scogs-badge scogs-badge-blue"><?php echo esc_html( round( $stats['month_margin'], 1 ) ); ?>% <?php esc_html_e( 'Margin', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Marketing & Strategic Cross-Promotion -->
            <div class="scogs-brand-box">
                <div class="scogs-brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                </div>
                <div class="scogs-brand-text">
                    <strong><?php esc_html_e( 'Safeguard Your Profit Margin Floor Automatically', 'simple-cost-of-goods-for-woocommerce' ); ?></strong>
                    <p>
                        <?php esc_html_e( 'Are competitor price wars eroding your margins? MarketPulse monitors 100+ marketplaces 24/7 and auto-reprices your catalog while strictly protecting your minimum cost margins.', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        <br>
                        <a href="https://usemarketpulse.com?utm_source=cogs_plugin&utm_medium=dashboard_banner&utm_campaign=cogs_free" target="_blank" class="scogs-link">
                            <?php esc_html_e( 'Learn more about MarketPulse Dynamic Repricer &rarr;', 'simple-cost-of-goods-for-woocommerce' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}

