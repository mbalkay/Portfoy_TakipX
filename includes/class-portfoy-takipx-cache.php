<?php

/**
 * Advanced Caching System for Portföy TakipX
 */
class Portfoy_TakipX_Cache {

	private static $instance = null;
	private $cache_group = 'portfoy_takipx';
	private $default_expiration = 3600; // 1 hour

	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Initialize cache hooks
		add_action( 'init', array( $this, 'init_cache' ) );
	}

	/**
	 * Initialize caching system
	 */
	public function init_cache() {
		// Add cache groups
		wp_cache_add_global_groups( array( $this->cache_group ) );
		
		// Schedule cache cleanup
		if ( ! wp_next_scheduled( 'portfoy_takipx_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'portfoy_takipx_cache_cleanup' );
		}
		
		add_action( 'portfoy_takipx_cache_cleanup', array( $this, 'cleanup_expired_cache' ) );
	}

	/**
	 * Set cache with automatic expiration and compression
	 */
	public function set( $key, $data, $expiration = null ) {
		$expiration = $expiration ?: $this->default_expiration;
		
		// Compress large data
		if ( is_array( $data ) || is_object( $data ) ) {
			$data = maybe_serialize( $data );
		}
		
		if ( strlen( $data ) > 1024 ) { // Compress if larger than 1KB
			$data = gzcompress( $data, 9 );
			$compressed = true;
		} else {
			$compressed = false;
		}

		$cache_data = array(
			'data' => $data,
			'compressed' => $compressed,
			'timestamp' => time(),
			'expiration' => $expiration
		);

		return wp_cache_set( $key, $cache_data, $this->cache_group, $expiration );
	}

	/**
	 * Get cached data with automatic decompression
	 */
	public function get( $key ) {
		$cache_data = wp_cache_get( $key, $this->cache_group );
		
		if ( $cache_data === false ) {
			return false;
		}

		// Check if expired
		if ( time() > ( $cache_data['timestamp'] + $cache_data['expiration'] ) ) {
			$this->delete( $key );
			return false;
		}

		$data = $cache_data['data'];
		
		// Decompress if needed
		if ( $cache_data['compressed'] ) {
			$data = gzuncompress( $data );
		}

		return maybe_unserialize( $data );
	}

	/**
	 * Delete cache entry
	 */
	public function delete( $key ) {
		return wp_cache_delete( $key, $this->cache_group );
	}

	/**
	 * Flush all plugin cache
	 */
	public function flush() {
		return wp_cache_flush();
	}

	/**
	 * Get cache with callback for cache miss
	 */
	public function get_or_set( $key, $callback, $expiration = null ) {
		$data = $this->get( $key );
		
		if ( $data === false ) {
			$data = call_user_func( $callback );
			if ( $data !== false ) {
				$this->set( $key, $data, $expiration );
			}
		}
		
		return $data;
	}

	/**
	 * Cache portfolio summary with intelligent invalidation
	 */
	public function cache_portfolio_summary( $user_id ) {
		$key = "portfolio_summary_{$user_id}";
		
		return $this->get_or_set( $key, function() use ( $user_id ) {
			$reports = new Portfoy_TakipX_Reports();
			return $reports->get_portfolio_summary();
		}, 900 ); // 15 minutes
	}

	/**
	 * Cache asset prices with short expiration
	 */
	public function cache_asset_price( $symbol, $price_data ) {
		$key = "asset_price_{$symbol}";
		return $this->set( $key, $price_data, 300 ); // 5 minutes
	}

	/**
	 * Get cached asset price
	 */
	public function get_cached_asset_price( $symbol ) {
		$key = "asset_price_{$symbol}";
		return $this->get( $key );
	}

	/**
	 * Cache performance data with longer expiration
	 */
	public function cache_performance_data( $user_id, $date_from, $date_to, $data ) {
		$key = "performance_{$user_id}_" . md5( $date_from . $date_to );
		return $this->set( $key, $data, 3600 ); // 1 hour
	}

	/**
	 * Invalidate cache when portfolio changes
	 */
	public function invalidate_portfolio_cache( $user_id ) {
		$patterns = array(
			"portfolio_summary_{$user_id}",
			"performance_{$user_id}_*",
			"allocation_{$user_id}",
			"risk_analysis_{$user_id}"
		);

		foreach ( $patterns as $pattern ) {
			if ( strpos( $pattern, '*' ) !== false ) {
				// For wildcard patterns, we need to iterate through possible keys
				// This is a simplified approach - in production, you might want to use Redis with SCAN
				$base_key = str_replace( '*', '', $pattern );
				for ( $i = 0; $i < 100; $i++ ) { // Reasonable limit
					$this->delete( $base_key . $i );
				}
			} else {
				$this->delete( $pattern );
			}
		}
	}

	/**
	 * Clean up expired cache entries
	 */
	public function cleanup_expired_cache() {
		// This would be implemented differently based on the caching backend
		// For WordPress object cache, we rely on the backend's own cleanup
		do_action( 'portfoy_takipx_cache_cleaned_up' );
	}

	/**
	 * Get cache statistics
	 */
	public function get_cache_stats() {
		return array(
			'cache_hits' => get_option( 'portfoy_takipx_cache_hits', 0 ),
			'cache_misses' => get_option( 'portfoy_takipx_cache_misses', 0 ),
			'cache_size' => $this->estimate_cache_size(),
			'last_cleanup' => get_option( 'portfoy_takipx_last_cache_cleanup', 0 )
		);
	}

	/**
	 * Estimate cache size (approximation)
	 */
	private function estimate_cache_size() {
		// This is an approximation since WordPress object cache doesn't provide size info
		return get_option( 'portfoy_takipx_estimated_cache_size', 0 );
	}

	/**
	 * Warm up cache with frequently accessed data
	 */
	public function warm_up_cache( $user_id ) {
		// Pre-load frequently accessed data
		$this->cache_portfolio_summary( $user_id );
		
		// Cache asset allocation
		$key = "allocation_{$user_id}";
		$this->get_or_set( $key, function() {
			$reports = new Portfoy_TakipX_Reports();
			return $reports->get_asset_allocation();
		}, 1800 ); // 30 minutes

		// Cache risk analysis
		$key = "risk_analysis_{$user_id}";
		$this->get_or_set( $key, function() {
			$reports = new Portfoy_TakipX_Reports();
			return $reports->get_risk_analysis();
		}, 3600 ); // 1 hour
	}
}

/**
 * Price Fetching Service with Multiple API Providers
 */
class Portfoy_TakipX_Price_Service {

	private $cache;
	private $api_providers;
	private $rate_limits;

	public function __construct() {
		$this->cache = Portfoy_TakipX_Cache::getInstance();
		$this->init_api_providers();
		$this->rate_limits = array();
	}

	/**
	 * Initialize API providers with fallback support
	 */
	private function init_api_providers() {
		$this->api_providers = array(
			'crypto' => array(
				'coingecko' => array(
					'url' => 'https://api.coingecko.com/api/v3/simple/price',
					'rate_limit' => 50, // requests per minute
					'active' => true
				),
				'binance' => array(
					'url' => 'https://api.binance.com/api/v3/ticker/price',
					'rate_limit' => 1200, // requests per minute
					'active' => true
				)
			),
			'stock' => array(
				'alphavantage' => array(
					'url' => 'https://www.alphavantage.co/query',
					'api_key' => get_option( 'portfoy_takipx_alphavantage_key', '' ),
					'rate_limit' => 5, // requests per minute
					'active' => !empty( get_option( 'portfoy_takipx_alphavantage_key', '' ) )
				),
				'yahoo' => array(
					'url' => 'https://query1.finance.yahoo.com/v8/finance/chart',
					'rate_limit' => 2000, // requests per hour
					'active' => true
				)
			),
			'forex' => array(
				'exchangerate' => array(
					'url' => 'https://api.exchangerate-api.com/v4/latest',
					'rate_limit' => 1500, // requests per month
					'active' => true
				),
				'fixer' => array(
					'url' => 'http://data.fixer.io/api/latest',
					'api_key' => get_option( 'portfoy_takipx_fixer_key', '' ),
					'rate_limit' => 1000, // requests per month
					'active' => !empty( get_option( 'portfoy_takipx_fixer_key', '' ) )
				)
			)
		);
	}

	/**
	 * Fetch current price with intelligent provider selection
	 */
	public function fetch_current_price( $symbol, $asset_type ) {
		// Check cache first
		$cached_price = $this->cache->get_cached_asset_price( $symbol );
		if ( $cached_price !== false ) {
			return $cached_price;
		}

		// Try to fetch from available providers
		$providers = $this->api_providers[ $asset_type ] ?? array();
		
		foreach ( $providers as $provider_name => $provider_config ) {
			if ( ! $provider_config['active'] ) {
				continue;
			}

			// Check rate limits
			if ( $this->is_rate_limited( $provider_name ) ) {
				continue;
			}

			try {
				$price_data = $this->fetch_from_provider( $symbol, $asset_type, $provider_name );
				
				if ( $price_data !== false ) {
					// Cache the successful result
					$this->cache->cache_asset_price( $symbol, $price_data );
					
					// Update rate limit counter
					$this->update_rate_limit( $provider_name );
					
					// Store in price history
					$this->store_price_history( $symbol, $asset_type, $price_data, $provider_name );
					
					return $price_data;
				}
			} catch ( Exception $e ) {
				error_log( "Portfoy TakipX: Error fetching price from {$provider_name}: " . $e->getMessage() );
				continue;
			}
		}

		return false;
	}

	/**
	 * Fetch price from specific provider
	 */
	private function fetch_from_provider( $symbol, $asset_type, $provider_name ) {
		$provider = $this->api_providers[ $asset_type ][ $provider_name ];
		
		switch ( $asset_type ) {
			case 'crypto':
				return $this->fetch_crypto_price( $symbol, $provider_name, $provider );
			case 'stock':
				return $this->fetch_stock_price( $symbol, $provider_name, $provider );
			case 'forex':
				return $this->fetch_forex_price( $symbol, $provider_name, $provider );
			default:
				return false;
		}
	}

	/**
	 * Fetch cryptocurrency price
	 */
	private function fetch_crypto_price( $symbol, $provider_name, $provider ) {
		switch ( $provider_name ) {
			case 'coingecko':
				$url = $provider['url'] . '?ids=' . strtolower( $symbol ) . '&vs_currencies=usd,try';
				$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
				
				if ( is_wp_error( $response ) ) {
					return false;
				}
				
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data[ strtolower( $symbol ) ] ) ) {
					return array(
						'price_usd' => $data[ strtolower( $symbol ) ]['usd'],
						'price_try' => $data[ strtolower( $symbol ) ]['try'] ?? null,
						'timestamp' => time(),
						'provider' => $provider_name
					);
				}
				break;

			case 'binance':
				$url = $provider['url'] . '?symbol=' . strtoupper( $symbol ) . 'USDT';
				$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
				
				if ( is_wp_error( $response ) ) {
					return false;
				}
				
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data['price'] ) ) {
					return array(
						'price_usd' => floatval( $data['price'] ),
						'price_try' => null,
						'timestamp' => time(),
						'provider' => $provider_name
					);
				}
				break;
		}
		
		return false;
	}

	/**
	 * Fetch stock price
	 */
	private function fetch_stock_price( $symbol, $provider_name, $provider ) {
		switch ( $provider_name ) {
			case 'alphavantage':
				if ( empty( $provider['api_key'] ) ) {
					return false;
				}
				
				$url = $provider['url'] . '?function=GLOBAL_QUOTE&symbol=' . $symbol . '&apikey=' . $provider['api_key'];
				$response = wp_remote_get( $url, array( 'timeout' => 15 ) );
				
				if ( is_wp_error( $response ) ) {
					return false;
				}
				
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data['Global Quote']['05. price'] ) ) {
					return array(
						'price' => floatval( $data['Global Quote']['05. price'] ),
						'change' => floatval( $data['Global Quote']['09. change'] ),
						'change_percent' => str_replace( '%', '', $data['Global Quote']['10. change percent'] ),
						'timestamp' => time(),
						'provider' => $provider_name
					);
				}
				break;

			case 'yahoo':
				$url = $provider['url'] . '/' . $symbol;
				$response = wp_remote_get( $url, array( 'timeout' => 15 ) );
				
				if ( is_wp_error( $response ) ) {
					return false;
				}
				
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data['chart']['result'][0]['meta']['regularMarketPrice'] ) ) {
					$result = $data['chart']['result'][0];
					return array(
						'price' => floatval( $result['meta']['regularMarketPrice'] ),
						'change' => floatval( $result['meta']['regularMarketPrice'] - $result['meta']['previousClose'] ),
						'change_percent' => (($result['meta']['regularMarketPrice'] - $result['meta']['previousClose']) / $result['meta']['previousClose']) * 100,
						'timestamp' => time(),
						'provider' => $provider_name
					);
				}
				break;
		}
		
		return false;
	}

	/**
	 * Fetch forex price
	 */
	private function fetch_forex_price( $symbol, $provider_name, $provider ) {
		// Parse forex symbol (e.g., EUR/USD)
		$parts = explode( '/', $symbol );
		if ( count( $parts ) !== 2 ) {
			return false;
		}
		
		$base = $parts[0];
		$quote = $parts[1];
		
		switch ( $provider_name ) {
			case 'exchangerate':
				$url = $provider['url'] . '/' . $base;
				$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
				
				if ( is_wp_error( $response ) ) {
					return false;
				}
				
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data['rates'][ $quote ] ) ) {
					return array(
						'price' => floatval( $data['rates'][ $quote ] ),
						'timestamp' => time(),
						'provider' => $provider_name
					);
				}
				break;

			case 'fixer':
				if ( empty( $provider['api_key'] ) ) {
					return false;
				}
				
				$url = $provider['url'] . '?access_key=' . $provider['api_key'] . '&base=' . $base . '&symbols=' . $quote;
				$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
				
				if ( is_wp_error( $response ) ) {
					return false;
				}
				
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data['rates'][ $quote ] ) ) {
					return array(
						'price' => floatval( $data['rates'][ $quote ] ),
						'timestamp' => time(),
						'provider' => $provider_name
					);
				}
				break;
		}
		
		return false;
	}

	/**
	 * Check if provider is rate limited
	 */
	private function is_rate_limited( $provider_name ) {
		$key = "rate_limit_{$provider_name}";
		$limit_data = get_transient( $key );
		
		if ( $limit_data === false ) {
			return false;
		}
		
		$provider_config = null;
		foreach ( $this->api_providers as $asset_type => $providers ) {
			if ( isset( $providers[ $provider_name ] ) ) {
				$provider_config = $providers[ $provider_name ];
				break;
			}
		}
		
		if ( ! $provider_config ) {
			return false;
		}
		
		return $limit_data['count'] >= $provider_config['rate_limit'];
	}

	/**
	 * Update rate limit counter
	 */
	private function update_rate_limit( $provider_name ) {
		$key = "rate_limit_{$provider_name}";
		$limit_data = get_transient( $key );
		
		if ( $limit_data === false ) {
			$limit_data = array( 'count' => 0, 'timestamp' => time() );
		}
		
		$limit_data['count']++;
		
		// Reset counter every hour
		if ( time() - $limit_data['timestamp'] > 3600 ) {
			$limit_data = array( 'count' => 1, 'timestamp' => time() );
		}
		
		set_transient( $key, $limit_data, 3600 );
	}

	/**
	 * Store price data in history table
	 */
	private function store_price_history( $symbol, $asset_type, $price_data, $provider ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'portfoy_takipx_price_history';
		
		$price = $price_data['price'] ?? $price_data['price_usd'] ?? 0;
		$change_24h = $price_data['change'] ?? 0;
		$change_percent_24h = $price_data['change_percent'] ?? 0;
		
		$wpdb->replace(
			$table_name,
			array(
				'symbol' => $symbol,
				'asset_type' => $asset_type,
				'price' => $price,
				'volume' => $price_data['volume'] ?? 0,
				'market_cap' => $price_data['market_cap'] ?? 0,
				'price_change_24h' => $change_24h,
				'price_change_percentage_24h' => $change_percent_24h,
				'recorded_at' => current_time( 'mysql' ),
				'source' => $provider
			),
			array( '%s', '%s', '%f', '%d', '%f', '%f', '%f', '%s', '%s' )
		);
	}

	/**
	 * Bulk update prices for all active assets
	 */
	public function bulk_update_prices( $user_id = null ) {
		global $wpdb;
		
		$assets_table = $wpdb->prefix . 'portfoy_takipx_assets';
		
		$where_clause = "WHERE status = 'active'";
		$params = array();
		
		if ( $user_id ) {
			$where_clause .= " AND user_id = %d";
			$params[] = $user_id;
		}
		
		$query = "SELECT DISTINCT symbol, asset_type FROM {$assets_table} {$where_clause}";
		
		if ( ! empty( $params ) ) {
			$assets = $wpdb->get_results( $wpdb->prepare( $query, $params ) );
		} else {
			$assets = $wpdb->get_results( $query );
		}
		
		$updated_count = 0;
		$failed_count = 0;
		
		foreach ( $assets as $asset ) {
			$price_data = $this->fetch_current_price( $asset->symbol, $asset->asset_type );
			
			if ( $price_data !== false ) {
				$price = $price_data['price'] ?? $price_data['price_usd'] ?? 0;
				
				// Update asset prices
				$updated = $wpdb->update(
					$assets_table,
					array( 'current_price' => $price, 'updated_at' => current_time( 'mysql' ) ),
					array( 'symbol' => $asset->symbol ),
					array( '%f', '%s' ),
					array( '%s' )
				);
				
				if ( $updated !== false ) {
					$updated_count++;
				} else {
					$failed_count++;
				}
			} else {
				$failed_count++;
			}
			
			// Small delay to respect rate limits
			usleep( 100000 ); // 100ms
		}
		
		// Log the update results
		error_log( "Portfoy TakipX: Bulk price update completed. Updated: {$updated_count}, Failed: {$failed_count}" );
		
		// Update the statistics
		update_option( 'portfoy_takipx_last_price_update', time() );
		update_option( 'portfoy_takipx_price_update_stats', array(
			'updated' => $updated_count,
			'failed' => $failed_count,
			'timestamp' => time()
		) );
		
		return array(
			'updated' => $updated_count,
			'failed' => $failed_count
		);
	}

	/**
	 * Get provider statistics
	 */
	public function get_provider_stats() {
		$stats = array();
		
		foreach ( $this->api_providers as $asset_type => $providers ) {
			foreach ( $providers as $provider_name => $provider_config ) {
				$key = "rate_limit_{$provider_name}";
				$limit_data = get_transient( $key );
				
				$stats[ $provider_name ] = array(
					'asset_type' => $asset_type,
					'active' => $provider_config['active'],
					'rate_limit' => $provider_config['rate_limit'],
					'current_usage' => $limit_data ? $limit_data['count'] : 0,
					'usage_percentage' => $limit_data ? round( ($limit_data['count'] / $provider_config['rate_limit']) * 100, 2 ) : 0
				);
			}
		}
		
		return $stats;
	}
}