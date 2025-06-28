<?php

/**
 * The core plugin class.
 */
class Portfoy_TakipX {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		if ( defined( 'PORTFOY_TAKIPX_VERSION' ) ) {
			$this->version = PORTFOY_TAKIPX_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'portfoy-takipx';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once PORTFOY_TAKIPX_PLUGIN_DIR . 'includes/class-portfoy-takipx-loader.php';
		$this->loader = new Portfoy_TakipX_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Portfoy_TakipX_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_reports_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );

		// AJAX handlers
		$this->loader->add_action( 'wp_ajax_portfoy_get_portfolio_summary', $plugin_admin, 'ajax_get_portfolio_summary' );
		$this->loader->add_action( 'wp_ajax_portfoy_get_asset_performance', $plugin_admin, 'ajax_get_asset_performance' );

		// Scheduled events for automatic price updates and snapshots
		$this->loader->add_action( 'portfoy_takipx_hourly_update', $plugin_admin, 'bulk_update_prices' );
		$this->loader->add_action( 'portfoy_takipx_daily_snapshot', $plugin_admin, 'generate_portfolio_snapshots' );

		// Schedule cron events if not already scheduled
		if ( ! wp_next_scheduled( 'portfoy_takipx_hourly_update' ) ) {
			wp_schedule_event( time(), 'hourly', 'portfoy_takipx_hourly_update' );
		}
		
		if ( ! wp_next_scheduled( 'portfoy_takipx_daily_snapshot' ) ) {
			wp_schedule_event( time(), 'daily', 'portfoy_takipx_daily_snapshot' );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 */
	private function define_public_hooks() {
		$plugin_public = new Portfoy_TakipX_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'init_shortcodes' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}