<?php

/**
 * The API functionality of the plugin.
 */
class Portfoy_TakipX_API {

	/**
	 * Initialize the API endpoints.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		register_rest_route( 'portfoy-takipx/v1', '/assets', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_assets' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/assets', array(
			'methods' => 'POST',
			'callback' => array( $this, 'create_asset' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/assets/(?P<id>\d+)', array(
			'methods' => 'PUT',
			'callback' => array( $this, 'update_asset' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/assets/(?P<id>\d+)', array(
			'methods' => 'DELETE',
			'callback' => array( $this, 'delete_asset' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/portfolio/summary', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_portfolio_summary' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );
	}

	/**
	 * Check if user has permission to access API.
	 */
	public function check_permission() {
		return is_user_logged_in();
	}

	/**
	 * Get user's assets.
	 */
	public function get_assets( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();

		$assets = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
			$user_id
		) );

		return rest_ensure_response( $assets );
	}

	/**
	 * Create a new asset.
	 */
	public function create_asset( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();

		$data = array(
			'user_id' => $user_id,
			'asset_type' => sanitize_text_field( $request['asset_type'] ),
			'symbol' => sanitize_text_field( $request['symbol'] ),
			'name' => sanitize_text_field( $request['name'] ),
			'quantity' => floatval( $request['quantity'] ),
			'purchase_price' => floatval( $request['purchase_price'] ),
			'current_price' => floatval( $request['current_price'] ?? 0 ),
			'purchase_date' => sanitize_text_field( $request['purchase_date'] ),
		);

		$result = $wpdb->insert( $table_name, $data );

		if ( $result === false ) {
			return new WP_Error( 'insert_failed', 'Failed to create asset', array( 'status' => 500 ) );
		}

		$data['id'] = $wpdb->insert_id;
		return rest_ensure_response( $data );
	}

	/**
	 * Update an existing asset.
	 */
	public function update_asset( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();
		$asset_id = $request['id'];

		// Check if asset belongs to user
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
			$asset_id, $user_id
		) );

		if ( ! $existing ) {
			return new WP_Error( 'not_found', 'Asset not found', array( 'status' => 404 ) );
		}

		$data = array(
			'asset_type' => sanitize_text_field( $request['asset_type'] ),
			'symbol' => sanitize_text_field( $request['symbol'] ),
			'name' => sanitize_text_field( $request['name'] ),
			'quantity' => floatval( $request['quantity'] ),
			'purchase_price' => floatval( $request['purchase_price'] ),
			'current_price' => floatval( $request['current_price'] ?? 0 ),
			'purchase_date' => sanitize_text_field( $request['purchase_date'] ),
		);

		$result = $wpdb->update( $table_name, $data, array( 'id' => $asset_id, 'user_id' => $user_id ) );

		if ( $result === false ) {
			return new WP_Error( 'update_failed', 'Failed to update asset', array( 'status' => 500 ) );
		}

		$data['id'] = $asset_id;
		return rest_ensure_response( $data );
	}

	/**
	 * Delete an asset.
	 */
	public function delete_asset( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();
		$asset_id = $request['id'];

		$result = $wpdb->delete( $table_name, array( 'id' => $asset_id, 'user_id' => $user_id ) );

		if ( $result === false ) {
			return new WP_Error( 'delete_failed', 'Failed to delete asset', array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true, 'id' => $asset_id ) );
	}

	/**
	 * Get portfolio summary.
	 */
	public function get_portfolio_summary( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();

		$summary = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				asset_type,
				COUNT(*) as total_assets,
				SUM(quantity * purchase_price) as total_invested,
				SUM(quantity * current_price) as current_value
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

		return rest_ensure_response( array(
			'summary_by_type' => $summary,
			'total_invested' => $total_invested,
			'current_value' => $total_current_value,
			'profit_loss' => $profit_loss,
			'profit_loss_percentage' => $profit_loss_percentage,
		) );
	}
}

// Initialize the API
new Portfoy_TakipX_API();