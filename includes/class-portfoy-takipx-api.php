<?php

/**
 * The API functionality of the plugin.
 */
class Portfoy_TakipX_API {

	private $cache;
	private $price_service;

	/**
	 * Initialize the API endpoints.
	 */
	public function __construct() {
		$this->cache = Portfoy_TakipX_Cache::getInstance();
		$this->price_service = new Portfoy_TakipX_Price_Service();
		
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Sanitize float value for REST API.
	 */
	public function sanitize_float( $value ) {
		return floatval( $value );
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		// Asset management endpoints
		register_rest_route( 'portfoy-takipx/v1', '/assets', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_assets' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args' => array(
				'page' => array(
					'default' => 1,
					'sanitize_callback' => 'absint',
				),
				'per_page' => array(
					'default' => 20,
					'sanitize_callback' => 'absint',
				),
				'search' => array(
					'sanitize_callback' => 'sanitize_text_field',
				),
				'asset_type' => array(
					'sanitize_callback' => 'sanitize_text_field',
				),
				'orderby' => array(
					'default' => 'created_at',
					'sanitize_callback' => 'sanitize_sql_orderby',
				),
				'order' => array(
					'default' => 'DESC',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/assets', array(
			'methods' => 'POST',
			'callback' => array( $this, 'create_asset' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args' => array(
				'asset_type' => array(
					'required' => true,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => array( $this, 'validate_asset_type' ),
				),
				'symbol' => array(
					'required' => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'name' => array(
					'required' => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'quantity' => array(
					'required' => true,
					'sanitize_callback' => array( $this, 'sanitize_float' ),
					'validate_callback' => array( $this, 'validate_positive_number' ),
				),
				'purchase_price' => array(
					'required' => true,
					'sanitize_callback' => array( $this, 'sanitize_float' ),
					'validate_callback' => array( $this, 'validate_positive_number' ),
				),
				'current_price' => array(
					'sanitize_callback' => array( $this, 'sanitize_float' ),
					'validate_callback' => array( $this, 'validate_positive_number' ),
				),
				'purchase_date' => array(
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => array( $this, 'validate_date' ),
				),
				'broker' => array(
					'sanitize_callback' => 'sanitize_text_field',
				),
				'commission' => array(
					'sanitize_callback' => array( $this, 'sanitize_float' ),
				),
				'notes' => array(
					'sanitize_callback' => 'sanitize_textarea_field',
				),
			),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/assets/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_asset' ),
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

		// Portfolio endpoints
		register_rest_route( 'portfoy-takipx/v1', '/portfolio/summary', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_portfolio_summary' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/portfolio/performance', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_portfolio_performance' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args' => array(
				'date_from' => array(
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => array( $this, 'validate_date' ),
				),
				'date_to' => array(
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => array( $this, 'validate_date' ),
				),
			),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/portfolio/allocation', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_portfolio_allocation' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/portfolio/risk-analysis', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_risk_analysis' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		// Price endpoints
		register_rest_route( 'portfoy-takipx/v1', '/prices/(?P<symbol>[a-zA-Z0-9\-]+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_asset_price' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args' => array(
				'asset_type' => array(
					'required' => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/prices/update', array(
			'methods' => 'POST',
			'callback' => array( $this, 'update_all_prices' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );

		// Transaction endpoints
		register_rest_route( 'portfoy-takipx/v1', '/transactions', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_transactions' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/transactions', array(
			'methods' => 'POST',
			'callback' => array( $this, 'create_transaction' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		// Analytics endpoints
		register_rest_route( 'portfoy-takipx/v1', '/analytics/top-performers', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_top_performers' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/analytics/monthly-performance', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_monthly_performance' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		// Export endpoints
		register_rest_route( 'portfoy-takipx/v1', '/export/portfolio', array(
			'methods' => 'GET',
			'callback' => array( $this, 'export_portfolio' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args' => array(
				'format' => array(
					'default' => 'csv',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		// Cache management endpoints
		register_rest_route( 'portfoy-takipx/v1', '/cache/clear', array(
			'methods' => 'POST',
			'callback' => array( $this, 'clear_cache' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );

		register_rest_route( 'portfoy-takipx/v1', '/cache/stats', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_cache_stats' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );
	}

	/**
	 * Check if user has permission to access API.
	 */
	public function check_permission() {
		return is_user_logged_in();
	}

	/**
	 * Check if user has admin permission.
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get user's assets with advanced filtering and pagination.
	 */
	public function get_assets( $request ) {
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
			$user_id = get_current_user_id();

			// Get parameters
			$page = $request->get_param( 'page' );
			$per_page = min( $request->get_param( 'per_page' ), 100 ); // Max 100 items per page
			$search = $request->get_param( 'search' );
			$asset_type = $request->get_param( 'asset_type' );
			$orderby = $request->get_param( 'orderby' );
			$order = $request->get_param( 'order' );

			$offset = ( $page - 1 ) * $per_page;

			// Build WHERE clause
			$where_conditions = array( "user_id = %d", "status = 'active'" );
			$where_values = array( $user_id );

			if ( $search ) {
				$where_conditions[] = "(symbol LIKE %s OR name LIKE %s)";
				$where_values[] = '%' . $wpdb->esc_like( $search ) . '%';
				$where_values[] = '%' . $wpdb->esc_like( $search ) . '%';
			}

			if ( $asset_type ) {
				$where_conditions[] = "asset_type = %s";
				$where_values[] = $asset_type;
			}

			$where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );

			// Get total count
			$count_query = "SELECT COUNT(*) FROM $table_name $where_clause";
			$total_items = $wpdb->get_var( $wpdb->prepare( $count_query, $where_values ) );

			// Main query with calculations
			$query = "
				SELECT *,
					   (quantity * current_price) as current_value,
					   (quantity * purchase_price) as invested_value,
					   ((quantity * current_price) - (quantity * purchase_price)) as profit_loss,
					   (CASE WHEN purchase_price > 0 
						THEN (((current_price - purchase_price) / purchase_price) * 100)
						ELSE 0 END) as profit_loss_percentage
				FROM $table_name
				$where_clause
				ORDER BY $orderby $order
				LIMIT %d OFFSET %d
			";

			$query_values = array_merge( $where_values, array( $per_page, $offset ) );
			$assets = $wpdb->get_results( $wpdb->prepare( $query, $query_values ) );

			// Calculate pagination
			$total_pages = ceil( $total_items / $per_page );

			return rest_ensure_response( array(
				'data' => $assets,
				'pagination' => array(
					'page' => $page,
					'per_page' => $per_page,
					'total_items' => (int) $total_items,
					'total_pages' => $total_pages,
					'has_next' => $page < $total_pages,
					'has_prev' => $page > 1,
				),
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error fetching assets' );
		}
	}

	/**
	 * Get single asset.
	 */
	public function get_asset( $request ) {
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
			$user_id = get_current_user_id();
			$asset_id = $request->get_param( 'id' );

			$asset = $wpdb->get_row( $wpdb->prepare(
				"SELECT *,
						(quantity * current_price) as current_value,
						(quantity * purchase_price) as invested_value,
						((quantity * current_price) - (quantity * purchase_price)) as profit_loss,
						(CASE WHEN purchase_price > 0 
						 THEN (((current_price - purchase_price) / purchase_price) * 100)
						 ELSE 0 END) as profit_loss_percentage
				 FROM $table_name 
				 WHERE id = %d AND user_id = %d",
				$asset_id,
				$user_id
			) );

			if ( ! $asset ) {
				return new WP_Error( 'asset_not_found', 'Asset not found', array( 'status' => 404 ) );
			}

			// Get price history for this asset
			$price_history_table = $wpdb->prefix . 'portfoy_takipx_price_history';
			$price_history = $wpdb->get_results( $wpdb->prepare(
				"SELECT price, recorded_at, price_change_percentage_24h 
				 FROM $price_history_table 
				 WHERE symbol = %s 
				 ORDER BY recorded_at DESC 
				 LIMIT 30",
				$asset->symbol
			) );

			$asset->price_history = $price_history;

			return rest_ensure_response( $asset );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error fetching asset' );
		}
	}

	/**
	 * Create a new asset.
	 */
	public function create_asset( $request ) {
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
			$user_id = get_current_user_id();

			// Check if asset already exists for this user
			$existing = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM $table_name WHERE user_id = %d AND symbol = %s AND asset_type = %s",
				$user_id,
				$request->get_param( 'symbol' ),
				$request->get_param( 'asset_type' )
			) );

			if ( $existing ) {
				return new WP_Error( 'asset_exists', 'Asset already exists in your portfolio', array( 'status' => 409 ) );
			}

			// Fetch current price if not provided
			$current_price = $request->get_param( 'current_price' );
			if ( ! $current_price ) {
				$price_data = $this->price_service->fetch_current_price( 
					$request->get_param( 'symbol' ), 
					$request->get_param( 'asset_type' ) 
				);
				$current_price = $price_data ? ($price_data['price'] ?? $price_data['price_usd'] ?? 0) : 0;
			}

			$result = $wpdb->insert(
				$table_name,
				array(
					'user_id' => $user_id,
					'asset_type' => $request->get_param( 'asset_type' ),
					'symbol' => strtoupper( $request->get_param( 'symbol' ) ),
					'name' => $request->get_param( 'name' ),
					'quantity' => $request->get_param( 'quantity' ),
					'purchase_price' => $request->get_param( 'purchase_price' ),
					'current_price' => $current_price,
					'purchase_date' => $request->get_param( 'purchase_date' ) ?: current_time( 'mysql' ),
					'broker' => $request->get_param( 'broker' ),
					'commission' => $request->get_param( 'commission' ) ?: 0,
					'notes' => $request->get_param( 'notes' ),
					'status' => 'active',
				),
				array( '%d', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%f', '%s', '%s' )
			);

			if ( $result === false ) {
				throw new Exception( 'Failed to create asset: ' . $wpdb->last_error );
			}

			$asset_id = $wpdb->insert_id;

			// Create initial transaction record
			$this->create_initial_transaction( $asset_id, $request );

			// Invalidate cache
			$this->cache->invalidate_portfolio_cache( $user_id );

			// Get the created asset
			$asset = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d",
				$asset_id
			) );

			return rest_ensure_response( array(
				'message' => 'Asset created successfully',
				'asset' => $asset,
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error creating asset' );
		}
	}

	/**
	 * Update an asset.
	 */
	public function update_asset( $request ) {
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
			$user_id = get_current_user_id();
			$asset_id = $request->get_param( 'id' );

			// Verify ownership
			$asset = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
				$asset_id,
				$user_id
			) );

			if ( ! $asset ) {
				return new WP_Error( 'asset_not_found', 'Asset not found', array( 'status' => 404 ) );
			}

			// Build update data
			$update_data = array();
			$update_format = array();

			$updatable_fields = array(
				'asset_type' => '%s',
				'symbol' => '%s', 
				'name' => '%s',
				'quantity' => '%f',
				'purchase_price' => '%f',
				'current_price' => '%f',
				'purchase_date' => '%s',
				'broker' => '%s',
				'commission' => '%f',
				'notes' => '%s'
			);

			foreach ( $updatable_fields as $field => $format ) {
				$value = $request->get_param( $field );
				if ( $value !== null ) {
					$update_data[ $field ] = $value;
					$update_format[] = $format;
				}
			}

			if ( empty( $update_data ) ) {
				return new WP_Error( 'no_data', 'No data provided for update', array( 'status' => 400 ) );
			}

			$update_data['updated_at'] = current_time( 'mysql' );
			$update_format[] = '%s';

			$result = $wpdb->update(
				$table_name,
				$update_data,
				array( 'id' => $asset_id, 'user_id' => $user_id ),
				$update_format,
				array( '%d', '%d' )
			);

			if ( $result === false ) {
				throw new Exception( 'Failed to update asset: ' . $wpdb->last_error );
			}

			// Invalidate cache
			$this->cache->invalidate_portfolio_cache( $user_id );

			// Get updated asset
			$updated_asset = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d",
				$asset_id
			) );

			return rest_ensure_response( array(
				'message' => 'Asset updated successfully',
				'asset' => $updated_asset,
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error updating asset' );
		}
	}

	/**
	 * Delete an asset.
	 */
	public function delete_asset( $request ) {
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
			$user_id = get_current_user_id();
			$asset_id = $request->get_param( 'id' );

			// Verify ownership
			$asset = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
				$asset_id,
				$user_id
			) );

			if ( ! $asset ) {
				return new WP_Error( 'asset_not_found', 'Asset not found', array( 'status' => 404 ) );
			}

			// Soft delete - mark as archived
			$result = $wpdb->update(
				$table_name,
				array( 'status' => 'archived', 'updated_at' => current_time( 'mysql' ) ),
				array( 'id' => $asset_id, 'user_id' => $user_id ),
				array( '%s', '%s' ),
				array( '%d', '%d' )
			);

			if ( $result === false ) {
				throw new Exception( 'Failed to delete asset: ' . $wpdb->last_error );
			}

			// Invalidate cache
			$this->cache->invalidate_portfolio_cache( $user_id );

			return rest_ensure_response( array(
				'message' => 'Asset deleted successfully',
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error deleting asset' );
		}
	}

	/**
	 * Get portfolio summary with caching.
	 */
	public function get_portfolio_summary( $request ) {
		try {
			$user_id = get_current_user_id();
			
			$summary = $this->cache->cache_portfolio_summary( $user_id );
			
			return rest_ensure_response( $summary );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error fetching portfolio summary' );
		}
	}

	/**
	 * Get portfolio performance data.
	 */
	public function get_portfolio_performance( $request ) {
		try {
			$user_id = get_current_user_id();
			$date_from = $request->get_param( 'date_from' ) ?: date( 'Y-m-d', strtotime( '-1 year' ) );
			$date_to = $request->get_param( 'date_to' ) ?: date( 'Y-m-d' );

			$cache_key = "performance_{$user_id}_" . md5( $date_from . $date_to );
			
			$performance = $this->cache->get_or_set( $cache_key, function() use ( $date_from, $date_to ) {
				$reports = new Portfoy_TakipX_Reports();
				return $reports->get_performance_analysis( $date_from, $date_to );
			}, 3600 );

			return rest_ensure_response( $performance );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error fetching portfolio performance' );
		}
	}

	/**
	 * Get portfolio allocation.
	 */
	public function get_portfolio_allocation( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();

		$allocation = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				asset_type,
				SUM(quantity * current_price) as current_value,
				COUNT(*) as asset_count
			FROM $table_name 
			WHERE user_id = %d 
			GROUP BY asset_type",
			$user_id
		) );

		$total_value = 0;
		foreach ( $allocation as $item ) {
			$total_value += $item->current_value;
		}

		// Calculate percentages
		foreach ( $allocation as $item ) {
			$item->percentage = $total_value > 0 ? ( $item->current_value / $total_value ) * 100 : 0;
		}

		return rest_ensure_response( array(
			'allocation' => $allocation,
			'total_value' => $total_value,
		) );
	}

	/**
	 * Get risk analysis.
	 */
	public function get_risk_analysis( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();

		$assets = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE user_id = %d",
			$user_id
		) );

		$total_value = 0;
		$portfolio_return = 0;
		$risk_scores = array();

		foreach ( $assets as $asset ) {
			$value = $asset->quantity * $asset->current_price;
			$return_pct = ( $asset->current_price - $asset->purchase_price ) / $asset->purchase_price * 100;
			
			$total_value += $value;
			$portfolio_return += $return_pct * $value;

			// Simple risk scoring based on asset type
			$risk_score = $this->get_asset_risk_score( $asset->asset_type );
			$risk_scores[] = $risk_score * ( $value / max( $total_value, 1 ) );
		}

		$avg_return = $total_value > 0 ? $portfolio_return / $total_value : 0;
		$avg_risk = array_sum( $risk_scores );

		return rest_ensure_response( array(
			'average_return' => $avg_return,
			'risk_score' => $avg_risk,
			'risk_level' => $this->get_risk_level( $avg_risk ),
			'recommendations' => $this->get_risk_recommendations( $avg_risk ),
		) );
	}

	/**
	 * Get transactions.
	 */
	public function get_transactions( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_transactions';
		$user_id = get_current_user_id();

		$page = $request->get_param( 'page' ) ?? 1;
		$per_page = $request->get_param( 'per_page' ) ?? 20;
		$offset = ( $page - 1 ) * $per_page;

		$transactions = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE user_id = %d 
			ORDER BY transaction_date DESC 
			LIMIT %d OFFSET %d",
			$user_id, $per_page, $offset
		) );

		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
			$user_id
		) );

		return rest_ensure_response( array(
			'transactions' => $transactions,
			'total' => intval( $total ),
			'page' => $page,
			'per_page' => $per_page,
		) );
	}

	/**
	 * Create transaction.
	 */
	public function create_transaction( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_transactions';
		$user_id = get_current_user_id();

		$data = array(
			'user_id' => $user_id,
			'asset_id' => intval( $request['asset_id'] ),
			'transaction_type' => sanitize_text_field( $request['transaction_type'] ),
			'quantity' => floatval( $request['quantity'] ),
			'price' => floatval( $request['price'] ),
			'transaction_date' => sanitize_text_field( $request['transaction_date'] ),
			'commission' => floatval( $request['commission'] ?? 0 ),
			'notes' => sanitize_textarea_field( $request['notes'] ?? '' ),
		);

		$result = $wpdb->insert( $table_name, $data );

		if ( $result === false ) {
			return new WP_Error( 'insert_failed', 'Failed to create transaction', array( 'status' => 500 ) );
		}

		$data['id'] = $wpdb->insert_id;
		return rest_ensure_response( $data );
	}

	/**
	 * Get top performers.
	 */
	public function get_top_performers( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		$user_id = get_current_user_id();

		$limit = $request->get_param( 'limit' ) ?? 10;

		$performers = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				*,
				(current_price - purchase_price) / purchase_price * 100 as return_percentage,
				(quantity * current_price - quantity * purchase_price) as profit_loss
			FROM $table_name 
			WHERE user_id = %d 
			ORDER BY return_percentage DESC 
			LIMIT %d",
			$user_id, $limit
		) );

		return rest_ensure_response( array(
			'top_performers' => $performers,
			'limit' => $limit,
		) );
	}

	/**
	 * Get monthly performance.
	 */
	public function get_monthly_performance( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_portfolio_snapshots';
		$user_id = get_current_user_id();

		$months = $request->get_param( 'months' ) ?? 12;

		$performance = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				DATE_FORMAT(snapshot_date, '%%Y-%%m') as month,
				AVG(total_value) as avg_value,
				MAX(total_value) as max_value,
				MIN(total_value) as min_value
			FROM $table_name 
			WHERE user_id = %d 
			AND snapshot_date >= DATE_SUB(NOW(), INTERVAL %d MONTH)
			GROUP BY DATE_FORMAT(snapshot_date, '%%Y-%%m')
			ORDER BY month ASC",
			$user_id, $months
		) );

		return rest_ensure_response( array(
			'monthly_performance' => $performance,
			'months' => $months,
		) );
	}

	/**
	 * Get current price for an asset.
	 */
	public function get_asset_price( $request ) {
		try {
			$symbol = $request->get_param( 'symbol' );
			$asset_type = $request->get_param( 'asset_type' );

			$price_data = $this->price_service->fetch_current_price( $symbol, $asset_type );

			if ( $price_data === false ) {
				return new WP_Error( 'price_not_found', 'Could not fetch current price', array( 'status' => 404 ) );
			}

			return rest_ensure_response( $price_data );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error fetching asset price' );
		}
	}

	/**
	 * Update all prices.
	 */
	public function update_all_prices( $request ) {
		try {
			$result = $this->price_service->bulk_update_prices();
			
			return rest_ensure_response( array(
				'message' => 'Prices updated successfully',
				'stats' => $result,
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error updating prices' );
		}
	}

	/**
	 * Clear cache.
	 */
	public function clear_cache( $request ) {
		try {
			$this->cache->flush();
			
			return rest_ensure_response( array(
				'message' => 'Cache cleared successfully',
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error clearing cache' );
		}
	}

	/**
	 * Get cache statistics.
	 */
	public function get_cache_stats( $request ) {
		try {
			$stats = $this->cache->get_cache_stats();
			$provider_stats = $this->price_service->get_provider_stats();
			
			return rest_ensure_response( array(
				'cache' => $stats,
				'providers' => $provider_stats,
			) );

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error fetching cache stats' );
		}
	}

	/**
	 * Export portfolio data.
	 */
	public function export_portfolio( $request ) {
		try {
			$format = $request->get_param( 'format' );
			$user_id = get_current_user_id();

			$reports = new Portfoy_TakipX_Reports();
			$report_data = $reports->generate_portfolio_report();

			switch ( $format ) {
				case 'csv':
					return $this->export_csv( $report_data );
				case 'json':
					return rest_ensure_response( $report_data );
				default:
					return new WP_Error( 'invalid_format', 'Invalid export format', array( 'status' => 400 ) );
			}

		} catch ( Exception $e ) {
			return $this->handle_error( $e, 'Error exporting portfolio' );
		}
	}

	/**
	 * Create initial transaction record.
	 */
	private function create_initial_transaction( $asset_id, $request ) {
		global $wpdb;
		$transactions_table = $wpdb->prefix . 'portfoy_takipx_transactions';
		
		$quantity = $request->get_param( 'quantity' );
		$purchase_price = $request->get_param( 'purchase_price' );
		$commission = $request->get_param( 'commission' ) ?: 0;
		
		$wpdb->insert(
			$transactions_table,
			array(
				'user_id' => get_current_user_id(),
				'asset_id' => $asset_id,
				'transaction_type' => 'buy',
				'quantity' => $quantity,
				'price' => $purchase_price,
				'commission' => $commission,
				'total_amount' => ($quantity * $purchase_price) + $commission,
				'transaction_date' => $request->get_param( 'purchase_date' ) ?: current_time( 'mysql' ),
				'broker' => $request->get_param( 'broker' ),
				'notes' => 'Initial purchase',
			),
			array( '%d', '%d', '%s', '%f', '%f', '%f', '%f', '%s', '%s', '%s' )
		);
	}

	/**
	 * Export data as CSV.
	 */
	private function export_csv( $data ) {
		$filename = 'portfolio_export_' . date( 'Y-m-d' ) . '.csv';
		
		// Set headers for file download
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		
		$output = fopen( 'php://output', 'w' );
		
		// Write CSV content
		fputcsv( $output, array( 'Portfolio Export - ' . date( 'Y-m-d H:i:s' ) ) );
		fputcsv( $output, array() );
		
		// Summary section
		fputcsv( $output, array( 'PORTFOLIO SUMMARY' ) );
		foreach ( $data['summary'] as $key => $value ) {
			fputcsv( $output, array( ucwords( str_replace( '_', ' ', $key ) ), $value ) );
		}
		
		fclose( $output );
		exit;
	}

	/**
	 * Handle API errors consistently.
	 */
	private function handle_error( $exception, $message = 'An error occurred' ) {
		error_log( 'Portfoy TakipX API Error: ' . $exception->getMessage() );
		
		return new WP_Error(
			'api_error',
			$message . ': ' . $exception->getMessage(),
			array( 'status' => 500 )
		);
	}

	/**
	 * Get risk score for asset type.
	 */
	private function get_asset_risk_score( $asset_type ) {
		$risk_scores = array(
			'bond' => 1,
			'stock' => 3,
			'forex' => 4,
			'commodity' => 4,
			'crypto' => 5,
		);

		return $risk_scores[ $asset_type ] ?? 3;
	}

	/**
	 * Get risk level description.
	 */
	private function get_risk_level( $risk_score ) {
		if ( $risk_score <= 1.5 ) {
			return 'Low';
		} elseif ( $risk_score <= 2.5 ) {
			return 'Low-Medium';
		} elseif ( $risk_score <= 3.5 ) {
			return 'Medium';
		} elseif ( $risk_score <= 4.5 ) {
			return 'Medium-High';
		} else {
			return 'High';
		}
	}

	/**
	 * Get risk recommendations.
	 */
	private function get_risk_recommendations( $risk_score ) {
		if ( $risk_score <= 2 ) {
			return array(
				'Consider adding some growth assets to your portfolio',
				'Your portfolio is very conservative',
			);
		} elseif ( $risk_score <= 3 ) {
			return array(
				'Well-balanced portfolio',
				'Consider regular rebalancing',
			);
		} elseif ( $risk_score <= 4 ) {
			return array(
				'Higher risk portfolio',
				'Consider adding some stable assets',
				'Monitor volatility regularly',
			);
		} else {
			return array(
				'Very high risk portfolio',
				'Consider diversifying with lower-risk assets',
				'Review your risk tolerance',
			);
		}
	}

	/**
	 * Validation functions
	 */
	public function validate_asset_type( $value ) {
		$valid_types = array( 'stock', 'crypto', 'forex', 'commodity', 'bond' );
		return in_array( $value, $valid_types );
	}

	public function validate_positive_number( $value ) {
		return is_numeric( $value ) && $value > 0;
	}

	public function validate_date( $value ) {
		return strtotime( $value ) !== false;
	}
}

// Initialize the API
new Portfoy_TakipX_API();