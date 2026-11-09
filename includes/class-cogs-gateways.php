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
     * Get configured gateway rules with smart defaults
     *
     * @return array
     */
    public static function get_rules() {
        $rules = get_option( self::OPTION_KEY, array() );

        // If empty, supply sensible defaults for common gateways
        if ( empty( $rules ) ) {
            $rules = array(
                'bkash'       => array( 'percent' => 1.5, 'fixed' => 0.0 ),
                'nagad'       => array( 'percent' => 1.2, 'fixed' => 0.0 ),
                'rocket'      => array( 'percent' => 1.5, 'fixed' => 0.0 ),
                'sslcommerz'  => array( 'percent' => 2.5, 'fixed' => 0.0 ),
                'shurjopay'   => array( 'percent' => 2.0, 'fixed' => 0.0 ),
                'stripe'      => array( 'percent' => 2.9, 'fixed' => 0.30 ),
                'paypal'      => array( 'percent' => 3.49, 'fixed' => 0.49 ),
                'cod'         => array( 'percent' => 0.0, 'fixed' => 0.0 ),
                'bacs'        => array( 'percent' => 0.0, 'fixed' => 0.0 ),
            );
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
     * Save rules from admin form
     *
     * @param array $posted_rules
     */
    public static function save_rules( $posted_rules ) {
        $clean_rules = array();

        if ( is_array( $posted_rules ) ) {
            foreach ( $posted_rules as $gateway_id => $values ) {
                $clean_rules[ sanitize_key( $gateway_id ) ] = array(
                    'percent' => isset( $values['percent'] ) ? (float) wc_format_decimal( $values['percent'] ) : 0.0,
                    'fixed'   => isset( $values['fixed'] ) ? (float) wc_format_decimal( $values['fixed'] ) : 0.0,
                );
            }
        }

        update_option( self::OPTION_KEY, $clean_rules );
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
