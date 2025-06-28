<?php
/**
 * Advanced Reports Display Page
 */
?>

<div class="wrap">
    <h1>📊 Portföy Raporları</h1>
    
    <div class="reports-container">
        <!-- Report Generation Form -->
        <div class="report-generator">
            <div class="card">
                <h2>📈 Rapor Oluştur</h2>
                <form method="post" id="report-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_from">Başlangıç Tarihi:</label>
                            <input type="date" name="date_from" id="date_from" 
                                   value="<?php echo date( 'Y-m-d', strtotime( '-1 year' ) ); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="date_to">Bitiş Tarihi:</label>
                            <input type="date" name="date_to" id="date_to" 
                                   value="<?php echo date( 'Y-m-d' ); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="report_type">Rapor Türü:</label>
                            <select name="report_type" id="report_type">
                                <option value="comprehensive">Kapsamlı Rapor</option>
                                <option value="performance">Performans Analizi</option>
                                <option value="risk">Risk Analizi</option>
                                <option value="allocation">Varlık Dağılımı</option>
                                <option value="transactions">İşlem Özeti</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="generate_report" class="button button-primary">
                                🔍 Rapor Oluştur
                            </button>
                            <button type="submit" name="export_pdf" class="button">
                                📄 PDF Dışa Aktar
                            </button>
                            <button type="submit" name="export_excel" class="button">
                                📊 Excel Dışa Aktar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ( isset( $report_data ) && $report_data ) : ?>
        <!-- Report Display -->
        <div class="report-display">
            
            <!-- Executive Summary -->
            <div class="report-section">
                <div class="card">
                    <h2>📋 Yönetici Özeti</h2>
                    <div class="executive-summary">
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="summary-value">₺<?php echo number_format( $report_data['summary']['current_value'], 2 ); ?></div>
                                <div class="summary-label">Toplam Portföy Değeri</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-value">₺<?php echo number_format( $report_data['summary']['total_invested'], 2 ); ?></div>
                                <div class="summary-label">Toplam Yatırım</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-value <?php echo $report_data['summary']['total_profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                    ₺<?php echo number_format( $report_data['summary']['total_profit_loss'], 2 ); ?>
                                </div>
                                <div class="summary-label">Net Kar/Zarar</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-value <?php echo $report_data['summary']['roi_percentage'] >= 0 ? 'positive' : 'negative'; ?>">
                                    %<?php echo number_format( $report_data['summary']['roi_percentage'], 2 ); ?>
                                </div>
                                <div class="summary-label">ROI</div>
                            </div>
                        </div>
                        
                        <div class="key-metrics">
                            <h3>📊 Anahtar Metrikler</h3>
                            <div class="metrics-grid">
                                <div class="metric">
                                    <span class="metric-label">Sharpe Oranı:</span>
                                    <span class="metric-value"><?php echo $report_data['summary']['sharpe_ratio']; ?></span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Volatilite:</span>
                                    <span class="metric-value"><?php echo $report_data['summary']['volatility']; ?>%</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Çeşitlendirme Skoru:</span>
                                    <span class="metric-value"><?php echo $report_data['summary']['diversification_score']; ?></span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">En İyi Performans:</span>
                                    <span class="metric-value positive"><?php echo $report_data['summary']['best_performer']; ?>%</span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">En Kötü Performans:</span>
                                    <span class="metric-value negative"><?php echo $report_data['summary']['worst_performer']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Analysis -->
            <div class="report-section">
                <div class="card">
                    <h2>📈 Performans Analizi</h2>
                    <div class="performance-analysis">
                        <div class="chart-container">
                            <canvas id="performance-timeline-chart"></canvas>
                        </div>
                        
                        <div class="performance-metrics">
                            <div class="metrics-row">
                                <div class="metric-card">
                                    <h4>📊 Toplam Getiri</h4>
                                    <div class="metric-value large <?php echo $report_data['performance']['metrics']['total_return'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo number_format( $report_data['performance']['metrics']['total_return'], 2 ); ?>%
                                    </div>
                                </div>
                                <div class="metric-card">
                                    <h4>📅 Yıllık Getiri</h4>
                                    <div class="metric-value large">
                                        <?php echo number_format( $report_data['performance']['metrics']['annualized_return'] * 100, 2 ); ?>%
                                    </div>
                                </div>
                                <div class="metric-card">
                                    <h4>⬇️ Maksimum Düşüş</h4>
                                    <div class="metric-value large negative">
                                        <?php echo number_format( $report_data['performance']['metrics']['max_drawdown'], 2 ); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Allocation -->
            <div class="report-section">
                <div class="card">
                    <h2>🥧 Varlık Dağılımı</h2>
                    <div class="allocation-analysis">
                        <div class="allocation-chart">
                            <canvas id="allocation-pie-chart"></canvas>
                        </div>
                        
                        <div class="allocation-table">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Varlık Türü</th>
                                        <th>Miktar</th>
                                        <th>Değer</th>
                                        <th>Oran</th>
                                        <th>Kar/Zarar</th>
                                        <th>Getiri %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $report_data['asset_allocation']['by_type'] as $allocation ) : ?>
                                    <tr>
                                        <td>
                                            <span class="asset-type-badge" style="background-color: <?php echo $this->get_asset_type_color( $allocation->asset_type ); ?>">
                                                <?php echo ucfirst( $allocation->asset_type ); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $allocation->count; ?> varlık</td>
                                        <td>₺<?php echo number_format( $allocation->total_value, 2 ); ?></td>
                                        <td>
                                            <div class="percentage-bar">
                                                <div class="percentage-fill" style="width: <?php echo $allocation->percentage; ?>%;"></div>
                                                <span class="percentage-text"><?php echo $allocation->percentage; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="<?php echo $allocation->profit_loss >= 0 ? 'positive' : 'negative'; ?>">
                                            ₺<?php echo number_format( $allocation->profit_loss, 2 ); ?>
                                        </td>
                                        <td class="<?php echo $allocation->profit_loss_percentage >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo number_format( $allocation->profit_loss_percentage, 2 ); ?>%
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="report-section">
                <div class="card">
                    <h2>🏆 En İyi ve En Kötü Performanslar</h2>
                    <div class="performers-analysis">
                        <div class="performers-row">
                            <div class="performers-column">
                                <h3>🚀 En İyi Performanslar</h3>
                                <div class="performers-list">
                                    <?php foreach ( array_slice( $report_data['top_performers']['top_performers'], 0, 5 ) as $performer ) : ?>
                                    <div class="performer-item positive">
                                        <div class="performer-info">
                                            <span class="performer-symbol"><?php echo esc_html( $performer->symbol ); ?></span>
                                            <span class="performer-name"><?php echo esc_html( $performer->name ); ?></span>
                                        </div>
                                        <div class="performer-return positive">
                                            <?php echo number_format( $performer->return_percentage, 2 ); ?>%
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="performers-column">
                                <h3>📉 En Kötü Performanslar</h3>
                                <div class="performers-list">
                                    <?php foreach ( array_slice( $report_data['top_performers']['worst_performers'], 0, 5 ) as $performer ) : ?>
                                    <div class="performer-item negative">
                                        <div class="performer-info">
                                            <span class="performer-symbol"><?php echo esc_html( $performer->symbol ); ?></span>
                                            <span class="performer-name"><?php echo esc_html( $performer->name ); ?></span>
                                        </div>
                                        <div class="performer-return negative">
                                            <?php echo number_format( $performer->return_percentage, 2 ); ?>%
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Analysis -->
            <div class="report-section">
                <div class="card">
                    <h2>⚖️ Risk Analizi</h2>
                    <div class="risk-analysis">
                        <div class="risk-metrics">
                            <div class="risk-overview">
                                <div class="risk-score">
                                    <div class="risk-circle">
                                        <div class="risk-value"><?php echo $report_data['risk_analysis']['risk_score']; ?></div>
                                        <div class="risk-label">Risk Skoru</div>
                                    </div>
                                </div>
                                
                                <div class="risk-details">
                                    <div class="risk-metric">
                                        <span class="risk-metric-label">Portföy Volatilitesi:</span>
                                        <span class="risk-metric-value"><?php echo $report_data['risk_analysis']['portfolio_volatility']; ?>%</span>
                                    </div>
                                    <div class="risk-metric">
                                        <span class="risk-metric-label">VaR (95%):</span>
                                        <span class="risk-metric-value"><?php echo $report_data['risk_analysis']['var_95']; ?>%</span>
                                    </div>
                                    <div class="risk-metric">
                                        <span class="risk-metric-label">VaR (99%):</span>
                                        <span class="risk-metric-value"><?php echo $report_data['risk_analysis']['var_99']; ?>%</span>
                                    </div>
                                    <div class="risk-metric">
                                        <span class="risk-metric-label">Beta:</span>
                                        <span class="risk-metric-value"><?php echo $report_data['risk_analysis']['beta']; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ( isset( $report_data['risk_analysis']['recommended_actions'] ) ) : ?>
                            <div class="risk-recommendations">
                                <h4>💡 Öneriler</h4>
                                <ul class="recommendations-list">
                                    <?php foreach ( $report_data['risk_analysis']['recommended_actions'] as $recommendation ) : ?>
                                    <li><?php echo esc_html( $recommendation ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Performance -->
            <?php if ( isset( $report_data['monthly_performance'] ) && !empty( $report_data['monthly_performance']['monthly_data'] ) ) : ?>
            <div class="report-section">
                <div class="card">
                    <h2>📅 Aylık Performans</h2>
                    <div class="monthly-performance">
                        <div class="monthly-chart">
                            <canvas id="monthly-performance-chart"></canvas>
                        </div>
                        
                        <div class="monthly-stats">
                            <div class="monthly-highlights">
                                <div class="highlight-item">
                                    <span class="highlight-label">En İyi Ay:</span>
                                    <span class="highlight-value positive">
                                        <?php echo $report_data['monthly_performance']['best_month']['month'] ?? 'N/A'; ?>
                                        (<?php echo number_format( $report_data['monthly_performance']['best_month']['return'] ?? 0, 2 ); ?>%)
                                    </span>
                                </div>
                                <div class="highlight-item">
                                    <span class="highlight-label">En Kötü Ay:</span>
                                    <span class="highlight-value negative">
                                        <?php echo $report_data['monthly_performance']['worst_month']['month'] ?? 'N/A'; ?>
                                        (<?php echo number_format( $report_data['monthly_performance']['worst_month']['return'] ?? 0, 2 ); ?>%)
                                    </span>
                                </div>
                                <div class="highlight-item">
                                    <span class="highlight-label">Tutarlılık Skoru:</span>
                                    <span class="highlight-value">
                                        <?php echo number_format( $report_data['monthly_performance']['consistency_score'] ?? 0, 2 ); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Transaction Summary -->
            <div class="report-section">
                <div class="card">
                    <h2>💱 İşlem Özeti</h2>
                    <div class="transaction-summary">
                        <?php if ( isset( $report_data['transaction_summary']['summary'] ) && !empty( $report_data['transaction_summary']['summary'] ) ) : ?>
                        <div class="transaction-overview">
                            <div class="transaction-types">
                                <?php foreach ( $report_data['transaction_summary']['summary'] as $transaction_type ) : ?>
                                <div class="transaction-type-card">
                                    <h4><?php echo ucfirst( $transaction_type->transaction_type ); ?></h4>
                                    <div class="transaction-count"><?php echo $transaction_type->count; ?> işlem</div>
                                    <div class="transaction-amount">₺<?php echo number_format( $transaction_type->total_amount, 2 ); ?></div>
                                    <div class="transaction-avg">Ort: ₺<?php echo number_format( $transaction_type->avg_amount, 2 ); ?></div>
                                    <?php if ( $transaction_type->total_commission > 0 ) : ?>
                                    <div class="transaction-commission">Komisyon: ₺<?php echo number_format( $transaction_type->total_commission, 2 ); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else : ?>
                        <p>Bu dönemde işlem bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.reports-container {
    max-width: 1200px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-row {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
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
    display: flex;
    gap: 10px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.summary-value {
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 10px;
}

.summary-label {
    color: #666;
    font-size: 0.9em;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.metric {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.metric-label {
    font-weight: 600;
}

.chart-container {
    margin: 20px 0;
    height: 400px;
}

.allocation-analysis {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
    align-items: start;
}

.allocation-chart {
    max-width: 300px;
}

.asset-type-badge {
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}

.percentage-bar {
    position: relative;
    background: #f0f0f0;
    border-radius: 10px;
    height: 20px;
    width: 100px;
}

.percentage-fill {
    background: #1e88e5;
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.percentage-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8em;
    font-weight: bold;
    color: white;
}

.performers-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.performer-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.performer-item.positive {
    background: rgba(76, 175, 80, 0.1);
    border-left: 4px solid #4caf50;
}

.performer-item.negative {
    background: rgba(244, 67, 54, 0.1);
    border-left: 4px solid #f44336;
}

.performer-symbol {
    font-weight: bold;
    font-size: 1.1em;
}

.performer-name {
    color: #666;
    font-size: 0.9em;
}

.performer-return {
    font-weight: bold;
    font-size: 1.2em;
}

.risk-overview {
    display: flex;
    gap: 30px;
    align-items: center;
    margin-bottom: 30px;
}

.risk-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, #4caf50 0deg 120deg, #ff9800 120deg 240deg, #f44336 240deg 360deg);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.risk-value {
    font-size: 2em;
}

.risk-label {
    font-size: 0.9em;
}

.risk-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.risk-metric {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.recommendations-list {
    list-style: none;
    padding: 0;
}

.recommendations-list li {
    padding: 10px;
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    margin-bottom: 10px;
    border-radius: 4px;
}

.monthly-performance {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.monthly-highlights {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.highlight-item {
    display: flex;
    justify-content: space-between;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.highlight-label {
    font-weight: 600;
}

.transaction-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.transaction-type-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.transaction-type-card h4 {
    margin-top: 0;
    color: #333;
}

.transaction-count {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 10px;
}

.transaction-amount {
    font-size: 1.5em;
    font-weight: bold;
    color: #1e88e5;
    margin-bottom: 5px;
}

.transaction-avg {
    color: #666;
    font-size: 0.9em;
}

.transaction-commission {
    color: #f44336;
    font-size: 0.9em;
    margin-top: 5px;
}

.positive {
    color: #4caf50;
}

.negative {
    color: #f44336;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
    }
    
    .allocation-analysis {
        grid-template-columns: 1fr;
    }
    
    .performers-row {
        grid-template-columns: 1fr;
    }
    
    .monthly-performance {
        grid-template-columns: 1fr;
    }
    
    .risk-overview {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ( isset( $report_data ) && $report_data ) : ?>
    initializeReportCharts();
    <?php endif; ?>
});

function initializeReportCharts() {
    // Performance Timeline Chart
    <?php if ( isset( $report_data['performance']['chart_data'] ) ) : ?>
    const performanceCtx = document.getElementById('performance-timeline-chart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'line',
            data: <?php echo json_encode( $report_data['performance']['chart_data'] ); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Portföy Performansı Zaman Çizelgesi'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // Asset Allocation Pie Chart
    <?php if ( isset( $report_data['asset_allocation']['chart_data'] ) ) : ?>
    const allocationCtx = document.getElementById('allocation-pie-chart');
    if (allocationCtx) {
        new Chart(allocationCtx, {
            type: 'pie',
            data: <?php echo json_encode( $report_data['asset_allocation']['chart_data'] ); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Varlık Türü Dağılımı'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // Monthly Performance Chart
    <?php if ( isset( $report_data['monthly_performance']['monthly_data'] ) && !empty( $report_data['monthly_performance']['monthly_data'] ) ) : ?>
    const monthlyCtx = document.getElementById('monthly-performance-chart');
    if (monthlyCtx) {
        const monthlyData = {
            labels: <?php echo json_encode( array_column( $report_data['monthly_performance']['monthly_data'], 'month' ) ); ?>,
            datasets: [{
                label: 'Aylık Getiri (%)',
                data: <?php echo json_encode( array_column( $report_data['monthly_performance']['monthly_data'], 'avg_return' ) ); ?>,
                borderColor: '#1e88e5',
                backgroundColor: 'rgba(30, 136, 229, 0.1)',
                tension: 0.4
            }]
        };

        new Chart(monthlyCtx, {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Aylık Performans Trendi'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    <?php endif; ?>
}
</script>