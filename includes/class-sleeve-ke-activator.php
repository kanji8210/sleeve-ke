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
        self::create_custom_roles();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom user roles.
     *
     * Creates Employer, Candidate, and sleve_admin roles with appropriate capabilities.
     */
    private static function create_custom_roles() {
        
        // Employer Role
        add_role(
            'employer',
            __( 'Employer', 'sleeve-ke' ),
            array(
                'read'              => true,
                'edit_posts'        => true,
                'delete_posts'      => true,
                'publish_posts'     => true,
                'upload_files'      => true,
                'manage_jobs'       => true,
                'view_applications' => true,
                'manage_payments'   => true,
            )
        );

        // Candidate Role
        add_role(
            'candidate',
            __( 'Candidate', 'sleeve-ke' ),
            array(
                'read'              => true,
                'edit_posts'        => false,
                'upload_files'      => true,
                'view_jobs'         => true,
                'apply_to_jobs'     => true,
                'manage_profile'    => true,
            )
        );

        // Sleeve Admin Role (manages everything in the system)
        add_role(
            'sleve_admin',
            __( 'Sleeve Admin', 'sleeve-ke' ),
            array(
                'read'                   => true,
                'edit_posts'             => true,
                'delete_posts'           => true,
                'publish_posts'          => true,
                'upload_files'           => true,
                'manage_options'         => true,
                'manage_applications'    => true,
                'manage_jobs'            => true,
                'manage_candidates'      => true,
                'manage_employers'       => true,
                'manage_payments'        => true,
                'view_all_applications'  => true,
                'view_all_jobs'          => true,
                'view_all_candidates'    => true,
                'view_all_employers'     => true,
                'view_all_payments'      => true,
                'edit_users'             => true,
                'delete_users'           => true,
            )
        );
    }
}
