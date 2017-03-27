<?php
/**
 * Plugin Name: PayUIndia - Onsite Payment
 * Plugin URI:  http://tasolglobal.com
 * Description: Give all payment methods on you site.
 * Author: Tailored Solutions Pvt. Ltd.
 * Author URI: http://tasolglobal.com
 * Plugin URI: http://tasolglobal.com
 * Text Domain: payuindia
 * Domain Path: /languages/
 * Version: 1.0
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @version     1.0 - initial version
 * @package     PayUIndia
 * @author      Tailored Solutions Pvt. Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Direct Access not allow.
	exit;
}

if ( ! defined( 'PAYUINDIA_PLUGIN_DIR_PATH' ) ) {
	define( 'PAYUINDIA_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'PAYUINDIA_PLUGIN_DIR_URL' ) ) {
	define( 'PAYUINDIA_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PAYUINDIA_PLUGIN_CLASSES_DIR' ) ) {
	define( 'PAYUINDIA_PLUGIN_CLASSES_DIR', plugin_dir_path( __FILE__ ) . 'includes/' );
}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ,true ) ) {
	require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-payu-init.php';
	load_plugin_textdomain( 'payuindia', false,  dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
} else {
	die( 'Woocommerce is required to run this plugin.' );
}

