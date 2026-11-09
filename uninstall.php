<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Simple_Cost_Of_Goods_WooCommerce
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Clean up plugin options
delete_option( 'scogs_include_shipping' );
delete_option( 'scogs_include_tax' );
delete_transient( 'scogs_dashboard_stats' );

