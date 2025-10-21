<?php
/**
 * Plugin Name:       Sleeve KE
 * Plugin URI:        https://github.com/kanji8210/sleeve-ke
 * Description:       A comprehensive job board and recruitment management plugin for WordPress with custom user roles and database tables.
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
 * Creates roles and database tables.
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
 * Load frontend registration forms class.
 */
if ( ! is_admin() ) {
    require_once SLEEVE_KE_PLUGIN_DIR . 'public/class-sleeve-ke-registration-forms.php';
    require_once SLEEVE_KE_PLUGIN_DIR . 'public/class-sleeve-ke-job-display.php';
    
    // Initialize frontend classes on init
    add_action( 'init', function() {
        new Sleeve_KE_Registration_Forms();
        new Sleeve_KE_Job_Display();
    } );
}

/**
 * Begins execution of the plugin.
 */
function run_sleeve_ke() {
    $plugin = new Sleeve_KE();
    $plugin->run();
}
run_sleeve_ke();

/**
 * Utility function to send Sleeve KE notifications
 * 
 * @param string $type Notification type
 * @param string $recipient Email address
 * @param array $variables Template variables
 * @return bool Success/failure
 */
function sleeve_ke_send_notification( $type, $recipient, $variables = array() ) {
    // Check if notifications are enabled
    if ( ! get_option( 'sleeve_ke_enable_notifications', 1 ) ) {
        return false;
    }
    
    // Load notifications class if not already loaded
    if ( ! class_exists( 'Sleeve_KE_Notifications' ) ) {
        require_once SLEEVE_KE_PLUGIN_DIR . 'admin/class-sleeve-ke-notifications.php';
    }
    
    $notifications = new Sleeve_KE_Notifications();
    return $notifications->send_notification( $type, $recipient, $variables );
}

/**
 * Trigger new application notification
 * 
 * @param int $application_id Application ID
 */
function sleeve_ke_trigger_new_application( $application_id ) {
    do_action( 'sleeve_ke_new_application_submitted', $application_id );
}

/**
 * Trigger new employer registration notification
 * 
 * @param int $employer_id Employer ID
 */
function sleeve_ke_trigger_new_employer( $employer_id ) {
    do_action( 'sleeve_ke_new_employer_registered', $employer_id );
}

/**
 * Trigger new candidate registration notification
 * 
 * @param int $candidate_id Candidate ID
 */
function sleeve_ke_trigger_new_candidate( $candidate_id ) {
    do_action( 'sleeve_ke_new_candidate_registered', $candidate_id );
}

/**
 * Trigger job posted notification
 * 
 * @param int $job_id Job ID
 */
function sleeve_ke_trigger_job_posted( $job_id ) {
    do_action( 'sleeve_ke_job_posted', $job_id );
}
