<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    Sleeve_KE
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Remove custom roles created by the plugin.
 */
function sleeve_ke_remove_roles() {
    remove_role( 'employer' );
    remove_role( 'candidate' );
    remove_role( 'sleve_admin' );
}

sleeve_ke_remove_roles();

/**
 * Clean up options and transients.
 * Add any plugin-specific cleanup code here.
 */
// delete_option( 'sleeve_ke_option_name' );
// delete_transient( 'sleeve_ke_transient_name' );
