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
	
	$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
	
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		asset_type varchar(20) NOT NULL,
		symbol varchar(10) NOT NULL,
		name varchar(100) NOT NULL,
		quantity decimal(18,8) NOT NULL,
		purchase_price decimal(18,8) NOT NULL,
		current_price decimal(18,8) DEFAULT 0,
		purchase_date datetime DEFAULT CURRENT_TIMESTAMP,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY asset_type (asset_type)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
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
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/class-portfoy-takipx-admin.php';
require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'public/class-portfoy-takipx-public.php';

/**
 * Initialize the plugin
 */
function portfoy_takipx_init() {
	$plugin = new Portfoy_TakipX();
	$plugin->run();
}
add_action( 'plugins_loaded', 'portfoy_takipx_init' );