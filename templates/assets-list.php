<?php
/**
 * Assets List Template
 * Display list of assets with optional filtering
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$user_id = $atts['user_id'];
$asset_type = $atts['type'];
$limit = intval( $atts['limit'] );

$public = new Portfoy_TakipX_Public( 'portfoy-takipx', PORTFOY_TAKIPX_VERSION );
$assets = $public->get_user_assets( $user_id, $asset_type, $limit );
?>

<div class="portfoy-assets-list wp-block-group">
	<?php if ( ! empty( $asset_type ) ): ?>
	<h3 class="wp-block-heading"><?php echo $public->get_asset_type_label( $asset_type ); ?> Varlıklarım</h3>
	<?php else: ?>
	<h3 class="wp-block-heading">Varlıklarım</h3>
	<?php endif; ?>

	<?php if ( empty( $assets ) ): ?>
	<div class="no-assets-message">
		<p>
			<?php if ( ! empty( $asset_type ) ): ?>
				Bu kategoride henüz varlık bulunmuyor.
			<?php else: ?>
				Henüz varlık eklenmemiş.
			<?php endif; ?>
		</p>
	</div>
	<?php else: ?>
	<div class="assets-grid">
		<?php foreach ( $assets as $asset ): ?>
		<?php 
		$profit_data = $public->calculate_profit_loss( $asset );
		$profit_class = $profit_data['profit_loss'] >= 0 ? 'profit-positive' : 'profit-negative';
		?>
		<div class="asset-card" data-type="<?php echo esc_attr( $asset->asset_type ); ?>">
			<div class="asset-header">
				<div class="asset-info">
					<h4 class="asset-symbol"><?php echo esc_html( $asset->symbol ); ?></h4>
					<p class="asset-name"><?php echo esc_html( $asset->name ); ?></p>
					<span class="asset-type-badge <?php echo esc_attr( $asset->asset_type ); ?>">
						<?php echo $public->get_asset_type_label( $asset->asset_type ); ?>
					</span>
				</div>
				<?php if ( is_user_logged_in() && $user_id == get_current_user_id() ): ?>
				<div class="asset-actions">
					<button class="action-btn edit-btn" data-id="<?php echo $asset->id; ?>" title="Düzenle">
						<span class="dashicons dashicons-edit"></span>
					</button>
					<button class="action-btn delete-btn" data-id="<?php echo $asset->id; ?>" title="Sil">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</div>
				<?php endif; ?>
			</div>

			<div class="asset-details">
				<div class="detail-row">
					<span class="detail-label">Miktar:</span>
					<span class="detail-value"><?php echo number_format( $asset->quantity, 8 ); ?></span>
				</div>
				<div class="detail-row">
					<span class="detail-label">Alış Fiyatı:</span>
					<span class="detail-value"><?php echo $public->format_currency( $asset->purchase_price ); ?></span>
				</div>
				<div class="detail-row">
					<span class="detail-label">Güncel Fiyat:</span>
					<span class="detail-value">
						<?php echo $public->format_currency( $asset->current_price ?: $asset->purchase_price ); ?>
					</span>
				</div>
				<div class="detail-row">
					<span class="detail-label">Toplam Değer:</span>
					<span class="detail-value total-value">
						<?php echo $public->format_currency( $profit_data['total_value'] ); ?>
					</span>
				</div>
				<div class="detail-row">
					<span class="detail-label">Kar/Zarar:</span>
					<span class="detail-value <?php echo $profit_class; ?>">
						<?php echo $public->format_currency( $profit_data['profit_loss'] ); ?>
						<small>(<?php echo number_format( $profit_data['profit_loss_percentage'], 2 ); ?>%)</small>
					</span>
				</div>
				<div class="detail-row">
					<span class="detail-label">Alış Tarihi:</span>
					<span class="detail-value">
						<?php echo date_i18n( 'd.m.Y H:i', strtotime( $asset->purchase_date ) ); ?>
					</span>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $limit > 0 && count( $assets ) >= $limit ): ?>
	<div class="view-all-link">
		<?php 
		$portfolio_page_id = get_option( 'portfoy_takipx_page_id' );
		if ( $portfolio_page_id ) {
			$portfolio_url = get_permalink( $portfolio_page_id );
			echo '<a href="' . esc_url( $portfolio_url ) . '" class="wp-element-button wp-block-button__link outline">Tümünü Görüntüle</a>';
		}
		?>
	</div>
	<?php endif; ?>
	<?php endif; ?>
</div>

<style>
.assets-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.asset-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 20px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
	transition: box-shadow 0.3s ease;
}

.asset-card:hover {
	box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.asset-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 15px;
}

.asset-symbol {
	font-size: 18px;
	font-weight: bold;
	margin: 0 0 5px 0;
	color: var(--wp--preset--color--foreground, #000);
}

.asset-name {
	font-size: 14px;
	color: #666;
	margin: 0 0 8px 0;
}

.asset-type-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	color: #fff;
}

.asset-type-badge.stock { background-color: #2271b1; }
.asset-type-badge.crypto { background-color: #f77b00; }
.asset-type-badge.forex { background-color: #00a32a; }
.asset-type-badge.commodity { background-color: #b32d2e; }
.asset-type-badge.bond { background-color: #6c2eb1; }

.asset-actions {
	display: flex;
	gap: 5px;
}

.action-btn {
	background: none;
	border: 1px solid #ccc;
	border-radius: 4px;
	width: 30px;
	height: 30px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.3s ease;
}

.action-btn:hover {
	background-color: #f0f0f0;
}

.edit-btn:hover { border-color: #2271b1; color: #2271b1; }
.delete-btn:hover { border-color: #d63638; color: #d63638; }

.detail-row {
	display: flex;
	justify-content: space-between;
	margin-bottom: 8px;
	font-size: 14px;
}

.detail-label {
	color: #666;
}

.detail-value {
	font-weight: 500;
	text-align: right;
}

.detail-value.total-value {
	font-weight: bold;
	font-size: 16px;
}

.profit-positive { color: #00a32a; }
.profit-negative { color: #d63638; }

.no-assets-message {
	text-align: center;
	padding: 40px;
	background: #f9f9f9;
	border-radius: 8px;
	margin-top: 20px;
}

.view-all-link {
	text-align: center;
	margin-top: 20px;
}

/* Twenty Twenty-Three theme compatibility */
.wp-site-blocks .asset-card {
	border-color: var(--wp--preset--color--contrast-2, #e0e0e0);
	background-color: var(--wp--preset--color--base, #fff);
}

.wp-site-blocks .asset-symbol {
	color: var(--wp--preset--color--contrast, #000);
}

.wp-site-blocks .detail-label {
	color: var(--wp--preset--color--contrast-3, #666);
}
</style>