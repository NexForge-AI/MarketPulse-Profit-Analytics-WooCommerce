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
        add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'on_order_status_changed' ), 10, 4 );

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
     *
     * @param WC_Order_Item_Product $item
     * @param string                $cart_item_key
     * @param array                 $values
     * @param WC_Order              $order
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
     * Calculate and store total order cost & profit
     *
     * @param int|WC_Order $order Order ID or WC_Order instance
     * @return array Calculated stats
     */
    public static function calculate_order_profit( $order ) {
        if ( is_numeric( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return array( 'cost' => 0.0, 'profit' => 0.0, 'margin' => 0.0 );
        }

        $total_cost = 0.0;
        $items      = $order->get_items();

        foreach ( $items as $item_id => $item ) {
            if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
                continue;
            }

            $cost = $item->get_meta( self::META_ITEM_COST, true );

            // If not snapshotted yet, fetch current product cost as fallback
            if ( '' === $cost || false === $cost ) {
                $product = $item->get_product();
                $cost    = $product ? SCOGS_Product::get_product_cost( $product ) : 0.0;
                $item->update_meta_data( self::META_ITEM_COST, wc_format_decimal( $cost ) );
                $item->save();
            }

            $qty         = (int) $item->get_quantity();
            $total_cost += ( (float) $cost * $qty );
        }

        // Get revenue settings
        $include_shipping = 'yes' === get_option( 'scogs_include_shipping', 'no' );
        $include_tax      = 'yes' === get_option( 'scogs_include_tax', 'no' );

        $revenue = (float) $order->get_subtotal();
        if ( $include_shipping ) {
            $revenue += (float) $order->get_shipping_total();
        }
        if ( $include_tax ) {
            $revenue += (float) $order->get_total_tax();
        }

        // Net profit
        $profit = $revenue - $total_cost;
        $margin = $revenue > 0 ? ( $profit / $revenue ) * 100 : 0.0;

        // Save order metadata (compatible with HPOS & post meta)
        $order->update_meta_data( self::META_ORDER_COST, wc_format_decimal( $total_cost ) );
        $order->update_meta_data( self::META_ORDER_PROFIT, wc_format_decimal( $profit ) );
        $order->update_meta_data( self::META_ORDER_MARGIN, round( $margin, 2 ) );
        $order->save();

        return array(
            'cost'    => $total_cost,
            'profit'  => $profit,
            'margin'  => $margin,
            'revenue' => $revenue,
        );
    }

    /**
     * Recalculate on manual order items save
     */
    public static function recalculate_order_profit( $order_id, $items ) {
        self::calculate_order_profit( $order_id );
    }

    /**
     * Recalculate on status changes (like Processing, Completed)
     */
    public static function on_order_status_changed( $order_id, $old_status, $new_status, $order ) {
        self::calculate_order_profit( $order );
    }

    /**
     * Register Order Details Meta Box
     */
    public static function register_order_meta_box() {
        add_meta_box(
            'scogs-order-profit-box',
            __( 'Profit & Margin Breakdown', 'simple-cost-of-goods-for-woocommerce' ),
            array( __CLASS__, 'render_order_meta_box' ),
            null,
            'side',
            'high'
        );
    }

    /**
     * Render Order Details Meta Box
     */
    public static function render_order_meta_box( $post_or_order ) {
        $order = ( $post_or_order instanceof WC_Order ) ? $post_or_order : wc_get_order( $post_or_order->ID );
        if ( ! $order ) {
            return;
        }

        $stats = self::calculate_order_profit( $order );

        $badge_class = $stats['margin'] >= 30 ? 'scogs-badge-green' : ( $stats['margin'] >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );
        ?>
        <div class="scogs-order-summary-card">
            <div class="scogs-metric-row">
                <span class="scogs-metric-label"><?php esc_html_e( 'Order Revenue:', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value"><?php echo wc_price( $stats['revenue'] ); ?></span>
            </div>
            <div class="scogs-metric-row">
                <span class="scogs-metric-label"><?php esc_html_e( 'Cost of Goods (COGS):', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value scogs-cost-text">&minus; <?php echo wc_price( $stats['cost'] ); ?></span>
            </div>
            <div class="scogs-divider"></div>
            <div class="scogs-metric-row scogs-profit-highlight">
                <span class="scogs-metric-label"><?php esc_html_e( 'Net Profit:', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-metric-value scogs-profit-val"><?php echo wc_price( $stats['profit'] ); ?></span>
            </div>
            <div class="scogs-metric-row">
                <span class="scogs-metric-label"><?php esc_html_e( 'Net Margin:', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="scogs-badge <?php echo esc_attr( $badge_class ); ?>">
                    <?php echo esc_html( round( $stats['margin'], 1 ) ); ?>%
                </span>
            </div>
            <div class="scogs-meta-footer">
                <small><?php esc_html_e( 'Automated cost tracking by NexForge AI', 'simple-cost-of-goods-for-woocommerce' ); ?></small>
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
            echo '<strong>' . esc_html__( 'COGS:', 'simple-cost-of-goods-for-woocommerce' ) . '</strong> ' . wc_price( $cost ) . ' &times; ' . esc_html( $qty ) . ' = ' . wc_price( $line_cost );
            echo ' &bull; <strong>' . esc_html__( 'Profit:', 'simple-cost-of-goods-for-woocommerce' ) . '</strong> <span style="color:#007cba; font-weight:600;">' . wc_price( $profit ) . '</span> (' . esc_html( round( $margin, 1 ) ) . '%)';
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
                $new_columns['scogs_order_profit'] = __( 'Net Profit', 'simple-cost-of-goods-for-woocommerce' );
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

        // If missing, calculate now
        if ( '' === $profit || false === $profit ) {
            $stats  = self::calculate_order_profit( $order );
            $profit = $stats['profit'];
            $margin = $stats['margin'];
        }

        $profit = (float) $profit;
        $margin = (float) $margin;

        $badge_class = $margin >= 30 ? 'scogs-badge-green' : ( $margin >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );

        echo '<div class="scogs-order-profit-cell">';
        echo '<span class="scogs-profit-number">' . wc_price( $profit ) . '</span><br>';
        echo '<span class="scogs-badge ' . esc_attr( $badge_class ) . '" style="font-size:10px; padding:1px 6px;">' . esc_html( round( $margin, 1 ) ) . '% margin</span>';
        echo '</div>';
    }
}
