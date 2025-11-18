<?php
/**
 * Fired during plugin activation.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Sleeve_KE_Activator {

    /**
     * Plugin activation handler.
     *
     * Registers custom user roles and sets up capabilities.
     */
    public static function activate() {
        // Include the roles class
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-roles.php';
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-database.php';
        
        // Create roles and database tables
        Sleeve_KE_Roles::create_roles();
        $created_tables = Sleeve_KE_Database::create_tables();
        
        // Store activation info for admin notice
        if (!empty($created_tables)) {
            set_transient('sleeve_ke_activation_tables', $created_tables, 60);
        }
        set_transient('sleeve_ke_activation_success', true, 60);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
