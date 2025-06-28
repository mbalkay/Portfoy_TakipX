<?php

/**
 * Advanced Logging System for Portföy TakipX
 */
class Portfoy_TakipX_Logger {

	private static $instance = null;
	private $log_level;
	private $log_dir;
	private $max_log_size = 10485760; // 10MB
	private $max_log_files = 5;

	const LEVEL_DEBUG = 1;
	const LEVEL_INFO = 2;
	const LEVEL_WARNING = 3;
	const LEVEL_ERROR = 4;
	const LEVEL_CRITICAL = 5;

	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->log_level = get_option( 'portfoy_takipx_log_level', self::LEVEL_ERROR );
		$this->log_dir = WP_CONTENT_DIR . '/uploads/portfoy-takipx-logs/';
		
		// Create log directory if it doesn't exist
		if ( ! file_exists( $this->log_dir ) ) {
			wp_mkdir_p( $this->log_dir );
			
			// Add .htaccess to protect log files
			$htaccess_content = "Order deny,allow\nDeny from all";
			file_put_contents( $this->log_dir . '.htaccess', $htaccess_content );
		}

		// Schedule log cleanup
		if ( ! wp_next_scheduled( 'portfoy_takipx_log_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'portfoy_takipx_log_cleanup' );
		}
		
		add_action( 'portfoy_takipx_log_cleanup', array( $this, 'cleanup_old_logs' ) );
	}

	/**
	 * Log debug message
	 */
	public function debug( $message, $context = array() ) {
		$this->log( self::LEVEL_DEBUG, $message, $context );
	}

	/**
	 * Log info message
	 */
	public function info( $message, $context = array() ) {
		$this->log( self::LEVEL_INFO, $message, $context );
	}

	/**
	 * Log warning message
	 */
	public function warning( $message, $context = array() ) {
		$this->log( self::LEVEL_WARNING, $message, $context );
	}

	/**
	 * Log error message
	 */
	public function error( $message, $context = array() ) {
		$this->log( self::LEVEL_ERROR, $message, $context );
	}

	/**
	 * Log critical message
	 */
	public function critical( $message, $context = array() ) {
		$this->log( self::LEVEL_CRITICAL, $message, $context );
	}

	/**
	 * Main logging method
	 */
	private function log( $level, $message, $context = array() ) {
		// Check if this level should be logged
		if ( $level < $this->log_level ) {
			return;
		}

		$level_names = array(
			self::LEVEL_DEBUG => 'DEBUG',
			self::LEVEL_INFO => 'INFO',
			self::LEVEL_WARNING => 'WARNING',
			self::LEVEL_ERROR => 'ERROR',
			self::LEVEL_CRITICAL => 'CRITICAL',
		);

		$level_name = $level_names[ $level ] ?? 'UNKNOWN';
		$timestamp = current_time( 'Y-m-d H:i:s' );
		$user_id = get_current_user_id();
		$ip_address = $this->get_client_ip();
		
		// Format context data
		$context_string = '';
		if ( ! empty( $context ) ) {
			$context_string = ' | Context: ' . json_encode( $context );
		}

		// Build log entry
		$log_entry = sprintf(
			"[%s] %s | User: %d | IP: %s | %s%s\n",
			$timestamp,
			$level_name,
			$user_id,
			$ip_address,
			$message,
			$context_string
		);

		// Determine log file
		$log_file = $this->get_log_file( $level );

		// Write to log file
		$this->write_to_log( $log_file, $log_entry );

		// Also log critical errors to WordPress error log
		if ( $level >= self::LEVEL_CRITICAL ) {
			error_log( "Portfoy TakipX CRITICAL: {$message}" );
		}
	}

	/**
	 * Get appropriate log file based on level
	 */
	private function get_log_file( $level ) {
		$date = current_time( 'Y-m-d' );
		
		switch ( $level ) {
			case self::LEVEL_DEBUG:
				return $this->log_dir . "debug-{$date}.log";
			case self::LEVEL_INFO:
				return $this->log_dir . "info-{$date}.log";
			case self::LEVEL_WARNING:
				return $this->log_dir . "warning-{$date}.log";
			case self::LEVEL_ERROR:
			case self::LEVEL_CRITICAL:
				return $this->log_dir . "error-{$date}.log";
			default:
				return $this->log_dir . "general-{$date}.log";
		}
	}

	/**
	 * Write log entry to file with rotation
	 */
	private function write_to_log( $log_file, $log_entry ) {
		// Check if log file needs rotation
		if ( file_exists( $log_file ) && filesize( $log_file ) > $this->max_log_size ) {
			$this->rotate_log_file( $log_file );
		}

		// Write to log file
		file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Rotate log file when it gets too large
	 */
	private function rotate_log_file( $log_file ) {
		$base_name = pathinfo( $log_file, PATHINFO_FILENAME );
		$extension = pathinfo( $log_file, PATHINFO_EXTENSION );
		$dir = dirname( $log_file );

		// Rotate existing numbered files
		for ( $i = $this->max_log_files - 1; $i >= 1; $i-- ) {
			$old_file = "{$dir}/{$base_name}.{$i}.{$extension}";
			$new_file = "{$dir}/{$base_name}." . ($i + 1) . ".{$extension}";
			
			if ( file_exists( $old_file ) ) {
				if ( $i + 1 > $this->max_log_files ) {
					unlink( $old_file ); // Delete oldest log
				} else {
					rename( $old_file, $new_file );
				}
			}
		}

		// Move current log to .1
		if ( file_exists( $log_file ) ) {
			rename( $log_file, "{$dir}/{$base_name}.1.{$extension}" );
		}
	}

	/**
	 * Log API request/response for debugging
	 */
	public function log_api_request( $url, $method, $data = null, $response = null, $duration = null ) {
		$context = array(
			'url' => $url,
			'method' => $method,
			'request_data' => $data,
			'response_code' => is_wp_error( $response ) ? 'ERROR' : wp_remote_retrieve_response_code( $response ),
			'duration_ms' => $duration,
		);

		if ( is_wp_error( $response ) ) {
			$this->error( "API Request failed: {$url}", $context );
		} else {
			$this->debug( "API Request completed: {$url}", $context );
		}
	}

	/**
	 * Log database query performance
	 */
	public function log_db_query( $query, $duration, $results_count = null ) {
		$context = array(
			'query' => $query,
			'duration_ms' => $duration,
			'results_count' => $results_count,
		);

		if ( $duration > 1000 ) { // Log slow queries (>1 second)
			$this->warning( "Slow database query detected", $context );
		} else {
			$this->debug( "Database query executed", $context );
		}
	}

	/**
	 * Log user actions for audit trail
	 */
	public function log_user_action( $action, $details = array() ) {
		$context = array(
			'action' => $action,
			'user_id' => get_current_user_id(),
			'user_email' => wp_get_current_user()->user_email,
			'details' => $details,
		);

		$this->info( "User action: {$action}", $context );
	}

	/**
	 * Log portfolio changes
	 */
	public function log_portfolio_change( $action, $asset_data = array() ) {
		$context = array(
			'action' => $action,
			'asset_symbol' => $asset_data['symbol'] ?? '',
			'asset_type' => $asset_data['asset_type'] ?? '',
			'quantity' => $asset_data['quantity'] ?? 0,
			'price' => $asset_data['price'] ?? 0,
		);

		$this->info( "Portfolio change: {$action}", $context );
	}

	/**
	 * Log price update activities
	 */
	public function log_price_update( $symbol, $old_price, $new_price, $source = 'manual' ) {
		$context = array(
			'symbol' => $symbol,
			'old_price' => $old_price,
			'new_price' => $new_price,
			'source' => $source,
			'change_percent' => $old_price > 0 ? (($new_price - $old_price) / $old_price) * 100 : 0,
		);

		$this->debug( "Price updated for {$symbol}", $context );
	}

	/**
	 * Log security events
	 */
	public function log_security_event( $event, $details = array() ) {
		$context = array(
			'event' => $event,
			'ip_address' => $this->get_client_ip(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'details' => $details,
		);

		$this->warning( "Security event: {$event}", $context );
	}

	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		
		return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}

	/**
	 * Get recent log entries for admin display
	 */
	public function get_recent_logs( $level = null, $limit = 100 ) {
		$log_files = glob( $this->log_dir . '*.log' );
		$entries = array();

		foreach ( $log_files as $file ) {
			$file_level = $this->get_file_level( basename( $file ) );
			
			if ( $level && $file_level !== $level ) {
				continue;
			}

			$lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			
			foreach ( array_reverse( $lines ) as $line ) {
				if ( count( $entries ) >= $limit ) {
					break 2;
				}
				
				$entries[] = $this->parse_log_entry( $line );
			}
		}

		// Sort by timestamp (newest first)
		usort( $entries, function( $a, $b ) {
			return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
		} );

		return array_slice( $entries, 0, $limit );
	}

	/**
	 * Parse log entry into structured data
	 */
	private function parse_log_entry( $line ) {
		$pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+) \| User: (\d+) \| IP: ([^\|]+) \| (.+)/';
		
		if ( preg_match( $pattern, $line, $matches ) ) {
			return array(
				'timestamp' => $matches[1],
				'level' => $matches[2],
				'user_id' => $matches[3],
				'ip_address' => trim( $matches[4] ),
				'message' => $matches[5],
				'raw' => $line,
			);
		}

		return array(
			'timestamp' => '',
			'level' => 'UNKNOWN',
			'user_id' => 0,
			'ip_address' => '',
			'message' => $line,
			'raw' => $line,
		);
	}

	/**
	 * Get log level from filename
	 */
	private function get_file_level( $filename ) {
		if ( strpos( $filename, 'debug-' ) === 0 ) return 'DEBUG';
		if ( strpos( $filename, 'info-' ) === 0 ) return 'INFO';
		if ( strpos( $filename, 'warning-' ) === 0 ) return 'WARNING';
		if ( strpos( $filename, 'error-' ) === 0 ) return 'ERROR';
		return 'GENERAL';
	}

	/**
	 * Get log statistics
	 */
	public function get_log_stats() {
		$stats = array(
			'total_size' => 0,
			'file_count' => 0,
			'level_counts' => array(
				'DEBUG' => 0,
				'INFO' => 0,
				'WARNING' => 0,
				'ERROR' => 0,
				'CRITICAL' => 0,
			),
		);

		$log_files = glob( $this->log_dir . '*.log' );
		
		foreach ( $log_files as $file ) {
			$stats['total_size'] += filesize( $file );
			$stats['file_count']++;
			
			$level = $this->get_file_level( basename( $file ) );
			if ( isset( $stats['level_counts'][ $level ] ) ) {
				$line_count = count( file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) );
				$stats['level_counts'][ $level ] += $line_count;
			}
		}

		return $stats;
	}

	/**
	 * Clear all log files
	 */
	public function clear_logs() {
		$log_files = glob( $this->log_dir . '*.log*' );
		
		foreach ( $log_files as $file ) {
			if ( basename( $file ) !== '.htaccess' ) {
				unlink( $file );
			}
		}

		$this->info( 'All log files cleared by user' );
	}

	/**
	 * Cleanup old log files (called by cron)
	 */
	public function cleanup_old_logs() {
		$cutoff_date = strtotime( '-30 days' );
		$log_files = glob( $this->log_dir . '*.log*' );
		$deleted_count = 0;

		foreach ( $log_files as $file ) {
			if ( filemtime( $file ) < $cutoff_date ) {
				unlink( $file );
				$deleted_count++;
			}
		}

		if ( $deleted_count > 0 ) {
			$this->info( "Cleaned up {$deleted_count} old log files" );
		}
	}

	/**
	 * Export logs for debugging
	 */
	public function export_logs( $format = 'txt' ) {
		$filename = 'portfoy-takipx-logs-' . current_time( 'Y-m-d-H-i-s' );
		
		switch ( $format ) {
			case 'zip':
				return $this->export_logs_zip( $filename );
			case 'txt':
			default:
				return $this->export_logs_txt( $filename );
		}
	}

	/**
	 * Export logs as text file
	 */
	private function export_logs_txt( $filename ) {
		$log_files = glob( $this->log_dir . '*.log' );
		$content = "Portfoy TakipX Logs Export\n";
		$content .= "Generated: " . current_time( 'Y-m-d H:i:s' ) . "\n";
		$content .= str_repeat( '=', 50 ) . "\n\n";

		foreach ( $log_files as $file ) {
			$content .= "File: " . basename( $file ) . "\n";
			$content .= str_repeat( '-', 30 ) . "\n";
			$content .= file_get_contents( $file );
			$content .= "\n\n";
		}

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.txt"' );
		header( 'Content-Length: ' . strlen( $content ) );
		
		echo $content;
		exit;
	}

	/**
	 * Export logs as ZIP file
	 */
	private function export_logs_zip( $filename ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			wp_die( 'ZipArchive class not available. Please use text export instead.' );
		}

		$zip = new ZipArchive();
		$zip_file = sys_get_temp_dir() . '/' . $filename . '.zip';

		if ( $zip->open( $zip_file, ZipArchive::CREATE ) !== TRUE ) {
			wp_die( 'Could not create ZIP file.' );
		}

		$log_files = glob( $this->log_dir . '*.log' );
		
		foreach ( $log_files as $file ) {
			$zip->addFile( $file, basename( $file ) );
		}

		$zip->close();

		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.zip"' );
		header( 'Content-Length: ' . filesize( $zip_file ) );
		
		readfile( $zip_file );
		unlink( $zip_file );
		exit;
	}

	/**
	 * Set log level
	 */
	public function set_log_level( $level ) {
		$this->log_level = $level;
		update_option( 'portfoy_takipx_log_level', $level );
	}

	/**
	 * Get current log level
	 */
	public function get_log_level() {
		return $this->log_level;
	}

	/**
	 * Check if logging is enabled for a specific level
	 */
	public function is_level_enabled( $level ) {
		return $level >= $this->log_level;
	}
}