<?php
/**
 * Standalone Unit Tests for Simple Cost of Goods Calculation Engine
 * Author: NexForge AI
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Mock WordPress / WooCommerce helper functions for standalone execution
if ( ! function_exists( 'sanitize_key' ) ) {
    function sanitize_key( $key ) {
        return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
    }
}

if ( ! function_exists( 'wc_format_decimal' ) ) {
    function wc_format_decimal( $number, $dp = false, $trim_zeros = false ) {
        return (string) floatval( $number );
    }
}

if ( ! function_exists( 'get_option' ) ) {
    $GLOBALS['_mock_options'] = array();
    function get_option( $key, $default = false ) {
        return isset( $GLOBALS['_mock_options'][ $key ] ) ? $GLOBALS['_mock_options'][ $key ] : $default;
    }
    function update_option( $key, $value ) {
        $GLOBALS['_mock_options'][ $key ] = $value;
        return true;
    }
}

if ( ! class_exists( 'SCOGS_Order' ) ) {
    class SCOGS_Order {
        const META_ITEM_COST    = '_cogs_item_cost';
        const META_ORDER_COST   = '_cogs_order_cost';
        const META_ORDER_PROFIT = '_cogs_order_profit';
        const META_ORDER_MARGIN = '_cogs_order_margin';
    }
}

if ( ! function_exists( 'wc_get_order' ) ) {
    function wc_get_order( $id ) {
        return false;
    }
}

require_once dirname( __DIR__ ) . '/includes/class-cogs-gateways.php';
require_once dirname( __DIR__ ) . '/includes/class-cogs-calculations.php';

class SCOGS_Calculation_Tests {

    private $passed = 0;
    private $failed = 0;

    public function run() {
        echo "========================================================\n";
        echo " Running Simple Cost of Goods Core Math Engine Tests\n";
        echo "========================================================\n\n";

        $this->test_gateway_default_rules();
        $this->test_bkash_fee_calculation();
        $this->test_stripe_fee_calculation();
        $this->test_cod_zero_fee();
        $this->test_gross_revenue_formula();
        $this->test_gross_profit_formula();
        $this->test_shipping_deficit_calculation();
        $this->test_true_net_profit_waterfall();
        $this->test_refund_object_safety();

        echo "\n--------------------------------------------------------\n";
        echo "Test Results: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "--------------------------------------------------------\n";

        if ( $this->failed > 0 ) {
            exit( 1 );
        }
    }

    private function assert_equals( $expected, $actual, $test_name ) {
        if ( abs( $expected - $actual ) < 0.001 ) {
            echo " [PASS] {$test_name}\n";
            $this->passed++;
        } else {
            echo " [FAIL] {$test_name}: Expected {$expected}, got {$actual}\n";
            $this->failed++;
        }
    }

    private function test_gateway_default_rules() {
        $rules = SCOGS_Gateways::get_rules();
        $this->assert_equals( 1.5, $rules['bkash']['percent'], 'bKash default rate is 1.5%' );
        $this->assert_equals( 2.9, $rules['stripe']['percent'], 'Stripe default rate is 2.9%' );
        $this->assert_equals( 0.30, $rules['stripe']['fixed'], 'Stripe default fixed fee is $0.30' );
    }

    private function test_bkash_fee_calculation() {
        // Order of 1,000 BDT with bKash (1.5% + 0) = 15.00 BDT
        $fee = SCOGS_Gateways::calculate_fee( 'bkash', 1000.0 );
        $this->assert_equals( 15.0, $fee, 'bKash fee for 1,000 BDT order' );
    }

    private function test_stripe_fee_calculation() {
        // Order of $100 with Stripe (2.9% + $0.30) = $2.90 + $0.30 = $3.20
        $fee = SCOGS_Gateways::calculate_fee( 'stripe', 100.0 );
        $this->assert_equals( 3.20, $fee, 'Stripe fee for $100 order' );
    }

    private function test_cod_zero_fee() {
        $fee = SCOGS_Gateways::calculate_fee( 'cod', 500.0 );
        $this->assert_equals( 0.0, $fee, 'Cash on Delivery has 0 gateway fee' );
    }

    private function test_gross_revenue_formula() {
        $qty   = 3;
        $price = 40.0;
        $gross_revenue = $price * $qty;
        $this->assert_equals( 120.0, $gross_revenue, 'Gross Revenue = Price * Qty ($40 * 3 = $120)' );
    }

    private function test_gross_profit_formula() {
        $revenue   = 120.0;
        $discounts = 10.0;
        $cogs      = 50.0;
        $gross_profit = $revenue - $discounts - $cogs;
        $this->assert_equals( 60.0, $gross_profit, 'Gross Profit = Revenue ($120) - Discounts ($10) - COGS ($50) = $60' );
    }

    private function test_shipping_deficit_calculation() {
        // Customer paid $8, Courier invoice is $10 -> deficit is $2
        $customer_paid = 8.0;
        $courier_cost  = 10.0;
        $deficit       = $courier_cost > $customer_paid ? ( $courier_cost - $customer_paid ) : 0.0;
        $this->assert_equals( 2.0, $deficit, 'Shipping Deficit: $10 courier - $8 charged = $2' );

        // Customer paid $12, Courier cost is $10 -> deficit is 0
        $customer_paid = 12.0;
        $courier_cost  = 10.0;
        $deficit       = $courier_cost > $customer_paid ? ( $courier_cost - $customer_paid ) : 0.0;
        $this->assert_equals( 0.0, $deficit, 'Shipping Surplus: Deficit is 0 when courier is less than charged' );
    }

    private function test_true_net_profit_waterfall() {
        // Gross Revenue: $120
        // COGS: -$50
        // Gross Profit: $70
        // Gateway Fee (Stripe): -$3.78 (on $120)
        // Shipping Deficit: -$2.00
        // True Net Profit = $70 - $3.78 - $2.00 = $64.22
        $revenue          = 120.0;
        $cogs             = 50.0;
        $discounts        = 0.0;
        $gross_profit     = $revenue - $discounts - $cogs;
        $gateway_fee      = SCOGS_Gateways::calculate_fee( 'stripe', $revenue ); // 2.9% of 120 + 0.30 = 3.48 + 0.30 = 3.78
        $shipping_deficit = 2.0;

        $true_net_profit  = $gross_profit - $gateway_fee - $shipping_deficit;
        $net_margin       = ( $true_net_profit / $revenue ) * 100;

        $this->assert_equals( 64.22, $true_net_profit, 'True Net Profit Waterfall: $120 rev - $50 cogs - $3.78 fee - $2 deficit = $64.22' );
        $this->assert_equals( 53.517, round( $net_margin, 3 ), 'Net Profit Margin % = (64.22 / 120) * 100 = 53.517%' );
    }

    private function test_refund_object_safety() {
        // Passing null or non-order object safely returns zero metrics without throwing fatal error
        $empty = SCOGS_Calculations::calculate_order_financials( null, false );
        $this->assert_equals( 0.0, $empty['gross_revenue'], 'Safety: Non-order object returns 0 revenue safely' );
        $this->assert_equals( 0.0, $empty['gross_profit'], 'Safety: Non-order object returns 0 profit safely' );
    }
}

$tests = new SCOGS_Calculation_Tests();
$tests->run();

