<?php
/**
 * Provide an admin settings view for the plugin
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields( 'portfoy_takipx_settings' );
        do_settings_sections( 'portfoy_takipx_settings' );
        ?>
        
        <h2>Genel Ayarlar</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Plugin Durumu</th>
                <td>
                    <p class="description">Plugin aktif ve çalışıyor.</p>
                    <?php 
                    $page_id = get_option( 'portfoy_takipx_page_id' );
                    if ( $page_id ) {
                        $page_url = get_permalink( $page_id );
                        echo '<p><a href="' . esc_url( $page_url ) . '" target="_blank">Portföy Sayfasını Görüntüle</a></p>';
                    }
                    ?>
                </td>
            </tr>
        </table>

        <h2>Tema Uyumluluğu</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Twenty Twenty-Three Uyumluluğu</th>
                <td>
                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                    <strong>Uyumlu</strong>
                    <p class="description">Plugin, Twenty Twenty-Three teması ile tamamen uyumludur.</p>
                </td>
            </tr>
        </table>

        <h2>API Ayarları</h2>
        <table class="form-table">
            <tr>
                <th scope="row">API Durumu</th>
                <td>
                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                    <strong>Aktif</strong>
                    <p class="description">REST API endpoints aktif ve kullanılabilir.</p>
                    <p><strong>Base URL:</strong> <code><?php echo esc_url( rest_url( 'portfoy-takipx/v1/' ) ); ?></code></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <h2>Sistem Bilgileri</h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th>Özellik</th>
                <th>Değer</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Plugin Versiyonu</td>
                <td><?php echo esc_html( PORTFOY_TAKIPX_VERSION ); ?></td>
            </tr>
            <tr>
                <td>WordPress Versiyonu</td>
                <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
            </tr>
            <tr>
                <td>PHP Versiyonu</td>
                <td><?php echo esc_html( PHP_VERSION ); ?></td>
            </tr>
            <tr>
                <td>Aktif Tema</td>
                <td><?php echo esc_html( wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' ) ); ?></td>
            </tr>
            <tr>
                <td>Veritabanı</td>
                <td>
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'portfoy_takipx_assets';
                    $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
                    if ( $table_exists ) {
                        echo '<span class="dashicons dashicons-yes-alt" style="color: green;"></span> Tablolar oluşturuldu';
                    } else {
                        echo '<span class="dashicons dashicons-warning" style="color: red;"></span> Tablolar oluşturulmadı';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Portföy Sayfası</td>
                <td>
                    <?php
                    $page_id = get_option( 'portfoy_takipx_page_id' );
                    if ( $page_id && get_post( $page_id ) ) {
                        echo '<span class="dashicons dashicons-yes-alt" style="color: green;"></span> Oluşturuldu (ID: ' . esc_html( $page_id ) . ')';
                    } else {
                        echo '<span class="dashicons dashicons-warning" style="color: red;"></span> Oluşturulmadı';
                    }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>

    <h2>Şortkod Kullanımı</h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th>Şortkod</th>
                <th>Açıklama</th>
                <th>Kullanım</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[portfoy_takipx_portfolio]</code></td>
                <td>Tam portföy görünümü</td>
                <td>Sayfa veya yazılarda kullanın</td>
            </tr>
            <tr>
                <td><code>[portfoy_takipx_summary]</code></td>
                <td>Portföy özeti</td>
                <td>Widget alanlarında kullanın</td>
            </tr>
            <tr>
                <td><code>[portfoy_takipx_assets type="crypto"]</code></td>
                <td>Belirli tür varlıkları göster</td>
                <td>Tür bazlı görünüm için</td>
            </tr>
        </tbody>
    </table>
</div>

<style>
.portfoy-takipx-settings .form-table th {
    width: 200px;
}

.portfoy-takipx-settings .description {
    font-style: italic;
    color: #666;
}

.portfoy-takipx-settings .dashicons {
    vertical-align: middle;
    margin-right: 5px;
}
</style>