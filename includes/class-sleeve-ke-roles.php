<?php
/**
 * Handle custom user roles
 *
 * @package Sleeve_KE
 */

class Sleeve_KE_Roles {
    
    /**
     * Create custom user roles for the plugin
     */
    public static function create_roles() {
        // Create Employer role
        add_role(
            'employer',
            __('Employer', 'sleeve-ke'),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => true,
                // Custom capabilities for employers
                'create_jobs' => true,
                'edit_jobs' => true,
                'delete_jobs' => true,
                'view_applications' => true,
                'manage_applications' => true,
            )
        );
        
        // Create Candidate role
        add_role(
            'candidate',
            __('Candidate', 'sleeve-ke'),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => true,
                // Custom capabilities for candidates
                'view_jobs' => true,
                'apply_for_jobs' => true,
                'view_own_applications' => true,
                'edit_own_profile' => true,
            )
        );
        
        // Create Sleve Admin role (with elevated permissions)
        add_role(
            'sleve_admin',
            __('Sleve Admin', 'sleeve-ke'),
            array(
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'upload_files' => true,
                'edit_pages' => true,
                'delete_pages' => true,
                'publish_pages' => true,
                'edit_others_posts' => true,
                'delete_others_posts' => true,
                // Custom capabilities for sleve admin
                'manage_jobs' => true,
                'manage_applications' => true,
                'manage_candidates' => true,
                'manage_employers' => true,
                'manage_payments' => true,
                'view_all_applications' => true,
                'view_all_jobs' => true,
                'edit_all_jobs' => true,
                'delete_all_jobs' => true,
                'view_payments' => true,
                'process_payments' => true,
            )
        );
        
        // Add custom capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_jobs');
            $admin_role->add_cap('manage_applications');
            $admin_role->add_cap('manage_candidates');
            $admin_role->add_cap('manage_employers');
            $admin_role->add_cap('manage_payments');
        }
    }
    
    /**
     * Remove custom user roles
     */
    public static function remove_roles() {
        remove_role('employer');
        remove_role('candidate');
        remove_role('sleve_admin');
        
        // Remove custom capabilities from administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('manage_jobs');
            $admin_role->remove_cap('manage_applications');
            $admin_role->remove_cap('manage_candidates');
            $admin_role->remove_cap('manage_employers');
            $admin_role->remove_cap('manage_payments');
        }
    }
}
