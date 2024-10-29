<?php

/**
 *
 * @link              https://profiles.wordpress.org/palmoduledev
 * @since             1.0.0
 * @package           Auto_Register_Wc
 *
 * @wordpress-plugin
 * Plugin Name:       Auto Register for WooCommerce
 * Plugin URI:        https://profiles.wordpress.org
 * Description:       Once activated, Auto Register for WooCommerce will create a WordPress user account for your customer.
 * Version:           1.0.1
 * Author:            palmoduledev
 * Author URI:        https://profiles.wordpress.org/palmoduledev
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       auto-register-for-woocommerce
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('AUTO_REGISTER_WC_PLUGIN_VERSION', '1.0.1');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-auto-register-for-woocommerce-activator.php
 */
function activate_auto_register_wc() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-auto-register-for-woocommerce-activator.php';
    Auto_Register_Wc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-auto-register-for-woocommerce-deactivator.php
 */
function deactivate_auto_register_wc() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-auto-register-for-woocommerce-deactivator.php';
    Auto_Register_Wc_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_auto_register_wc');
register_deactivation_hook(__FILE__, 'deactivate_auto_register_wc');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-auto-register-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_auto_register_wc() {

    $plugin = new Auto_Register_Wc();
    $plugin->run();
}

run_auto_register_wc();
