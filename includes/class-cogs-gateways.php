<?php
/**
 * Payment Gateway Fee Auto-Detection & Rule Engine
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Gateways {

    const OPTION_KEY = 'scogs_gateway_fee_rules';

    /**
     * Initialize gateway hooks
     */
    public static function init() {
        // Nothing heavy on init; rules are evaluated on order processing
    }

    /**
     * Get default preset rules for popular mobile banking & international gateways
     *
     * @return array
     */
    public static function get_default_presets() {
        return array(
            'bkash'      => array( 'title' => 'bKash Merchant / PGW', 'percent' => 1.5, 'fixed' => 0.0 ),
            'nagad'      => array( 'title' => 'Nagad Merchant', 'percent' => 1.2, 'fixed' => 0.0 ),
            'rocket'     => array( 'title' => 'Rocket (DBBL)', 'percent' => 1.5, 'fixed' => 0.0 ),
            'sslcommerz' => array( 'title' => 'SSLCommerz Gateway', 'percent' => 2.5, 'fixed' => 0.0 ),
            'shurjopay'  => array( 'title' => 'Shurjopay Gateway', 'percent' => 2.0, 'fixed' => 0.0 ),
            'amarpay'    => array( 'title' => 'Aamarpay', 'percent' => 2.2, 'fixed' => 0.0 ),
            'stripe'     => array( 'title' => 'Stripe Credit Cards', 'percent' => 2.9, 'fixed' => 0.30 ),
            'paypal'     => array( 'title' => 'PayPal Express', 'percent' => 3.49, 'fixed' => 0.49 ),
            'cod'        => array( 'title' => 'Cash on Delivery (COD)', 'percent' => 0.0, 'fixed' => 0.0 ),
            'bacs'       => array( 'title' => 'Direct Bank Transfer', 'percent' => 0.0, 'fixed' => 0.0 ),
            'cheque'     => array( 'title' => 'Check Payments', 'percent' => 0.0, 'fixed' => 0.0 ),
        );
    }

    /**
     * Get configured gateway rules with smart defaults
     *
     * @return array
     */
    public static function get_rules() {
        $saved    = get_option( self::OPTION_KEY, array() );
        $defaults = self::get_default_presets();
        $rules    = array();

        // 1. Fill from default presets
        foreach ( $defaults as $key => $preset ) {
            $rules[ $key ] = array(
                'percent' => isset( $saved[ $key ]['percent'] ) ? (float) $saved[ $key ]['percent'] : (float) $preset['percent'],
                'fixed'   => isset( $saved[ $key ]['fixed'] ) ? (float) $saved[ $key ]['fixed'] : (float) $preset['fixed'],
            );
        }

        // 2. Include any additional custom rules saved by merchant
        if ( is_array( $saved ) ) {
            foreach ( $saved as $key => $val ) {
                if ( ! isset( $rules[ $key ] ) ) {
                    $rules[ $key ] = array(
                        'percent' => isset( $val['percent'] ) ? (float) $val['percent'] : 0.0,
                        'fixed'   => isset( $val['fixed'] ) ? (float) $val['fixed'] : 0.0,
                    );
                }
            }
        }

        return $rules;
    }

    /**
     * Calculate transaction fee for a specific payment method and order amount
     *
     * @param string $payment_method Gateway slug (e.g. 'stripe', 'bkash', 'cod')
     * @param float  $order_total    Total charged amount
     * @return float
     */
    public static function calculate_fee( $payment_method, $order_total ) {
        if ( empty( $payment_method ) || $order_total <= 0 ) {
            return 0.0;
        }

        $rules = self::get_rules();
        $fee   = 0.0;

        // Normalize gateway key (handle variations like 'woocommerce_bkash' -> 'bkash')
        $matched_key = $payment_method;
        foreach ( array_keys( $rules ) as $rule_key ) {
            if ( stripos( $payment_method, $rule_key ) !== false ) {
                $matched_key = $rule_key;
                break;
            }
        }

        if ( isset( $rules[ $matched_key ] ) ) {
            $percent = isset( $rules[ $matched_key ]['percent'] ) ? (float) $rules[ $matched_key ]['percent'] : 0.0;
            $fixed   = isset( $rules[ $matched_key ]['fixed'] ) ? (float) $rules[ $matched_key ]['fixed'] : 0.0;

            $fee = ( $order_total * ( $percent / 100 ) ) + $fixed;
        }

        return round( max( 0.0, $fee ), 2 );
    }

    /**
     * Save rules from admin form (merging cleanly without dropping presets)
     *
     * @param array $posted_rules
     */
    public static function save_rules( $posted_rules ) {
        $existing = self::get_rules();

        if ( is_array( $posted_rules ) ) {
            foreach ( $posted_rules as $gateway_id => $values ) {
                $clean_key = sanitize_key( $gateway_id );
                if ( empty( $clean_key ) ) {
                    continue;
                }
                $existing[ $clean_key ] = array(
                    'percent' => isset( $values['percent'] ) ? (float) wc_format_decimal( $values['percent'] ) : 0.0,
                    'fixed'   => isset( $values['fixed'] ) ? (float) wc_format_decimal( $values['fixed'] ) : 0.0,
                );
            }
        }

        update_option( self::OPTION_KEY, $existing );
    }

    /**
     * Save a single custom gateway rule
     *
     * @param string $gateway_id
     * @param float  $percent
     * @param float  $fixed
     */
    public static function save_single_rule( $gateway_id, $percent, $fixed ) {
        $clean_key = sanitize_key( $gateway_id );
        if ( empty( $clean_key ) ) {
            return;
        }

        $rules = self::get_rules();
        $rules[ $clean_key ] = array(
            'percent' => (float) wc_format_decimal( $percent ),
            'fixed'   => (float) wc_format_decimal( $fixed ),
        );

        update_option( self::OPTION_KEY, $rules );
    }

    /**
     * Delete a custom gateway rule
     *
     * @param string $gateway_id
     */
    public static function delete_rule( $gateway_id ) {
        $clean_key = sanitize_key( $gateway_id );
        $rules     = get_option( self::OPTION_KEY, array() );

        if ( isset( $rules[ $clean_key ] ) ) {
            unset( $rules[ $clean_key ] );
            update_option( self::OPTION_KEY, $rules );
        }
    }

    /**
     * Get list of all installed payment gateways in WooCommerce
     *
     * @return array
     */
    public static function get_available_gateways() {
        if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
            return array();
        }

        $all_gateways = WC()->payment_gateways()->payment_gateways();
        $list         = array();

        foreach ( $all_gateways as $gateway_id => $gateway ) {
            $list[ $gateway_id ] = array(
                'title'       => ! empty( $gateway->title ) ? $gateway->title : $gateway->get_method_title(),
                'enabled'     => 'yes' === $gateway->enabled,
                'description' => $gateway->get_method_description(),
            );
        }

        return $list;
    }
}

