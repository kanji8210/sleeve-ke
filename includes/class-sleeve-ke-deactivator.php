<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Sleeve_KE_Deactivator {

    /**
     * Plugin deactivation handler.
     *
     * Performs cleanup tasks during plugin deactivation.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
