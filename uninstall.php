<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package Sleeve_KE
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include required classes
require_once plugin_dir_path(__FILE__) . 'includes/class-sleeve-ke-roles.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sleeve-ke-database.php';

// Remove custom roles
Sleeve_KE_Roles::remove_roles();

// Drop all database tables
Sleeve_KE_Database::drop_tables();

// Remove plugin options
delete_option('sleeve_ke_activated');
delete_option('sleeve_ke_version');
delete_option('sleeve_ke_db_version');

// Clear any transients
delete_transient('sleeve_ke_*');
