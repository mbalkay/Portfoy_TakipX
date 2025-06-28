<?php

/**
 * The public-facing functionality of the plugin.
 */
class Portfoy_TakipX_Public {

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
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		// Enhanced modern public styles
		wp_enqueue_style( $this->plugin_name, PORTFOY_TAKIPX_PLUGIN_URL . 'public/css/portfoy-takipx-public.css', array(), $this->version, 'all' );
		
		// Google Fonts for better typography
		wp_enqueue_style( 'inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, PORTFOY_TAKIPX_PLUGIN_URL . 'public/js/portfoy-takipx-public.js', array( 'jquery' ), $this->version, false );
		
		// Localize script for AJAX
		wp_localize_script( $this->plugin_name, 'portfoy_takipx_public', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'portfoy_takipx_public_nonce' ),
			'rest_url' => rest_url( 'portfoy-takipx/v1/' ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'is_user_logged_in' => is_user_logged_in(),
		) );
	}

	/**
	 * Initialize shortcodes.
	 */
	public function init_shortcodes() {
		add_shortcode( 'portfoy_takipx_portfolio', array( $this, 'portfolio_shortcode' ) );
		add_shortcode( 'portfoy_takipx_summary', array( $this, 'summary_shortcode' ) );
		add_shortcode( 'portfoy_takipx_assets', array( $this, 'assets_shortcode' ) );
	}

	/**
	 * Portfolio shortcode callback.
	 */
	public function portfolio_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'user_id' => get_current_user_id(),
			'show_add_form' => is_user_logged_in() ? 'true' : 'false',
		), $atts, 'portfoy_takipx_portfolio' );

		if ( ! is_user_logged_in() ) {
			return $this->login_message();
		}

		ob_start();
		include PORTFOY_TAKIPX_PLUGIN_DIR . 'templates/portfolio-display.php';
		return ob_get_clean();
	}

	/**
	 * Summary shortcode callback.
	 */
	public function summary_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'user_id' => get_current_user_id(),
			'show_charts' => 'true',
		), $atts, 'portfoy_takipx_summary' );

		if ( ! is_user_logged_in() ) {
			return $this->login_message();
		}

		ob_start();
		include PORTFOY_TAKIPX_PLUGIN_DIR . 'templates/portfolio-summary.php';
		return ob_get_clean();
	}

	/**
	 * Assets shortcode callback.
	 */
	public function assets_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'type' => '',
			'limit' => -1,
			'user_id' => get_current_user_id(),
		), $atts, 'portfoy_takipx_assets' );

		if ( ! is_user_logged_in() ) {
			return $this->login_message();
		}

		ob_start();
		include PORTFOY_TAKIPX_PLUGIN_DIR . 'templates/assets-list.php';
		return ob_get_clean();
	}

	/**
	 * Get user assets.
	 */
	public function get_user_assets( $user_id, $asset_type = '', $limit = -1 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';

		$sql = "SELECT * FROM $table_name WHERE user_id = %d";
		$params = array( $user_id );

		if ( ! empty( $asset_type ) ) {
			$sql .= " AND asset_type = %s";
			$params[] = $asset_type;
		}

		$sql .= " ORDER BY created_at DESC";

		if ( $limit > 0 ) {
			$sql .= " LIMIT %d";
			$params[] = $limit;
		}

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Get portfolio summary for user.
	 */
	public function get_portfolio_summary( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';

		$summary = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				asset_type,
				COUNT(*) as total_assets,
				SUM(quantity * purchase_price) as total_invested,
				SUM(quantity * COALESCE(current_price, purchase_price)) as current_value
			FROM $table_name 
			WHERE user_id = %d 
			GROUP BY asset_type",
			$user_id
		) );

		$total_invested = 0;
		$total_current_value = 0;

		foreach ( $summary as $item ) {
			$total_invested += $item->total_invested;
			$total_current_value += $item->current_value;
		}

		$profit_loss = $total_current_value - $total_invested;
		$profit_loss_percentage = $total_invested > 0 ? ( $profit_loss / $total_invested ) * 100 : 0;

		return array(
			'summary_by_type' => $summary,
			'total_invested' => $total_invested,
			'current_value' => $total_current_value,
			'profit_loss' => $profit_loss,
			'profit_loss_percentage' => $profit_loss_percentage,
		);
	}

	/**
	 * Login message for non-logged-in users.
	 */
	private function login_message() {
		$login_url = wp_login_url( get_permalink() );
		return '<div class="portfoy-login-message">
			<p>Portföyünüzü görüntülemek için <a href="' . esc_url( $login_url ) . '">giriş yapın</a>.</p>
		</div>';
	}

	/**
	 * Format currency for display.
	 */
	public function format_currency( $amount, $currency = '₺' ) {
		return $currency . number_format( $amount, 2, ',', '.' );
	}

	/**
	 * Get asset type label.
	 */
	public function get_asset_type_label( $type ) {
		$labels = array(
			'stock' => 'Hisse Senedi',
			'crypto' => 'Kripto Para',
			'forex' => 'Döviz',
			'commodity' => 'Emtia',
			'bond' => 'Tahvil',
		);

		return isset( $labels[ $type ] ) ? $labels[ $type ] : ucfirst( $type );
	}

	/**
	 * Calculate profit/loss for an asset.
	 */
	public function calculate_profit_loss( $asset ) {
		$current_price = ! empty( $asset->current_price ) ? $asset->current_price : $asset->purchase_price;
		$total_value = $asset->quantity * $current_price;
		$total_invested = $asset->quantity * $asset->purchase_price;
		$profit_loss = $total_value - $total_invested;
		$profit_loss_percentage = $total_invested > 0 ? ( $profit_loss / $total_invested ) * 100 : 0;

		return array(
			'total_value' => $total_value,
			'total_invested' => $total_invested,
			'profit_loss' => $profit_loss,
			'profit_loss_percentage' => $profit_loss_percentage,
		);
	}
}