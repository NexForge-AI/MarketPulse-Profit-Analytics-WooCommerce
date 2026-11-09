<?php
/**
 * Core Financial Calculation Engine
 * Implements the exact 5 financial accounting formulas for WooCommerce orders & stores.
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Calculations {

    /**
     * Calculate all financial metrics for a single WooCommerce order
     *
     * @param WC_Order|int $order Order object or ID
     * @param bool         $save  Whether to persist calculated meta to database (set false for read-only reports)
     * @return array Calculated financial metrics
     */
    public static function calculate_order_financials( $order, $save = true ) {
        if ( is_numeric( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return self::get_empty_financials();
        }

        // Fast path: If read-only mode and saved metadata already exists, read directly without re-calculating or saving
        $saved_profit = $order->get_meta( SCOGS_Order::META_ORDER_PROFIT, true );
        $saved_cogs   = $order->get_meta( SCOGS_Order::META_ORDER_COST, true );

        if ( ! $save && '' !== $saved_profit && false !== $saved_profit && '' !== $saved_cogs && false !== $saved_cogs ) {
            $gross_revenue          = (float) $order->get_subtotal();
            $discount_total         = (float) $order->get_discount_total();
            $total_cogs             = (float) $saved_cogs;
            $customer_paid_shipping = (float) $order->get_shipping_total();
            $actual_courier_cost    = (float) $order->get_meta( '_cogs_actual_shipping_cost', true );

            // Dynamically calculate shipping deficit from actual courier cost and customer paid shipping
            $saved_deficit = $order->get_meta( '_cogs_shipping_deficit', true );
            if ( '' !== $saved_deficit && false !== $saved_deficit ) {
                $shipping_deficit = (float) $saved_deficit;
            } elseif ( $actual_courier_cost > $customer_paid_shipping ) {
                $shipping_deficit = $actual_courier_cost - $customer_paid_shipping;
            } else {
                $shipping_deficit = 0.0;
            }

            $saved_gp     = $order->get_meta( '_cogs_gross_profit', true );
            $gross_profit = ( '' !== $saved_gp && false !== $saved_gp ) ? (float) $saved_gp : ( $gross_revenue - $discount_total - $total_cogs );
            $gross_margin = $gross_revenue > 0 ? ( $gross_profit / $gross_revenue ) * 100 : 0.0;
            $gateway_fee  = (float) $order->get_meta( '_cogs_gateway_fee', true );

            // True net profit accounts for fees and courier deficit
            $net_profit = $gross_profit - $gateway_fee - $shipping_deficit;
            $net_margin = $gross_revenue > 0 ? ( $net_profit / $gross_revenue ) * 100 : 0.0;

            return array(
                'gross_revenue'          => $gross_revenue,
                'discount_total'         => $discount_total,
                'total_cogs'             => $total_cogs,
                'gross_profit'           => $gross_profit,
                'gross_margin'           => $gross_margin,
                'gateway_fee'            => $gateway_fee,
                'customer_paid_shipping' => $customer_paid_shipping,
                'actual_courier_cost'    => $actual_courier_cost,
                'shipping_deficit'       => $shipping_deficit,
                'net_profit'             => $net_profit,
                'net_margin'             => $net_margin,
                'order_total'            => (float) $order->get_total(),
            );
        }

        // 1. Gross Revenue = Total product prices sold (before discount/tax/shipping deductions)
        $gross_revenue = 0.0;
        $total_cogs    = 0.0;
        $items         = $order->get_items();

        foreach ( $items as $item_id => $item ) {
            if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
                continue;
            }

            $qty = (int) $item->get_quantity();

            // Product sale price at purchase
            $subtotal       = (float) $item->get_subtotal();
            $gross_revenue += $subtotal;

            // Unit cost snapshot
            $unit_cost = $item->get_meta( SCOGS_Order::META_ITEM_COST, true );
            if ( '' === $unit_cost || false === $unit_cost ) {
                $product   = $item->get_product();
                $unit_cost = $product ? SCOGS_Product::get_product_cost( $product ) : 0.0;
                $item->update_meta_data( SCOGS_Order::META_ITEM_COST, wc_format_decimal( $unit_cost ) );
                $item->save();
            }

            $total_cogs += ( (float) $unit_cost * $qty );
        }

        // Discounts given
        $discount_total = (float) $order->get_discount_total();

        // 2. Gross Profit = Gross Revenue - Discounts - Total COGS
        $gross_profit = $gross_revenue - $discount_total - $total_cogs;
        $gross_margin = $gross_revenue > 0 ? ( $gross_profit / $gross_revenue ) * 100 : 0.0;

        // 3. Payment Gateway Fee Auto-detection
        $payment_method = $order->get_payment_method();
        $order_total    = (float) $order->get_total();
        $gateway_fee    = SCOGS_Gateways::calculate_fee( $payment_method, $order_total );

        // 4. Shipping Deficit / Surplus
        $customer_paid_shipping = (float) $order->get_shipping_total();
        $actual_courier_cost    = $order->get_meta( '_cogs_actual_shipping_cost', true );
        $shipping_deficit       = 0.0;

        if ( '' !== $actual_courier_cost && false !== $actual_courier_cost ) {
            $actual_courier_cost = (float) $actual_courier_cost;
            // If actual courier is greater than customer charge, it's an out-of-pocket deficit
            if ( $actual_courier_cost > $customer_paid_shipping ) {
                $shipping_deficit = $actual_courier_cost - $customer_paid_shipping;
            }
        } else {
            $actual_courier_cost = 0.0;
        }

        // 5. True Net Profit = Gross Profit - Payment Fees - Shipping Deficit
        $net_profit = $gross_profit - $gateway_fee - $shipping_deficit;
        $net_margin = $gross_revenue > 0 ? ( $net_profit / $gross_revenue ) * 100 : 0.0;

        // Persist order metadata only when requested (one-time on checkout/status change)
        if ( $save ) {
            $order->update_meta_data( SCOGS_Order::META_ORDER_COST, wc_format_decimal( $total_cogs ) );
            $order->update_meta_data( '_cogs_gateway_fee', wc_format_decimal( $gateway_fee ) );
            $order->update_meta_data( '_cogs_shipping_deficit', wc_format_decimal( $shipping_deficit ) );
            $order->update_meta_data( SCOGS_Order::META_ORDER_PROFIT, wc_format_decimal( $net_profit ) );
            $order->update_meta_data( SCOGS_Order::META_ORDER_MARGIN, round( $net_margin, 2 ) );
            $order->update_meta_data( '_cogs_gross_profit', wc_format_decimal( $gross_profit ) );
            $order->save();
        }

        return array(
            'gross_revenue'          => $gross_revenue,
            'discount_total'         => $discount_total,
            'total_cogs'             => $total_cogs,
            'gross_profit'           => $gross_profit,
            'gross_margin'           => $gross_margin,
            'gateway_fee'            => $gateway_fee,
            'customer_paid_shipping' => $customer_paid_shipping,
            'actual_courier_cost'    => $actual_courier_cost,
            'shipping_deficit'       => $shipping_deficit,
            'net_profit'             => $net_profit,
            'net_margin'             => $net_margin,
            'order_total'            => $order_total,
        );
    }

    /**
     * Return empty zero metrics structure
     *
     * @return array
     */
    public static function get_empty_financials() {
        return array(
            'gross_revenue'          => 0.0,
            'discount_total'         => 0.0,
            'total_cogs'             => 0.0,
            'gross_profit'           => 0.0,
            'gross_margin'           => 0.0,
            'gateway_fee'            => 0.0,
            'customer_paid_shipping' => 0.0,
            'actual_courier_cost'    => 0.0,
            'shipping_deficit'       => 0.0,
            'net_profit'             => 0.0,
            'net_margin'             => 0.0,
            'order_total'            => 0.0,
        );
    }
}

