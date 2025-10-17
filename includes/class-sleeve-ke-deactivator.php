<?php
/**
 * Fired during plugin deactivation
 *
 * @package Sleeve_KE
 */

class Sleeve_KE_Deactivator {
    
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Note: We don't remove roles or tables on deactivation
        // This is handled by the uninstall.php file if the user chooses to uninstall
    }
}
