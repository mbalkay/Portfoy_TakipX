<?php

/**
 * The admin-specific functionality of the plugin.
 */
class Portfoy_TakipX_Admin {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, PORTFOY_TAKIPX_PLUGIN_URL . 'admin/css/portfoy-takipx-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, PORTFOY_TAKIPX_PLUGIN_URL . 'admin/js/portfoy-takipx-admin.js', array( 'jquery' ), $this->version, false );
		
		// Localize script for AJAX
		wp_localize_script( $this->plugin_name, 'portfoy_takipx_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'portfoy_takipx_nonce' ),
			'rest_url' => rest_url( 'portfoy-takipx/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Portföy TakipX',
			'Portföy TakipX',
			'manage_options',
			'portfoy-takipx',
			array( $this, 'admin_page' ),
			'dashicons-chart-line',
			30
		);

		add_submenu_page(
			'portfoy-takipx',
			'Portföy Yönetimi',
			'Portföy Yönetimi',
			'manage_options',
			'portfoy-takipx',
			array( $this, 'admin_page' )
		);

		add_submenu_page(
			'portfoy-takipx',
			'Ayarlar',
			'Ayarlar',
			'manage_options',
			'portfoy-takipx-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Initialize admin settings.
	 */
	public function admin_init() {
		register_setting( 'portfoy_takipx_settings', 'portfoy_takipx_settings' );

		add_settings_section(
			'portfoy_takipx_api_section',
			'API Ayarları',
			array( $this, 'api_section_callback' ),
			'portfoy_takipx_settings'
		);

		add_settings_field(
			'auto_update_prices',
			'Otomatik Fiyat Güncelleme',
			array( $this, 'auto_update_prices_callback' ),
			'portfoy_takipx_settings',
			'portfoy_takipx_api_section'
		);

		add_settings_field(
			'update_frequency',
			'Güncelleme Sıklığı (dakika)',
			array( $this, 'update_frequency_callback' ),
			'portfoy_takipx_settings',
			'portfoy_takipx_api_section'
		);

		add_settings_field(
			'display_currency',
			'Görüntüleme Para Birimi',
			array( $this, 'display_currency_callback' ),
			'portfoy_takipx_settings',
			'portfoy_takipx_api_section'
		);
	}

	/**
	 * Main admin page.
	 */
	public function admin_page() {
		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-admin-display.php';
	}

	/**
	 * Settings page.
	 */
	public function settings_page() {
		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-admin-settings.php';
	}

	/**
	 * API section callback.
	 */
	public function api_section_callback() {
		echo '<p>API ve veri güncelleme ayarlarını buradan yapılandırabilirsiniz.</p>';
	}

	/**
	 * Auto update prices callback.
	 */
	public function auto_update_prices_callback() {
		$options = get_option( 'portfoy_takipx_settings' );
		$auto_update = isset( $options['auto_update_prices'] ) ? $options['auto_update_prices'] : 0;
		echo '<input type="checkbox" name="portfoy_takipx_settings[auto_update_prices]" value="1" ' . checked( 1, $auto_update, false ) . ' />';
		echo '<label for="auto_update_prices">Fiyatları otomatik olarak güncelle</label>';
	}

	/**
	 * Update frequency callback.
	 */
	public function update_frequency_callback() {
		$options = get_option( 'portfoy_takipx_settings' );
		$frequency = isset( $options['update_frequency'] ) ? $options['update_frequency'] : 15;
		echo '<input type="number" name="portfoy_takipx_settings[update_frequency]" value="' . esc_attr( $frequency ) . '" min="1" max="1440" />';
		echo '<p class="description">Fiyatların ne sıklıkla güncelleneceğini belirler (1-1440 dakika)</p>';
	}

	/**
	 * Display currency callback.
	 */
	public function display_currency_callback() {
		$options = get_option( 'portfoy_takipx_settings' );
		$currency = isset( $options['display_currency'] ) ? $options['display_currency'] : 'TRY';
		
		$currencies = array(
			'TRY' => 'Türk Lirası (₺)',
			'USD' => 'US Dollar ($)',
			'EUR' => 'Euro (€)',
			'BTC' => 'Bitcoin (₿)',
		);

		echo '<select name="portfoy_takipx_settings[display_currency]">';
		foreach ( $currencies as $code => $name ) {
			echo '<option value="' . esc_attr( $code ) . '" ' . selected( $currency, $code, false ) . '>' . esc_html( $name ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Enhanced admin page with advanced features
	 */
	public function admin_page() {
		// Check for CSV export request
		if ( isset( $_POST['export_csv'] ) ) {
			$this->handle_csv_export();
			return;
		}

		// Handle bulk actions
		if ( isset( $_POST['action'] ) && $_POST['action'] !== '-1' ) {
			$this->handle_bulk_actions();
		}

		// Initialize the advanced assets table
		$assets_table = new Portfoy_TakipX_Assets_Table();
		$assets_table->process_bulk_action();
		$assets_table->prepare_items();

		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-admin-display-advanced.php';
	}

	/**
	 * Advanced settings page
	 */
	public function settings_page() {
		// Handle report generation
		if ( isset( $_POST['generate_report'] ) ) {
			$this->handle_report_generation();
		}

		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-admin-settings.php';
	}

	/**
	 * Add reports submenu
	 */
	public function add_reports_menu() {
		add_submenu_page(
			'portfoy-takipx',
			'Raporlar',
			'Raporlar',
			'manage_options',
			'portfoy-takipx-reports',
			array( $this, 'reports_page' )
		);

		add_submenu_page(
			'portfoy-takipx',
			'Analitik',
			'Analitik',
			'manage_options',
			'portfoy-takipx-analytics',
			array( $this, 'analytics_page' )
		);

		add_submenu_page(
			'portfoy-takipx',
			'İzleme Listesi',
			'İzleme Listesi',
			'manage_options',
			'portfoy-takipx-watchlist',
			array( $this, 'watchlist_page' )
		);
	}

	/**
	 * Reports page
	 */
	public function reports_page() {
		$reports = new Portfoy_TakipX_Reports();
		$report_data = null;

		if ( isset( $_POST['generate_report'] ) ) {
			$date_from = sanitize_text_field( $_POST['date_from'] );
			$date_to = sanitize_text_field( $_POST['date_to'] );
			$report_data = $reports->generate_portfolio_report( $date_from, $date_to );
		}

		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-reports-display.php';
	}

	/**
	 * Analytics page
	 */
	public function analytics_page() {
		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-analytics-display.php';
	}

	/**
	 * Watchlist page
	 */
	public function watchlist_page() {
		include_once PORTFOY_TAKIPX_PLUGIN_DIR . 'admin/partials/portfoy-takipx-watchlist-display.php';
	}

	/**
	 * Handle CSV export
	 */
	private function handle_csv_export() {
		$reports = new Portfoy_TakipX_Reports();
		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( $_POST['date_from'] ) : date( 'Y-m-d', strtotime( '-1 year' ) );
		$date_to = isset( $_POST['date_to'] ) ? sanitize_text_field( $_POST['date_to'] ) : date( 'Y-m-d' );
		
		$report_data = $reports->generate_portfolio_report( $date_from, $date_to );
		$reports->export_to_csv( $report_data );
	}

	/**
	 * Handle bulk actions
	 */
	private function handle_bulk_actions() {
		$action = sanitize_text_field( $_POST['action'] );
		
		switch ( $action ) {
			case 'update_prices':
				$this->bulk_update_prices();
				break;
			case 'generate_snapshots':
				$this->generate_portfolio_snapshots();
				break;
			case 'sync_price_history':
				$this->sync_price_history();
				break;
		}
	}

	/**
	 * Bulk update prices from external APIs
	 */
	private function bulk_update_prices() {
		global $wpdb;
		
		$assets_table = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();
		
		$assets = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT symbol, asset_type FROM $assets_table WHERE user_id = %d AND status = 'active'",
			$user_id
		) );

		foreach ( $assets as $asset ) {
			$new_price = $this->fetch_current_price( $asset->symbol, $asset->asset_type );
			
			if ( $new_price !== false ) {
				$wpdb->update(
					$assets_table,
					array( 'current_price' => $new_price, 'updated_at' => current_time( 'mysql' ) ),
					array( 'symbol' => $asset->symbol, 'user_id' => $user_id )
				);

				// Also update price history
				$this->update_price_history( $asset->symbol, $asset->asset_type, $new_price );
			}
		}

		wp_redirect( add_query_arg( 'updated', 'prices', admin_url( 'admin.php?page=portfoy-takipx' ) ) );
		exit;
	}

	/**
	 * Generate portfolio snapshots for performance tracking
	 */
	private function generate_portfolio_snapshots() {
		global $wpdb;
		
		$user_id = get_current_user_id();
		$assets_table = $wpdb->prefix . 'portfoy_takipx_assets';
		$snapshots_table = $wpdb->prefix . 'portfoy_takipx_portfolio_snapshots';

		// Calculate current portfolio metrics
		$portfolio_data = $wpdb->get_row( $wpdb->prepare(
			"SELECT 
				COUNT(*) as asset_count,
				SUM(quantity * purchase_price) as total_invested,
				SUM(quantity * current_price) as total_value,
				SUM((quantity * current_price) - (quantity * purchase_price)) as profit_loss
			FROM $assets_table 
			WHERE user_id = %d AND status = 'active'",
			$user_id
		) );

		$profit_loss_percentage = $portfolio_data->total_invested > 0 
			? (($portfolio_data->profit_loss / $portfolio_data->total_invested) * 100)
			: 0;

		// Insert snapshot
		$wpdb->replace(
			$snapshots_table,
			array(
				'user_id' => $user_id,
				'total_value' => $portfolio_data->total_value,
				'total_invested' => $portfolio_data->total_invested,
				'profit_loss' => $portfolio_data->profit_loss,
				'profit_loss_percentage' => $profit_loss_percentage,
				'asset_count' => $portfolio_data->asset_count,
				'snapshot_date' => current_time( 'mysql' ),
				'snapshot_type' => 'manual'
			),
			array( '%d', '%f', '%f', '%f', '%f', '%d', '%s', '%s' )
		);

		wp_redirect( add_query_arg( 'updated', 'snapshot', admin_url( 'admin.php?page=portfoy-takipx' ) ) );
		exit;
	}

	/**
	 * Fetch current price from external API
	 */
	private function fetch_current_price( $symbol, $asset_type ) {
		// This is a simplified implementation
		// In real implementation, you would integrate with actual financial APIs
		
		switch ( $asset_type ) {
			case 'crypto':
				return $this->fetch_crypto_price( $symbol );
			case 'stock':
				return $this->fetch_stock_price( $symbol );
			case 'forex':
				return $this->fetch_forex_price( $symbol );
			default:
				return false;
		}
	}

	/**
	 * Update price history table
	 */
	private function update_price_history( $symbol, $asset_type, $price ) {
		global $wpdb;
		
		$price_history_table = $wpdb->prefix . 'portfoy_takipx_price_history';
		
		// Get previous price for change calculation
		$previous_price = $wpdb->get_var( $wpdb->prepare(
			"SELECT price FROM $price_history_table 
			WHERE symbol = %s 
			ORDER BY recorded_at DESC 
			LIMIT 1",
			$symbol
		) );

		$price_change = $previous_price ? $price - $previous_price : 0;
		$price_change_percentage = $previous_price > 0 ? (($price_change / $previous_price) * 100) : 0;

		$wpdb->replace(
			$price_history_table,
			array(
				'symbol' => $symbol,
				'asset_type' => $asset_type,
				'price' => $price,
				'price_change_24h' => $price_change,
				'price_change_percentage_24h' => $price_change_percentage,
				'recorded_at' => current_time( 'mysql' ),
				'source' => 'api'
			),
			array( '%s', '%s', '%f', '%f', '%f', '%s', '%s' )
		);
	}

	/**
	 * AJAX handlers for real-time updates
	 */
	public function ajax_get_portfolio_summary() {
		check_ajax_referer( 'portfoy_takipx_nonce', 'nonce' );
		
		$reports = new Portfoy_TakipX_Reports();
		$summary = $reports->get_portfolio_summary();
		
		wp_send_json_success( $summary );
	}

	public function ajax_get_asset_performance() {
		check_ajax_referer( 'portfoy_takipx_nonce', 'nonce' );
		
		$asset_id = intval( $_POST['asset_id'] );
		$performance_data = $this->get_asset_performance_data( $asset_id );
		
		wp_send_json_success( $performance_data );
	}

	/**
	 * Simplified API methods (to be implemented with real APIs)
	 */
	private function fetch_crypto_price( $symbol ) {
		// Integration with CoinGecko, Binance, etc.
		return false;
	}

	private function fetch_stock_price( $symbol ) {
		// Integration with Alpha Vantage, Yahoo Finance, etc.
		return false;
	}

	private function fetch_forex_price( $symbol ) {
		// Integration with forex APIs
		return false;
	}
}