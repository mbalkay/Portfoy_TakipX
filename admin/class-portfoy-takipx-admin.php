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
}