/**
 * Modern Portfolio Dashboard JavaScript
 * Advanced interactions and features for the next-generation dashboard
 */

class PortfolioDashboard {
    constructor() {
        this.charts = {};
        this.isInitialized = false;
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        this.bindEvents();
        this.initializeCharts();
        this.loadDashboardData();
        this.startRealTimeUpdates();
        this.initializeInteractions();
        
        this.isInitialized = true;
    }

    bindEvents() {
        // Add asset modal
        const addAssetTrigger = document.getElementById('add-asset-trigger');
        const addFirstAsset = document.getElementById('add-first-asset');
        const addAssetModal = document.getElementById('add-asset-modal');
        const closeAssetModal = document.getElementById('close-asset-modal');
        const cancelAddAsset = document.getElementById('cancel-add-asset');

        if (addAssetTrigger) {
            addAssetTrigger.addEventListener('click', () => this.openAddAssetModal());
        }
        
        if (addFirstAsset) {
            addFirstAsset.addEventListener('click', () => this.openAddAssetModal());
        }

        if (closeAssetModal) {
            closeAssetModal.addEventListener('click', () => this.closeAddAssetModal());
        }

        if (cancelAddAsset) {
            cancelAddAsset.addEventListener('click', () => this.closeAddAssetModal());
        }

        if (addAssetModal) {
            addAssetModal.addEventListener('click', (e) => {
                if (e.target === addAssetModal) {
                    this.closeAddAssetModal();
                }
            });
        }

        // User menu dropdown
        const userMenuTrigger = document.getElementById('user-menu-trigger');
        const userDropdown = document.getElementById('user-dropdown');
        
        if (userMenuTrigger && userDropdown) {
            userMenuTrigger.addEventListener('click', () => {
                userDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuTrigger.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        }

        // Logout functionality
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.handleLogout());
        }

        // Add asset form submission
        const addAssetForm = document.getElementById('add-asset-form');
        if (addAssetForm) {
            addAssetForm.addEventListener('submit', (e) => this.handleAddAsset(e));
        }

        // Asset type change for symbol suggestions
        const assetTypeSelect = document.getElementById('asset-type');
        const assetSymbolInput = document.getElementById('asset-symbol');
        
        if (assetTypeSelect && assetSymbolInput) {
            assetTypeSelect.addEventListener('change', () => {
                this.updateSymbolSuggestions();
            });
            
            assetSymbolInput.addEventListener('input', () => {
                this.showSymbolSuggestions();
            });
        }

        // Real-time price fetching
        const symbolInput = document.getElementById('asset-symbol');
        const priceInput = document.getElementById('asset-price');
        
        if (symbolInput && priceInput) {
            symbolInput.addEventListener('blur', () => {
                this.fetchAssetPrice(symbolInput.value, priceInput);
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));
    }

    openAddAssetModal() {
        const modal = document.getElementById('add-asset-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.style.opacity = '0';
            modal.offsetHeight; // Force reflow
            modal.style.opacity = '1';
            
            // Focus first input
            const firstInput = modal.querySelector('.form-input');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    closeAddAssetModal() {
        const modal = document.getElementById('add-asset-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                this.resetAddAssetForm();
            }, 300);
        }
    }

    resetAddAssetForm() {
        const form = document.getElementById('add-asset-form');
        if (form) {
            form.reset();
            form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        }
    }

    async handleAddAsset(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('.btn-primary');
        
        // Validation
        if (!this.validateAddAssetForm(form)) {
            return;
        }
        
        this.setButtonLoading(submitBtn, true);
        
        try {
            const response = await this.makeRequest('portfoy_add_asset', {
                asset_type: formData.get('asset_type'),
                symbol: formData.get('symbol'),
                name: formData.get('name'),
                quantity: formData.get('quantity'),
                purchase_price: formData.get('purchase_price'),
                purchase_date: formData.get('purchase_date'),
                notes: formData.get('notes'),
                nonce: portfoy_takipx_public.nonce
            });

            if (response.success) {
                this.showNotification('Varlık başarıyla eklendi!', 'success');
                this.closeAddAssetModal();
                this.refreshDashboardData();
            } else {
                throw new Error(response.data.message);
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    }

    validateAddAssetForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Bu alan zorunludur');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        // Additional validations
        const quantity = form.querySelector('#asset-quantity');
        const price = form.querySelector('#asset-price');
        
        if (quantity && parseFloat(quantity.value) <= 0) {
            this.showFieldError(quantity, 'Miktar 0\'dan büyük olmalıdır');
            isValid = false;
        }
        
        if (price && parseFloat(price.value) <= 0) {
            this.showFieldError(price, 'Fiyat 0\'dan büyük olmalıdır');
            isValid = false;
        }

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('error');
        let errorEl = field.parentNode.querySelector('.field-error');
        
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'field-error';
            field.parentNode.appendChild(errorEl);
        }
        
        errorEl.textContent = message;
        errorEl.style.color = 'var(--error-600)';
        errorEl.style.fontSize = 'var(--font-size-sm)';
        errorEl.style.marginTop = 'var(--space-1)';
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorEl = field.parentNode.querySelector('.field-error');
        if (errorEl) {
            errorEl.remove();
        }
    }

    async handleLogout() {
        try {
            const response = await this.makeRequest('portfoy_logout_user', {});
            
            if (response.success) {
                window.location.href = response.data.redirect_url;
            }
        } catch (error) {
            console.error('Logout error:', error);
        }
    }

    initializeCharts() {
        // Portfolio allocation chart
        this.initializeAllocationChart();
        
        // Performance mini chart
        this.initializePerformanceChart();
        
        // Update time display
        this.updateMarketTime();
    }

    initializeAllocationChart() {
        const canvas = document.getElementById('portfolio-allocation-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // Sample data - would be replaced with real data
        const data = {
            labels: ['Hisse Senedi', 'Kripto Para', 'Döviz', 'Emtia'],
            datasets: [{
                data: [40, 30, 20, 10],
                backgroundColor: [
                    'var(--chart-color-stock)',
                    'var(--chart-color-crypto)',
                    'var(--chart-color-forex)',
                    'var(--chart-color-commodity)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };

        this.charts.allocation = this.createDoughnutChart(ctx, data);
    }

    initializePerformanceChart() {
        const canvas = document.getElementById('performance-mini-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // Sample performance data
        const data = {
            labels: ['', '', '', '', '', '', ''],
            datasets: [{
                data: [100, 105, 98, 110, 108, 115, 112],
                borderColor: 'var(--success-500)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        };

        this.charts.performance = this.createLineChart(ctx, data);
    }

    createDoughnutChart(ctx, data) {
        return new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    createLineChart(ctx, data) {
        return new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }

    async loadDashboardData() {
        try {
            // Load portfolio summary
            await this.loadPortfolioSummary();
            
            // Load recent assets
            await this.loadRecentAssets();
            
            // Update charts with real data
            await this.updateChartsData();
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    async loadPortfolioSummary() {
        try {
            const response = await this.makeRequest('portfoy_get_portfolio_summary', {
                nonce: portfoy_takipx_public.nonce
            });

            if (response.success) {
                this.updatePortfolioCards(response.data);
            }
        } catch (error) {
            console.error('Error loading portfolio summary:', error);
        }
    }

    updatePortfolioCards(data) {
        // Update total portfolio value
        const totalValueEl = document.getElementById('total-portfolio-value');
        if (totalValueEl) {
            this.animateValue(totalValueEl, 0, data.current_value || 0, 1000);
        }

        // Update profit/loss indicators
        const changeIndicators = document.querySelectorAll('.change-indicator');
        changeIndicators.forEach(indicator => {
            const isPositive = (data.profit_loss || 0) >= 0;
            indicator.classList.toggle('positive', isPositive);
            indicator.classList.toggle('negative', !isPositive);
        });
    }

    animateValue(element, start, end, duration) {
        if (!element) return;
        
        const startTime = performance.now();
        const difference = end - start;

        const step = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = start + (difference * this.easeOutCubic(progress));
            element.textContent = this.formatCurrency(currentValue);
            
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    async fetchAssetPrice(symbol, priceInput) {
        if (!symbol || !priceInput) return;

        try {
            // This would be replaced with actual price API
            const mockPrice = Math.random() * 1000 + 10;
            priceInput.value = mockPrice.toFixed(2);
            
            // Add visual feedback
            priceInput.style.background = 'var(--success-50)';
            setTimeout(() => {
                priceInput.style.background = '';
            }, 1000);
            
        } catch (error) {
            console.error('Error fetching price:', error);
        }
    }

    updateSymbolSuggestions() {
        const assetType = document.getElementById('asset-type').value;
        const suggestions = this.getSymbolSuggestionsForType(assetType);
        
        // Update placeholder
        const symbolInput = document.getElementById('asset-symbol');
        if (symbolInput) {
            symbolInput.placeholder = suggestions.slice(0, 3).join(', ') + '...';
        }
    }

    getSymbolSuggestionsForType(type) {
        const suggestions = {
            stock: ['AAPL', 'GOOGL', 'MSFT', 'TSLA', 'AMZN', 'BIST:THYAO', 'BIST:AKBNK'],
            crypto: ['BTC', 'ETH', 'ADA', 'SOL', 'AVAX', 'DOT', 'MATIC'],
            forex: ['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD', 'USD/TRY'],
            commodity: ['GOLD', 'SILVER', 'OIL', 'WHEAT', 'CORN'],
            bond: ['US10Y', 'DE10Y', 'TR10Y', 'UK10Y']
        };
        
        return suggestions[type] || [];
    }

    showSymbolSuggestions() {
        const symbolInput = document.getElementById('asset-symbol');
        const suggestionsDiv = document.getElementById('symbol-suggestions');
        
        if (!symbolInput || !suggestionsDiv) return;
        
        const query = symbolInput.value.toLowerCase();
        const assetType = document.getElementById('asset-type').value;
        
        if (query.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        const suggestions = this.getSymbolSuggestionsForType(assetType)
            .filter(symbol => symbol.toLowerCase().includes(query))
            .slice(0, 5);
        
        if (suggestions.length > 0) {
            suggestionsDiv.innerHTML = suggestions
                .map(symbol => `<div class="suggestion-item" data-symbol="${symbol}">${symbol}</div>`)
                .join('');
            suggestionsDiv.style.display = 'block';
            
            // Add click handlers
            suggestionsDiv.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    symbolInput.value = item.dataset.symbol;
                    suggestionsDiv.style.display = 'none';
                    
                    // Auto-fill name if possible
                    this.autoFillAssetName(item.dataset.symbol);
                    
                    // Fetch price
                    const priceInput = document.getElementById('asset-price');
                    this.fetchAssetPrice(item.dataset.symbol, priceInput);
                });
            });
        } else {
            suggestionsDiv.style.display = 'none';
        }
    }

    autoFillAssetName(symbol) {
        const nameInput = document.getElementById('asset-name');
        if (!nameInput || nameInput.value) return;
        
        // Mock asset names - would be replaced with real data
        const assetNames = {
            'AAPL': 'Apple Inc.',
            'GOOGL': 'Alphabet Inc.',
            'MSFT': 'Microsoft Corporation',
            'TSLA': 'Tesla Inc.',
            'BTC': 'Bitcoin',
            'ETH': 'Ethereum',
            'EUR/USD': 'Euro / US Dollar',
            'GOLD': 'Gold Spot'
        };
        
        if (assetNames[symbol]) {
            nameInput.value = assetNames[symbol];
        }
    }

    startRealTimeUpdates() {
        // Update market time
        setInterval(() => this.updateMarketTime(), 1000);
        
        // Update portfolio data every 30 seconds
        setInterval(() => this.refreshDashboardData(), 30000);
        
        // Update prices every 60 seconds
        setInterval(() => this.updateAssetPrices(), 60000);
    }

    updateMarketTime() {
        const marketTimeEl = document.getElementById('market-time');
        if (marketTimeEl) {
            const now = new Date();
            marketTimeEl.textContent = now.toLocaleTimeString('tr-TR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }

    async refreshDashboardData() {
        await this.loadPortfolioSummary();
        await this.loadRecentAssets();
    }

    async updateAssetPrices() {
        // This would update asset prices in real-time
        console.log('Updating asset prices...');
    }

    initializeInteractions() {
        // Asset item interactions
        document.querySelectorAll('.asset-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (!e.target.closest('.asset-actions-btn')) {
                    this.showAssetDetails(item.dataset.assetId);
                }
            });
        });

        // Card hover effects
        this.initializeCardAnimations();
        
        // Initialize tooltips
        this.initializeTooltips();
    }

    initializeCardAnimations() {
        const cards = document.querySelectorAll('.overview-card, .dashboard-panel, .news-item');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    }

    initializeTooltips() {
        // Add tooltips to buttons and interactive elements
        document.querySelectorAll('[title]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.getAttribute('title'));
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: var(--gray-900);
            color: white;
            padding: var(--space-2) var(--space-3);
            border-radius: var(--rounded-md);
            font-size: var(--font-size-sm);
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        setTimeout(() => tooltip.style.opacity = '1', 10);
        
        this.currentTooltip = tooltip;
    }

    hideTooltip() {
        if (this.currentTooltip) {
            this.currentTooltip.style.opacity = '0';
            setTimeout(() => {
                if (this.currentTooltip) {
                    this.currentTooltip.remove();
                    this.currentTooltip = null;
                }
            }, 200);
        }
    }

    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + N: New asset
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            this.openAddAssetModal();
        }
        
        // Escape: Close modals
        if (e.key === 'Escape') {
            this.closeAddAssetModal();
            
            // Close user dropdown
            const userDropdown = document.getElementById('user-dropdown');
            if (userDropdown) {
                userDropdown.classList.remove('active');
            }
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    ${this.getNotificationIcon(type)}
                </div>
                <div class="notification-message">${message}</div>
                <button class="notification-close">×</button>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--${type === 'success' ? 'success' : type === 'error' ? 'error' : 'primary'}-500);
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            max-width: 400px;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            this.removeNotification(notification);
        }, 5000);
        
        // Close button
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.removeNotification(notification);
        });
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    removeNotification(notification) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    setButtonLoading(button, loading) {
        if (!button) return;
        
        if (loading) {
            button.disabled = true;
            button.style.pointerEvents = 'none';
            
            const originalText = button.innerHTML;
            button.dataset.originalText = originalText;
            
            button.innerHTML = `
                <div class="loading-spinner" style="
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255,255,255,0.3);
                    border-top: 2px solid white;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-right: 8px;
                "></div>
                Yükleniyor...
            `;
        } else {
            button.disabled = false;
            button.style.pointerEvents = '';
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }
    }

    async makeRequest(action, data) {
        const response = await fetch(portfoy_takipx_public.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                ...data
            })
        });
        
        if (!response.ok) {
            throw new Error('Network error');
        }
        
        return await response.json();
    }
}

// Initialize dashboard when DOM is ready
function initializePortfolioDashboard() {
    if (document.querySelector('.portfoy-dashboard-container')) {
        new PortfolioDashboard();
    }
}

// Auto-initialize if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePortfolioDashboard);
} else {
    initializePortfolioDashboard();
}

// Export for external use
window.PortfolioDashboard = PortfolioDashboard;