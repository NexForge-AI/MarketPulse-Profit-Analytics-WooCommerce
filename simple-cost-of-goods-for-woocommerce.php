<?php
/**
 * Plugin Name:       Simple Cost of Goods & Profit Tracker for WooCommerce
 * Plugin URI:        https://usemarketpulse.com/plugins/simple-cost-of-goods
 * Description:       Lightweight, high-performance Cost of Goods Sold (COGS) and net profit margin tracker for WooCommerce. Track real profit per product and order with zero server bloat and 100% HPOS compatibility.
 * Version:           1.0.0
 * Author:            NexForge AI
 * Author URI:        https://usemarketpulse.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-cost-of-goods-for-woocommerce
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 * WC tested up to:   9.4
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants
define( 'SCOGS_VERSION', '1.0.0' );
define( 'SCOGS_FILE', __FILE__ );
define( 'SCOGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SCOGS_URL', plugin_dir_url( __FILE__ ) );
define( 'SCOGS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare High-Performance Order Storage (HPOS) Compatibility
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/**
 * Main Plugin Class
 */
final class Simple_Cost_Of_Goods_WooCommerce {

    /**
     * Singleton instance
     *
     * @var Simple_Cost_Of_Goods_WooCommerce
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return Simple_Cost_Of_Goods_WooCommerce
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize plugin once all plugins are loaded
     */
    public function init() {
        // Verify WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        // Load translations
        load_plugin_textdomain(
            'simple-cost-of-goods-for-woocommerce',
            false,
            dirname( SCOGS_BASENAME ) . '/languages'
        );

        // Load modules
        $this->includes();

        // Enqueue admin assets
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Add action links in plugins table
        add_filter( 'plugin_action_links_' . SCOGS_BASENAME, array( $this, 'plugin_action_links' ) );
    }

    /**
     * Include required core classes
     */
    private function includes() {
        require_once SCOGS_PATH . 'includes/class-cogs-product.php';
        require_once SCOGS_PATH . 'includes/class-cogs-order.php';
        require_once SCOGS_PATH . 'includes/class-cogs-dashboard.php';
        require_once SCOGS_PATH . 'includes/class-cogs-settings.php';

        // Initialize components
        SCOGS_Product::init();
        SCOGS_Order::init();
        SCOGS_Dashboard::init();
        SCOGS_Settings::init();
    }

    /**
     * Enqueue admin styles and scripts only on WooCommerce relevant pages
     *
     * @param string $hook Admin page hook
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        // Only load on product, order, dashboard, and settings screens
        $valid_screens = array(
            'product',
            'edit-product',
            'shop_order',
            'edit-shop_order',
            'woocommerce_page_wc-orders',
            'dashboard',
            'woocommerce_page_wc-settings'
        );

        $is_valid = in_array( $screen->id, $valid_screens, true ) || in_array( $screen->post_type, array( 'product', 'shop_order' ), true );

        if ( $is_valid ) {
            wp_enqueue_style(
                'scogs-admin-style',
                SCOGS_URL . 'assets/css/admin-style.css',
                array(),
                SCOGS_VERSION
            );
        }
    }

    /**
     * Add settings & author links to plugins list table
     *
     * @param array $links Existing action links
     * @return array Modified action links
     */
    public function plugin_action_links( $links ) {
        $custom_links = array(
            '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=products&section=scogs' ) ) . '">' . __( 'Settings', 'simple-cost-of-goods-for-woocommerce' ) . '</a>',
            '<a href="https://usemarketpulse.com" target="_blank" style="font-weight:600; color:#2271b1;">' . __( 'NexForge AI', 'simple-cost-of-goods-for-woocommerce' ) . '</a>'
        );
        return array_merge( $custom_links, $links );
    }

    /**
     * Admin notice if WooCommerce is inactive
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php esc_html_e( 'Simple Cost of Goods & Profit Tracker', 'simple-cost-of-goods-for-woocommerce' ); ?></strong> <?php esc_html_e( 'requires WooCommerce to be installed and active.', 'simple-cost-of-goods-for-woocommerce' ); ?></p>
        </div>
        <?php
    }
}

/**
 * Return main instance of plugin
 *
 * @return Simple_Cost_Of_Goods_WooCommerce
 */
function scogs() {
    return Simple_Cost_Of_Goods_WooCommerce::instance();
}

// Kick off the plugin
scogs();
