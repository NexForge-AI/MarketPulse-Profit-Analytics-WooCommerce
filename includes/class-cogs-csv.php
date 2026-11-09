<?php
/**
 * WooCommerce Native CSV Import & Export Integration
 * Allows bulk uploading and downloading Cost of Goods (COGS) and Suppliers via standard CSV.
 */

defined( 'ABSPATH' ) || exit;

class SCOGS_CSV {

    /**
     * Initialize CSV hooks
     */
    public static function init() {
        // Product CSV Exporter
        add_filter( 'woocommerce_product_export_column_names', array( __CLASS__, 'add_export_columns' ) );
        add_filter( 'woocommerce_product_export_product_default_columns', array( __CLASS__, 'add_export_columns' ) );
        add_filter( 'woocommerce_product_export_product_column_scogs_cost_price', array( __CLASS__, 'export_cost_column_value' ), 10, 2 );
        add_filter( 'woocommerce_product_export_product_column_scogs_supplier', array( __CLASS__, 'export_supplier_column_value' ), 10, 2 );

        // Product CSV Importer
        add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'add_import_mapping_options' ) );
        add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'add_default_import_mappings' ) );
        add_action( 'woocommerce_product_import_inserted_product_object', array( __CLASS__, 'process_imported_product' ), 10, 2 );
    }

    /**
     * Add Cost & Supplier to WooCommerce Product CSV Exporter
     */
    public static function add_export_columns( $columns ) {
        $columns['scogs_cost_price'] = __( 'Cost of Goods (COGS)', 'simple-cost-of-goods-for-woocommerce' );
        $columns['scogs_supplier']   = __( 'Supplier', 'simple-cost-of-goods-for-woocommerce' );
        return $columns;
    }

    /**
     * Export Cost column value
     */
    public static function export_cost_column_value( $value, $product ) {
        $cost = $product->get_meta( SCOGS_Product::META_KEY_COST, true );
        return ( '' !== $cost && false !== $cost ) ? wc_format_decimal( $cost ) : '';
    }

    /**
     * Export Supplier column value
     */
    public static function export_supplier_column_value( $value, $product ) {
        $supplier = $product->get_meta( SCOGS_Product::META_KEY_SUPPLIER, true );
        return ! empty( $supplier ) ? $supplier : '';
    }

    /**
     * Add mapping options to Product CSV Importer dropdown
     */
    public static function add_import_mapping_options( $options ) {
        $options['scogs_cost_price'] = __( 'Cost of Goods (COGS)', 'simple-cost-of-goods-for-woocommerce' );
        $options['scogs_supplier']   = __( 'Supplier', 'simple-cost-of-goods-for-woocommerce' );
        return $options;
    }

    /**
     * Default column mappings during CSV upload
     */
    public static function add_default_import_mappings( $mappings ) {
        $mappings[ __( 'Cost of Goods (COGS)', 'simple-cost-of-goods-for-woocommerce' ) ] = 'scogs_cost_price';
        $mappings['cogs']            = 'scogs_cost_price';
        $mappings['cost_price']      = 'scogs_cost_price';
        $mappings['cost']            = 'scogs_cost_price';
        $mappings['purchase_price']  = 'scogs_cost_price';
        $mappings[ __( 'Supplier', 'simple-cost-of-goods-for-woocommerce' ) ]             = 'scogs_supplier';
        $mappings['supplier']        = 'scogs_supplier';
        $mappings['vendor']          = 'scogs_supplier';
        return $mappings;
    }

    /**
     * Save Cost and Supplier when product is imported from CSV
     */
    public static function process_imported_product( $product, $data ) {
        if ( isset( $data['scogs_cost_price'] ) && '' !== $data['scogs_cost_price'] ) {
            $product->update_meta_data( SCOGS_Product::META_KEY_COST, wc_format_decimal( $data['scogs_cost_price'] ) );
        }

        if ( isset( $data['scogs_supplier'] ) && '' !== $data['scogs_supplier'] ) {
            $product->update_meta_data( SCOGS_Product::META_KEY_SUPPLIER, sanitize_text_field( $data['scogs_supplier'] ) );
        }

        $product->save();
    }
}

