<?php
/**
 * Portfolio Summary Template
 * Compact summary view for widgets or smaller spaces
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$user_id = $atts['user_id'];
$show_charts = $atts['show_charts'] === 'true';
$public = new Portfoy_TakipX_Public( 'portfoy-takipx', PORTFOY_TAKIPX_VERSION );
$summary = $public->get_portfolio_summary( $user_id );
?>

<div class="portfoy-summary-widget wp-block-group">
	<h3 class="wp-block-heading">Portföy Özeti</h3>
	
	<div class="summary-stats">
		<div class="stat-item">
			<span class="stat-label">Toplam Değer:</span>
			<span class="stat-value"><?php echo $public->format_currency( $summary['current_value'] ); ?></span>
		</div>
		<div class="stat-item">
			<span class="stat-label">Toplam Yatırım:</span>
			<span class="stat-value"><?php echo $public->format_currency( $summary['total_invested'] ); ?></span>
		</div>
		<div class="stat-item">
			<span class="stat-label">Kar/Zarar:</span>
			<span class="stat-value <?php echo $summary['profit_loss'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
				<?php echo $public->format_currency( $summary['profit_loss'] ); ?>
				(<?php echo number_format( $summary['profit_loss_percentage'], 2 ); ?>%)
			</span>
		</div>
	</div>

	<?php if ( ! empty( $summary['summary_by_type'] ) ): ?>
	<div class="type-breakdown">
		<h4>Varlık Türü Dağılımı</h4>
		<?php foreach ( $summary['summary_by_type'] as $type_data ): ?>
		<div class="type-item">
			<span class="type-label"><?php echo $public->get_asset_type_label( $type_data->asset_type ); ?>:</span>
			<span class="type-value"><?php echo $public->format_currency( $type_data->current_value ); ?></span>
			<span class="type-count">(<?php echo $type_data->total_assets; ?> varlık)</span>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $show_charts === 'true' ): ?>
	<div class="summary-chart">
		<canvas id="summary-pie-chart"></canvas>
	</div>
	<?php endif; ?>

	<div class="summary-actions">
		<?php 
		$portfolio_page_id = get_option( 'portfoy_takipx_page_id' );
		if ( $portfolio_page_id ) {
			$portfolio_url = get_permalink( $portfolio_page_id );
			echo '<a href="' . esc_url( $portfolio_url ) . '" class="wp-element-button wp-block-button__link">Detaylı Görünüm</a>';
		}
		?>
	</div>
</div>

<?php if ( $show_charts === 'true' ): ?>
<script>
// Initialize summary chart when document is ready
document.addEventListener('DOMContentLoaded', function() {
	if (typeof Chart !== 'undefined') {
		initSummaryChart();
	}
});

function initSummaryChart() {
	var ctx = document.getElementById('summary-pie-chart');
	if (!ctx) return;

	var data = <?php echo json_encode( $summary['summary_by_type'] ); ?>;
	var labels = [];
	var values = [];
	var colors = ['#2271b1', '#f77b00', '#00a32a', '#b32d2e', '#6c2eb1'];

	data.forEach(function(item, index) {
		labels.push(getAssetTypeLabel(item.asset_type));
		values.push(parseFloat(item.current_value));
	});

	new Chart(ctx, {
		type: 'pie',
		data: {
			labels: labels,
			datasets: [{
				data: values,
				backgroundColor: colors.slice(0, labels.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'bottom',
					labels: {
						padding: 10,
						usePointStyle: true
					}
				}
			}
		}
	});
}

function getAssetTypeLabel(type) {
	var labels = {
		'stock': 'Hisse Senedi',
		'crypto': 'Kripto Para',
		'forex': 'Döviz',
		'commodity': 'Emtia',
		'bond': 'Tahvil'
	};
	return labels[type] || type;
}
</script>
<?php endif; ?>