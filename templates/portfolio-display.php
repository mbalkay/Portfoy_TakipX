<?php
/**
 * Portfolio Display Template
 * This template is designed to be compatible with Twenty Twenty-Three theme
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$user_id = $atts['user_id'];
$show_add_form = $atts['show_add_form'] === 'true';
$public = new Portfoy_TakipX_Public( 'portfoy-takipx', PORTFOY_TAKIPX_VERSION );
?>

<div class="portfoy-takipx-container wp-block-group">
	<div class="portfoy-summary-section wp-block-group">
		<h2 class="wp-block-heading">Portföy Özeti</h2>
		<div class="portfoy-summary-grid">
			<div class="summary-card">
				<h3>Toplam Portföy Değeri</h3>
				<div class="value" id="public-total-value">₺0.00</div>
				<div class="change" id="public-total-change">₺0.00 (0.00%)</div>
			</div>
			<div class="summary-card">
				<h3>Toplam Yatırım</h3>
				<div class="value" id="public-total-investment">₺0.00</div>
			</div>
			<div class="summary-card">
				<h3>Kar/Zarar</h3>
				<div class="value" id="public-profit-loss">₺0.00</div>
				<div class="change" id="public-profit-percentage">0.00%</div>
			</div>
		</div>
	</div>

	<?php if ( $show_add_form ): ?>
	<div class="portfoy-add-section wp-block-group">
		<details class="wp-block-details">
			<summary class="wp-block-details__summary">Yeni Varlık Ekle</summary>
			<div class="wp-block-details__content">
				<form id="public-add-asset-form" class="portfoy-add-form">
					<div class="form-row">
						<div class="form-field">
							<label for="asset_type">Varlık Türü</label>
							<select name="asset_type" id="asset_type" required>
								<option value="">Seçiniz...</option>
								<option value="stock">Hisse Senedi</option>
								<option value="crypto">Kripto Para</option>
								<option value="forex">Döviz</option>
								<option value="commodity">Emtia</option>
								<option value="bond">Tahvil</option>
							</select>
						</div>
						<div class="form-field">
							<label for="symbol">Sembol</label>
							<input type="text" name="symbol" id="symbol" placeholder="Örn: AAPL, BTC" required maxlength="10" />
						</div>
					</div>
					<div class="form-row">
						<div class="form-field">
							<label for="name">Ad</label>
							<input type="text" name="name" id="name" placeholder="Varlık adı" required maxlength="100" />
						</div>
						<div class="form-field">
							<label for="quantity">Miktar</label>
							<input type="number" name="quantity" id="quantity" step="0.00000001" min="0" required />
						</div>
					</div>
					<div class="form-row">
						<div class="form-field">
							<label for="purchase_price">Alış Fiyatı</label>
							<input type="number" name="purchase_price" id="purchase_price" step="0.00000001" min="0" required />
						</div>
						<div class="form-field">
							<label for="current_price">Güncel Fiyat</label>
							<input type="number" name="current_price" id="current_price" step="0.00000001" min="0" />
						</div>
					</div>
					<div class="form-row">
						<div class="form-field">
							<label for="purchase_date">Alış Tarihi</label>
							<input type="datetime-local" name="purchase_date" id="purchase_date" required />
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="wp-element-button wp-block-button__link">Varlık Ekle</button>
						<span class="spinner"></span>
					</div>
				</form>
			</div>
		</details>
	</div>
	<?php endif; ?>

	<div class="portfoy-assets-section wp-block-group">
		<h2 class="wp-block-heading">Varlıklarım</h2>
		<div class="portfoy-filters">
			<button class="filter-btn active" data-type="">Tümü</button>
			<button class="filter-btn" data-type="stock">Hisse Senedi</button>
			<button class="filter-btn" data-type="crypto">Kripto</button>
			<button class="filter-btn" data-type="forex">Döviz</button>
			<button class="filter-btn" data-type="commodity">Emtia</button>
			<button class="filter-btn" data-type="bond">Tahvil</button>
		</div>
		<div class="portfoy-assets-grid" id="public-assets-grid">
			<div class="loading-message">Varlıklar yükleniyor...</div>
		</div>
	</div>

	<div class="portfoy-analytics-section wp-block-group">
		<h2 class="wp-block-heading">Portföy Analizi</h2>
		<div class="analytics-grid">
			<div class="chart-container">
				<h3>Varlık Türü Dağılımı</h3>
				<canvas id="public-type-chart"></canvas>
			</div>
			<div class="chart-container">
				<h3>Performans Genel Bakış</h3>
				<div class="performance-stats" id="public-performance-stats">
					<!-- Performance stats will be loaded here -->
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Edit Asset Modal -->
<div id="public-edit-modal" class="portfoy-modal" style="display: none;">
	<div class="modal-content">
		<div class="modal-header">
			<h3>Varlık Düzenle</h3>
			<button type="button" class="modal-close">&times;</button>
		</div>
		<form id="public-edit-form">
			<input type="hidden" name="asset_id" />
			<div class="form-row">
				<div class="form-field">
					<label for="edit_asset_type">Varlık Türü</label>
					<select name="asset_type" id="edit_asset_type" required>
						<option value="stock">Hisse Senedi</option>
						<option value="crypto">Kripto Para</option>
						<option value="forex">Döviz</option>
						<option value="commodity">Emtia</option>
						<option value="bond">Tahvil</option>
					</select>
				</div>
				<div class="form-field">
					<label for="edit_symbol">Sembol</label>
					<input type="text" name="symbol" id="edit_symbol" required maxlength="10" />
				</div>
			</div>
			<div class="form-row">
				<div class="form-field">
					<label for="edit_name">Ad</label>
					<input type="text" name="name" id="edit_name" required maxlength="100" />
				</div>
				<div class="form-field">
					<label for="edit_quantity">Miktar</label>
					<input type="number" name="quantity" id="edit_quantity" step="0.00000001" min="0" required />
				</div>
			</div>
			<div class="form-row">
				<div class="form-field">
					<label for="edit_purchase_price">Alış Fiyatı</label>
					<input type="number" name="purchase_price" id="edit_purchase_price" step="0.00000001" min="0" required />
				</div>
				<div class="form-field">
					<label for="edit_current_price">Güncel Fiyat</label>
					<input type="number" name="current_price" id="edit_current_price" step="0.00000001" min="0" />
				</div>
			</div>
			<div class="form-row">
				<div class="form-field">
					<label for="edit_purchase_date">Alış Tarihi</label>
					<input type="datetime-local" name="purchase_date" id="edit_purchase_date" required />
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="wp-element-button wp-block-button__link">Güncelle</button>
				<button type="button" class="wp-element-button wp-block-button__link outline" onclick="closePublicEditModal()">İptal</button>
				<span class="spinner"></span>
			</div>
		</form>
	</div>
</div>

<script>
// Set default purchase date to now
document.addEventListener('DOMContentLoaded', function() {
	var now = new Date();
	now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
	var defaultDate = now.toISOString().slice(0, 16);
	
	var purchaseDateInput = document.getElementById('purchase_date');
	if (purchaseDateInput && !purchaseDateInput.value) {
		purchaseDateInput.value = defaultDate;
	}
});
</script>