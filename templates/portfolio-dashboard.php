<?php
/**
 * Next-Generation Portfolio Dashboard Template
 * Modern, responsive, and interactive dashboard design
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$user_id = $atts['user_id'];
$show_add_form = $atts['show_add_form'] === 'true';
$public = new Portfoy_TakipX_Public( 'portfoy-takipx', PORTFOY_TAKIPX_VERSION );

// Get user data
$user = get_userdata( $user_id );
$portfolio_summary = $public->get_portfolio_summary( $user_id );
$user_assets = $public->get_user_assets( $user_id, '', 10 );
?>

<div class="portfoy-dashboard-container">
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="user-welcome">
                <div class="user-avatar">
                    <?php echo get_avatar( $user_id, 48, '', '', array( 'class' => 'avatar-img' ) ); ?>
                    <div class="online-indicator"></div>
                </div>
                <div class="welcome-text">
                    <h1 class="welcome-title">Hoş geldin, <?php echo esc_html( $user->first_name ); ?>!</h1>
                    <p class="welcome-subtitle">Portföyünü kontrol et ve yatırımlarını takip et</p>
                </div>
            </div>
            
            <div class="header-actions">
                <button class="action-btn add-asset-btn" id="add-asset-trigger">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5V19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Varlık Ekle</span>
                </button>
                
                <button class="action-btn notification-btn" id="notification-trigger">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6981 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="notification-badge">3</span>
                </button>
                
                <div class="user-menu-trigger" id="user-menu-trigger">
                    <button class="action-btn user-menu-btn">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 1C18.0751 1 23 5.92487 23 12C23 18.0751 18.0751 23 12 23C5.92487 23 1 18.0751 1 12C1 5.92487 5.92487 1 12 1Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M20.2677 18.5C19.8473 17.8915 19.2964 17.3962 18.6564 17.0535C18.0165 16.7108 17.3063 16.5307 16.5845 16.5307C15.8627 16.5307 15.1525 16.7108 14.5126 17.0535C13.8726 17.3962 13.3217 17.8915 12.9013 18.5" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                    
                    <div class="user-dropdown" id="user-dropdown">
                        <div class="dropdown-header">
                            <div class="user-info">
                                <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                <span><?php echo esc_html( $user->user_email ); ?></span>
                            </div>
                        </div>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item">
                                <svg class="item-icon" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Profil Ayarları
                            </a>
                            <a href="#" class="dropdown-item">
                                <svg class="item-icon" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                    <path d="M19.4 15C19.2669 15.3016 19.2272 15.6362 19.286 15.9606C19.3448 16.285 19.4995 16.5843 19.73 16.82L19.79 16.88C19.976 17.0657 20.1235 17.2863 20.2241 17.5291C20.3248 17.7719 20.3766 18.0322 20.3766 18.295C20.3766 18.5578 20.3248 18.8181 20.2241 19.0609C20.1235 19.3037 19.976 19.5243 19.79 19.71C19.6043 19.896 19.3837 20.0435 19.1409 20.1441C18.8981 20.2448 18.6378 20.2966 18.375 20.2966C18.1122 20.2966 17.8519 20.2448 17.6091 20.1441C17.3663 20.0435 17.1457 19.896 16.96 19.71L16.9 19.65C16.6643 19.4195 16.365 19.2648 16.0406 19.206C15.7162 19.1472 15.3816 19.1869 15.08 19.32C14.7842 19.4468 14.532 19.6572 14.3543 19.9255C14.1766 20.1938 14.0813 20.5082 14.08 20.83V21C14.08 21.5304 13.8693 22.0391 13.4942 22.4142C13.1191 22.7893 12.6104 23 12.08 23C11.5496 23 11.0409 22.7893 10.6658 22.4142C10.2907 22.0391 10.08 21.5304 10.08 21V20.91C10.0723 20.579 9.96512 20.2583 9.77251 19.9887C9.5799 19.7191 9.31074 19.5135 9 19.4C8.69838 19.2669 8.36381 19.2272 8.03941 19.286C7.71502 19.3448 7.41568 19.4995 7.18 19.73L7.12 19.79C6.93425 19.976 6.71368 20.1235 6.47088 20.2241C6.22808 20.3248 5.96783 20.3766 5.705 20.3766C5.44217 20.3766 5.18192 20.3248 4.93912 20.2241C4.69632 20.1235 4.47575 19.976 4.29 19.79C4.10405 19.6043 3.95653 19.3837 3.85588 19.1409C3.75523 18.8981 3.70343 18.6378 3.70343 18.375C3.70343 18.1122 3.75523 17.8519 3.85588 17.6091C3.95653 17.3663 4.10405 17.1457 4.29 16.96L4.35 16.9C4.58054 16.6643 4.73519 16.365 4.794 16.0406C4.85282 15.7162 4.81312 15.3816 4.68 15.08C4.55324 14.7842 4.34276 14.532 4.07447 14.3543C3.80618 14.1766 3.49179 14.0813 3.17 14.08H3C2.46957 14.08 1.96086 13.8693 1.58579 13.4942C1.21071 13.1191 1 12.6104 1 12.08C1 11.5496 1.21071 11.0409 1.58579 10.6658C1.96086 10.2907 2.46957 10.08 3 10.08H3.09C3.42099 10.0723 3.74171 9.96512 4.01131 9.77251C4.28091 9.5799 4.48649 9.31074 4.6 9C4.73312 8.69838 4.77282 8.36381 4.714 8.03941C4.65519 7.71502 4.50054 7.41568 4.27 7.18L4.21 7.12C4.02405 6.93425 3.87653 6.71368 3.77588 6.47088C3.67523 6.22808 3.62343 5.96783 3.62343 5.705C3.62343 5.44217 3.67523 5.18192 3.77588 4.93912C3.87653 4.69632 4.02405 4.47575 4.21 4.29C4.39575 4.10405 4.61632 3.95653 4.85912 3.85588C5.10192 3.75523 5.36217 3.70343 5.625 3.70343C5.88783 3.70343 6.14808 3.75523 6.39088 3.85588C6.63368 3.95653 6.85425 4.10405 7.04 4.29L7.1 4.35C7.33568 4.58054 7.63502 4.73519 7.95941 4.794C8.28381 4.85282 8.61838 4.81312 8.92 4.68H9C9.29577 4.55324 9.54802 4.34276 9.72569 4.07447C9.90337 3.80618 9.99872 3.49179 10 3.17V3C10 2.46957 10.2107 1.96086 10.5858 1.58579C10.9609 1.21071 11.4696 1 12 1C12.5304 1 13.0391 1.21071 13.4142 1.58579C13.7893 1.96086 14 2.46957 14 3V3.09C14.0013 3.41179 14.0966 3.72618 14.2743 3.99447C14.452 4.26276 14.7042 4.47324 15 4.6C15.3016 4.73312 15.6362 4.77282 15.9606 4.714C16.285 4.65519 16.5843 4.50054 16.82 4.27L16.88 4.21C17.0657 4.02405 17.2863 3.87653 17.5291 3.77588C17.7719 3.67523 18.0322 3.62343 18.295 3.62343C18.5578 3.62343 18.8181 3.67523 19.0609 3.77588C19.3037 3.87653 19.5243 4.02405 19.71 4.21C19.896 4.39575 20.0435 4.61632 20.1441 4.85912C20.2448 5.10192 20.2966 5.36217 20.2966 5.625C20.2966 5.88783 20.2448 6.14808 20.1441 6.39088C20.0435 6.63368 19.896 6.85425 19.71 7.04L19.65 7.1C19.4195 7.33568 19.2648 7.63502 19.206 7.95941C19.1472 8.28381 19.1869 8.61838 19.32 8.92V9C19.4468 9.29577 19.6572 9.54802 19.9255 9.72569C20.1938 9.90337 20.5082 9.99872 20.83 10H21C21.5304 10 22.0391 10.2107 22.4142 10.5858C22.7893 10.9609 23 11.4696 23 12C23 12.5304 22.7893 13.0391 22.4142 13.4142C22.0391 13.7893 21.5304 14 21 14H20.91C20.5882 14.0013 20.2738 14.0966 20.0055 14.2743C19.7372 14.452 19.5268 14.7042 19.4 15V15Z" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Hesap Ayarları
                            </a>
                            <div class="dropdown-divider"></div>
                            <button class="dropdown-item logout-btn" id="logout-btn">
                                <svg class="item-icon" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2"/>
                                    <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2"/>
                                    <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Çıkış Yap
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Overview Cards -->
    <section class="portfolio-overview">
        <div class="overview-grid">
            <div class="overview-card primary-card">
                <div class="card-header">
                    <div class="card-icon primary-bg">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title">Toplam Portföy Değeri</h3>
                        <div class="card-meta">Son güncelleme: Az önce</div>
                    </div>
                </div>
                <div class="card-content">
                    <div class="value-display">
                        <span class="currency">₺</span>
                        <span class="amount" id="total-portfolio-value"><?php echo number_format( $portfolio_summary['current_value'], 2, ',', '.' ); ?></span>
                    </div>
                    <div class="change-indicator <?php echo $portfolio_summary['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                        <svg class="change-icon" viewBox="0 0 24 24" fill="none">
                            <?php if ( $portfolio_summary['profit_loss'] >= 0 ): ?>
                                <path d="M7 14L12 9L17 14" stroke="currentColor" stroke-width="2"/>
                            <?php else: ?>
                                <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2"/>
                            <?php endif; ?>
                        </svg>
                        <span class="change-amount">₺<?php echo number_format( abs( $portfolio_summary['profit_loss'] ), 2, ',', '.' ); ?></span>
                        <span class="change-percentage">(<?php echo number_format( abs( $portfolio_summary['profit_loss_percentage'] ), 2 ); ?>%)</span>
                    </div>
                </div>
                <div class="card-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo max( 0, min( 100, ( $portfolio_summary['current_value'] / max( $portfolio_summary['total_invested'], 1 ) ) * 100 ) ); ?>%"></div>
                    </div>
                    <div class="progress-labels">
                        <span>Yatırılan: ₺<?php echo number_format( $portfolio_summary['total_invested'], 0, ',', '.' ); ?></span>
                        <span>Hedef: ₺<?php echo number_format( $portfolio_summary['total_invested'] * 1.2, 0, ',', '.' ); ?></span>
                    </div>
                </div>
            </div>

            <div class="overview-card success-card">
                <div class="card-header">
                    <div class="card-icon success-bg">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title">Kazanç/Kayıp</h3>
                        <div class="card-meta">Bugünkü performans</div>
                    </div>
                </div>
                <div class="card-content">
                    <div class="value-display <?php echo $portfolio_summary['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                        <span class="currency">₺</span>
                        <span class="amount"><?php echo number_format( abs( $portfolio_summary['profit_loss'] ), 2, ',', '.' ); ?></span>
                    </div>
                    <div class="performance-chart">
                        <canvas id="performance-mini-chart" width="100" height="40"></canvas>
                    </div>
                </div>
            </div>

            <div class="overview-card warning-card">
                <div class="card-header">
                    <div class="card-icon warning-bg">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 11H15L17 6H7L9 11Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M9 11V16C9 16.5304 9.21071 17.0391 9.58579 17.4142C9.96086 17.7893 10.4696 18 11 18H13C13.5304 18 14.0391 17.7893 14.4142 17.4142C14.7893 17.0391 15 16.5304 15 16V11" stroke="currentColor" stroke-width="2"/>
                            <path d="M7 6L4 2" stroke="currentColor" stroke-width="2"/>
                            <path d="M17 6L20 2" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 22V18" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title">Aktif Varlıklar</h3>
                        <div class="card-meta">Çeşitlendirilmiş portföy</div>
                    </div>
                </div>
                <div class="card-content">
                    <div class="value-display">
                        <span class="amount"><?php echo count( $user_assets ); ?></span>
                        <span class="unit">varlık</span>
                    </div>
                    <div class="asset-types">
                        <?php
                        $asset_types = array();
                        foreach ( $portfolio_summary['summary_by_type'] as $type_summary ) {
                            $asset_types[] = $public->get_asset_type_label( $type_summary->asset_type );
                        }
                        echo implode( ' • ', $asset_types );
                        ?>
                    </div>
                </div>
            </div>

            <div class="overview-card info-card">
                <div class="card-header">
                    <div class="card-icon info-bg">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title">Piyasa Durumu</h3>
                        <div class="card-meta">Anlık durum</div>
                    </div>
                </div>
                <div class="card-content">
                    <div class="market-status positive">
                        <div class="status-indicator"></div>
                        <span>Piyasalar Açık</span>
                    </div>
                    <div class="market-time">
                        <span id="market-time"><?php echo current_time( 'H:i' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions & Recent Activity -->
    <section class="dashboard-main">
        <div class="main-grid">
            <!-- Recent Assets -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <svg class="title-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Son Varlıklarım
                    </h2>
                    <button class="panel-action" id="view-all-assets">
                        <span>Tümünü Gör</span>
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                
                <div class="panel-content">
                    <?php if ( ! empty( $user_assets ) ): ?>
                        <div class="assets-list">
                            <?php foreach ( array_slice( $user_assets, 0, 5 ) as $asset ): 
                                $profit_loss = $public->calculate_profit_loss( $asset );
                            ?>
                                <div class="asset-item" data-asset-id="<?php echo $asset->id; ?>">
                                    <div class="asset-info">
                                        <div class="asset-icon">
                                            <?php
                                            $icon_class = '';
                                            switch ( $asset->asset_type ) {
                                                case 'stock': $icon_class = 'ST'; break;
                                                case 'crypto': $icon_class = 'CR'; break;
                                                case 'forex': $icon_class = 'FX'; break;
                                                case 'commodity': $icon_class = 'CM'; break;
                                                default: $icon_class = 'AS'; break;
                                            }
                                            echo $icon_class;
                                            ?>
                                        </div>
                                        <div class="asset-details">
                                            <div class="asset-name"><?php echo esc_html( $asset->symbol ); ?></div>
                                            <div class="asset-meta">
                                                <?php echo $public->get_asset_type_label( $asset->asset_type ); ?>
                                                • <?php echo number_format( $asset->quantity, 4 ); ?> adet
                                            </div>
                                        </div>
                                    </div>
                                    <div class="asset-performance">
                                        <div class="asset-value">₺<?php echo number_format( $profit_loss['total_value'], 2, ',', '.' ); ?></div>
                                        <div class="asset-change <?php echo $profit_loss['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $profit_loss['profit_loss'] >= 0 ? '+' : ''; ?>₺<?php echo number_format( $profit_loss['profit_loss'], 2, ',', '.' ); ?>
                                            (<?php echo $profit_loss['profit_loss'] >= 0 ? '+' : ''; ?><?php echo number_format( $profit_loss['profit_loss_percentage'], 2 ); ?>%)
                                        </div>
                                    </div>
                                    <button class="asset-actions-btn" data-asset-id="<?php echo $asset->id; ?>">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                                            <circle cx="19" cy="12" r="1" fill="currentColor"/>
                                            <circle cx="5" cy="12" r="1" fill="currentColor"/>
                                        </svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"/>
                                    <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <h3>Henüz varlığınız yok</h3>
                            <p>İlk varlığınızı ekleyerek portföy oluşturmaya başlayın</p>
                            <button class="empty-action-btn" id="add-first-asset">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5V19" stroke="currentColor" stroke-width="2"/>
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                İlk Varlığımı Ekle
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Portfolio Allocation Chart -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h2 class="panel-title">
                        <svg class="title-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M21.21 15.89C20.5738 17.3945 19.5244 18.6778 18.1955 19.5834C16.8665 20.489 15.3128 21.0803 13.6844 21.2973C12.0561 21.5143 10.4084 21.3489 8.87634 20.8166C7.34426 20.2843 5.97971 19.4034 4.90682 18.2418C3.83392 17.0802 3.08668 15.6756 2.72675 14.1657C2.36683 12.6558 2.40626 11.0855 2.84149 9.59481C3.27672 8.10413 4.09389 6.74249 5.21653 5.63993C6.33917 4.53736 7.73062 3.73316 9.25 3.29" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M22 12C22 10.6868 21.7413 9.38642 21.2388 8.17317C20.7362 6.95991 19.9997 5.85752 19.0711 4.92893C18.1425 4.00035 17.0401 3.26375 15.8268 2.7612C14.6136 2.25866 13.3132 2 12 2V12H22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Portföy Dağılımı
                    </h2>
                </div>
                
                <div class="panel-content">
                    <div class="chart-container">
                        <canvas id="portfolio-allocation-chart" width="300" height="300"></canvas>
                    </div>
                    <div class="allocation-legend">
                        <?php foreach ( $portfolio_summary['summary_by_type'] as $type_summary ): 
                            $percentage = $portfolio_summary['current_value'] > 0 ? 
                                ( $type_summary->current_value / $portfolio_summary['current_value'] ) * 100 : 0;
                        ?>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: var(--chart-color-<?php echo $type_summary->asset_type; ?>);"></div>
                                <div class="legend-label">
                                    <span class="legend-name"><?php echo $public->get_asset_type_label( $type_summary->asset_type ); ?></span>
                                    <span class="legend-value"><?php echo number_format( $percentage, 1 ); ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Market News & Updates -->
    <section class="market-updates">
        <div class="panel-header">
            <h2 class="panel-title">
                <svg class="title-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2"/>
                    <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                </svg>
                Piyasa Haberleri
            </h2>
            <button class="panel-action">Tümünü Gör</button>
        </div>
        
        <div class="news-grid">
            <div class="news-item">
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-source">Bloomberg</span>
                        <span class="news-time">2 saat önce</span>
                    </div>
                    <h3 class="news-title">Kripto para piyasasında yeni gelişmeler</h3>
                    <p class="news-excerpt">Bitcoin ve diğer kripto paralar bugün güçlü bir yükseliş trendinde...</p>
                </div>
                <div class="news-image">
                    <div class="placeholder-image">CHART</div>
                </div>
            </div>
            
            <div class="news-item">
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-source">Reuters</span>
                        <span class="news-time">4 saat önce</span>
                    </div>
                    <h3 class="news-title">Merkez bankası faiz kararları</h3>
                    <p class="news-excerpt">Merkez bankasının beklenen faiz kararı piyasalarda hareketliliğe neden oldu...</p>
                </div>
                <div class="news-image">
                    <div class="placeholder-image">🏦</div>
                </div>
            </div>
            
            <div class="news-item">
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-source">Financial Times</span>
                        <span class="news-time">6 saat önce</span>
                    </div>
                    <h3 class="news-title">Teknoloji hisseleri analizi</h3>
                    <p class="news-excerpt">Büyük teknoloji şirketlerinin son çeyrek sonuçları beklentileri aştı...</p>
                </div>
                <div class="news-image">
                    <div class="placeholder-image">💻</div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Asset Modal -->
<div class="modal-overlay" id="add-asset-modal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Yeni Varlık Ekle</h2>
            <button class="modal-close" id="close-asset-modal">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18" stroke="currentColor" stroke-width="2"/>
                    <path d="M6 6L18 18" stroke="currentColor" stroke-width="2"/>
                </svg>
            </button>
        </div>
        <div class="modal-content">
            <form id="add-asset-form" class="modern-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="asset-type" class="form-label">Varlık Türü</label>
                        <select id="asset-type" name="asset_type" class="form-select" required>
                            <option value="">Seçiniz...</option>
                            <option value="stock">Hisse Senedi</option>
                            <option value="crypto">Kripto Para</option>
                            <option value="forex">Döviz</option>
                            <option value="commodity">Emtia</option>
                            <option value="bond">Tahvil</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="asset-symbol" class="form-label">Sembol</label>
                        <div class="input-with-suggestions">
                            <input type="text" id="asset-symbol" name="symbol" class="form-input" placeholder="AAPL, BTC, EUR/USD..." required>
                            <div class="suggestions-dropdown" id="symbol-suggestions"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="asset-name" class="form-label">Varlık Adı</label>
                        <input type="text" id="asset-name" name="name" class="form-input" placeholder="Apple Inc." required>
                    </div>
                    
                    <div class="form-group">
                        <label for="asset-quantity" class="form-label">Miktar</label>
                        <input type="number" id="asset-quantity" name="quantity" class="form-input" step="0.00000001" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="asset-price" class="form-label">Alış Fiyatı</label>
                        <div class="input-with-currency">
                            <span class="currency-symbol">₺</span>
                            <input type="number" id="asset-price" name="purchase_price" class="form-input" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="asset-date" class="form-label">Alış Tarihi</label>
                        <input type="date" id="asset-date" name="purchase_date" class="form-input" value="<?php echo date( 'Y-m-d' ); ?>" required>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="asset-notes" class="form-label">Notlar (İsteğe bağlı)</label>
                    <textarea id="asset-notes" name="notes" class="form-textarea" placeholder="Bu yatırım hakkında notlarınız..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-add-asset">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5V19" stroke="currentColor" stroke-width="2"/>
                            <path d="M5 12H19" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Varlık Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
// Modern dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts and interactive elements
    initializePortfolioDashboard();
});
</script>