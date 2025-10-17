<?php
/**
 * Plugin Name: Sleeve KE
 * Plugin URI: https://github.com/kanji8210/sleeve-ke
 * Description: A job board and recruitment management plugin for WordPress with custom user roles and database tables.
 * Version: 1.0.0
 * Author: Kanji8210
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sleeve-ke
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('SLEEVE_KE_VERSION', '1.0.0');
define('SLEEVE_KE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SLEEVE_KE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke.php';

/**
 * Activation hook - Creates roles and database tables
 */
function activate_sleeve_ke() {
    require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-activator.php';
    Sleeve_KE_Activator::activate();
}

/**
 * Deactivation hook
 */
function deactivate_sleeve_ke() {
    require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-deactivator.php';
    Sleeve_KE_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_sleeve_ke');
register_deactivation_hook(__FILE__, 'deactivate_sleeve_ke');

/**
 * Begin execution of the plugin
 */
function run_sleeve_ke() {
    $plugin = new Sleeve_KE();
    $plugin->run();
}
run_sleeve_ke();
