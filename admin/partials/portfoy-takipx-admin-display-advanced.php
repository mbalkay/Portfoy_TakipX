<?php
/**
 * Advanced admin display with sophisticated features
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <!-- Advanced Dashboard -->
    <div class="portfoy-advanced-dashboard">
        <!-- Real-time Summary Cards -->
        <div class="summary-cards-grid">
            <div class="summary-card portfolio-value">
                <div class="card-icon">💼</div>
                <div class="card-content">
                    <h3>Toplam Portföy Değeri</h3>
                    <div class="value" id="total-portfolio-value">Yükleniyor...</div>
                    <div class="change" id="total-portfolio-change">-</div>
                    <div class="trend-indicator" id="value-trend"></div>
                </div>
            </div>
            
            <div class="summary-card investment">
                <div class="card-icon">💰</div>
                <div class="card-content">
                    <h3>Toplam Yatırım</h3>
                    <div class="value" id="total-investment">Yükleniyor...</div>
                    <div class="subtitle">Başlangıç sermayesi</div>
                </div>
            </div>
            
            <div class="summary-card performance">
                <div class="card-icon">📈</div>
                <div class="card-content">
                    <h3>Kar/Zarar</h3>
                    <div class="value" id="profit-loss">Yükleniyor...</div>
                    <div class="change" id="profit-loss-percentage">-</div>
                    <div class="subtitle">ROI: <span id="roi-percentage">-</span></div>
                </div>
            </div>
            
            <div class="summary-card risk">
                <div class="card-icon">⚖️</div>
                <div class="card-content">
                    <h3>Risk Profili</h3>
                    <div class="value" id="risk-score">Yükleniyor...</div>
                    <div class="subtitle">Volatilite: <span id="volatility">-</span></div>
                </div>
            </div>
            
            <div class="summary-card diversification">
                <div class="card-icon">🎯</div>
                <div class="card-content">
                    <h3>Çeşitlendirme</h3>
                    <div class="value" id="diversification-score">Yükleniyor...</div>
                    <div class="subtitle">Sharpe Oranı: <span id="sharpe-ratio">-</span></div>
                </div>
            </div>
            
            <div class="summary-card assets-count">
                <div class="card-icon">📊</div>
                <div class="card-content">
                    <h3>Toplam Varlık</h3>
                    <div class="value" id="total-assets">Yükleniyor...</div>
                    <div class="subtitle">Aktif pozisyonlar</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-bar">
            <button type="button" class="button button-primary" onclick="updateAllPrices()">
                🔄 Fiyatları Güncelle
            </button>
            <button type="button" class="button" onclick="generateSnapshot()">
                📸 Anlık Görüntü Al
            </button>
            <button type="button" class="button" onclick="exportPortfolio()">
                📊 Rapor Dışa Aktar
            </button>
            <button type="button" class="button" onclick="openAnalytics()">
                📈 Detaylı Analiz
            </button>
        </div>

        <!-- Advanced Filters and Search -->
        <div class="advanced-filters">
            <form method="get" id="assets-filter-form">
                <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label>🔍 Arama:</label>
                        <input type="text" name="s" value="<?php echo esc_attr( isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '' ); ?>" 
                               placeholder="Sembol veya isim ara..." />
                    </div>
                    
                    <div class="filter-group">
                        <label>📂 Tür:</label>
                        <?php
                        $asset_types = array(
                            'stock' => 'Hisse Senedi',
                            'crypto' => 'Kripto Para', 
                            'forex' => 'Döviz',
                            'commodity' => 'Emtia',
                            'bond' => 'Tahvil'
                        );
                        $current_type = isset( $_REQUEST['asset_type'] ) ? $_REQUEST['asset_type'] : '';
                        ?>
                        <select name="asset_type">
                            <option value="">Tüm Türler</option>
                            <?php foreach ( $asset_types as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_type, $value ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>📅 Tarih Aralığı:</label>
                        <input type="date" name="date_from" value="<?php echo esc_attr( isset( $_REQUEST['date_from'] ) ? $_REQUEST['date_from'] : '' ); ?>" />
                        <span>-</span>
                        <input type="date" name="date_to" value="<?php echo esc_attr( isset( $_REQUEST['date_to'] ) ? $_REQUEST['date_to'] : '' ); ?>" />
                    </div>
                    
                    <div class="filter-group">
                        <label>📈 Performans:</label>
                        <select name="performance_filter">
                            <option value="">Tümü</option>
                            <option value="winners" <?php selected( isset( $_REQUEST['performance_filter'] ) ? $_REQUEST['performance_filter'] : '', 'winners' ); ?>>Kazananlar</option>
                            <option value="losers" <?php selected( isset( $_REQUEST['performance_filter'] ) ? $_REQUEST['performance_filter'] : '', 'losers' ); ?>>Kaybedenler</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="button">Filtrele</button>
                    <button type="button" class="button" onclick="clearFilters()">Temizle</button>
                </div>
            </form>
        </div>

        <!-- Advanced Assets Table -->
        <div class="assets-table-advanced">
            <form method="post" id="assets-form">
                <?php
                $assets_table->display();
                ?>
            </form>
        </div>

        <!-- Performance Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <h3>📊 Portföy Performansı</h3>
                <canvas id="performance-chart" width="800" height="400"></canvas>
            </div>
            
            <div class="charts-row">
                <div class="chart-container half">
                    <h3>🥧 Varlık Dağılımı</h3>
                    <canvas id="allocation-chart" width="400" height="400"></canvas>
                </div>
                
                <div class="chart-container half">
                    <h3>📈 Kar/Zarar Dağılımı</h3>
                    <canvas id="profit-loss-chart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>

        <!-- Advanced Modals -->
        
        <!-- Asset Details Modal -->
        <div id="asset-details-modal" class="modal advanced-modal" style="display: none;">
            <div class="modal-content large">
                <div class="modal-header">
                    <h3 id="asset-details-title">Varlık Detayları</h3>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="asset-details-content">
                        <div class="details-tabs">
                            <button class="tab-button active" data-tab="overview">Genel Bakış</button>
                            <button class="tab-button" data-tab="performance">Performans</button>
                            <button class="tab-button" data-tab="transactions">İşlemler</button>
                            <button class="tab-button" data-tab="analytics">Analitik</button>
                        </div>
                        
                        <div class="tab-content active" id="overview-tab">
                            <div id="asset-overview-content">
                                <!-- Asset overview will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="tab-content" id="performance-tab">
                            <canvas id="asset-performance-chart"></canvas>
                        </div>
                        
                        <div class="tab-content" id="transactions-tab">
                            <div id="asset-transactions-list">
                                <!-- Transactions will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="tab-content" id="analytics-tab">
                            <div id="asset-analytics-content">
                                <!-- Analytics will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Add Asset Modal -->
        <div id="quick-add-asset-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>⚡ Hızlı Varlık Ekle</h3>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="quick-add-asset-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Sembol *</label>
                                <input type="text" name="symbol" required autocomplete="off" />
                                <div class="symbol-suggestions" id="symbol-suggestions"></div>
                            </div>
                            
                            <div class="form-group">
                                <label>Tür *</label>
                                <select name="asset_type" required>
                                    <option value="">Seçiniz...</option>
                                    <option value="stock">Hisse Senedi</option>
                                    <option value="crypto">Kripto Para</option>
                                    <option value="forex">Döviz</option>
                                    <option value="commodity">Emtia</option>
                                    <option value="bond">Tahvil</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Miktar *</label>
                                <input type="number" name="quantity" step="0.00000001" required />
                            </div>
                            
                            <div class="form-group">
                                <label>Alış Fiyatı *</label>
                                <input type="number" name="purchase_price" step="0.00000001" required />
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="button button-primary">Ekle</button>
                            <button type="button" class="button" onclick="closeQuickAddModal()">İptal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Floating Action Button -->
        <div class="floating-action-btn" onclick="openQuickAddModal()" title="Hızlı Varlık Ekle">
            <span class="dashicons dashicons-plus-alt"></span>
        </div>
    </div>
</div>

<style>
.portfoy-advanced-dashboard {
    max-width: 1400px;
}

.summary-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-5px);
}

.summary-card.portfolio-value {
    background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
}

.summary-card.investment {
    background: linear-gradient(135deg, #43a047 0%, #388e3c 100%);
}

.summary-card.performance {
    background: linear-gradient(135deg, #fb8c00 0%, #f57c00 100%);
}

.summary-card.risk {
    background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
}

.summary-card.diversification {
    background: linear-gradient(135deg, #8e24aa 0%, #7b1fa2 100%);
}

.summary-card.assets-count {
    background: linear-gradient(135deg, #00acc1 0%, #0097a7 100%);
}

.card-icon {
    font-size: 2.5em;
    margin-right: 15px;
}

.card-content h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.card-content .value {
    font-size: 1.8em;
    font-weight: bold;
    margin-bottom: 5px;
}

.card-content .change {
    font-size: 0.9em;
    opacity: 0.8;
}

.card-content .subtitle {
    font-size: 0.8em;
    opacity: 0.7;
    margin-top: 5px;
}

.quick-actions-bar {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.advanced-filters {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    font-size: 12px;
    color: #666;
}

.assets-table-advanced {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.charts-section {
    display: grid;
    gap: 20px;
    margin-top: 30px;
}

.charts-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.chart-container.half {
    min-height: 300px;
}

.chart-container h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
}

.advanced-modal .modal-content {
    max-width: 900px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.details-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.details-tabs .tab-button {
    background: none;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.details-tabs .tab-button.active {
    border-bottom-color: #1e88e5;
    color: #1e88e5;
}

.floating-action-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: #1e88e5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(30, 136, 229, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.floating-action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(30, 136, 229, 0.6);
}

.floating-action-btn .dashicons {
    color: white;
    font-size: 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-weight: 600;
    color: #333;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.positive {
    color: #4caf50;
}

.negative {
    color: #f44336;
}

@media (max-width: 768px) {
    .summary-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-row {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Advanced JavaScript functionality will be added here
document.addEventListener('DOMContentLoaded', function() {
    loadPortfolioSummary();
    initializeCharts();
    setupRealTimeUpdates();
    bindAdvancedEvents();
});

function loadPortfolioSummary() {
    // Load real-time portfolio data
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'portfoy_get_portfolio_summary',
            nonce: '<?php echo wp_create_nonce( "portfoy_takipx_nonce" ); ?>'
        },
        success: function(response) {
            if (response.success) {
                updateSummaryCards(response.data);
            }
        }
    });
}

function updateSummaryCards(data) {
    document.getElementById('total-portfolio-value').textContent = '₺' + Number(data.current_value).toLocaleString('tr-TR', {minimumFractionDigits: 2});
    document.getElementById('total-investment').textContent = '₺' + Number(data.total_invested).toLocaleString('tr-TR', {minimumFractionDigits: 2});
    document.getElementById('profit-loss').textContent = '₺' + Number(data.total_profit_loss).toLocaleString('tr-TR', {minimumFractionDigits: 2});
    document.getElementById('profit-loss-percentage').textContent = data.roi_percentage + '%';
    document.getElementById('roi-percentage').textContent = data.roi_percentage + '%';
    document.getElementById('total-assets').textContent = data.total_assets;
    document.getElementById('risk-score').textContent = data.volatility + '%';
    document.getElementById('volatility').textContent = data.volatility + '%';
    document.getElementById('diversification-score').textContent = data.diversification_score;
    document.getElementById('sharpe-ratio').textContent = data.sharpe_ratio;
}

function initializeCharts() {
    // Initialize Chart.js charts
    // Implementation details for charts
}

function setupRealTimeUpdates() {
    // Set up WebSocket or polling for real-time updates
    setInterval(loadPortfolioSummary, 60000); // Update every minute
}

function bindAdvancedEvents() {
    // Bind all advanced event handlers
}

function updateAllPrices() {
    // Implementation for bulk price update
}

function generateSnapshot() {
    // Implementation for generating portfolio snapshot
}

function exportPortfolio() {
    // Implementation for exporting portfolio data
}

function openAnalytics() {
    window.location.href = '<?php echo admin_url( "admin.php?page=portfoy-takipx-analytics" ); ?>';
}

function clearFilters() {
    // Clear all filters
    window.location.href = '<?php echo admin_url( "admin.php?page=portfoy-takipx" ); ?>';
}

function openQuickAddModal() {
    document.getElementById('quick-add-asset-modal').style.display = 'block';
}

function closeQuickAddModal() {
    document.getElementById('quick-add-asset-modal').style.display = 'none';
}
</script>