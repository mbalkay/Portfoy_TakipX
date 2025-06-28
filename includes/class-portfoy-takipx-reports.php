<?php

/**
 * Advanced Reporting System for Portfolio Analysis
 */
class Portfoy_TakipX_Reports {

	private $user_id;
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->user_id = get_current_user_id();
	}

	/**
	 * Generate comprehensive portfolio report
	 */
	public function generate_portfolio_report( $date_from = null, $date_to = null ) {
		$date_from = $date_from ?: date( 'Y-m-d', strtotime( '-1 year' ) );
		$date_to = $date_to ?: date( 'Y-m-d' );

		return array(
			'summary' => $this->get_portfolio_summary(),
			'performance' => $this->get_performance_analysis( $date_from, $date_to ),
			'asset_allocation' => $this->get_asset_allocation(),
			'top_performers' => $this->get_top_performers(),
			'risk_analysis' => $this->get_risk_analysis(),
			'transaction_summary' => $this->get_transaction_summary( $date_from, $date_to ),
			'monthly_performance' => $this->get_monthly_performance( $date_from, $date_to ),
			'dividend_analysis' => $this->get_dividend_analysis( $date_from, $date_to )
		);
	}

	/**
	 * Get detailed portfolio summary with advanced metrics
	 */
	public function get_portfolio_summary() {
		$assets_table = $this->wpdb->prefix . 'portfoy_takipx_assets';
		
		$query = "
			SELECT 
				COUNT(*) as total_assets,
				SUM(quantity * purchase_price) as total_invested,
				SUM(quantity * current_price) as current_value,
				SUM((quantity * current_price) - (quantity * purchase_price)) as total_profit_loss,
				AVG(CASE WHEN purchase_price > 0 
					THEN ((current_price - purchase_price) / purchase_price) * 100 
					ELSE 0 END) as avg_return_percentage,
				MAX(((current_price - purchase_price) / purchase_price) * 100) as best_performer,
				MIN(((current_price - purchase_price) / purchase_price) * 100) as worst_performer
			FROM {$assets_table} 
			WHERE user_id = %d AND status = 'active'
		";

		$summary = $this->wpdb->get_row( $this->wpdb->prepare( $query, $this->user_id ) );

		// Calculate additional metrics
		$roi = $summary->total_invested > 0 
			? (($summary->current_value - $summary->total_invested) / $summary->total_invested) * 100 
			: 0;

		$sharpe_ratio = $this->calculate_sharpe_ratio();
		$volatility = $this->calculate_portfolio_volatility();

		return array(
			'total_assets' => (int) $summary->total_assets,
			'total_invested' => (float) $summary->total_invested,
			'current_value' => (float) $summary->current_value,
			'total_profit_loss' => (float) $summary->total_profit_loss,
			'roi_percentage' => round( $roi, 2 ),
			'avg_return_percentage' => round( (float) $summary->avg_return_percentage, 2 ),
			'best_performer' => round( (float) $summary->best_performer, 2 ),
			'worst_performer' => round( (float) $summary->worst_performer, 2 ),
			'sharpe_ratio' => round( $sharpe_ratio, 2 ),
			'volatility' => round( $volatility, 2 ),
			'diversification_score' => $this->calculate_diversification_score()
		);
	}

	/**
	 * Get performance analysis over time
	 */
	public function get_performance_analysis( $date_from, $date_to ) {
		$snapshots_table = $this->wpdb->prefix . 'portfoy_takipx_portfolio_snapshots';
		
		$query = "
			SELECT 
				DATE(snapshot_date) as date,
				total_value,
				total_invested,
				profit_loss,
				profit_loss_percentage
			FROM {$snapshots_table}
			WHERE user_id = %d 
			AND snapshot_date BETWEEN %s AND %s
			ORDER BY snapshot_date ASC
		";

		$performance_data = $this->wpdb->get_results( $this->wpdb->prepare( 
			$query, 
			$this->user_id, 
			$date_from . ' 00:00:00', 
			$date_to . ' 23:59:59' 
		) );

		// Calculate performance metrics
		$metrics = array(
			'total_return' => 0,
			'annualized_return' => 0,
			'max_drawdown' => 0,
			'win_rate' => 0,
			'avg_win' => 0,
			'avg_loss' => 0,
			'profit_factor' => 0
		);

		if ( count( $performance_data ) > 1 ) {
			$first_value = reset( $performance_data )->total_value;
			$last_value = end( $performance_data )->total_value;
			
			$metrics['total_return'] = $first_value > 0 
				? (($last_value - $first_value) / $first_value) * 100 
				: 0;

			$days = count( $performance_data );
			$metrics['annualized_return'] = pow( 1 + ($metrics['total_return'] / 100), 365 / $days ) - 1;
			$metrics['max_drawdown'] = $this->calculate_max_drawdown( $performance_data );
		}

		return array(
			'data' => $performance_data,
			'metrics' => $metrics,
			'chart_data' => $this->format_chart_data( $performance_data )
		);
	}

	/**
	 * Get asset allocation breakdown
	 */
	public function get_asset_allocation() {
		$assets_table = $this->wpdb->prefix . 'portfoy_takipx_assets';
		
		$query = "
			SELECT 
				asset_type,
				COUNT(*) as count,
				SUM(quantity * current_price) as total_value,
				SUM(quantity * purchase_price) as total_invested,
				SUM((quantity * current_price) - (quantity * purchase_price)) as profit_loss
			FROM {$assets_table}
			WHERE user_id = %d AND status = 'active'
			GROUP BY asset_type
			ORDER BY total_value DESC
		";

		$allocation_data = $this->wpdb->get_results( $this->wpdb->prepare( $query, $this->user_id ) );

		$total_portfolio_value = array_sum( array_column( $allocation_data, 'total_value' ) );

		foreach ( $allocation_data as &$item ) {
			$item->percentage = $total_portfolio_value > 0 
				? round( ($item->total_value / $total_portfolio_value) * 100, 2 )
				: 0;
			$item->profit_loss_percentage = $item->total_invested > 0 
				? round( ($item->profit_loss / $item->total_invested) * 100, 2 )
				: 0;
		}

		return array(
			'by_type' => $allocation_data,
			'chart_data' => $this->format_pie_chart_data( $allocation_data ),
			'diversification_analysis' => $this->analyze_diversification( $allocation_data )
		);
	}

	/**
	 * Get top performing assets
	 */
	public function get_top_performers( $limit = 10 ) {
		$assets_table = $this->wpdb->prefix . 'portfoy_takipx_assets';
		
		$query = "
			SELECT 
				symbol,
				name,
				asset_type,
				quantity,
				purchase_price,
				current_price,
				(quantity * current_price) as current_value,
				(quantity * purchase_price) as invested_value,
				((quantity * current_price) - (quantity * purchase_price)) as profit_loss,
				(CASE WHEN purchase_price > 0 
					THEN ((current_price - purchase_price) / purchase_price) * 100 
					ELSE 0 END) as return_percentage
			FROM {$assets_table}
			WHERE user_id = %d AND status = 'active'
			ORDER BY return_percentage DESC
			LIMIT %d
		";

		$top_performers = $this->wpdb->get_results( $this->wpdb->prepare( $query, $this->user_id, $limit ) );

		// Also get worst performers
		$query_worst = str_replace( 'DESC', 'ASC', $query );
		$worst_performers = $this->wpdb->get_results( $this->wpdb->prepare( $query_worst, $this->user_id, $limit ) );

		return array(
			'top_performers' => $top_performers,
			'worst_performers' => $worst_performers,
			'performance_distribution' => $this->get_performance_distribution()
		);
	}

	/**
	 * Risk analysis and metrics
	 */
	public function get_risk_analysis() {
		$price_history_table = $this->wpdb->prefix . 'portfoy_takipx_price_history';
		$assets_table = $this->wpdb->prefix . 'portfoy_takipx_assets';

		// Get portfolio composition
		$portfolio_query = "
			SELECT symbol, (quantity * current_price) as position_value
			FROM {$assets_table}
			WHERE user_id = %d AND status = 'active'
		";
		$portfolio = $this->wpdb->get_results( $this->wpdb->prepare( $portfolio_query, $this->user_id ) );

		$total_value = array_sum( array_column( $portfolio, 'position_value' ) );
		$risk_metrics = array();

		foreach ( $portfolio as $position ) {
			$weight = $total_value > 0 ? $position->position_value / $total_value : 0;
			
			// Get price volatility for this asset
			$volatility_query = "
				SELECT STDDEV(price_change_percentage_24h) as volatility
				FROM {$price_history_table}
				WHERE symbol = %s
				AND recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
			";
			
			$volatility = $this->wpdb->get_var( $this->wpdb->prepare( $volatility_query, $position->symbol ) );
			
			$risk_metrics[] = array(
				'symbol' => $position->symbol,
				'weight' => $weight,
				'volatility' => (float) $volatility,
				'risk_contribution' => $weight * (float) $volatility
			);
		}

		$portfolio_volatility = sqrt( array_sum( array_column( $risk_metrics, 'risk_contribution' ) ) );
		
		return array(
			'portfolio_volatility' => round( $portfolio_volatility, 2 ),
			'var_95' => $this->calculate_value_at_risk( 0.95 ),
			'var_99' => $this->calculate_value_at_risk( 0.99 ),
			'beta' => $this->calculate_portfolio_beta(),
			'risk_metrics' => $risk_metrics,
			'risk_score' => $this->calculate_risk_score( $portfolio_volatility ),
			'recommended_actions' => $this->get_risk_recommendations( $portfolio_volatility )
		);
	}

	/**
	 * Transaction summary and analysis
	 */
	public function get_transaction_summary( $date_from, $date_to ) {
		$transactions_table = $this->wpdb->prefix . 'portfoy_takipx_transactions';
		
		$query = "
			SELECT 
				transaction_type,
				COUNT(*) as count,
				SUM(total_amount) as total_amount,
				SUM(commission) as total_commission,
				AVG(total_amount) as avg_amount
			FROM {$transactions_table}
			WHERE user_id = %d
			AND transaction_date BETWEEN %s AND %s
			GROUP BY transaction_type
		";

		$transaction_summary = $this->wpdb->get_results( $this->wpdb->prepare( 
			$query, 
			$this->user_id, 
			$date_from . ' 00:00:00', 
			$date_to . ' 23:59:59' 
		) );

		// Get monthly transaction trends
		$monthly_query = "
			SELECT 
				DATE_FORMAT(transaction_date, '%Y-%m') as month,
				transaction_type,
				SUM(total_amount) as amount
			FROM {$transactions_table}
			WHERE user_id = %d
			AND transaction_date BETWEEN %s AND %s
			GROUP BY month, transaction_type
			ORDER BY month ASC
		";

		$monthly_trends = $this->wpdb->get_results( $this->wpdb->prepare( 
			$monthly_query, 
			$this->user_id, 
			$date_from . ' 00:00:00', 
			$date_to . ' 23:59:59' 
		) );

		return array(
			'summary' => $transaction_summary,
			'monthly_trends' => $monthly_trends,
			'commission_analysis' => $this->analyze_commissions( $date_from, $date_to ),
			'trading_frequency' => $this->calculate_trading_frequency( $date_from, $date_to )
		);
	}

	/**
	 * Monthly performance breakdown
	 */
	public function get_monthly_performance( $date_from, $date_to ) {
		$snapshots_table = $this->wpdb->prefix . 'portfoy_takipx_portfolio_snapshots';
		
		$query = "
			SELECT 
				DATE_FORMAT(snapshot_date, '%Y-%m') as month,
				AVG(total_value) as avg_value,
				AVG(profit_loss_percentage) as avg_return,
				MIN(total_value) as min_value,
				MAX(total_value) as max_value
			FROM {$snapshots_table}
			WHERE user_id = %d
			AND snapshot_date BETWEEN %s AND %s
			GROUP BY month
			ORDER BY month ASC
		";

		$monthly_data = $this->wpdb->get_results( $this->wpdb->prepare( 
			$query, 
			$this->user_id, 
			$date_from . ' 00:00:00', 
			$date_to . ' 23:59:59' 
		) );

		// Calculate month-over-month growth
		for ( $i = 1; $i < count( $monthly_data ); $i++ ) {
			$prev_value = $monthly_data[ $i - 1 ]->avg_value;
			$curr_value = $monthly_data[ $i ]->avg_value;
			
			$monthly_data[ $i ]->mom_growth = $prev_value > 0 
				? round( (($curr_value - $prev_value) / $prev_value) * 100, 2 )
				: 0;
		}

		return array(
			'monthly_data' => $monthly_data,
			'best_month' => $this->find_best_month( $monthly_data ),
			'worst_month' => $this->find_worst_month( $monthly_data ),
			'consistency_score' => $this->calculate_consistency_score( $monthly_data )
		);
	}

	/**
	 * Dividend and income analysis
	 */
	public function get_dividend_analysis( $date_from, $date_to ) {
		$transactions_table = $this->wpdb->prefix . 'portfoy_takipx_transactions';
		
		$query = "
			SELECT 
				a.symbol,
				a.name,
				a.asset_type,
				SUM(CASE WHEN t.transaction_type = 'dividend' THEN t.total_amount ELSE 0 END) as total_dividends,
				COUNT(CASE WHEN t.transaction_type = 'dividend' THEN 1 END) as dividend_count,
				a.quantity * a.purchase_price as invested_amount
			FROM {$this->wpdb->prefix}portfoy_takipx_assets a
			LEFT JOIN {$transactions_table} t ON a.id = t.asset_id
			WHERE a.user_id = %d
			AND (t.transaction_date IS NULL OR t.transaction_date BETWEEN %s AND %s)
			GROUP BY a.id
			HAVING total_dividends > 0
			ORDER BY total_dividends DESC
		";

		$dividend_data = $this->wpdb->get_results( $this->wpdb->prepare( 
			$query, 
			$this->user_id, 
			$date_from . ' 00:00:00', 
			$date_to . ' 23:59:59' 
		) );

		foreach ( $dividend_data as &$item ) {
			$item->dividend_yield = $item->invested_amount > 0 
				? round( ($item->total_dividends / $item->invested_amount) * 100, 2 )
				: 0;
		}

		return array(
			'dividend_assets' => $dividend_data,
			'total_dividends' => array_sum( array_column( $dividend_data, 'total_dividends' ) ),
			'avg_yield' => $this->calculate_average_dividend_yield( $dividend_data ),
			'dividend_growth' => $this->calculate_dividend_growth( $date_from, $date_to )
		);
	}

	/**
	 * Helper calculation methods
	 */
	private function calculate_sharpe_ratio() {
		// Simplified Sharpe ratio calculation
		$returns = $this->get_portfolio_returns();
		if ( empty( $returns ) ) return 0;

		$avg_return = array_sum( $returns ) / count( $returns );
		$risk_free_rate = 0.02; // Assume 2% risk-free rate
		$std_dev = $this->calculate_standard_deviation( $returns );

		return $std_dev > 0 ? ($avg_return - $risk_free_rate) / $std_dev : 0;
	}

	private function calculate_portfolio_volatility() {
		$returns = $this->get_portfolio_returns();
		return $this->calculate_standard_deviation( $returns );
	}

	private function calculate_diversification_score() {
		$allocation = $this->get_asset_allocation();
		$hhi = 0;
		
		foreach ( $allocation['by_type'] as $type ) {
			$weight = $type->percentage / 100;
			$hhi += pow( $weight, 2 );
		}

		// Convert HHI to diversification score (0-100, higher is better)
		return round( (1 - $hhi) * 100, 2 );
	}

	private function calculate_max_drawdown( $performance_data ) {
		$max_value = 0;
		$max_drawdown = 0;

		foreach ( $performance_data as $point ) {
			if ( $point->total_value > $max_value ) {
				$max_value = $point->total_value;
			}
			
			$drawdown = ($max_value - $point->total_value) / $max_value;
			if ( $drawdown > $max_drawdown ) {
				$max_drawdown = $drawdown;
			}
		}

		return round( $max_drawdown * 100, 2 );
	}

	private function calculate_value_at_risk( $confidence_level ) {
		$returns = $this->get_portfolio_returns();
		if ( empty( $returns ) ) return 0;

		sort( $returns );
		$index = floor( count( $returns ) * (1 - $confidence_level) );
		
		return isset( $returns[ $index ] ) ? round( $returns[ $index ] * 100, 2 ) : 0;
	}

	private function calculate_portfolio_beta() {
		// Simplified beta calculation against market index
		// This would require market data for accurate calculation
		return 1.0;
	}

	private function get_portfolio_returns() {
		$snapshots_table = $this->wpdb->prefix . 'portfoy_takipx_portfolio_snapshots';
		
		$query = "
			SELECT profit_loss_percentage
			FROM {$snapshots_table}
			WHERE user_id = %d
			AND snapshot_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
			ORDER BY snapshot_date ASC
		";

		$results = $this->wpdb->get_col( $this->wpdb->prepare( $query, $this->user_id ) );
		return array_map( 'floatval', $results );
	}

	private function calculate_standard_deviation( $values ) {
		if ( empty( $values ) ) return 0;
		
		$mean = array_sum( $values ) / count( $values );
		$sum_squares = array_sum( array_map( function( $x ) use ( $mean ) {
			return pow( $x - $mean, 2 );
		}, $values ) );

		return sqrt( $sum_squares / count( $values ) );
	}

	/**
	 * Export methods
	 */
	public function export_to_csv( $report_data ) {
		$filename = 'portfolio_report_' . date( 'Y-m-d' ) . '.csv';
		
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		
		$output = fopen( 'php://output', 'w' );
		
		// Write CSV headers and data
		fputcsv( $output, array( 'Report Generated:', date( 'Y-m-d H:i:s' ) ) );
		fputcsv( $output, array() );
		
		// Portfolio Summary
		fputcsv( $output, array( 'PORTFOLIO SUMMARY' ) );
		foreach ( $report_data['summary'] as $key => $value ) {
			fputcsv( $output, array( ucwords( str_replace( '_', ' ', $key ) ), $value ) );
		}
		
		// Add other sections...
		
		fclose( $output );
		exit;
	}

	/**
	 * Chart data formatting
	 */
	private function format_chart_data( $data ) {
		return array(
			'labels' => array_column( $data, 'date' ),
			'datasets' => array(
				array(
					'label' => 'Portfolio Value',
					'data' => array_column( $data, 'total_value' ),
					'borderColor' => '#1e88e5',
					'backgroundColor' => 'rgba(30, 136, 229, 0.1)'
				)
			)
		);
	}

	private function format_pie_chart_data( $allocation_data ) {
		return array(
			'labels' => array_column( $allocation_data, 'asset_type' ),
			'datasets' => array(
				array(
					'data' => array_column( $allocation_data, 'percentage' ),
					'backgroundColor' => array( '#1e88e5', '#ff9800', '#4caf50', '#795548', '#9c27b0' )
				)
			)
		);
	}
}