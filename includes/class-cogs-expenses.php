<?php
/**
 * Business Operating Expenses Manager
 * Tracks non-product overhead (Facebook/Google Ads, Server/Hosting, Salaries, Office Rent)
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_Expenses {

    /**
     * Get table name with prefix
     *
     * @return string
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'scogs_expenses';
    }

    /**
     * Initialize expense hooks
     */
    public static function init() {
        add_action( 'admin_post_scogs_add_expense', array( __CLASS__, 'handle_add_expense' ) );
        add_action( 'admin_post_scogs_delete_expense', array( __CLASS__, 'handle_delete_expense' ) );
    }

    /**
     * Create expenses table upon plugin activation
     */
    public static function create_table() {
        global $wpdb;
        $table_name      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL auto_increment,
            expense_date date NOT NULL,
            category varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            amount decimal(10,2) NOT NULL default '0.00',
            notes text NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY idx_expense_date (expense_date)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Get standard business expense categories
     *
     * @return array
     */
    public static function get_categories() {
        return array(
            'advertising' => __( 'Advertising & Marketing (Meta/Google)', 'simple-cost-of-goods-for-woocommerce' ),
            'server'      => __( 'Server, Hosting & Domains', 'simple-cost-of-goods-for-woocommerce' ),
            'salaries'    => __( 'Staff Salaries & Freelancers', 'simple-cost-of-goods-for-woocommerce' ),
            'rent'        => __( 'Office / Warehouse Rent & Utilities', 'simple-cost-of-goods-for-woocommerce' ),
            'packaging'   => __( 'Boxes, Packaging & Printing', 'simple-cost-of-goods-for-woocommerce' ),
            'software'    => __( 'Software Subscriptions & SaaS', 'simple-cost-of-goods-for-woocommerce' ),
            'other'       => __( 'Other Miscellaneous Expenses', 'simple-cost-of-goods-for-woocommerce' ),
        );
    }

    /**
     * Add an expense entry
     *
     * @param string $date
     * @param string $category
     * @param string $title
     * @param float  $amount
     * @param string $notes
     * @return int|false
     */
    public static function add_expense( $date, $category, $title, $amount, $notes = '' ) {
        global $wpdb;

        $table_name = self::get_table_name();
        $inserted   = $wpdb->insert(
            $table_name,
            array(
                'expense_date' => sanitize_text_field( $date ),
                'category'     => sanitize_key( $category ),
                'title'        => sanitize_text_field( $title ),
                'amount'       => wc_format_decimal( $amount ),
                'notes'        => sanitize_textarea_field( $notes ),
                'created_at'   => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%f', '%s', '%s' )
        );

        // Invalidate cached stats and reports
        if ( class_exists( 'SCOGS_Reports' ) ) {
            SCOGS_Reports::flush_cache();
        } else {
            delete_transient( 'scogs_dashboard_stats' );
        }

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Delete an expense entry
     *
     * @param int $id
     * @return bool
     */
    public static function delete_expense( $id ) {
        global $wpdb;
        $table_name = self::get_table_name();
        $deleted    = $wpdb->delete( $table_name, array( 'id' => absint( $id ) ), array( '%d' ) );

        if ( class_exists( 'SCOGS_Reports' ) ) {
            SCOGS_Reports::flush_cache();
        } else {
            delete_transient( 'scogs_dashboard_stats' );
        }
        return (bool) $deleted;
    }

    /**
     * Query expenses between two dates
     *
     * @param string $start_date Y-m-d
     * @param string $end_date   Y-m-d
     * @return array
     */
    public static function get_expenses( $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table_name = esc_sql( self::get_table_name() );

        if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM `{$table_name}` WHERE `expense_date` BETWEEN %s AND %s ORDER BY `expense_date` DESC, `id` DESC",
                    $start_date,
                    $end_date
                )
            );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results( "SELECT * FROM `{$table_name}` ORDER BY `expense_date` DESC, `id` DESC LIMIT 100" );
    }

    /**
     * Get aggregate sum of expenses between two dates
     *
     * @param string $start_date Y-m-d
     * @param string $end_date   Y-m-d
     * @return float
     */
    public static function get_total_expenses( $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table_name = esc_sql( self::get_table_name() );

        if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $sum = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(`amount`) FROM `{$table_name}` WHERE `expense_date` BETWEEN %s AND %s",
                    $start_date,
                    $end_date
                )
            );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
            $sum = $wpdb->get_var( "SELECT SUM(`amount`) FROM `{$table_name}`" );
        }

        return (float) ( $sum ? $sum : 0.0 );
    }

    /**
     * Handle POST submission when adding an expense
     */
    public static function handle_add_expense() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Unauthorized user.', 'simple-cost-of-goods-for-woocommerce' ) );
        }

        check_admin_referer( 'scogs_add_expense_nonce' );

        $date     = ! empty( $_POST['expense_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expense_date'] ) ) : current_time( 'Y-m-d' );
        $category = ! empty( $_POST['category'] ) ? sanitize_key( wp_unslash( $_POST['category'] ) ) : 'other';
        $title    = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : esc_html__( 'Operating Expense', 'simple-cost-of-goods-for-woocommerce' );
        $amount   = ! empty( $_POST['amount'] ) ? (float) wc_format_decimal( wp_unslash( $_POST['amount'] ) ) : 0.0;
        $notes    = ! empty( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

        if ( $amount > 0 ) {
            self::add_expense( $date, $category, $title, $amount, $notes );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=scogs-analytics&tab=expenses&added=1' ) );
        exit;
    }

    /**
     * Handle GET request to delete an expense
     */
    public static function handle_delete_expense() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Unauthorized user.', 'simple-cost-of-goods-for-woocommerce' ) );
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        check_admin_referer( 'scogs_delete_expense_' . $id );

        if ( $id > 0 ) {
            self::delete_expense( $id );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=scogs-analytics&tab=expenses&deleted=1' ) );
        exit;
    }
}

