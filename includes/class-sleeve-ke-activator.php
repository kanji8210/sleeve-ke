<?php
/**
 * Fired during plugin activation
 *
 * @package Sleeve_KE
 */

class Sleeve_KE_Activator {
    
    /**
     * Activate the plugin - create roles and database tables
     */
    public static function activate() {
        // Load required classes
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-roles.php';
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-database.php';
        
        // Create custom roles
        Sleeve_KE_Roles::create_roles();
        
        // Create database tables
        Sleeve_KE_Database::create_tables();
        
        // Save activation timestamp
        update_option('sleeve_ke_activated', current_time('mysql'));
        update_option('sleeve_ke_version', SLEEVE_KE_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
