<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Simple_Cost_Of_Goods_WooCommerce
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Clean up plugin options and tables
global $wpdb;

delete_option( 'scogs_version' );
delete_option( 'scogs_gateway_fee_rules' );
delete_option( 'scogs_include_shipping' );
delete_option( 'scogs_include_tax' );

// Clean up cached transients
delete_transient( 'scogs_dashboard_stats' );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_scogs_%' OR option_name LIKE '_transient_timeout_scogs_%'" );

// Drop operating expenses table
$table_expenses = $wpdb->prefix . 'scogs_expenses';
$wpdb->query( "DROP TABLE IF EXISTS `{$table_expenses}`" );

