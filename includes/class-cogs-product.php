<?php
/**
 * Product Cost of Goods & Supplier Fields & Columns
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Product {

    const META_KEY_COST     = '_cogs_cost_price';
    const META_KEY_SUPPLIER = '_cogs_supplier';

    /**
     * Initialize product hooks
     */
    public static function init() {
        // Simple product pricing & supplier fields
        add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'add_simple_product_cost_field' ) );
        add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_simple_product_meta' ) );

        // Variable product pricing & supplier fields
        add_action( 'woocommerce_variation_options_pricing', array( __CLASS__, 'add_variation_cost_field' ), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_meta' ), 10, 2 );

        // Product list table columns
        add_filter( 'manage_edit-product_columns', array( __CLASS__, 'add_product_list_columns' ), 15 );
        add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'render_product_list_columns' ), 10, 2 );

        // Quick Edit support
        add_action( 'quick_edit_custom_box', array( __CLASS__, 'render_quick_edit_field' ), 10, 2 );
        add_action( 'woocommerce_product_quick_edit_save', array( __CLASS__, 'save_quick_edit_field' ) );

        // Supplier filter on products table
        add_action( 'restrict_manage_posts', array( __CLASS__, 'render_supplier_filter' ) );
        add_filter( 'parse_query', array( __CLASS__, 'filter_products_by_supplier' ) );
    }

    /**
     * Add Cost Price & Supplier field to Simple Product edit screen
     */
    public static function add_simple_product_cost_field() {
        global $post;

        echo '<div class="options_group scogs-pricing-group">';

        // 1. Cost Price (COGS)
        woocommerce_wp_text_input( array(
            'id'          => self::META_KEY_COST,
            'label'       => __( 'Cost Price (COGS)', 'simple-cost-of-goods-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'placeholder' => __( 'e.g. 15.00', 'simple-cost-of-goods-for-woocommerce' ),
            'desc_tip'    => true,
            'description' => __( 'Enter the wholesale purchase or manufacturing cost per unit. Used to calculate actual net profit margins.', 'simple-cost-of-goods-for-woocommerce' ),
            'type'        => 'text',
            'data_type'   => 'price',
        ) );

        // 2. Supplier
        woocommerce_wp_text_input( array(
            'id'          => self::META_KEY_SUPPLIER,
            'label'       => __( 'Supplier / Vendor', 'simple-cost-of-goods-for-woocommerce' ),
            'placeholder' => __( 'e.g. Acme Wholesale Ltd.', 'simple-cost-of-goods-for-woocommerce' ),
            'desc_tip'    => true,
            'description' => __( 'Track which vendor or distributor supplies this item.', 'simple-cost-of-goods-for-woocommerce' ),
            'type'        => 'text',
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
                echo '<label>' . esc_html__( 'Est. Gross Margin', 'simple-cost-of-goods-for-woocommerce' ) . '</label>';
                echo '<span class="scogs-badge ' . esc_attr( $badge_class ) . '">';
                echo wc_price( $profit ) . ' (' . esc_html( round( $margin, 1 ) ) . '%)';
                echo '</span>';
                echo '</p>';
            }
        }

        echo '</div>';
    }

    /**
     * Save Cost Price & Supplier for Simple Product
     *
     * @param int $post_id
     */
    public static function save_simple_product_meta( $post_id ) {
        // Save Cost
        if ( isset( $_POST[ self::META_KEY_COST ] ) ) {
            $cost = wc_clean( wp_unslash( $_POST[ self::META_KEY_COST ] ) );
            if ( '' === $cost ) {
                delete_post_meta( $post_id, self::META_KEY_COST );
            } else {
                update_post_meta( $post_id, self::META_KEY_COST, wc_format_decimal( $cost ) );
            }
        }

        // Save Supplier
        if ( isset( $_POST[ self::META_KEY_SUPPLIER ] ) ) {
            $supplier = sanitize_text_field( wp_unslash( $_POST[ self::META_KEY_SUPPLIER ] ) );
            if ( '' === $supplier ) {
                delete_post_meta( $post_id, self::META_KEY_SUPPLIER );
            } else {
                update_post_meta( $post_id, self::META_KEY_SUPPLIER, $supplier );
            }
        }
    }

    /**
     * Add Cost Price & Supplier inside Variation rows
     */
    public static function add_variation_cost_field( $loop, $variation_data, $variation ) {
        $cost     = get_post_meta( $variation->ID, self::META_KEY_COST, true );
        $supplier = get_post_meta( $variation->ID, self::META_KEY_SUPPLIER, true );

        echo '<div class="form-row form-row-full scogs-variation-fields">';

        woocommerce_wp_text_input( array(
            'id'            => self::META_KEY_COST . '[' . $loop . ']',
            'label'         => __( 'Cost Price (COGS)', 'simple-cost-of-goods-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'value'         => $cost,
            'placeholder'   => __( 'e.g. 10.00', 'simple-cost-of-goods-for-woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'Wholesale purchase cost for this specific variation.', 'simple-cost-of-goods-for-woocommerce' ),
            'wrapper_class' => 'form-row form-row-first',
            'type'          => 'text',
            'data_type'     => 'price',
        ) );

        woocommerce_wp_text_input( array(
            'id'            => self::META_KEY_SUPPLIER . '[' . $loop . ']',
            'label'         => __( 'Supplier', 'simple-cost-of-goods-for-woocommerce' ),
            'value'         => $supplier,
            'placeholder'   => __( 'e.g. Acme Ltd.', 'simple-cost-of-goods-for-woocommerce' ),
            'wrapper_class' => 'form-row form-row-last',
            'type'          => 'text',
        ) );

        echo '</div>';
    }

    /**
     * Save Cost Price & Supplier for Variation
     */
    public static function save_variation_meta( $variation_id, $i ) {
        if ( isset( $_POST[ self::META_KEY_COST ][ $i ] ) ) {
            $cost = wc_clean( wp_unslash( $_POST[ self::META_KEY_COST ][ $i ] ) );
            if ( '' === $cost ) {
                delete_post_meta( $variation_id, self::META_KEY_COST );
            } else {
                update_post_meta( $variation_id, self::META_KEY_COST, wc_format_decimal( $cost ) );
            }
        }

        if ( isset( $_POST[ self::META_KEY_SUPPLIER ][ $i ] ) ) {
            $supplier = sanitize_text_field( wp_unslash( $_POST[ self::META_KEY_SUPPLIER ][ $i ] ) );
            if ( '' === $supplier ) {
                delete_post_meta( $variation_id, self::META_KEY_SUPPLIER );
            } else {
                update_post_meta( $variation_id, self::META_KEY_SUPPLIER, $supplier );
            }
        }
    }

    /**
     * Add Cost, Margin, and Supplier columns to WooCommerce Products table
     */
    public static function add_product_list_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'price' === $key ) {
                $new_columns['scogs_cost']     = __( 'Cost', 'simple-cost-of-goods-for-woocommerce' );
                $new_columns['scogs_margin']   = __( 'Margin', 'simple-cost-of-goods-for-woocommerce' );
                $new_columns['scogs_supplier'] = __( 'Supplier', 'simple-cost-of-goods-for-woocommerce' );
            }
        }

        return $new_columns;
    }

    /**
     * Render product list table column contents
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

                echo '<span class="scogs-badge ' . esc_attr( $badge_class ) . '" title="' . esc_attr__( 'Gross Profit: ', 'simple-cost-of-goods-for-woocommerce' ) . wc_price( $profit ) . '">';
                echo esc_html( round( $margin, 1 ) ) . '%';
                echo '</span>';
            } else {
                echo '<span class="scogs-muted">&mdash;</span>';
            }
        }

        if ( 'scogs_supplier' === $column ) {
            $supplier = $product->get_meta( self::META_KEY_SUPPLIER, true );
            if ( ! empty( $supplier ) ) {
                echo '<span class="scogs-supplier-tag">' . esc_html( $supplier ) . '</span>';
            } else {
                echo '<span class="scogs-muted">&mdash;</span>';
            }
        }
    }

    /**
     * Render Cost & Supplier fields in Quick Edit box
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
            <label class="alignleft" style="margin-left:12px;">
                <span class="title"><?php esc_html_e( 'Supplier', 'simple-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="<?php echo esc_attr( self::META_KEY_SUPPLIER ); ?>" class="text" placeholder="<?php esc_attr_e( 'Supplier name', 'simple-cost-of-goods-for-woocommerce' ); ?>" value="">
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Save Quick Edit fields
     */
    public static function save_quick_edit_field( $product ) {
        if ( isset( $_REQUEST[ self::META_KEY_COST ] ) ) {
            $cost = wc_clean( wp_unslash( $_REQUEST[ self::META_KEY_COST ] ) );
            if ( '' !== $cost ) {
                $product->update_meta_data( self::META_KEY_COST, wc_format_decimal( $cost ) );
            }
        }

        if ( isset( $_REQUEST[ self::META_KEY_SUPPLIER ] ) ) {
            $supplier = sanitize_text_field( wp_unslash( $_REQUEST[ self::META_KEY_SUPPLIER ] ) );
            if ( '' !== $supplier ) {
                $product->update_meta_data( self::META_KEY_SUPPLIER, $supplier );
            }
        }
    }

    /**
     * Render dropdown filter for Supplier above products table
     */
    public static function render_supplier_filter() {
        global $typenow, $wpdb;

        if ( 'product' !== $typenow ) {
            return;
        }

        $suppliers = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value ASC",
            self::META_KEY_SUPPLIER
        ) );

        if ( empty( $suppliers ) ) {
            return;
        }

        $current_supplier = isset( $_GET['filter_supplier'] ) ? sanitize_text_field( $_GET['filter_supplier'] ) : '';
        ?>
        <select name="filter_supplier" id="filter_supplier">
            <option value=""><?php esc_html_e( 'All Suppliers', 'simple-cost-of-goods-for-woocommerce' ); ?></option>
            <?php foreach ( $suppliers as $sup ) : ?>
                <option value="<?php echo esc_attr( $sup ); ?>" <?php selected( $current_supplier, $sup ); ?>>
                    <?php echo esc_html( $sup ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Filter query by supplier
     */
    public static function filter_products_by_supplier( $query ) {
        global $pagenow, $typenow;

        if ( 'edit.php' === $pagenow && 'product' === $typenow && ! empty( $_GET['filter_supplier'] ) ) {
            $supplier = sanitize_text_field( $_GET['filter_supplier'] );
            $query->query_vars['meta_query'][] = array(
                'key'     => self::META_KEY_SUPPLIER,
                'value'   => $supplier,
                'compare' => '=',
            );
        }
    }

    /**
     * Helper to get cost price of simple or variable product
     */
    public static function get_product_cost( $product ) {
        if ( ! $product ) {
            return 0.0;
        }

        $cost = $product->get_meta( self::META_KEY_COST, true );
        if ( '' !== $cost && false !== $cost ) {
            return (float) $cost;
        }

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
