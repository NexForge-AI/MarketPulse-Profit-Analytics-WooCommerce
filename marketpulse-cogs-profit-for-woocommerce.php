<?php
/**
 * Plugin Name:       MarketPulse COGS & Profit for WooCommerce
 * Plugin URI:        https://usemarketpulse.com/plugins/simple-cost-of-goods
 * Description:       Lightweight, high-performance Cost of Goods Sold (COGS) and true net profit margin tracker for WooCommerce by NexForge AI, creators of MarketPulse. Track real profit per product and order with operating expenses, gateway fee auto-detection, and 100% HPOS compatibility.
 * Version:           1.0.0
 * Author:            NexForge AI
 * Author URI:        https://usemarketpulse.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       marketpulse-cogs-profit-for-woocommerce
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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party hook provided by WooCommerce core.
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/**
 * Main Plugin Class
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
final class MarketPulse_COGS_Profit_For_WooCommerce {

    /**
     * Singleton instance
     *
     * @var MarketPulse_COGS_Profit_For_WooCommerce
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return MarketPulse_COGS_Profit_For_WooCommerce
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
        register_activation_hook( SCOGS_FILE, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Plugin activation routine
     */
    public function activate() {
        require_once SCOGS_PATH . 'includes/class-cogs-expenses.php';
        SCOGS_Expenses::create_table();
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
        require_once SCOGS_PATH . 'includes/class-cogs-calculations.php';
        require_once SCOGS_PATH . 'includes/class-cogs-gateways.php';
        require_once SCOGS_PATH . 'includes/class-cogs-expenses.php';
        require_once SCOGS_PATH . 'includes/class-cogs-product.php';
        require_once SCOGS_PATH . 'includes/class-cogs-order.php';
        require_once SCOGS_PATH . 'includes/class-cogs-csv.php';
        require_once SCOGS_PATH . 'includes/class-cogs-reports.php';
        require_once SCOGS_PATH . 'includes/class-cogs-dashboard.php';
        require_once SCOGS_PATH . 'includes/class-cogs-settings.php';

        // Initialize components
        SCOGS_Gateways::init();
        SCOGS_Expenses::init();
        SCOGS_Product::init();
        SCOGS_Order::init();
        SCOGS_CSV::init();
        SCOGS_Reports::init();
        SCOGS_Dashboard::init();
        SCOGS_Settings::init();

        // Auto-create database tables on upgrade or copy without needing re-activation
        $installed_ver = get_option( 'scogs_version', '0' );
        if ( version_compare( $installed_ver, SCOGS_VERSION, '<' ) ) {
            SCOGS_Expenses::create_table();
            update_option( 'scogs_version', SCOGS_VERSION );
        }
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

        // Only load on product, order, dashboard, analytics and settings screens
        $valid_screens = array(
            'product',
            'edit-product',
            'shop_order',
            'edit-shop_order',
            'woocommerce_page_wc-orders',
            'dashboard',
            'woocommerce_page_wc-settings',
            'woocommerce_page_scogs-analytics'
        );

        $is_valid = in_array( $screen->id, $valid_screens, true ) || in_array( $screen->post_type, array( 'product', 'shop_order' ), true );

        if ( $is_valid ) {
            $css_ver = file_exists( SCOGS_PATH . 'assets/css/admin-style.css' ) ? filemtime( SCOGS_PATH . 'assets/css/admin-style.css' ) : SCOGS_VERSION;
            $js_ver  = file_exists( SCOGS_PATH . 'assets/js/admin.js' ) ? filemtime( SCOGS_PATH . 'assets/js/admin.js' ) : SCOGS_VERSION;

            wp_enqueue_style(
                'scogs-admin-style',
                SCOGS_URL . 'assets/css/admin-style.css',
                array(),
                $css_ver
            );

            wp_enqueue_script(
                'scogs-admin-script',
                SCOGS_URL . 'assets/js/admin.js',
                array( 'jquery' ),
                $js_ver,
                true
            );

            wp_localize_script(
                'scogs-admin-script',
                'scogsAdminParams',
                array(
                    'currency_symbol' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
                    'currency_pos'    => get_option( 'woocommerce_currency_pos', 'left' ),
                    'decimal_sep'     => function_exists( 'wc_get_price_decimal_separator' ) ? wc_get_price_decimal_separator() : '.',
                    'thousand_sep'    => function_exists( 'wc_get_price_thousand_separator' ) ? wc_get_price_thousand_separator() : ',',
                    'num_decimals'    => function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2,
                    'logo_url'        => SCOGS_URL . 'assets/images/logo-icon-64.png',
                )
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
            '<a href="' . esc_url( admin_url( 'admin.php?page=scogs-analytics' ) ) . '" style="font-weight:600;">' . esc_html__( 'MarketPulse Profit Analytics', 'marketpulse-cogs-profit-for-woocommerce' ) . '</a>',
            '<a href="https://usemarketpulse.com" target="_blank" rel="noopener noreferrer" style="color:#2271b1;">' . esc_html__( 'NexForge AI', 'marketpulse-cogs-profit-for-woocommerce' ) . '</a>'
        );
        return array_merge( $custom_links, $links );
    }

    /**
     * Admin notice if WooCommerce is inactive
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php esc_html_e( 'MarketPulse COGS & Profit for WooCommerce', 'marketpulse-cogs-profit-for-woocommerce' ); ?></strong> <?php esc_html_e( 'requires WooCommerce to be installed and active.', 'marketpulse-cogs-profit-for-woocommerce' ); ?></p>
        </div>
        <?php
    }
}

/**
 * Return main instance of plugin
 *
 * @return MarketPulse_COGS_Profit_For_WooCommerce
 */
function scogs() {
    return MarketPulse_COGS_Profit_For_WooCommerce::instance();
}

// Backwards compatibility alias
class_alias( 'MarketPulse_COGS_Profit_For_WooCommerce', 'Simple_Cost_Of_Goods_WooCommerce' );

// Kick off the plugin
scogs();
