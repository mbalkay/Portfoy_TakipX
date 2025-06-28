<?php
/**
 * Modern admin display template for Portföy TakipX
 * Enhanced UI with auto-fetch capabilities and modern design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap portfoy-takipx-modern">
    <!-- Modern Header -->
    <div class="portfoy-header">
        <h1 class="portfoy-title">Portföy TakipX</h1>
        <div class="portfoy-actions">
            <button type="button" class="btn btn-secondary" data-modal-target="settings-modal" data-tooltip="Ayarlar">
                Ayarlar
            </button>
            <button type="button" class="btn btn-success" data-modal-target="add-asset-modal" data-tooltip="Yeni varlık ekle (Ctrl+N)">
                Varlık Ekle
            </button>
            <button type="button" class="btn btn-primary" onclick="PortfoyTakipX.refreshPortfolioData()" data-tooltip="Verileri yenile (Ctrl+R)">
                Yenile
            </button>
        </div>
    </div>

    <!-- Summary Cards Grid -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-icon portfolio-icon">PF</div>
            <div class="summary-card-title">Toplam Portföy Değeri</div>
            <div class="summary-card-value" id="total-value-value">₺0.00</div>
            <div class="summary-card-change" id="total-value-change">
                <span class="change-neutral">Hesaplanıyor...</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-icon investment-icon">INV</div>
            <div class="summary-card-title">Toplam Yatırım</div>
            <div class="summary-card-value" id="total-investment-value">₺0.00</div>
            <div class="summary-card-change">
                <span class="change-neutral">Başlangıç sermayesi</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-icon profit-icon">P&L</div>
            <div class="summary-card-title">Kar/Zarar</div>
            <div class="summary-card-value" id="profit-loss-value">₺0.00</div>
            <div class="summary-card-change" id="profit-loss-change">
                <span class="change-neutral">Hesaplanıyor...</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-icon assets-icon">AST</div>
            <div class="summary-card-title">Toplam Varlık</div>
            <div class="summary-card-value" id="assets-count-value">0</div>
            <div class="summary-card-change">
                <span class="change-neutral">Aktif pozisyonlar</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-icon risk-icon">RSK</div>
            <div class="summary-card-title">Risk Skoru</div>
            <div class="summary-card-value" id="risk-score-value">-</div>
            <div class="summary-card-change">
                <span class="change-neutral">Volatilite analizi</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-icon diversification-icon">DIV</div>
            <div class="summary-card-title">Çeşitlendirme</div>
            <div class="summary-card-value" id="diversification-value">-</div>
            <div class="summary-card-change">
                <span class="change-neutral">Sharpe oranı</span>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="card mb-lg">
        <div class="card-header">
            <h3 class="card-title">Filtreler ve Arama</h3>
        </div>
        <div class="card-body">
            <div class="filter-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: var(--spacing-md); align-items: end;">
                <div class="form-group">
                    <label class="form-label">Varlık Ara</label>
                    <input type="text" class="form-input" id="asset-search" placeholder="Sembol veya isim ara...">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Varlık Türü</label>
                    <select class="form-select" id="asset-type-filter">
                        <option value="">Tümü</option>
                        <option value="stock">Hisse Senedi</option>
                        <option value="crypto">Kripto Para</option>
                        <option value="forex">Döviz</option>
                        <option value="commodity">Emtia</option>
                        <option value="bond">Tahvil</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Performans</label>
                    <select class="form-select" id="performance-filter">
                        <option value="">Tümü</option>
                        <option value="positive">Karlı</option>
                        <option value="negative">Zararlı</option>
                        <option value="neutral">Başabaş</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                        🔍 Filtrele
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Table -->
    <div class="portfolio-table-container">
        <div style="padding: var(--spacing-lg); border-bottom: 1px solid var(--gray-200); background: linear-gradient(135deg, var(--gray-50), white);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: var(--font-size-lg); font-weight: 600;">📊 Portföy Varlıkları</h3>
                <div style="display: flex; gap: var(--spacing-sm);">
                    <button type="button" class="btn btn-secondary btn-sm" id="bulk-update-prices" style="font-size: var(--font-size-sm);">
                        🔄 Toplu Güncelle
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulk-delete-assets" style="font-size: var(--font-size-sm);">
                        🗑️ Seçilenleri Sil
                    </button>
                </div>
            </div>
        </div>
        
        <table class="portfolio-table" id="assets-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all-assets">
                    </th>
                    <th>Varlık</th>
                    <th>Tür</th>
                    <th>Miktar</th>
                    <th>Alış Fiyatı</th>
                    <th>Güncel Fiyat</th>
                    <th>Değer</th>
                    <th>Kar/Zarar</th>
                    <th>%</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody id="assets-table-body">
                <tr>
                    <td colspan="10" style="text-align: center; padding: var(--spacing-2xl); color: var(--gray-500);">
                        <div class="loading">
                            <div class="spinner"></div>
                            Varlıklar yükleniyor...
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Asset Modal -->
<div class="modal-overlay" id="add-asset-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">➕ Yeni Varlık Ekle</h2>
        </div>
        <div class="modal-body">
            <form id="add-asset-form" autocomplete="off">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                    <div class="form-group">
                        <label class="form-label">Varlık Türü *</label>
                        <select name="asset_type" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <option value="stock">📈 Hisse Senedi</option>
                            <option value="crypto">₿ Kripto Para</option>
                            <option value="forex">💱 Döviz</option>
                            <option value="commodity">🌾 Emtia</option>
                            <option value="bond">📄 Tahvil</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sembol *</label>
                        <input type="text" name="symbol" class="form-input" placeholder="Örn: AAPL, BTC, USDTRY" required style="text-transform: uppercase;">
                        <small style="color: var(--gray-500); font-size: var(--font-size-xs);">Otomatik tamamlama için yazmaya başlayın</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Varlık Adı *</label>
                    <input type="text" name="name" class="form-input" placeholder="Otomatik olarak doldurulacak..." required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                    <div class="form-group">
                        <label class="form-label">Miktar *</label>
                        <input type="number" name="quantity" class="form-input" step="0.00000001" min="0" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Alış Fiyatı *</label>
                        <input type="number" name="purchase_price" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                    <div class="form-group" id="current-price-group">
                        <label class="form-label">Güncel Fiyat</label>
                        <input type="number" name="current_price" class="form-input" step="0.01" min="0" placeholder="Otomatik alınacak...">
                        <small style="color: var(--gray-500); font-size: var(--font-size-xs);">Boş bırakılırsa otomatik güncellenecek</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Komisyon</label>
                        <input type="number" name="commission" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                    <div class="form-group">
                        <label class="form-label">Alış Tarihi</label>
                        <input type="date" name="purchase_date" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Aracı Kurum</label>
                        <input type="text" name="broker" class="form-input" placeholder="İş Bankası, Binance, vb.">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notlar</label>
                    <textarea name="notes" class="form-input" rows="3" placeholder="İsteğe bağlı notlar..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-close>İptal</button>
            <button type="submit" form="add-asset-form" class="btn btn-primary">💾 Kaydet</button>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal-overlay" id="settings-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">⚙️ Ayarlar</h2>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Otomatik Yenileme</label>
                <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                    <input type="checkbox" id="auto-refresh-toggle" checked>
                    <span>Otomatik fiyat güncellemesi</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Yenileme Aralığı</label>
                <select id="refresh-interval" class="form-select">
                    <option value="60000">1 dakika</option>
                    <option value="300000" selected>5 dakika</option>
                    <option value="600000">10 dakika</option>
                    <option value="1800000">30 dakika</option>
                    <option value="3600000">1 saat</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Para Birimi</label>
                <select class="form-select">
                    <option value="TRY" selected>₺ Türk Lirası</option>
                    <option value="USD">$ ABD Doları</option>
                    <option value="EUR">€ Euro</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-close>İptal</button>
            <button type="button" class="btn btn-primary">💾 Kaydet</button>
        </div>
    </div>
</div>

<style>
/* Additional specific styles for this template */
.btn-sm {
    padding: var(--spacing-xs) var(--spacing-sm);
    font-size: var(--font-size-xs);
}

.filter-grid {
    align-items: end;
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
}

/* Loading state for table */
#assets-table.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Suggestion list styles */
.suggestion-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 100;
    max-height: 200px;
    overflow-y: auto;
}

.suggestion-item {
    padding: var(--spacing-sm) var(--spacing-md);
    cursor: pointer;
    border-bottom: 1px solid var(--gray-100);
    transition: background-color var(--transition-fast);
}

.suggestion-item:hover {
    background-color: var(--gray-50);
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* Tooltip styles */
.tooltip {
    position: absolute;
    background: var(--gray-900);
    color: white;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    z-index: 1000;
    pointer-events: none;
}
</style>

<script>
// Initialize the modern interface when document is ready
jQuery(document).ready(function($) {
    // Load the assets table
    loadAssetsTable();
    
    // Set up periodic refresh
    setInterval(function() {
        if ($('#auto-refresh-toggle').is(':checked')) {
            PortfoyTakipX.loadPortfolioData();
            loadAssetsTable();
        }
    }, parseInt($('#refresh-interval').val()) || 300000);
});

// Load and display assets in the table
function loadAssetsTable() {
    const tableBody = $('#assets-table-body');
    
    $.ajax({
        url: portfoy_takipx_admin.rest_url + 'assets',
        method: 'GET',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
        },
        success: function(response) {
            if (response && response.length > 0) {
                renderAssetsTable(response);
            } else {
                showEmptyState();
            }
        },
        error: function() {
            showErrorState();
        }
    });
}

// Render assets table with data
function renderAssetsTable(assets) {
    const tableBody = $('#assets-table-body');
    tableBody.empty();
    
    assets.forEach(function(asset) {
        const profitLoss = (asset.current_price - asset.purchase_price) * asset.quantity;
        const profitLossPercent = ((asset.current_price - asset.purchase_price) / asset.purchase_price) * 100;
        const profitLossClass = profitLoss >= 0 ? 'change-positive' : 'change-negative';
        
        const row = `
            <tr>
                <td><input type="checkbox" class="asset-checkbox" value="${asset.id}"></td>
                <td>
                    <div class="asset-symbol">
                        <div class="asset-icon">${asset.symbol.substring(0, 2)}</div>
                        <div class="asset-details">
                            <h4>${asset.symbol}</h4>
                            <p>${asset.name}</p>
                        </div>
                    </div>
                </td>
                <td><span class="asset-type-badge asset-type-${asset.asset_type}">${getAssetTypeLabel(asset.asset_type)}</span></td>
                <td>${parseFloat(asset.quantity).toLocaleString('tr-TR')}</td>
                <td>${PortfoyTakipX.formatCurrency(asset.purchase_price)}</td>
                <td>${PortfoyTakipX.formatCurrency(asset.current_price || asset.purchase_price)}</td>
                <td>${PortfoyTakipX.formatCurrency((asset.current_price || asset.purchase_price) * asset.quantity)}</td>
                <td class="${profitLossClass}">${PortfoyTakipX.formatCurrency(profitLoss)}</td>
                <td class="${profitLossClass}">${profitLossPercent.toFixed(2)}%</td>
                <td>
                    <button class="btn btn-secondary btn-sm edit-asset" data-id="${asset.id}" title="Düzenle">✏️</button>
                    <button class="btn btn-warning btn-sm refresh-asset-price" data-id="${asset.id}" title="Fiyat Güncelle">🔄</button>
                    <button class="btn btn-danger btn-sm delete-asset" data-id="${asset.id}" title="Sil">🗑️</button>
                </td>
            </tr>
        `;
        tableBody.append(row);
    });
}

// Show empty state
function showEmptyState() {
    $('#assets-table-body').html(`
        <tr>
            <td colspan="10" style="text-align: center; padding: var(--spacing-2xl);">
                <div style="color: var(--gray-500);">
                    <div style="font-size: 48px; margin-bottom: var(--spacing-md);">📊</div>
                    <h3>Henüz varlık eklenmemiş</h3>
                    <p>İlk varlığınızı eklemek için yukarıdaki "Varlık Ekle" butonunu kullanın.</p>
                    <button class="btn btn-primary" data-modal-target="add-asset-modal">➕ İlk Varlığını Ekle</button>
                </div>
            </td>
        </tr>
    `);
}

// Show error state
function showErrorState() {
    $('#assets-table-body').html(`
        <tr>
            <td colspan="10" style="text-align: center; padding: var(--spacing-2xl);">
                <div style="color: var(--error-500);">
                    <div style="font-size: 48px; margin-bottom: var(--spacing-md);">⚠️</div>
                    <h3>Veri yüklenirken hata oluştu</h3>
                    <p>Lütfen sayfayı yenilemeyi deneyin.</p>
                    <button class="btn btn-primary" onclick="loadAssetsTable()">🔄 Tekrar Dene</button>
                </div>
            </td>
        </tr>
    `);
}

// Get asset type label
function getAssetTypeLabel(type) {
    const labels = {
        'stock': 'Hisse',
        'crypto': 'Kripto',
        'forex': 'Döviz',
        'commodity': 'Emtia',
        'bond': 'Tahvil'
    };
    return labels[type] || type;
}

// Apply filters
function applyFilters() {
    const search = $('#asset-search').val();
    const assetType = $('#asset-type-filter').val();
    const performance = $('#performance-filter').val();
    
    // Filter logic here
    PortfoyTakipX.showNotification('🔍 Filtreler uygulandı', 'info');
}
</script>