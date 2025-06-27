/**
 * Public JavaScript for Portföy TakipX
 * Twenty Twenty-Three Theme Compatible
 */

(function($) {
    'use strict';

    // Global variables
    let currentFilter = '';
    let assetsData = [];
    let summaryData = {};

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.portfoy-takipx-container').length > 0) {
            initPortfolio();
        }
        if ($('.portfoy-summary-widget').length > 0) {
            initSummaryWidget();
        }
        if ($('.portfoy-assets-list').length > 0) {
            initAssetsList();
        }
    });

    // Initialize main portfolio functionality
    function initPortfolio() {
        loadPortfolioData();
        loadAssets();
        bindEvents();
        initModal();
        initFilters();
    }

    // Initialize summary widget
    function initSummaryWidget() {
        loadSummaryData();
    }

    // Initialize assets list
    function initAssetsList() {
        bindAssetListEvents();
    }

    // Bind event handlers
    function bindEvents() {
        // Add asset form submission
        $('#public-add-asset-form').on('submit', function(e) {
            e.preventDefault();
            addAsset();
        });

        // Edit asset form submission
        $('#public-edit-form').on('submit', function(e) {
            e.preventDefault();
            updateAsset();
        });

        // Edit button clicks (delegated for dynamic content)
        $(document).on('click', '.edit-btn', function() {
            var assetId = $(this).data('id');
            openEditModal(assetId);
        });

        // Delete button clicks (delegated for dynamic content)
        $(document).on('click', '.delete-btn', function() {
            var assetId = $(this).data('id');
            if (confirm('Bu varlığı silmek istediğinizden emin misiniz?')) {
                deleteAsset(assetId);
            }
        });
    }

    // Bind events for assets list (shortcode)
    function bindAssetListEvents() {
        $(document).on('click', '.edit-asset', function() {
            var assetId = $(this).data('id');
            openEditModal(assetId);
        });

        $(document).on('click', '.delete-asset', function() {
            var assetId = $(this).data('id');
            if (confirm('Bu varlığı silmek istediğinizden emin misiniz?')) {
                deleteAsset(assetId);
            }
        });
    }

    // Initialize filters
    function initFilters() {
        $('.filter-btn').on('click', function() {
            var filterType = $(this).data('type');
            
            // Update active filter button
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            // Apply filter
            currentFilter = filterType;
            filterAssets(filterType);
        });
    }

    // Load portfolio summary data
    function loadPortfolioData() {
        if (!portfoy_takipx_public.is_user_logged_in) {
            return;
        }

        $.ajax({
            url: portfoy_takipx_public.rest_url + 'portfolio/summary',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_public.rest_nonce);
            },
            success: function(response) {
                summaryData = response;
                updateSummaryCards(response);
                loadAnalytics(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading portfolio data:', error);
                showNotice('Portföy verisi yüklenirken hata oluştu.', 'error');
            }
        });
    }

    // Load summary data for widget
    function loadSummaryData() {
        if (!portfoy_takipx_public.is_user_logged_in) {
            return;
        }

        $.ajax({
            url: portfoy_takipx_public.rest_url + 'portfolio/summary',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_public.rest_nonce);
            },
            success: function(response) {
                updateSummaryWidget(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading summary data:', error);
            }
        });
    }

    // Update summary cards
    function updateSummaryCards(data) {
        var currency = '₺';
        
        $('#public-total-value').text(formatCurrency(data.current_value, currency));
        $('#public-total-investment').text(formatCurrency(data.total_invested, currency));
        
        var profitLoss = data.profit_loss;
        var profitLossClass = profitLoss >= 0 ? 'positive' : 'negative';
        
        $('#public-profit-loss').text(formatCurrency(profitLoss, currency))
                               .removeClass('positive negative')
                               .addClass(profitLossClass);
        
        $('#public-profit-percentage').text(data.profit_loss_percentage.toFixed(2) + '%')
                                     .removeClass('positive negative')
                                     .addClass(profitLossClass);
        
        var totalChangeValue = profitLoss;
        var totalChangePercentage = data.profit_loss_percentage;
        
        $('#public-total-change').text(formatCurrency(totalChangeValue, currency) + ' (' + totalChangePercentage.toFixed(2) + '%)')
                                 .removeClass('positive negative')
                                 .addClass(profitLossClass);
    }

    // Update summary widget
    function updateSummaryWidget(data) {
        // This function would update widget-specific elements
        // Implementation depends on widget structure
        console.log('Summary widget updated with data:', data);
    }

    // Load assets
    function loadAssets() {
        if (!portfoy_takipx_public.is_user_logged_in) {
            return;
        }

        $.ajax({
            url: portfoy_takipx_public.rest_url + 'assets',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_public.rest_nonce);
            },
            success: function(response) {
                assetsData = response;
                renderAssetsGrid(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading assets:', error);
                $('#public-assets-grid').html('<div class="loading-message">Varlıklar yüklenirken hata oluştu.</div>');
            }
        });
    }

    // Render assets grid
    function renderAssetsGrid(assets) {
        var container = $('#public-assets-grid');
        container.empty();

        if (assets.length === 0) {
            container.html('<div class="loading-message">Henüz varlık eklenmemiş.</div>');
            return;
        }

        assets.forEach(function(asset) {
            var totalValue = parseFloat(asset.quantity) * parseFloat(asset.current_price || asset.purchase_price);
            var totalInvested = parseFloat(asset.quantity) * parseFloat(asset.purchase_price);
            var profitLoss = totalValue - totalInvested;
            var profitLossPercentage = totalInvested > 0 ? (profitLoss / totalInvested) * 100 : 0;
            
            var profitLossClass = profitLoss >= 0 ? 'profit-positive' : 'profit-negative';
            
            var card = $(`
                <div class="asset-card" data-type="${asset.asset_type}">
                    <div class="asset-header">
                        <div class="asset-info">
                            <h4 class="asset-symbol">${asset.symbol}</h4>
                            <p class="asset-name">${asset.name}</p>
                            <span class="asset-type-badge ${asset.asset_type}">
                                ${getAssetTypeLabel(asset.asset_type)}
                            </span>
                        </div>
                        <div class="asset-actions">
                            <button class="action-btn edit-btn" data-id="${asset.id}" title="Düzenle">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button class="action-btn delete-btn" data-id="${asset.id}" title="Sil">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <div class="asset-details">
                        <div class="detail-row">
                            <span class="detail-label">Miktar:</span>
                            <span class="detail-value">${formatNumber(asset.quantity)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Alış Fiyatı:</span>
                            <span class="detail-value">₺${formatNumber(asset.purchase_price)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Güncel Fiyat:</span>
                            <span class="detail-value">₺${formatNumber(asset.current_price || asset.purchase_price)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Toplam Değer:</span>
                            <span class="detail-value total-value">₺${formatNumber(totalValue)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Kar/Zarar:</span>
                            <span class="detail-value ${profitLossClass}">
                                ₺${formatNumber(profitLoss)}<br>
                                <small>(${profitLossPercentage.toFixed(2)}%)</small>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Alış Tarihi:</span>
                            <span class="detail-value">
                                ${formatDate(asset.purchase_date)}
                            </span>
                        </div>
                    </div>
                </div>
            `);
            
            container.append(card);
        });

        // Apply current filter if any
        if (currentFilter) {
            filterAssets(currentFilter);
        }
    }

    // Filter assets
    function filterAssets(type) {
        $('.asset-card').each(function() {
            var cardType = $(this).data('type');
            if (type === '' || cardType === type) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Add new asset
    function addAsset() {
        var form = $('#public-add-asset-form');
        var formData = new FormData(form[0]);
        var data = {};
        
        for (var pair of formData.entries()) {
            data[pair[0]] = pair[1];
        }

        var spinner = form.find('.spinner');
        spinner.addClass('is-active');

        $.ajax({
            url: portfoy_takipx_public.rest_url + 'assets',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_public.rest_nonce);
            },
            success: function(response) {
                form[0].reset();
                
                // Reset the purchase date to current time
                var now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                form.find('input[name="purchase_date"]').val(now.toISOString().slice(0, 16));
                
                showNotice('Varlık başarıyla eklendi!', 'success');
                loadAssets();
                loadPortfolioData();
                
                // Close the details element
                form.closest('details').removeAttr('open');
            },
            error: function(xhr, status, error) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Varlık eklenirken hata oluştu.';
                showNotice(message, 'error');
            },
            complete: function() {
                spinner.removeClass('is-active');
            }
        });
    }

    // Modal functionality
    function initModal() {
        // Close modal when clicking close button
        $(document).on('click', '.modal-close', function() {
            closePublicEditModal();
        });

        // Close modal when clicking outside
        $(document).on('click', '.portfoy-modal', function(e) {
            if ($(e.target).is('.portfoy-modal')) {
                closePublicEditModal();
            }
        });

        // Close modal on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#public-edit-modal:visible').length > 0) {
                closePublicEditModal();
            }
        });
    }

    // Open edit modal
    function openEditModal(assetId) {
        var asset = assetsData.find(a => a.id == assetId);
        if (asset) {
            populateEditForm(asset);
            $('#public-edit-modal').show();
            $('body').addClass('modal-open');
        }
    }

    // Close edit modal
    window.closePublicEditModal = function() {
        $('#public-edit-modal').hide();
        $('body').removeClass('modal-open');
    };

    // Populate edit form
    function populateEditForm(asset) {
        var form = $('#public-edit-form');
        form.find('input[name="asset_id"]').val(asset.id);
        form.find('select[name="asset_type"]').val(asset.asset_type);
        form.find('input[name="symbol"]').val(asset.symbol);
        form.find('input[name="name"]').val(asset.name);
        form.find('input[name="quantity"]').val(asset.quantity);
        form.find('input[name="purchase_price"]').val(asset.purchase_price);
        form.find('input[name="current_price"]').val(asset.current_price);
        
        // Format date for datetime-local input
        var date = new Date(asset.purchase_date);
        var formattedDate = date.getFullYear() + '-' + 
            String(date.getMonth() + 1).padStart(2, '0') + '-' + 
            String(date.getDate()).padStart(2, '0') + 'T' + 
            String(date.getHours()).padStart(2, '0') + ':' + 
            String(date.getMinutes()).padStart(2, '0');
        form.find('input[name="purchase_date"]').val(formattedDate);
    }

    // Update asset
    function updateAsset() {
        var form = $('#public-edit-form');
        var assetId = form.find('input[name="asset_id"]').val();
        var formData = new FormData(form[0]);
        var data = {};
        
        for (var pair of formData.entries()) {
            if (pair[0] !== 'asset_id') {
                data[pair[0]] = pair[1];
            }
        }

        var spinner = form.find('.spinner');
        spinner.addClass('is-active');

        $.ajax({
            url: portfoy_takipx_public.rest_url + 'assets/' + assetId,
            method: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_public.rest_nonce);
            },
            success: function(response) {
                closePublicEditModal();
                showNotice('Varlık başarıyla güncellendi!', 'success');
                loadAssets();
                loadPortfolioData();
            },
            error: function(xhr, status, error) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Varlık güncellenirken hata oluştu.';
                showNotice(message, 'error');
            },
            complete: function() {
                spinner.removeClass('is-active');
            }
        });
    }

    // Delete asset
    function deleteAsset(assetId) {
        $.ajax({
            url: portfoy_takipx_public.rest_url + 'assets/' + assetId,
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_public.rest_nonce);
            },
            success: function(response) {
                showNotice('Varlık başarıyla silindi!', 'success');
                loadAssets();
                loadPortfolioData();
                
                // Also refresh any asset list shortcodes on the page
                if ($('.portfoy-assets-list').length > 0) {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Varlık silinirken hata oluştu.';
                showNotice(message, 'error');
            }
        });
    }

    // Load analytics
    function loadAnalytics(summaryData) {
        if (typeof Chart === 'undefined') {
            // Load Chart.js if not available
            loadChartJS(function() {
                initCharts(summaryData);
            });
        } else {
            initCharts(summaryData);
        }
        
        updatePerformanceStats(summaryData);
    }

    // Load Chart.js library
    function loadChartJS(callback) {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = callback;
        document.head.appendChild(script);
    }

    // Initialize charts
    function initCharts(data) {
        initTypeChart(data);
    }

    // Initialize asset type distribution chart
    function initTypeChart(data) {
        var ctx = document.getElementById('public-type-chart');
        if (!ctx || !data.summary_by_type || data.summary_by_type.length === 0) {
            return;
        }

        var labels = [];
        var values = [];
        var colors = ['#2271b1', '#f77b00', '#00a32a', '#b32d2e', '#6c2eb1'];

        data.summary_by_type.forEach(function(item, index) {
            labels.push(getAssetTypeLabel(item.asset_type));
            values.push(parseFloat(item.current_value));
        });

        new Chart(ctx, {
            type: 'doughnut',
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
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = formatCurrency(context.parsed, '₺');
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = ((context.parsed / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Update performance stats
    function updatePerformanceStats(data) {
        var container = $('#public-performance-stats');
        if (container.length === 0) return;

        var html = `
            <div class="performance-stat">
                <span class="stat-value">${data.summary_by_type ? data.summary_by_type.length : 0}</span>
                <span class="stat-label">Varlık Türü</span>
            </div>
            <div class="performance-stat">
                <span class="stat-value ${data.profit_loss >= 0 ? 'profit-positive' : 'profit-negative'}">
                    ${data.profit_loss_percentage.toFixed(1)}%
                </span>
                <span class="stat-label">Toplam Getiri</span>
            </div>
            <div class="performance-stat">
                <span class="stat-value">${formatCurrency(data.current_value, '₺')}</span>
                <span class="stat-label">Portföy Değeri</span>
            </div>
            <div class="performance-stat">
                <span class="stat-value">${getTotalAssetCount(data)}</span>
                <span class="stat-label">Toplam Varlık</span>
            </div>
        `;
        
        container.html(html);
    }

    // Helper functions
    function formatCurrency(amount, currency) {
        return currency + formatNumber(amount);
    }

    function formatNumber(number) {
        return parseFloat(number).toLocaleString('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 8
        });
    }

    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('tr-TR') + ' ' + date.toLocaleTimeString('tr-TR', { 
            hour: '2-digit', 
            minute: '2-digit' 
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

    function getTotalAssetCount(data) {
        if (!data.summary_by_type) return 0;
        return data.summary_by_type.reduce((total, item) => total + parseInt(item.total_assets), 0);
    }

    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $(`
            <div class="notice portfoy-notice ${noticeClass}" style="margin: 15px 0; padding: 10px 15px; border-left: 4px solid; background: #fff;">
                <p style="margin: 0;">${message}</p>
            </div>
        `);
        
        // Style the notice based on type
        if (type === 'success') {
            notice.css('border-left-color', '#00a32a');
        } else {
            notice.css('border-left-color', '#d63638');
        }
        
        // Find the best place to insert the notice
        var container = $('.portfoy-takipx-container, .portfoy-summary-widget, .portfoy-assets-list').first();
        if (container.length > 0) {
            container.prepend(notice);
        } else {
            $('body').prepend(notice);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 5000);
    }

    // Add some body classes for modal state
    $('body').append('<style>.modal-open { overflow: hidden; }</style>');

})(jQuery);