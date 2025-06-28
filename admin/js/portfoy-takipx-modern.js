/**
 * Modern Enhanced JavaScript for Portföy TakipX
 * Features: Auto-fetch asset data, modern UI interactions, real-time updates
 */

(function($) {
    'use strict';

    // Global state management
    const AppState = {
        assets: [],
        portfolio: {},
        settings: {},
        currentModal: null,
        refreshInterval: null
    };

    // Asset data sources for auto-fetch
    const AssetDataSources = {
        // Stock symbols with their exchanges
        stocks: {
            'AAPL': { name: 'Apple Inc.', exchange: 'NASDAQ' },
            'GOOGL': { name: 'Alphabet Inc.', exchange: 'NASDAQ' },
            'MSFT': { name: 'Microsoft Corporation', exchange: 'NASDAQ' },
            'TSLA': { name: 'Tesla Inc.', exchange: 'NASDAQ' },
            'AMZN': { name: 'Amazon.com Inc.', exchange: 'NASDAQ' },
            // Turkish stocks
            'AKBNK': { name: 'Akbank T.A.Ş.', exchange: 'BIST' },
            'THYAO': { name: 'Türk Hava Yolları A.O.', exchange: 'BIST' },
            'BIMAS': { name: 'BİM Birleşik Mağazalar A.Ş.', exchange: 'BIST' },
            'TCELL': { name: 'Turkcell İletişim Hizmetleri A.Ş.', exchange: 'BIST' },
            'ASELS': { name: 'Aselsan Elektronik Sanayi ve Ticaret A.Ş.', exchange: 'BIST' }
        },
        
        // Cryptocurrency symbols
        crypto: {
            'BTC': { name: 'Bitcoin', symbol: 'BTC' },
            'ETH': { name: 'Ethereum', symbol: 'ETH' },
            'BNB': { name: 'Binance Coin', symbol: 'BNB' },
            'ADA': { name: 'Cardano', symbol: 'ADA' },
            'SOL': { name: 'Solana', symbol: 'SOL' },
            'DOT': { name: 'Polkadot', symbol: 'DOT' },
            'AVAX': { name: 'Avalanche', symbol: 'AVAX' },
            'MATIC': { name: 'Polygon', symbol: 'MATIC' }
        },
        
        // Forex pairs
        forex: {
            'USDTRY': { name: 'US Dollar / Turkish Lira', base: 'USD', quote: 'TRY' },
            'EURTRY': { name: 'Euro / Turkish Lira', base: 'EUR', quote: 'TRY' },
            'GBPTRY': { name: 'British Pound / Turkish Lira', base: 'GBP', quote: 'TRY' },
            'EURUSD': { name: 'Euro / US Dollar', base: 'EUR', quote: 'USD' },
            'GBPUSD': { name: 'British Pound / US Dollar', base: 'GBP', quote: 'USD' }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        initializeApp();
        bindEvents();
        loadPortfolioData();
        startAutoRefresh();
        showWelcomeAnimation();
    });

    /**
     * Initialize the application
     */
    function initializeApp() {
        console.log('🚀 Portföy TakipX Modern UI Initialized');
        
        // Apply modern UI class
        $('body').addClass('portfoy-takipx-modern');
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize auto-complete for asset symbols
        initAutoComplete();
        
        // Initialize drag and drop for portfolio reorganization
        initDragAndDrop();
        
        // Initialize keyboard shortcuts
        initKeyboardShortcuts();
    }

    /**
     * Bind all event handlers
     */
    function bindEvents() {
        // Asset form events
        $('#add-asset-form').on('submit', handleAssetSubmit);
        $('#edit-asset-form').on('submit', handleAssetUpdate);
        
        // Modal events
        $(document).on('click', '[data-modal-target]', openModal);
        $(document).on('click', '[data-modal-close]', closeModal);
        $(document).on('click', '.modal-overlay', closeModalOnOverlay);
        
        // Asset management events
        $(document).on('click', '.edit-asset', handleEditAsset);
        $(document).on('click', '.delete-asset', handleDeleteAsset);
        $(document).on('click', '.refresh-asset-price', handleRefreshPrice);
        
        // Bulk operations
        $('#select-all-assets').on('change', handleSelectAllAssets);
        $(document).on('change', '.asset-checkbox', handleAssetSelection);
        $('#bulk-update-prices').on('click', handleBulkUpdatePrices);
        $('#bulk-delete-assets').on('click', handleBulkDeleteAssets);
        
        // Auto-fetch events
        $('input[name="symbol"]').on('input', debounce(handleSymbolInput, 300));
        $('select[name="asset_type"]').on('change', handleAssetTypeChange);
        
        // Real-time search
        $('#asset-search').on('input', debounce(handleAssetSearch, 300));
        
        // Filter events
        $('.filter-btn').on('click', handleFilterClick);
        $('#refresh-all').on('click', handleRefreshAll);
        
        // Settings events
        $('#auto-refresh-toggle').on('change', handleAutoRefreshToggle);
        $('#refresh-interval').on('change', handleRefreshIntervalChange);
    }

    /**
     * Auto-fetch asset information when symbol is entered
     */
    function handleSymbolInput(event) {
        const symbol = $(event.target).val().toUpperCase();
        const assetType = $('select[name="asset_type"]').val();
        
        if (symbol.length < 2) return;
        
        showLoading('Varlık bilgileri getiriliyor...');
        
        // Check if we have the symbol in our data sources
        const assetInfo = getAssetInfo(symbol, assetType);
        
        if (assetInfo) {
            // Auto-fill the form with fetched data
            autoFillAssetForm(assetInfo);
            
            // Fetch current price
            fetchCurrentPrice(symbol, assetType);
        } else {
            // Try to fetch from external API
            fetchAssetInfoFromAPI(symbol, assetType);
        }
        
        hideLoading();
    }

    /**
     * Get asset information from our data sources
     */
    function getAssetInfo(symbol, assetType) {
        switch (assetType) {
            case 'stock':
                return AssetDataSources.stocks[symbol] || null;
            case 'crypto':
                return AssetDataSources.crypto[symbol] || null;
            case 'forex':
                return AssetDataSources.forex[symbol] || null;
            default:
                return null;
        }
    }

    /**
     * Auto-fill asset form with fetched data
     */
    function autoFillAssetForm(assetInfo) {
        if (assetInfo.name) {
            $('input[name="name"]').val(assetInfo.name).addClass('auto-filled');
        }
        
        // Add visual feedback
        showNotification('✅ Varlık bilgileri otomatik olarak dolduruldu!', 'success');
        
        // Animate the filled fields
        $('.auto-filled').each(function() {
            $(this).addClass('animate-fade-in');
        });
    }

    /**
     * Fetch current price for an asset
     */
    function fetchCurrentPrice(symbol, assetType) {
        const loadingSpinner = showInlineLoading($('#current-price-group'));
        
        $.ajax({
            url: `${portfoy_takipx_admin.rest_url}assets/price`,
            method: 'GET',
            data: { symbol, asset_type: assetType },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                if (response.price) {
                    $('input[name="current_price"]').val(response.price).addClass('auto-filled');
                    showNotification(`💰 Güncel fiyat: ${formatCurrency(response.price)}`, 'info');
                }
            },
            error: function(xhr, status, error) {
                console.warn('Price fetch failed:', error);
                showNotification('⚠️ Güncel fiyat alınamadı, manuel giriş yapın', 'warning');
            },
            complete: function() {
                hideInlineLoading(loadingSpinner);
            }
        });
    }

    /**
     * Fetch asset info from external API
     */
    function fetchAssetInfoFromAPI(symbol, assetType) {
        // This would integrate with real APIs like Alpha Vantage, Yahoo Finance, etc.
        console.log(`Fetching ${symbol} (${assetType}) from external API...`);
        
        // Simulate API call
        setTimeout(() => {
            showNotification('ℹ️ Bu sembol için otomatik bilgi bulunamadı, manuel giriş yapın', 'info');
        }, 1000);
    }

    /**
     * Handle asset form submission with enhanced validation
     */
    function handleAssetSubmit(event) {
        event.preventDefault();
        
        const form = $(event.target);
        const formData = new FormData(form[0]);
        const data = Object.fromEntries(formData.entries());
        
        // Enhanced validation
        const validation = validateAssetData(data);
        if (!validation.isValid) {
            showNotification(`❌ ${validation.message}`, 'error');
            highlightInvalidFields(validation.fields);
            return;
        }
        
        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.html('<span class="spinner"></span> Ekleniyor...').prop('disabled', true);
        
        $.ajax({
            url: `${portfoy_takipx_admin.rest_url}assets`,
            method: 'POST',
            data: data,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                showNotification('✅ Varlık başarıyla eklendi!', 'success');
                closeModal();
                refreshAssetsTable();
                loadPortfolioData();
                form[0].reset();
                $('.auto-filled').removeClass('auto-filled');
            },
            error: function(xhr, status, error) {
                const errorMsg = xhr.responseJSON?.message || 'Varlık eklenirken hata oluştu';
                showNotification(`❌ ${errorMsg}`, 'error');
            },
            complete: function() {
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Enhanced asset data validation
     */
    function validateAssetData(data) {
        const errors = [];
        const invalidFields = [];
        
        if (!data.symbol || data.symbol.length < 1) {
            errors.push('Sembol gerekli');
            invalidFields.push('symbol');
        }
        
        if (!data.name || data.name.length < 2) {
            errors.push('Varlık adı en az 2 karakter olmalı');
            invalidFields.push('name');
        }
        
        if (!data.quantity || parseFloat(data.quantity) <= 0) {
            errors.push('Miktar pozitif bir sayı olmalı');
            invalidFields.push('quantity');
        }
        
        if (!data.purchase_price || parseFloat(data.purchase_price) <= 0) {
            errors.push('Alış fiyatı pozitif bir sayı olmalı');
            invalidFields.push('purchase_price');
        }
        
        if (data.commission && parseFloat(data.commission) < 0) {
            errors.push('Komisyon negatif olamaz');
            invalidFields.push('commission');
        }
        
        return {
            isValid: errors.length === 0,
            message: errors.join(', '),
            fields: invalidFields
        };
    }

    /**
     * Highlight invalid form fields
     */
    function highlightInvalidFields(fields) {
        // Remove previous highlights
        $('.form-input, .form-select').removeClass('invalid');
        
        // Add highlights to invalid fields
        fields.forEach(field => {
            $(`[name="${field}"]`).addClass('invalid');
        });
        
        // Add CSS for invalid state
        if (!$('#validation-styles').length) {
            $('<style id="validation-styles">.form-input.invalid, .form-select.invalid { border-color: var(--error-500); box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); }</style>').appendTo('head');
        }
    }

    /**
     * Load and update portfolio data with animations
     */
    function loadPortfolioData() {
        showLoading('Portföy verileri yükleniyor...');
        
        $.ajax({
            url: `${portfoy_takipx_admin.rest_url}portfolio/summary`,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', portfoy_takipx_admin.rest_nonce);
            },
            success: function(response) {
                AppState.portfolio = response;
                updateSummaryCards(response);
                updatePortfolioCharts(response);
            },
            error: function(xhr, status, error) {
                showNotification('❌ Portföy verileri yüklenirken hata oluştu', 'error');
                console.error('Portfolio data load error:', error);
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    /**
     * Update summary cards with smooth animations
     */
    function updateSummaryCards(data) {
        const cards = [
            {
                id: 'total-value',
                value: data.current_value,
                change: data.total_change,
                format: 'currency'
            },
            {
                id: 'total-investment',
                value: data.total_invested,
                format: 'currency'
            },
            {
                id: 'profit-loss',
                value: data.profit_loss,
                change: data.profit_loss_percentage,
                format: 'currency'
            },
            {
                id: 'assets-count',
                value: data.total_assets,
                format: 'number'
            }
        ];
        
        cards.forEach(card => {
            updateCard(card);
        });
    }

    /**
     * Update individual card with animation
     */
    function updateCard(cardData) {
        const valueElement = $(`#${cardData.id}-value`);
        const changeElement = $(`#${cardData.id}-change`);
        
        // Animate value change
        if (valueElement.length) {
            const currentValue = parseFloat(valueElement.data('value') || 0);
            const newValue = parseFloat(cardData.value || 0);
            
            if (currentValue !== newValue) {
                animateNumber(valueElement, currentValue, newValue, cardData.format);
                valueElement.data('value', newValue);
            }
        }
        
        // Update change indicator
        if (changeElement.length && cardData.change !== undefined) {
            const changeValue = parseFloat(cardData.change);
            const changeClass = changeValue >= 0 ? 'change-positive' : 'change-negative';
            const changeIcon = changeValue >= 0 ? '↗️' : '↘️';
            
            changeElement
                .removeClass('change-positive change-negative change-neutral')
                .addClass(changeClass)
                .html(`${changeIcon} ${Math.abs(changeValue).toFixed(2)}%`);
        }
    }

    /**
     * Animate number changes
     */
    function animateNumber(element, from, to, format) {
        $({ value: from }).animate({ value: to }, {
            duration: 1000,
            easing: 'easeOutCubic',
            step: function() {
                const value = format === 'currency' ? formatCurrency(this.value) : Math.round(this.value);
                element.text(value);
            },
            complete: function() {
                const value = format === 'currency' ? formatCurrency(to) : Math.round(to);
                element.text(value);
            }
        });
    }

    /**
     * Enhanced modal system
     */
    function openModal(event) {
        event.preventDefault();
        const modalId = $(event.currentTarget).data('modal-target');
        const modal = $(`#${modalId}`);
        
        if (modal.length) {
            AppState.currentModal = modalId;
            modal.addClass('active');
            $('body').addClass('modal-open');
            
            // Focus first input
            setTimeout(() => {
                modal.find('input, select, textarea').first().focus();
            }, 300);
        }
    }

    /**
     * Close modal
     */
    function closeModal() {
        const modal = $('.modal-overlay.active');
        modal.removeClass('active');
        $('body').removeClass('modal-open');
        AppState.currentModal = null;
        
        // Clear form data
        modal.find('form')[0]?.reset();
        $('.auto-filled').removeClass('auto-filled');
        $('.form-input, .form-select').removeClass('invalid');
    }

    /**
     * Close modal when clicking overlay
     */
    function closeModalOnOverlay(event) {
        if (event.target === event.currentTarget) {
            closeModal();
        }
    }

    /**
     * Auto-refresh functionality
     */
    function startAutoRefresh() {
        const interval = parseInt(localStorage.getItem('portfoy_refresh_interval') || '300000'); // 5 minutes default
        
        if (AppState.refreshInterval) {
            clearInterval(AppState.refreshInterval);
        }
        
        AppState.refreshInterval = setInterval(() => {
            refreshPortfolioData();
        }, interval);
    }

    /**
     * Refresh portfolio data
     */
    function refreshPortfolioData() {
        console.log('🔄 Auto-refreshing portfolio data...');
        loadPortfolioData();
        refreshAssetsTable();
    }

    /**
     * Refresh assets table
     */
    function refreshAssetsTable() {
        const table = $('#assets-table');
        if (table.length) {
            table.addClass('loading');
            
            // Reload table data
            setTimeout(() => {
                table.removeClass('loading');
                showNotification('✅ Varlık listesi güncellendi', 'success');
            }, 1000);
        }
    }

    /**
     * Enhanced notification system
     */
    function showNotification(message, type = 'info', duration = 4000) {
        const notification = $(`
            <div class="notification notification-${type} animate-fade-in">
                <div class="notification-content">
                    <span class="notification-message">${message}</span>
                    <button class="notification-close" onclick="$(this).parent().parent().remove()">×</button>
                </div>
            </div>
        `);
        
        // Add notification styles if not exists
        if (!$('#notification-styles').length) {
            $(`<style id="notification-styles">
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border-radius: var(--radius-lg);
                    box-shadow: var(--shadow-xl);
                    border-left: 4px solid var(--primary-500);
                    z-index: 9999;
                    max-width: 400px;
                    margin-bottom: 10px;
                }
                .notification-content {
                    padding: var(--spacing-md) var(--spacing-lg);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .notification-success { border-left-color: var(--success-500); }
                .notification-warning { border-left-color: var(--warning-500); }
                .notification-error { border-left-color: var(--error-500); }
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    margin-left: 10px;
                    opacity: 0.5;
                }
                .notification-close:hover { opacity: 1; }
            </style>`).appendTo('head');
        }
        
        $('body').append(notification);
        
        // Auto-remove after duration
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, duration);
    }

    /**
     * Loading states
     */
    function showLoading(message = 'Yükleniyor...') {
        const loader = $(`
            <div id="global-loader" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="spinner"></div>
                        <span>${message}</span>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(loader);
    }

    function hideLoading() {
        $('#global-loader').remove();
    }

    function showInlineLoading(element) {
        const spinner = $('<div class="inline-spinner"><div class="spinner"></div></div>');
        element.append(spinner);
        return spinner;
    }

    function hideInlineLoading(spinner) {
        spinner.remove();
    }

    /**
     * Utility functions
     */
    function formatCurrency(amount, currency = '₺') {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: currency === '₺' ? 'TRY' : 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    /**
     * Keyboard shortcuts
     */
    function initKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // Ctrl+N: New Asset
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                openModal({ currentTarget: $('[data-modal-target="add-asset-modal"]')[0] });
            }
            
            // Escape: Close Modal
            if (e.key === 'Escape' && AppState.currentModal) {
                closeModal();
            }
            
            // Ctrl+R: Refresh
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshPortfolioData();
            }
        });
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            const tooltip = $(this);
            const text = tooltip.data('tooltip');
            
            tooltip.on('mouseenter', function() {
                showTooltip(tooltip, text);
            }).on('mouseleave', function() {
                hideTooltip();
            });
        });
    }

    function showTooltip(element, text) {
        const tooltip = $(`<div class="tooltip">${text}</div>`);
        $('body').append(tooltip);
        
        const elementRect = element[0].getBoundingClientRect();
        tooltip.css({
            top: elementRect.top - tooltip.outerHeight() - 5,
            left: elementRect.left + (elementRect.width / 2) - (tooltip.outerWidth() / 2)
        });
    }

    function hideTooltip() {
        $('.tooltip').remove();
    }

    /**
     * Initialize auto-complete
     */
    function initAutoComplete() {
        const symbolInput = $('input[name="symbol"]');
        
        symbolInput.on('input', function() {
            const value = $(this).val().toUpperCase();
            const assetType = $('select[name="asset_type"]').val();
            
            if (value.length >= 1) {
                showSuggestions(this, value, assetType);
            } else {
                hideSuggestions();
            }
        });
    }

    function showSuggestions(input, value, assetType) {
        const suggestions = getSuggestions(value, assetType);
        
        if (suggestions.length > 0) {
            const suggestionList = $('<div class="suggestion-list"></div>');
            
            suggestions.forEach(suggestion => {
                const item = $(`<div class="suggestion-item" data-symbol="${suggestion.symbol}">${suggestion.symbol} - ${suggestion.name}</div>`);
                item.on('click', function() {
                    $(input).val(suggestion.symbol);
                    $('input[name="name"]').val(suggestion.name);
                    hideSuggestions();
                    fetchCurrentPrice(suggestion.symbol, assetType);
                });
                suggestionList.append(item);
            });
            
            hideSuggestions();
            $(input).after(suggestionList);
        }
    }

    function getSuggestions(value, assetType) {
        const dataSource = AssetDataSources[assetType] || {};
        const suggestions = [];
        
        Object.keys(dataSource).forEach(symbol => {
            if (symbol.includes(value)) {
                suggestions.push({
                    symbol: symbol,
                    name: dataSource[symbol].name
                });
            }
        });
        
        return suggestions.slice(0, 5); // Limit to 5 suggestions
    }

    function hideSuggestions() {
        $('.suggestion-list').remove();
    }

    /**
     * Initialize drag and drop
     */
    function initDragAndDrop() {
        // This would enable drag and drop reordering of portfolio items
        // Implementation would depend on the specific table/list structure
        console.log('Drag and drop initialized');
    }

    /**
     * Welcome animation
     */
    function showWelcomeAnimation() {
        $('.summary-card').each(function(index) {
            $(this).css('animation-delay', `${index * 100}ms`).addClass('animate-fade-in');
        });
    }

    // Expose functions to global scope for backwards compatibility
    window.PortfoyTakipX = {
        loadPortfolioData,
        refreshPortfolioData,
        showNotification,
        formatCurrency,
        openModal,
        closeModal
    };

})(jQuery);