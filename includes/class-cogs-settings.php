<?php
/**
 * WooCommerce Settings Integration for Cost of Goods
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Settings {

    /**
     * Initialize settings hooks
     */
    public static function init() {
        add_filter( 'woocommerce_get_sections_products', array( __CLASS__, 'add_settings_section' ) );
        add_filter( 'woocommerce_get_settings_products', array( __CLASS__, 'get_settings' ), 10, 2 );
    }

    /**
     * Add section tab under WooCommerce > Settings > Products
     */
    public static function add_settings_section( $sections ) {
        $sections['scogs'] = __( 'Cost of Goods (COGS)', 'marketpulse-cogs-profit-for-woocommerce' );
        return $sections;
    }

    /**
     * Get settings array
     */
    public static function get_settings( $settings, $current_section ) {
        if ( 'scogs' !== $current_section ) {
            return $settings;
        }

        return array(
            array(
                'title' => __( 'Cost of Goods & Profit Tracking Configuration', 'marketpulse-cogs-profit-for-woocommerce' ),
                'desc'  => __( 'Configure how net profit and margins are calculated across your store orders. Designed with zero database bloat by NexForge AI.', 'marketpulse-cogs-profit-for-woocommerce' ),
                'type'  => 'title',
                'id'    => 'scogs_settings_title',
            ),
            array(
                'title'   => __( 'Include Shipping in Profit', 'marketpulse-cogs-profit-for-woocommerce' ),
                'desc'    => __( 'Include customer-paid shipping charges in net revenue calculations.', 'marketpulse-cogs-profit-for-woocommerce' ),
                'id'      => 'scogs_include_shipping',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'title'   => __( 'Include Tax in Profit', 'marketpulse-cogs-profit-for-woocommerce' ),
                'desc'    => __( 'Include customer-paid taxes in net revenue calculations.', 'marketpulse-cogs-profit-for-woocommerce' ),
                'id'      => 'scogs_include_tax',
                'default' => 'no',
                'type'    => 'checkbox',
            ),
            array(
                'type' => 'sectionend',
                'id'   => 'scogs_settings_end',
            ),
        );
    }
}

