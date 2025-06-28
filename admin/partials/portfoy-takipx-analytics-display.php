<?php
/**
 * Advanced Analytics and System Monitoring Display
 */
?>

<div class="wrap">
    <h1>📊 Gelişmiş Analitik & Sistem İzleme</h1>
    
    <div class="analytics-dashboard">
        
        <!-- System Health Overview -->
        <div class="system-health-section">
            <div class="card">
                <h2>🔧 Sistem Durumu</h2>
                <div class="health-grid">
                    <div class="health-item">
                        <div class="health-icon">💾</div>
                        <div class="health-content">
                            <h3>Cache Durumu</h3>
                            <div class="health-status active">Aktif</div>
                            <div class="health-details">
                                <span>Hit Rate: 89.5%</span>
                                <span>Size: 45.2 MB</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="health-item">
                        <div class="health-icon">🌐</div>
                        <div class="health-content">
                            <h3>API Bağlantıları</h3>
                            <div class="health-status warning">Uyarı</div>
                            <div class="health-details">
                                <span>Active: 3/5</span>
                                <span>Last Update: 2 min ago</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="health-item">
                        <div class="health-icon">📊</div>
                        <div class="health-content">
                            <h3>Veritabanı</h3>
                            <div class="health-status active">Sağlıklı</div>
                            <div class="health-details">
                                <span>Query Time: 45ms avg</span>
                                <span>Size: 2.8 MB</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="health-item">
                        <div class="health-icon">📝</div>
                        <div class="health-content">
                            <h3>Log Sistemi</h3>
                            <div class="health-status active">Aktif</div>
                            <div class="health-details">
                                <span>Level: INFO</span>
                                <span>Size: 12.4 MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="performance-metrics-section">
            <div class="card">
                <h2>⚡ Performans Metrikleri</h2>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value">156ms</div>
                        <div class="metric-label">Ortalama Yanıt Süresi</div>
                        <div class="metric-change positive">↓ 12ms</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-value">2.8MB</div>
                        <div class="metric-label">Bellek Kullanımı</div>
                        <div class="metric-change negative">↑ 0.3MB</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-value">1,247</div>
                        <div class="metric-label">Günlük API Çağrısı</div>
                        <div class="metric-change positive">↑ 45</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-value">99.2%</div>
                        <div class="metric-label">Uptime</div>
                        <div class="metric-change neutral">→ 0%</div>
                    </div>
                </div>
                
                <div class="performance-chart">
                    <canvas id="performance-metrics-chart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- API Provider Status -->
        <div class="api-providers-section">
            <div class="card">
                <h2>🔌 API Sağlayıcı Durumu</h2>
                <div class="providers-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Sağlayıcı</th>
                                <th>Durum</th>
                                <th>Tip</th>
                                <th>Rate Limit</th>
                                <th>Kullanım</th>
                                <th>Son Günceleme</th>
                                <th>Yanıt Süresi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="provider-info">
                                        <span class="provider-name">CoinGecko</span>
                                        <span class="provider-url">api.coingecko.com</span>
                                    </div>
                                </td>
                                <td><span class="status-badge active">Aktif</span></td>
                                <td>Crypto</td>
                                <td>50/min</td>
                                <td>
                                    <div class="usage-bar">
                                        <div class="usage-fill" style="width: 65%;"></div>
                                        <span class="usage-text">32/50</span>
                                    </div>
                                </td>
                                <td>2 dakika önce</td>
                                <td><span class="response-time good">245ms</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="provider-info">
                                        <span class="provider-name">Alpha Vantage</span>
                                        <span class="provider-url">alphavantage.co</span>
                                    </div>
                                </td>
                                <td><span class="status-badge inactive">Pasif</span></td>
                                <td>Stock</td>
                                <td>5/min</td>
                                <td>
                                    <div class="usage-bar">
                                        <div class="usage-fill" style="width: 100%;"></div>
                                        <span class="usage-text">5/5</span>
                                    </div>
                                </td>
                                <td>15 dakika önce</td>
                                <td><span class="response-time slow">1.2s</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="provider-info">
                                        <span class="provider-name">Yahoo Finance</span>
                                        <span class="provider-url">finance.yahoo.com</span>
                                    </div>
                                </td>
                                <td><span class="status-badge active">Aktif</span></td>
                                <td>Stock</td>
                                <td>2000/hour</td>
                                <td>
                                    <div class="usage-bar">
                                        <div class="usage-fill" style="width: 23%;"></div>
                                        <span class="usage-text">456/2000</span>
                                    </div>
                                </td>
                                <td>30 saniye önce</td>
                                <td><span class="response-time good">180ms</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activity Log -->
        <div class="activity-log-section">
            <div class="card">
                <h2>📋 Son Aktiviteler</h2>
                <div class="log-controls">
                    <select id="log-level-filter">
                        <option value="">Tüm Seviyeler</option>
                        <option value="DEBUG">Debug</option>
                        <option value="INFO">Info</option>
                        <option value="WARNING">Warning</option>
                        <option value="ERROR">Error</option>
                        <option value="CRITICAL">Critical</option>
                    </select>
                    <button type="button" class="button" onclick="refreshLogs()">🔄 Yenile</button>
                    <button type="button" class="button" onclick="clearLogs()">🗑️ Temizle</button>
                    <button type="button" class="button" onclick="exportLogs()">📄 Dışa Aktar</button>
                </div>
                
                <div class="activity-log">
                    <div class="log-entry info">
                        <div class="log-time">14:32:15</div>
                        <div class="log-level">INFO</div>
                        <div class="log-message">Portfolio summary cache updated for user 1</div>
                        <div class="log-details">Context: {"cache_key": "portfolio_summary_1", "ttl": 900}</div>
                    </div>
                    
                    <div class="log-entry warning">
                        <div class="log-time">14:31:42</div>
                        <div class="log-level">WARNING</div>
                        <div class="log-message">API rate limit reached for Alpha Vantage</div>
                        <div class="log-details">Context: {"provider": "alphavantage", "limit": 5, "reset_time": "2024-01-15 15:00:00"}</div>
                    </div>
                    
                    <div class="log-entry debug">
                        <div class="log-time">14:31:20</div>
                        <div class="log-level">DEBUG</div>
                        <div class="log-message">Price update request for AAPL</div>
                        <div class="log-details">Context: {"symbol": "AAPL", "old_price": 195.50, "new_price": 196.20}</div>
                    </div>
                    
                    <div class="log-entry error">
                        <div class="log-time">14:30:55</div>
                        <div class="log-level">ERROR</div>
                        <div class="log-message">Failed to connect to CoinGecko API</div>
                        <div class="log-details">Context: {"url": "api.coingecko.com", "error": "Connection timeout"}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Analytics -->
        <div class="database-analytics-section">
            <div class="card">
                <h2>🗄️ Veritabanı Analitikleri</h2>
                <div class="db-stats-grid">
                    <div class="db-stat-card">
                        <h3>Toplam Varlık</h3>
                        <div class="stat-value">1,247</div>
                        <div class="stat-change positive">+89 bu ay</div>
                    </div>
                    
                    <div class="db-stat-card">
                        <h3>Fiyat Kayıtları</h3>
                        <div class="stat-value">45,892</div>
                        <div class="stat-change positive">+2,156 bugün</div>
                    </div>
                    
                    <div class="db-stat-card">
                        <h3>İşlem Sayısı</h3>
                        <div class="stat-value">8,934</div>
                        <div class="stat-change positive">+234 bu hafta</div>
                    </div>
                    
                    <div class="db-stat-card">
                        <h3>Aktif Kullanıcı</h3>
                        <div class="stat-value">156</div>
                        <div class="stat-change positive">+12 bu ay</div>
                    </div>
                </div>
                
                <div class="db-table-sizes">
                    <h3>Tablo Boyutları</h3>
                    <table class="table-sizes">
                        <tr>
                            <td>portfoy_takipx_assets</td>
                            <td>1.2 MB</td>
                            <td>1,247 kayıt</td>
                        </tr>
                        <tr>
                            <td>portfoy_takipx_price_history</td>
                            <td>8.9 MB</td>
                            <td>45,892 kayıt</td>
                        </tr>
                        <tr>
                            <td>portfoy_takipx_transactions</td>
                            <td>2.3 MB</td>
                            <td>8,934 kayıt</td>
                        </tr>
                        <tr>
                            <td>portfoy_takipx_portfolio_snapshots</td>
                            <td>890 KB</td>
                            <td>3,567 kayıt</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cache Management -->
        <div class="cache-management-section">
            <div class="card">
                <h2>💾 Önbellek Yönetimi</h2>
                <div class="cache-stats">
                    <div class="cache-overview">
                        <div class="cache-metric">
                            <label>Hit Rate:</label>
                            <span class="value good">89.5%</span>
                        </div>
                        <div class="cache-metric">
                            <label>Miss Rate:</label>
                            <span class="value">10.5%</span>
                        </div>
                        <div class="cache-metric">
                            <label>Cache Size:</label>
                            <span class="value">45.2 MB</span>
                        </div>
                        <div class="cache-metric">
                            <label>Entries:</label>
                            <span class="value">2,847</span>
                        </div>
                    </div>
                    
                    <div class="cache-actions">
                        <button type="button" class="button button-primary" onclick="warmUpCache()">
                            🔥 Önbelleği Isıt
                        </button>
                        <button type="button" class="button" onclick="clearCache()">
                            🗑️ Önbelleği Temizle
                        </button>
                        <button type="button" class="button" onclick="optimizeCache()">
                            ⚡ Optimize Et
                        </button>
                    </div>
                </div>
                
                <div class="cache-breakdown">
                    <h3>Önbellek Dağılımı</h3>
                    <canvas id="cache-breakdown-chart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- System Configuration -->
        <div class="system-config-section">
            <div class="card">
                <h2>⚙️ Sistem Yapılandırması</h2>
                <form method="post" id="system-config-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Log Seviyesi</th>
                            <td>
                                <select name="log_level">
                                    <option value="1">Debug</option>
                                    <option value="2" selected>Info</option>
                                    <option value="3">Warning</option>
                                    <option value="4">Error</option>
                                    <option value="5">Critical</option>
                                </select>
                                <p class="description">Hangi seviyedeki logların kaydedileceğini belirler</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Cache TTL (saniye)</th>
                            <td>
                                <input type="number" name="cache_ttl" value="3600" min="60" max="86400" />
                                <p class="description">Önbellek verilerinin ne kadar süre saklanacağını belirler</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">API Timeout (saniye)</th>
                            <td>
                                <input type="number" name="api_timeout" value="15" min="5" max="60" />
                                <p class="description">API isteklerinin maksimum bekleme süresi</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Otomatik Fiyat Güncelleme</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="auto_price_update" checked />
                                    Fiyatları otomatik olarak güncelle
                                </label>
                                <p class="description">Saatlik otomatik fiyat güncellemesini etkinleştirir</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Günlük Snapshot</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="daily_snapshot" checked />
                                    Günlük portföy snapshot'ı al
                                </label>
                                <p class="description">Performans takibi için günlük anlık görüntüler alır</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Ayarları Kaydet" />
                    </p>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.analytics-dashboard {
    max-width: 1400px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.health-item {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #1e88e5;
}

.health-icon {
    font-size: 2.5em;
    margin-right: 15px;
}

.health-content h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.health-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
    margin-bottom: 8px;
    display: inline-block;
}

.health-status.active {
    background: #d4edda;
    color: #155724;
}

.health-status.warning {
    background: #fff3cd;
    color: #856404;
}

.health-status.error {
    background: #f8d7da;
    color: #721c24;
}

.health-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.health-details span {
    font-size: 0.9em;
    color: #666;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
}

.metric-value {
    font-size: 2.5em;
    font-weight: bold;
    margin-bottom: 5px;
}

.metric-label {
    font-size: 0.9em;
    opacity: 0.9;
    margin-bottom: 10px;
}

.metric-change {
    font-size: 0.9em;
    font-weight: bold;
}

.metric-change.positive {
    color: #4caf50;
}

.metric-change.negative {
    color: #f44336;
}

.metric-change.neutral {
    color: #ff9800;
}

.providers-table {
    overflow-x: auto;
}

.provider-info {
    display: flex;
    flex-direction: column;
}

.provider-name {
    font-weight: bold;
}

.provider-url {
    font-size: 0.9em;
    color: #666;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.usage-bar {
    position: relative;
    background: #f0f0f0;
    border-radius: 10px;
    height: 20px;
    width: 100px;
    overflow: hidden;
}

.usage-fill {
    background: linear-gradient(90deg, #4caf50 0%, #ff9800 70%, #f44336 100%);
    height: 100%;
    transition: width 0.3s ease;
}

.usage-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8em;
    font-weight: bold;
    color: white;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
}

.response-time {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: bold;
}

.response-time.good {
    background: #d4edda;
    color: #155724;
}

.response-time.slow {
    background: #fff3cd;
    color: #856404;
}

.log-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    align-items: center;
}

.activity-log {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.log-entry {
    display: grid;
    grid-template-columns: 80px 80px 1fr auto;
    gap: 10px;
    padding: 10px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-entry.info {
    background: rgba(33, 150, 243, 0.05);
}

.log-entry.warning {
    background: rgba(255, 152, 0, 0.05);
}

.log-entry.error {
    background: rgba(244, 67, 54, 0.05);
}

.log-entry.debug {
    background: rgba(156, 39, 176, 0.05);
}

.log-time {
    font-family: monospace;
    font-size: 0.9em;
    color: #666;
}

.log-level {
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8em;
    font-weight: bold;
    text-align: center;
}

.log-entry.info .log-level {
    background: #2196f3;
    color: white;
}

.log-entry.warning .log-level {
    background: #ff9800;
    color: white;
}

.log-entry.error .log-level {
    background: #f44336;
    color: white;
}

.log-entry.debug .log-level {
    background: #9c27b0;
    color: white;
}

.log-message {
    font-weight: 500;
}

.log-details {
    font-family: monospace;
    font-size: 0.8em;
    color: #666;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.db-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.db-stat-card {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #1e88e5;
}

.db-stat-card h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.stat-value {
    font-size: 2em;
    font-weight: bold;
    color: #1e88e5;
    margin-bottom: 5px;
}

.stat-change {
    font-size: 0.9em;
    font-weight: 500;
}

.stat-change.positive {
    color: #4caf50;
}

.table-sizes {
    width: 100%;
    border-collapse: collapse;
}

.table-sizes td {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.table-sizes td:last-child {
    text-align: right;
    color: #666;
}

.cache-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.cache-metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.cache-metric label {
    font-weight: 600;
}

.cache-metric .value {
    font-weight: bold;
}

.cache-metric .value.good {
    color: #4caf50;
}

.cache-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .analytics-dashboard {
        padding: 10px;
    }
    
    .health-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .log-entry {
        grid-template-columns: 1fr;
        gap: 5px;
    }
    
    .cache-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupRealTimeUpdates();
});

function initializeCharts() {
    // Performance metrics chart
    const ctx = document.getElementById('performance-metrics-chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                datasets: [{
                    label: 'Yanıt Süresi (ms)',
                    data: [165, 159, 180, 181, 156, 155],
                    borderColor: '#1e88e5',
                    backgroundColor: 'rgba(30, 136, 229, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Cache breakdown chart
    const cacheCtx = document.getElementById('cache-breakdown-chart');
    if (cacheCtx) {
        new Chart(cacheCtx, {
            type: 'doughnut',
            data: {
                labels: ['Portfolio Data', 'Price Data', 'Reports', 'User Sessions', 'Other'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: ['#1e88e5', '#4caf50', '#ff9800', '#9c27b0', '#f44336']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}

function setupRealTimeUpdates() {
    // Update metrics every 30 seconds
    setInterval(updateMetrics, 30000);
}

function updateMetrics() {
    // Simulate real-time metric updates
    console.log('Updating real-time metrics...');
}

function refreshLogs() {
    window.location.reload();
}

function clearLogs() {
    if (confirm('Tüm log kayıtlarını silmek istediğinizden emin misiniz?')) {
        // AJAX call to clear logs
        console.log('Clearing logs...');
    }
}

function exportLogs() {
    window.open('<?php echo admin_url( "admin.php?page=portfoy-takipx-analytics&action=export_logs" ); ?>', '_blank');
}

function warmUpCache() {
    // AJAX call to warm up cache
    console.log('Warming up cache...');
}

function clearCache() {
    if (confirm('Önbelleği temizlemek istediğinizden emin misiniz?')) {
        // AJAX call to clear cache
        console.log('Clearing cache...');
    }
}

function optimizeCache() {
    // AJAX call to optimize cache
    console.log('Optimizing cache...');
}
</script>