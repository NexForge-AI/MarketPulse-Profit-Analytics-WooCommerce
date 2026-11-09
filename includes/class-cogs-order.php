<?php
/**
 * Order Profit Calculation & Admin Display (HPOS & Classic Compatible)
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Order {

    const META_ORDER_COST   = '_cogs_order_total_cost';
    const META_ORDER_PROFIT = '_cogs_order_net_profit';
    const META_ORDER_MARGIN = '_cogs_order_margin_percentage';
    const META_ITEM_COST    = '_cogs_item_cost';

    /**
     * Initialize order hooks
     */
    public static function init() {
        // Snapshot cost when line item is created at checkout
        add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'snapshot_line_item_cost' ), 10, 4 );

        // Recalculate on manual order item save / status change
        add_action( 'woocommerce_saved_order_items', array( __CLASS__, 'recalculate_order_profit' ), 10, 2 );

        // Targeted status hooks: Only run one-time profit calculation when order reaches paid/active states
        add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'on_order_paid' ), 10, 2 );
        add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'on_order_paid' ), 10, 2 );
        add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'on_order_invalidated' ), 10, 2 );
        add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'on_order_invalidated' ), 10, 2 );
        add_action( 'woocommerce_order_refunded', array( __CLASS__, 'on_order_refund_created' ), 10, 2 );
        add_action( 'woocommerce_order_partially_refunded', array( __CLASS__, 'on_order_refund_created' ), 10, 2 );

        // Save actual courier shipping cost
        add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_order_custom_meta' ) );
        add_action( 'woocommerce_update_order', array( __CLASS__, 'save_order_custom_meta' ) );

        // Admin order details screen meta box (Classic + HPOS)
        add_action( 'add_meta_boxes_shop_order', array( __CLASS__, 'register_order_meta_box' ) );
        add_action( 'add_meta_boxes_woocommerce_page_wc-orders', array( __CLASS__, 'register_order_meta_box' ) );

        // Show cost under each item in Order Edit screen
        add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'display_item_cost_in_order_editor' ), 10, 3 );

        // Classic order table list column
        add_filter( 'manage_shop_order_posts_columns', array( __CLASS__, 'add_order_profit_column' ), 20 );
        add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'render_classic_order_profit_column' ), 10, 2 );

        // Modern HPOS order table list column
        add_filter( 'woocommerce_shop_order_list_table_columns', array( __CLASS__, 'add_order_profit_column' ), 20 );
        add_action( 'woocommerce_shop_order_list_table_custom_column', array( __CLASS__, 'render_hpos_order_profit_column' ), 10, 2 );
    }

    /**
     * Snapshot cost price onto line item meta at checkout
     */
    public static function snapshot_line_item_cost( $item, $cart_item_key, $values, $order ) {
        $product = $item->get_product();
        if ( ! $product ) {
            return;
        }

        $cost = SCOGS_Product::get_product_cost( $product );
        $item->add_meta_data( self::META_ITEM_COST, wc_format_decimal( $cost ), true );
    }

    /**
     * Calculate and store total order financial metrics
     *
     * @param int|WC_Order $order
     * @param bool         $save  Whether to persist to database
     * @return array
     */
    public static function calculate_order_profit( $order, $save = true ) {
        return SCOGS_Calculations::calculate_order_financials( $order, $save );
    }

    /**
     * Recalculate on manual order items save
     */
    public static function recalculate_order_profit( $order_id, $items ) {
        self::calculate_order_profit( $order_id, true );
        SCOGS_Reports::flush_cache();
    }

    /**
     * Handle order paid transition (processing or completed)
     * Performs one-time snapshot calculation and metadata persistence
     */
    public static function on_order_paid( $order_id, $order = null ) {
        if ( ! $order && $order_id ) {
            $order = wc_get_order( $order_id );
        }
        if ( $order ) {
            self::calculate_order_profit( $order, true );
            SCOGS_Reports::flush_cache();
        }
    }

    /**
     * Invalidate cached report transients when an order is cancelled or refunded
     */
    public static function on_order_invalidated( $order_id, $order = null ) {
        SCOGS_Reports::flush_cache();
    }

    /**
     * Invalidate report cache and recalculate order metrics on refund creation
     *
     * @param int $order_id  Parent Order ID
     * @param int $refund_id Refund ID
     */
    public static function on_order_refund_created( $order_id, $refund_id = 0 ) {
        $order = wc_get_order( $order_id );
        if ( $order && is_a( $order, 'WC_Order' ) && ! is_a( $order, 'WC_Order_Refund' ) ) {
            SCOGS_Calculations::calculate_order_financials( $order, true );
        }
        SCOGS_Reports::flush_cache();
    }

    /**
     * Re-entrancy guard to prevent recursion during order updates
     *
     * @var bool
     */
    private static $is_saving_order_meta = false;

    /**
     * Save actual courier shipping cost from Order edit screen
     */
    public static function save_order_custom_meta( $order_id ) {
        // Prevent recursive saves
        if ( self::$is_saving_order_meta ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WooCommerce core prior to firing woocommerce_process_shop_order_meta.
        if ( ! isset( $_POST['_cogs_actual_shipping_cost'] ) ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        self::$is_saving_order_meta = true;

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $cost           = wc_clean( sanitize_text_field( wp_unslash( $_POST['_cogs_actual_shipping_cost'] ) ) );
        $formatted_cost = ( '' === $cost || false === $cost ) ? '' : wc_format_decimal( $cost );

        $order->update_meta_data( '_cogs_actual_shipping_cost', $formatted_cost );

        // Calculate and snapshot profit + deficit + margin in one pass
        SCOGS_Calculations::calculate_order_financials( $order, true );

        // Invalidate report and dashboard caches
        SCOGS_Reports::flush_cache();

        self::$is_saving_order_meta = false;
    }

    /**
     * Register Order Details Meta Box
     */
    public static function register_order_meta_box() {
        add_meta_box(
            'scogs-order-profit-box',
            __( 'Net Profit & Financial Waterfall', 'marketpulse-cogs-profit-for-woocommerce' ),
            array( __CLASS__, 'render_order_meta_box' ),
            null,
            'side',
            'high'
        );
    }

    /**
     * Render Order Details Meta Box (Financial Waterfall)
     */
    public static function render_order_meta_box( $post_or_order ) {
        $order = ( $post_or_order instanceof WC_Order ) ? $post_or_order : wc_get_order( $post_or_order->ID );
        if ( ! $order ) {
            return;
        }

        $stats = SCOGS_Calculations::calculate_order_financials( $order );

        $badge_class = $stats['net_margin'] >= 30 ? 'scogs-badge-green' : ( $stats['net_margin'] >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );
        $payment_method_title = $order->get_payment_method_title();
        ?>
        <div class="scogs-order-summary-card">
            <!-- 1. Gross Revenue -->
            <div class="scogs-metric-row">
                <span class="scogs-metric-label"><?php esc_html_e( 'Gross Revenue:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value"><?php echo wp_kses_post( wc_price( $stats['gross_revenue'] ) ); ?></span>
            </div>

            <!-- Discounts (if any) -->
            <?php if ( $stats['discount_total'] > 0 ) : ?>
            <div class="scogs-metric-row scogs-deduct-row">
                <span class="scogs-metric-label">&minus; <?php esc_html_e( 'Discounts:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value scogs-cost-text">&minus;<?php echo wp_kses_post( wc_price( $stats['discount_total'] ) ); ?></span>
            </div>
            <?php endif; ?>

            <!-- Total COGS -->
            <div class="scogs-metric-row scogs-deduct-row">
                <span class="scogs-metric-label">&minus; <?php esc_html_e( 'Total COGS:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value scogs-cost-text">&minus;<?php echo wp_kses_post( wc_price( $stats['total_cogs'] ) ); ?></span>
            </div>

            <!-- Subtotal: Gross Profit -->
            <div class="scogs-divider"></div>
            <div class="scogs-metric-row scogs-subtotal-row">
                <span class="scogs-metric-label"><strong><?php esc_html_e( 'Gross Profit:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></strong></span>
                <span class="scogs-metric-value"><strong><?php echo wp_kses_post( wc_price( $stats['gross_profit'] ) ); ?></strong> (<?php echo esc_html( round( $stats['gross_margin'], 1 ) ); ?>%)</span>
            </div>
            <div class="scogs-divider"></div>

            <!-- 2. Payment Gateway Fee -->
            <div class="scogs-metric-row scogs-deduct-row">
                <span class="scogs-metric-label">&minus; <?php echo esc_html( $payment_method_title ? $payment_method_title : __( 'Payment Fee', 'marketpulse-cogs-profit-for-woocommerce' ) ); ?>:</span>
                <span class="scogs-metric-value scogs-cost-text">&minus;<?php echo wp_kses_post( wc_price( $stats['gateway_fee'] ) ); ?></span>
            </div>

            <!-- 3. Shipping Analysis -->
            <div class="scogs-shipping-box">
                <div class="scogs-metric-row">
                    <span class="scogs-metric-label"><?php esc_html_e( 'Customer Shipping:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                    <span class="scogs-metric-value"><?php echo wp_kses_post( wc_price( $stats['customer_paid_shipping'] ) ); ?></span>
                </div>
                <div class="scogs-metric-row">
                    <label for="_cogs_actual_shipping_cost" class="scogs-metric-label"><?php esc_html_e( 'Actual Courier Cost:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></label>
                    <input type="text" 
                           id="_cogs_actual_shipping_cost" 
                           name="_cogs_actual_shipping_cost" 
                           value="<?php echo esc_attr( $stats['actual_courier_cost'] > 0 ? $stats['actual_courier_cost'] : '' ); ?>" 
                           placeholder="<?php esc_attr_e( 'e.g. 120', 'marketpulse-cogs-profit-for-woocommerce' ); ?>" 
                           style="width: 80px; text-align:right; padding:2px 6px; font-size:12px;">
                </div>
                <?php if ( $stats['shipping_deficit'] > 0 ) : ?>
                <div class="scogs-metric-row scogs-deduct-row" style="color:#b91c1c;">
                    <span class="scogs-metric-label">&minus; <?php esc_html_e( 'Shipping Deficit:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                    <span class="scogs-metric-value">&minus;<?php echo wp_kses_post( wc_price( $stats['shipping_deficit'] ) ); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- 4. True Net Profit / Loss -->
            <div class="scogs-divider" style="border-top:2px solid #cbd5e1;"></div>
            <div class="scogs-metric-row scogs-profit-highlight">
                <span class="scogs-metric-label"><?php echo $stats['net_profit'] < 0 ? esc_html__( 'True Net Loss:', 'marketpulse-cogs-profit-for-woocommerce' ) : esc_html__( 'True Net Profit:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value <?php echo $stats['net_profit'] < 0 ? 'scogs-loss-val' : 'scogs-profit-val'; ?>"><?php echo wp_kses_post( wc_price( $stats['net_profit'] ) ); ?></span>
            </div>
            <div class="scogs-metric-row">
                <span class="scogs-metric-label"><?php esc_html_e( 'Net Margin:', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                <span class="scogs-badge <?php echo esc_attr( $badge_class ); ?>">
                    <?php echo esc_html( round( $stats['net_margin'], 1 ) ); ?>%
                </span>
            </div>

            <!-- Visual Profit Leak / Loss Aversion Notice -->
            <?php
            $gross_rev = (float) $stats['gross_revenue'];
            if ( $gross_rev > 0 ) :
                $leak_est = round( $gross_rev * 0.12, 2 );
            ?>
            <div class="scogs-order-leak-box">
                <div class="scogs-order-leak-header">
                    <span class="scogs-pulse-dot" style="width:6px; height:6px;"></span>
                    <span class="scogs-order-leak-title"><?php esc_html_e( 'Estimated Profit Leak', 'marketpulse-cogs-profit-for-woocommerce' ); ?></span>
                    <span class="scogs-order-leak-tag">&minus;<?php echo wp_kses_post( wc_price( $leak_est ) ); ?></span>
                </div>
                <p class="scogs-order-leak-desc">
                    <?php esc_html_e( 'With static pricing, you may have left up to 12% extra margin on the table during competitor stockouts.', 'marketpulse-cogs-profit-for-woocommerce' ); ?>
                </p>
                <a href="https://usemarketpulse.com/signup?plan=cmu76n3c80005n2el6itwbile&utm_source=cogs_plugin&utm_medium=order_meta&utm_campaign=protect_margins" target="_blank" rel="noopener noreferrer" class="scogs-order-leak-btn">
                    <?php esc_html_e( 'Protect Margins with MarketPulse &rarr;', 'marketpulse-cogs-profit-for-woocommerce' ); ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="scogs-meta-footer">
                <small><?php esc_html_e( '100% accurate financial accounting by NexForge AI', 'marketpulse-cogs-profit-for-woocommerce' ); ?></small>
            </div>
        </div>
        <?php
    }

    /**
     * Display item cost under order items list in order edit screen
     */
    public static function display_item_cost_in_order_editor( $item_id, $item, $_product ) {
        if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
            return;
        }

        $cost = $item->get_meta( self::META_ITEM_COST, true );
        if ( '' === $cost || false === $cost ) {
            $cost = $_product ? SCOGS_Product::get_product_cost( $_product ) : 0.0;
        }

        $cost = (float) $cost;
        if ( $cost > 0 ) {
            $qty        = (int) $item->get_quantity();
            $line_total = (float) $item->get_total();
            $line_cost  = $cost * $qty;
            $profit     = $line_total - $line_cost;
            $margin     = $line_total > 0 ? ( $profit / $line_total ) * 100 : 0.0;

            echo '<div class="scogs-item-cost-snippet" style="font-size:11px; color:#50575e; margin-top:4px;">';
            echo '<strong>' . esc_html__( 'COGS:', 'marketpulse-cogs-profit-for-woocommerce' ) . '</strong> ' . wp_kses_post( wc_price( $cost ) ) . ' &times; ' . esc_html( $qty ) . ' = ' . wp_kses_post( wc_price( $line_cost ) );
            echo ' &bull; <strong>' . esc_html__( 'Item Profit:', 'marketpulse-cogs-profit-for-woocommerce' ) . '</strong> <span style="color:#059669; font-weight:600;">' . wp_kses_post( wc_price( $profit ) ) . '</span> (' . esc_html( round( $margin, 1 ) ) . '%)';
            echo '</div>';
        }
    }

    /**
     * Add Profit column to Orders list table
     */
    public static function add_order_profit_column( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'order_total' === $key ) {
                $new_columns['scogs_order_profit'] = __( 'Net Profit', 'marketpulse-cogs-profit-for-woocommerce' );
            }
        }

        return $new_columns;
    }

    /**
     * Render Classic order profit column
     */
    public static function render_classic_order_profit_column( $column, $post_id ) {
        if ( 'scogs_order_profit' === $column ) {
            $order = wc_get_order( $post_id );
            self::render_order_profit_cell( $order );
        }
    }

    /**
     * Render HPOS order profit column
     */
    public static function render_hpos_order_profit_column( $column, $order ) {
        if ( 'scogs_order_profit' === $column ) {
            self::render_order_profit_cell( $order );
        }
    }

    /**
     * Render the profit badge cell
     */
    private static function render_order_profit_cell( $order ) {
        if ( ! $order ) {
            echo '<span class="scogs-muted">&mdash;</span>';
            return;
        }

        $profit = $order->get_meta( self::META_ORDER_PROFIT, true );
        $margin = $order->get_meta( self::META_ORDER_MARGIN, true );

        // If missing, calculate and save once (lazy one-time initialization for historical orders)
        if ( '' === $profit || false === $profit ) {
            $stats  = SCOGS_Calculations::calculate_order_financials( $order, true );
            $profit = $stats['net_profit'];
            $margin = $stats['net_margin'];
        }

        $profit = (float) $profit;
        $margin = (float) $margin;

        $badge_class = $margin >= 30 ? 'scogs-badge-green' : ( $margin >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );

        echo '<div class="scogs-order-profit-cell">';
        echo '<span class="scogs-profit-number">' . wp_kses_post( wc_price( $profit ) ) . '</span><br>';
        echo '<span class="scogs-badge ' . esc_attr( $badge_class ) . '" style="font-size:10px; padding:1px 6px;">' . esc_html( round( $margin, 1 ) ) . '% margin</span>';
        echo '</div>';
    }
}
