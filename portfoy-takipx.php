<?php
/**
 * Plugin Name:       Portföy TakipX
 * Plugin URI:        https://github.com/mbalkay/Portfoy_TakipX
 * Description:       Borsa, Kripto ve diğer varlıkların takibini yapmak için bir portföy yönetim eklentisi.
 * Version:           0.1.0
 * Author:            GitHub Copilot & mbalkay
 * Author URI:        https://github.com/mbalkay
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       portfoy-takipx
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
function activate_portfoy_takipx() {
	// Activation code will go here.
}
register_activation_hook( __FILE__, 'activate_portfoy_takipx' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_portfoy_takipx() {
	// Deactivation code will go here.
}
register_deactivation_hook( __FILE__, 'deactivate_portfoy_takipx' );