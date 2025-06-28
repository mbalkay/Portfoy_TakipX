<?php
/**
 * Plugin Name:       Portföy TakipX
 * Plugin URI:        https://github.com/mbalkay/Portfoy_TakipX
 * Description:       Borsa, Kripto ve diğer varlıkların takibini yapmak için bir portföy yönetim eklentisi.
 * Version:           0.1.0
 * Author:            GitHub Copilot & mbalkay
 * Author URI:        https://github.com/mbalkay
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       portfoy-takipx
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
define( 'PORTFOY_TAKIPX_VERSION', '0.1.0' );
define( 'PORTFOY_TAKIPX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PORTFOY_TAKIPX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_portfoy_takipx() {
	// Create database tables
	portfoy_takipx_create_tables();
	
	// Create portfolio page
	portfoy_takipx_create_portfolio_page();
	
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_portfoy_takipx' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_portfoy_takipx() {
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_portfoy_takipx' );

/**
 * Create database tables for portfolio data
 */
function portfoy_takipx_create_tables() {
	global $wpdb;
	
	$charset_collate = $wpdb->get_charset_collate();
	
	// Main assets table with enhanced tracking
	$assets_table = $wpdb->prefix . 'portfoy_takipx_assets';
	$sql_assets = "CREATE TABLE $assets_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		asset_type varchar(20) NOT NULL,
		symbol varchar(20) NOT NULL,
		name varchar(100) NOT NULL,
		quantity decimal(18,8) NOT NULL,
		purchase_price decimal(18,8) NOT NULL,
		current_price decimal(18,8) DEFAULT 0,
		purchase_date datetime DEFAULT CURRENT_TIMESTAMP,
		notes text,
		status varchar(20) DEFAULT 'active',
		broker varchar(50),
		commission decimal(10,4) DEFAULT 0,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY asset_type (asset_type),
		KEY symbol (symbol),
		KEY status (status),
		KEY purchase_date (purchase_date)
	) $charset_collate;";
	
	// Price history table for tracking price changes
	$price_history_table = $wpdb->prefix . 'portfoy_takipx_price_history';
	$sql_price_history = "CREATE TABLE $price_history_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		symbol varchar(20) NOT NULL,
		asset_type varchar(20) NOT NULL,
		price decimal(18,8) NOT NULL,
		volume bigint(20) DEFAULT 0,
		market_cap decimal(20,2) DEFAULT 0,
		price_change_24h decimal(10,4) DEFAULT 0,
		price_change_percentage_24h decimal(10,4) DEFAULT 0,
		recorded_at datetime DEFAULT CURRENT_TIMESTAMP,
		source varchar(50) DEFAULT 'manual',
		PRIMARY KEY (id),
		UNIQUE KEY symbol_date (symbol, recorded_at),
		KEY symbol (symbol),
		KEY asset_type (asset_type),
		KEY recorded_at (recorded_at)
	) $charset_collate;";
	
	// Transactions table for detailed tracking
	$transactions_table = $wpdb->prefix . 'portfoy_takipx_transactions';
	$sql_transactions = "CREATE TABLE $transactions_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		asset_id mediumint(9) NOT NULL,
		transaction_type varchar(20) NOT NULL,
		quantity decimal(18,8) NOT NULL,
		price decimal(18,8) NOT NULL,
		commission decimal(10,4) DEFAULT 0,
		tax decimal(10,4) DEFAULT 0,
		total_amount decimal(18,8) NOT NULL,
		transaction_date datetime DEFAULT CURRENT_TIMESTAMP,
		notes text,
		reference_number varchar(100),
		broker varchar(50),
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY asset_id (asset_id),
		KEY transaction_type (transaction_type),
		KEY transaction_date (transaction_date),
		FOREIGN KEY (asset_id) REFERENCES {$assets_table}(id) ON DELETE CASCADE
	) $charset_collate;";
	
	// Portfolio snapshots for performance tracking
	$snapshots_table = $wpdb->prefix . 'portfoy_takipx_portfolio_snapshots';
	$sql_snapshots = "CREATE TABLE $snapshots_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		total_value decimal(18,8) NOT NULL,
		total_invested decimal(18,8) NOT NULL,
		profit_loss decimal(18,8) NOT NULL,
		profit_loss_percentage decimal(10,4) NOT NULL,
		asset_count int(11) NOT NULL,
		snapshot_date datetime DEFAULT CURRENT_TIMESTAMP,
		snapshot_type varchar(20) DEFAULT 'daily',
		notes text,
		PRIMARY KEY (id),
		UNIQUE KEY user_date_type (user_id, snapshot_date, snapshot_type),
		KEY user_id (user_id),
		KEY snapshot_date (snapshot_date),
		KEY snapshot_type (snapshot_type)
	) $charset_collate;";
	
	// Watchlist table for tracking potential investments
	$watchlist_table = $wpdb->prefix . 'portfoy_takipx_watchlist';
	$sql_watchlist = "CREATE TABLE $watchlist_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		symbol varchar(20) NOT NULL,
		asset_type varchar(20) NOT NULL,
		name varchar(100) NOT NULL,
		target_price decimal(18,8),
		alert_enabled tinyint(1) DEFAULT 0,
		notes text,
		added_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY user_symbol (user_id, symbol),
		KEY user_id (user_id),
		KEY symbol (symbol),
		KEY asset_type (asset_type)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	// Create all tables
	dbDelta( $sql_assets );
	dbDelta( $sql_price_history );
	dbDelta( $sql_transactions );
	dbDelta( $sql_snapshots );
	dbDelta( $sql_watchlist );
	
	// Update database version
	update_option( 'portfoy_takipx_db_version', '1.1' );
}

/**
 * Create the Portfolio page automatically
 */
function portfoy_takipx_create_portfolio_page() {
	$page_slug = 'portfoyum';
	$page_title = 'Portföyüm';
	
	// Check if page already exists
	$existing_page = get_page_by_path( $page_slug );
	
	if ( ! $existing_page ) {
		$page_id = wp_insert_post( array(
			'post_title'     => $page_title,
			'post_content'   => '[portfoy_takipx_portfolio]',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_name'      => $page_slug,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		) );
		
		if ( $page_id ) {
			update_option( 'portfoy_takipx_page_id', $page_id );
		}
	}
}

// Include core files
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'includes/class-portfoy-takipx.php';
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'includes/class-portfoy-takipx-api.php';
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'includes/class-portfoy-takipx-reports.php';
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/class-portfoy-takipx-admin.php';
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/class-portfoy-takipx-assets-table.php';
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'public/class-portfoy-takipx-public.php';

/**
 * Initialize the plugin
 */
function portfoy_takipx_init() {
	$plugin = new Portfoy_TakipX();
	$plugin->run();
}
add_action( 'plugins_loaded', 'portfoy_takipx_init' );