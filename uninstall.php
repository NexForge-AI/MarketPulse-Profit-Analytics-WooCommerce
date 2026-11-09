<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Simple_Cost_Of_Goods_WooCommerce
 */

defined( 'ABSPATH' ) || defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Clean up plugin options and tables
global $wpdb;

delete_option( 'scogs_version' );
delete_option( 'scogs_gateway_fee_rules' );
delete_option( 'scogs_include_shipping' );
delete_option( 'scogs_include_tax' );

// Clean up cached transients
delete_transient( 'scogs_dashboard_stats' );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like( '_transient_scogs_' ) . '%',
        $wpdb->esc_like( '_transient_timeout_scogs_' ) . '%'
    )
);

// Drop operating expenses table
$scogs_table_expenses = esc_sql( $wpdb->prefix . 'scogs_expenses' );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
$wpdb->query( "DROP TABLE IF EXISTS `{$scogs_table_expenses}`" );

