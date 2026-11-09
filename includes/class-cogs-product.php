<?php
/**
 * Product Cost of Goods Fields & Columns
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Product {

    const META_KEY_COST = '_cogs_cost_price';

    /**
     * Initialize product hooks
     */
    public static function init() {
        // Simple product pricing fields
        add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'add_simple_product_cost_field' ) );
        add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_simple_product_cost' ) );

        // Variable product pricing fields
        add_action( 'woocommerce_variation_options_pricing', array( __CLASS__, 'add_variation_cost_field' ), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_cost' ), 10, 2 );

        // Product list table columns
        add_filter( 'manage_edit-product_columns', array( __CLASS__, 'add_product_list_columns' ), 15 );
        add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'render_product_list_columns' ), 10, 2 );

        // Quick Edit support
        add_action( 'quick_edit_custom_box', array( __CLASS__, 'render_quick_edit_field' ), 10, 2 );
        add_action( 'woocommerce_product_quick_edit_save', array( __CLASS__, 'save_quick_edit_field' ) );
    }

    /**
     * Add Cost Price field to Simple Product edit screen
     */
    public static function add_simple_product_cost_field() {
        global $post;

        echo '<div class="options_group scogs-pricing-group">';

        woocommerce_wp_text_input( array(
            'id'          => self::META_KEY_COST,
            'label'       => __( 'Cost Price (COGS)', 'simple-cost-of-goods-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'placeholder' => __( 'e.g. 15.00', 'simple-cost-of-goods-for-woocommerce' ),
            'desc_tip'    => true,
            'description' => __( 'Enter the wholesale purchase or manufacturing cost per unit. Used to calculate actual net profit margins.', 'simple-cost-of-goods-for-woocommerce' ),
            'type'        => 'text',
            'data_type'   => 'price',
        ) );

        // Live margin preview if price exists
        $product = wc_get_product( $post->ID );
        if ( $product ) {
            $cost   = self::get_product_cost( $product );
            $price  = (float) $product->get_price();
            if ( $cost > 0 && $price > 0 ) {
                $profit = $price - $cost;
                $margin = ( $profit / $price ) * 100;
                $badge_class = $margin >= 30 ? 'scogs-badge-green' : ( $margin >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );

                echo '<p class="form-field scogs-live-margin">';
                echo '<label>' . esc_html__( 'Est. Profit Margin', 'simple-cost-of-goods-for-woocommerce' ) . '</label>';
                echo '<span class="scogs-badge ' . esc_attr( $badge_class ) . '">';
                echo wc_price( $profit ) . ' (' . esc_html( round( $margin, 1 ) ) . '%)';
                echo '</span>';
                echo '</p>';
            }
        }

        echo '</div>';
    }

    /**
     * Save Cost Price for Simple Product
     *
     * @param int $post_id Product post ID
     */
    public static function save_simple_product_cost( $post_id ) {
        if ( isset( $_POST[ self::META_KEY_COST ] ) ) {
            $cost = wc_clean( wp_unslash( $_POST[ self::META_KEY_COST ] ) );
            if ( '' === $cost ) {
                delete_post_meta( $post_id, self::META_KEY_COST );
            } else {
                update_post_meta( $post_id, self::META_KEY_COST, wc_format_decimal( $cost ) );
            }
        }
    }

    /**
     * Add Cost Price field inside Variation rows
     *
     * @param int     $loop           Loop counter
     * @param array   $variation_data Variation post data
     * @param WP_Post $variation      Variation post object
     */
    public static function add_variation_cost_field( $loop, $variation_data, $variation ) {
        $cost = get_post_meta( $variation->ID, self::META_KEY_COST, true );

        woocommerce_wp_text_input( array(
            'id'            => self::META_KEY_COST . '[' . $loop . ']',
            'label'         => __( 'Cost Price (COGS)', 'simple-cost-of-goods-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'value'         => $cost,
            'placeholder'   => __( 'e.g. 10.00', 'simple-cost-of-goods-for-woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'Wholesale purchase cost for this specific variation.', 'simple-cost-of-goods-for-woocommerce' ),
            'wrapper_class' => 'form-row form-row-full scogs-variation-cost',
            'type'          => 'text',
            'data_type'     => 'price',
        ) );
    }

    /**
     * Save Cost Price for Variation
     *
     * @param int $variation_id Variation post ID
     * @param int $i            Loop index
     */
    public static function save_variation_cost( $variation_id, $i ) {
        if ( isset( $_POST[ self::META_KEY_COST ][ $i ] ) ) {
            $cost = wc_clean( wp_unslash( $_POST[ self::META_KEY_COST ][ $i ] ) );
            if ( '' === $cost ) {
                delete_post_meta( $variation_id, self::META_KEY_COST );
            } else {
                update_post_meta( $variation_id, self::META_KEY_COST, wc_format_decimal( $cost ) );
            }
        }
    }

    /**
     * Add Cost and Margin columns to WooCommerce Products table
     *
     * @param array $columns Existing product columns
     * @return array Modified columns
     */
    public static function add_product_list_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'price' === $key ) {
                $new_columns['scogs_cost']   = __( 'Cost', 'simple-cost-of-goods-for-woocommerce' );
                $new_columns['scogs_margin'] = __( 'Margin', 'simple-cost-of-goods-for-woocommerce' );
            }
        }

        return $new_columns;
    }

    /**
     * Render product list table column contents
     *
     * @param string $column  Column identifier
     * @param int    $post_id Product post ID
     */
    public static function render_product_list_columns( $column, $post_id ) {
        $product = wc_get_product( $post_id );
        if ( ! $product ) {
            return;
        }

        if ( 'scogs_cost' === $column ) {
            $cost = self::get_product_cost( $product );
            if ( $cost > 0 ) {
                echo '<span class="scogs-cost-text">' . wc_price( $cost ) . '</span>';
            } else {
                echo '<span class="scogs-muted">&mdash;</span>';
            }
        }

        if ( 'scogs_margin' === $column ) {
            $cost  = self::get_product_cost( $product );
            $price = (float) $product->get_price();

            if ( $cost > 0 && $price > 0 ) {
                $profit = $price - $cost;
                $margin = ( $profit / $price ) * 100;
                $badge_class = $margin >= 30 ? 'scogs-badge-green' : ( $margin >= 15 ? 'scogs-badge-amber' : 'scogs-badge-red' );

                echo '<span class="scogs-badge ' . esc_attr( $badge_class ) . '" title="' . esc_attr__( 'Est. Profit: ', 'simple-cost-of-goods-for-woocommerce' ) . wc_price( $profit ) . '">';
                echo esc_html( round( $margin, 1 ) ) . '%';
                echo '</span>';
            } else {
                echo '<span class="scogs-muted">&mdash;</span>';
            }
        }
    }

    /**
     * Render Cost field in Quick Edit box
     *
     * @param string $column_name Column name
     * @param string $post_type   Post type
     */
    public static function render_quick_edit_field( $column_name, $post_type ) {
        if ( 'product' !== $post_type || 'scogs_cost' !== $column_name ) {
            return;
        }
        ?>
        <div class="inline-edit-group scogs-quick-edit-row">
            <label class="alignleft">
                <span class="title"><?php esc_html_e( 'Cost Price', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="<?php echo esc_attr( self::META_KEY_COST ); ?>" class="text wc_input_price" placeholder="<?php esc_attr_e( 'e.g. 15.00', 'simple-cost-of-goods-for-woocommerce' ); ?>" value="">
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Save Quick Edit cost price
     *
     * @param WC_Product $product Product instance
     */
    public static function save_quick_edit_field( $product ) {
        if ( isset( $_REQUEST[ self::META_KEY_COST ] ) ) {
            $cost = wc_clean( wp_unslash( $_REQUEST[ self::META_KEY_COST ] ) );
            if ( '' !== $cost ) {
                $product->update_meta_data( self::META_KEY_COST, wc_format_decimal( $cost ) );
            }
        }
    }

    /**
     * Helper to get cost price of simple or variable product
     *
     * @param WC_Product $product Product instance
     * @return float
     */
    public static function get_product_cost( $product ) {
        if ( ! $product ) {
            return 0.0;
        }

        $cost = $product->get_meta( self::META_KEY_COST, true );
        if ( '' !== $cost && false !== $cost ) {
            return (float) $cost;
        }

        // For variable product without specific parent cost, average child variations
        if ( $product->is_type( 'variable' ) ) {
            $children = $product->get_children();
            if ( ! empty( $children ) ) {
                $costs = array();
                foreach ( $children as $child_id ) {
                    $child_cost = get_post_meta( $child_id, self::META_KEY_COST, true );
                    if ( '' !== $child_cost && false !== $child_cost ) {
                        $costs[] = (float) $child_cost;
                    }
                }
                if ( ! empty( $costs ) ) {
                    return (float) ( array_sum( $costs ) / count( $costs ) );
                }
            }
        }

        return 0.0;
    }
}
