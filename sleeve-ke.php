<?php
/**
 * Plugin Name:       Sleeve KE
 * Plugin URI:        https://github.com/kanji8210/sleeve-ke
 * Description:       A comprehensive job management system for Kenya with custom user roles for Employers, Candidates, and Admin.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Sleeve KE Team
 * Author URI:        https://github.com/kanji8210
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sleeve-ke
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'SLEEVE_KE_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'SLEEVE_KE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'SLEEVE_KE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_sleeve_ke() {
    require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-activator.php';
    Sleeve_KE_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_sleeve_ke() {
    require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-deactivator.php';
    Sleeve_KE_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sleeve_ke' );
register_deactivation_hook( __FILE__, 'deactivate_sleeve_ke' );

/**
 * The core plugin class.
 */
require SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke.php';

/**
 * Begins execution of the plugin.
 */
function run_sleeve_ke() {
    $plugin = new Sleeve_KE();
    $plugin->run();
}
run_sleeve_ke();
