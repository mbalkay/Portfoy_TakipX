/**
 * Admin JavaScript for Portföy TakipX
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initTabs();
        loadPortfolioData();
        bindEvents();
        initModal();
    });

    // Initialize tab functionality
    function initTabs() {
        $('.tab-button').on('click', function() {
            var tabId = $(this).data('tab');
            
            // Update active tab button
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Update active tab content
            $('.tab-content').removeClass('active');
            $('#' + tabId + '-tab').addClass('active');
            
            // Load data for specific tabs
            if (tabId === 'assets') {
                loadAssets();
            } else if (tabId === 'analytics') {
                loadAnalytics();
            }
        });
    }

    // Bind event handlers
    function bindEvents() {
        // Add asset form submission
        $('#add-asset-form').on('submit', function(e) {
            e.preventDefault();
            addAsset();
        });

        // Edit asset form submission
        $('#edit-asset-form').on('submit', function(e) {
            e.preventDefault();
            updateAsset();
        });

        // Edit button clicks
        $(document).on('click', '.edit-asset', function() {
            var assetId = $(this).data('id');
            openEditModal(assetId);
        });

        // Delete button clicks
        $(document).on('click', '.delete-asset', function() {
            var assetId = $(this).data('id');
            if (confirm('Bu varlığı silmek istediğinizden emin misiniz?')) {
                deleteAsset(assetId);
            }
        });
    }

    // Load portfolio summary data
    function loadPortfolioData() {
        $.ajax({
            url: portfoy_takipx_admin.rest_url + 'portfolio/summary',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                updateSummaryCards(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading portfolio data:', error);
            }
        });
    }

    // Update summary cards with data
    function updateSummaryCards(data) {
        var currency = '₺'; // This could be dynamic based on settings
        
        $('#total-portfolio-value').text(formatCurrency(data.current_value, currency));
        $('#total-investment').text(formatCurrency(data.total_invested, currency));
        
        var profitLoss = data.profit_loss;
        var profitLossClass = profitLoss >= 0 ? 'positive' : 'negative';
        
        $('#profit-loss').text(formatCurrency(profitLoss, currency))
                        .removeClass('positive negative')
                        .addClass(profitLossClass);
        
        $('#profit-loss-percentage').text(data.profit_loss_percentage.toFixed(2) + '%')
                                   .removeClass('positive negative')
                                   .addClass(profitLossClass);
        
        // Count total assets
        var totalAssets = 0;
        if (data.summary_by_type) {
            data.summary_by_type.forEach(function(item) {
                totalAssets += parseInt(item.total_assets);
            });
        }
        $('#total-assets').text(totalAssets);
    }

    // Load assets table
    function loadAssets() {
        $.ajax({
            url: portfoy_takipx_admin.rest_url + 'assets',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                renderAssetsTable(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading assets:', error);
                $('#assets-table-body').html('<tr><td colspan="9" class="text-center">Varlıklar yüklenirken hata oluştu.</td></tr>');
            }
        });
    }

    // Render assets table
    function renderAssetsTable(assets) {
        var tbody = $('#assets-table-body');
        tbody.empty();

        if (assets.length === 0) {
            tbody.html('<tr><td colspan="9" class="text-center">Henüz varlık eklenmemiş.</td></tr>');
            return;
        }

        assets.forEach(function(asset) {
            var totalValue = parseFloat(asset.quantity) * parseFloat(asset.current_price || asset.purchase_price);
            var totalInvested = parseFloat(asset.quantity) * parseFloat(asset.purchase_price);
            var profitLoss = totalValue - totalInvested;
            var profitLossPercentage = totalInvested > 0 ? (profitLoss / totalInvested) * 100 : 0;
            
            var profitLossClass = profitLoss >= 0 ? 'profit-positive' : 'profit-negative';
            
            var row = `
                <tr>
                    <td><span class="asset-type-badge ${asset.asset_type}">${getAssetTypeLabel(asset.asset_type)}</span></td>
                    <td><strong>${asset.symbol}</strong></td>
                    <td>${asset.name}</td>
                    <td>${formatNumber(asset.quantity)}</td>
                    <td>₺${formatNumber(asset.purchase_price)}</td>
                    <td>₺${formatNumber(asset.current_price || asset.purchase_price)}</td>
                    <td><strong>₺${formatNumber(totalValue)}</strong></td>
                    <td class="${profitLossClass}">
                        ₺${formatNumber(profitLoss)}<br>
                        <small>(${profitLossPercentage.toFixed(2)}%)</small>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-button edit edit-asset" data-id="${asset.id}">Düzenle</button>
                            <button class="action-button delete delete-asset" data-id="${asset.id}">Sil</button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Add new asset
    function addAsset() {
        var form = $('#add-asset-form');
        var formData = new FormData(form[0]);
        var data = {};
        
        for (var pair of formData.entries()) {
            data[pair[0]] = pair[1];
        }

        var spinner = form.find('.spinner');
        spinner.addClass('is-active');

        $.ajax({
            url: portfoy_takipx_admin.rest_url + 'assets',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                form[0].reset();
                showNotice('Varlık başarıyla eklendi!', 'success');
                loadAssets();
                loadPortfolioData();
            },
            error: function(xhr, status, error) {
                showNotice('Varlık eklenirken hata oluştu: ' + error, 'error');
            },
            complete: function() {
                spinner.removeClass('is-active');
            }
        });
    }

    // Modal functionality
    function initModal() {
        // Close modal when clicking X
        $('.close').on('click', function() {
            closeEditModal();
        });

        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('#edit-asset-modal')) {
                closeEditModal();
            }
        });
    }

    // Open edit modal
    function openEditModal(assetId) {
        $.ajax({
            url: portfoy_takipx_admin.rest_url + 'assets',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(assets) {
                var asset = assets.find(a => a.id == assetId);
                if (asset) {
                    populateEditForm(asset);
                    $('#edit-asset-modal').show();
                }
            }
        });
    }

    // Close edit modal
    window.closeEditModal = function() {
        $('#edit-asset-modal').hide();
    };

    // Populate edit form
    function populateEditForm(asset) {
        var form = $('#edit-asset-form');
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
        var form = $('#edit-asset-form');
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
            url: portfoy_takipx_admin.rest_url + 'assets/' + assetId,
            method: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                closeEditModal();
                showNotice('Varlık başarıyla güncellendi!', 'success');
                loadAssets();
                loadPortfolioData();
            },
            error: function(xhr, status, error) {
                showNotice('Varlık güncellenirken hata oluştu: ' + error, 'error');
            },
            complete: function() {
                spinner.removeClass('is-active');
            }
        });
    }

    // Delete asset
    function deleteAsset(assetId) {
        $.ajax({
            url: portfoy_takipx_admin.rest_url + 'assets/' + assetId,
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                showNotice('Varlık başarıyla silindi!', 'success');
                loadAssets();
                loadPortfolioData();
            },
            error: function(xhr, status, error) {
                showNotice('Varlık silinirken hata oluştu: ' + error, 'error');
            }
        });
    }

    // Load analytics
    function loadAnalytics() {
        // This would load chart data and render charts
        // For now, just a placeholder
        console.log('Loading analytics...');
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

    function getAssetTypeLabel(type) {
        var labels = {
            'stock': 'Hisse',
            'crypto': 'Kripto',
            'forex': 'Döviz',
            'commodity': 'Emtia',
            'bond': 'Tahvil'
        };
        return labels[type] || type;
    }

    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice portfoy-notice ' + noticeClass + '"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 5000);
    }

})(jQuery);