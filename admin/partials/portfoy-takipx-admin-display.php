<?php
/**
 * Provide an admin area view for the plugin
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="portfoy-takipx-admin-container">
        <div class="portfoy-summary-cards">
            <div class="summary-card">
                <h3>Toplam Portföy Değeri</h3>
                <div class="value" id="total-portfolio-value">₺0.00</div>
                <div class="change" id="total-portfolio-change">₺0.00 (0.00%)</div>
            </div>
            <div class="summary-card">
                <h3>Toplam Yatırım</h3>
                <div class="value" id="total-investment">₺0.00</div>
            </div>
            <div class="summary-card">
                <h3>Kar/Zarar</h3>
                <div class="value" id="profit-loss">₺0.00</div>
                <div class="change" id="profit-loss-percentage">0.00%</div>
            </div>
            <div class="summary-card">
                <h3>Toplam Varlık</h3>
                <div class="value" id="total-assets">0</div>
            </div>
        </div>

        <div class="portfoy-tabs">
            <button class="tab-button active" data-tab="assets">Varlıklarım</button>
            <button class="tab-button" data-tab="add-asset">Varlık Ekle</button>
            <button class="tab-button" data-tab="analytics">Analitik</button>
        </div>

        <div class="tab-content active" id="assets-tab">
            <div class="assets-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Tür</th>
                            <th>Sembol</th>
                            <th>Ad</th>
                            <th>Miktar</th>
                            <th>Alış Fiyatı</th>
                            <th>Güncel Fiyat</th>
                            <th>Toplam Değer</th>
                            <th>Kar/Zarar</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="assets-table-body">
                        <tr>
                            <td colspan="9" class="text-center">Varlık yükleniyor...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-content" id="add-asset-tab">
            <div class="add-asset-form">
                <h3>Yeni Varlık Ekle</h3>
                <form id="add-asset-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Varlık Türü</th>
                            <td>
                                <select name="asset_type" required>
                                    <option value="">Seçiniz...</option>
                                    <option value="stock">Hisse Senedi</option>
                                    <option value="crypto">Kripto Para</option>
                                    <option value="forex">Döviz</option>
                                    <option value="commodity">Emtia</option>
                                    <option value="bond">Tahvil</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Sembol</th>
                            <td>
                                <input type="text" name="symbol" placeholder="Örn: AAPL, BTC, EUR/USD" required maxlength="10" />
                                <p class="description">Varlığın kısa kodu veya sembolü</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Ad</th>
                            <td>
                                <input type="text" name="name" placeholder="Örn: Apple Inc., Bitcoin" required maxlength="100" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Miktar</th>
                            <td>
                                <input type="number" name="quantity" step="0.00000001" min="0" required />
                                <p class="description">Sahip olduğunuz miktar</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Alış Fiyatı</th>
                            <td>
                                <input type="number" name="purchase_price" step="0.00000001" min="0" required />
                                <p class="description">Varlığı satın aldığınız fiyat</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Güncel Fiyat</th>
                            <td>
                                <input type="number" name="current_price" step="0.00000001" min="0" />
                                <p class="description">Mevcut piyasa fiyatı (opsiyonel)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Alış Tarihi</th>
                            <td>
                                <input type="datetime-local" name="purchase_date" required />
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Varlık Ekle" />
                        <span class="spinner"></span>
                    </p>
                </form>
            </div>
        </div>

        <div class="tab-content" id="analytics-tab">
            <div class="analytics-container">
                <h3>Portföy Analizi</h3>
                <div class="analytics-charts">
                    <div class="chart-container">
                        <h4>Varlık Türü Dağılımı</h4>
                        <canvas id="asset-type-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Performans Grafiği</h4>
                        <canvas id="performance-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Asset Modal -->
    <div id="edit-asset-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Varlık Düzenle</h3>
            <form id="edit-asset-form">
                <input type="hidden" name="asset_id" />
                <table class="form-table">
                    <tr>
                        <th scope="row">Varlık Türü</th>
                        <td>
                            <select name="asset_type" required>
                                <option value="stock">Hisse Senedi</option>
                                <option value="crypto">Kripto Para</option>
                                <option value="forex">Döviz</option>
                                <option value="commodity">Emtia</option>
                                <option value="bond">Tahvil</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Sembol</th>
                        <td><input type="text" name="symbol" required maxlength="10" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Ad</th>
                        <td><input type="text" name="name" required maxlength="100" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Miktar</th>
                        <td><input type="number" name="quantity" step="0.00000001" min="0" required /></td>
                    </tr>
                    <tr>
                        <th scope="row">Alış Fiyatı</th>
                        <td><input type="number" name="purchase_price" step="0.00000001" min="0" required /></td>
                    </tr>
                    <tr>
                        <th scope="row">Güncel Fiyat</th>
                        <td><input type="number" name="current_price" step="0.00000001" min="0" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Alış Tarihi</th>
                        <td><input type="datetime-local" name="purchase_date" required /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="Güncelle" />
                    <button type="button" class="button" onclick="closeEditModal()">İptal</button>
                    <span class="spinner"></span>
                </p>
            </form>
        </div>
    </div>
</div>