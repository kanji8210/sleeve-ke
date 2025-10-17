<?php
/**
 * Handle database table creation and management
 *
 * @package Sleeve_KE
 */

class Sleeve_KE_Database {
    
    /**
     * Create all required database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create jobs table
        $table_jobs = $wpdb->prefix . 'sleeve_jobs';
        $sql_jobs = "CREATE TABLE IF NOT EXISTS $table_jobs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            employer_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            description longtext NOT NULL,
            requirements longtext,
            salary_range varchar(100),
            location varchar(255),
            job_type varchar(50),
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at datetime,
            PRIMARY KEY (id),
            KEY employer_id (employer_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Create applications table
        $table_applications = $wpdb->prefix . 'sleeve_applications';
        $sql_applications = "CREATE TABLE IF NOT EXISTS $table_applications (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            job_id bigint(20) UNSIGNED NOT NULL,
            candidate_id bigint(20) UNSIGNED NOT NULL,
            cover_letter longtext,
            resume_url varchar(500),
            status varchar(20) DEFAULT 'pending',
            applied_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            notes longtext,
            PRIMARY KEY (id),
            KEY job_id (job_id),
            KEY candidate_id (candidate_id),
            KEY status (status),
            UNIQUE KEY unique_application (job_id, candidate_id)
        ) $charset_collate;";
        
        // Create candidates table (extended profile information)
        $table_candidates = $wpdb->prefix . 'sleeve_candidates';
        $sql_candidates = "CREATE TABLE IF NOT EXISTS $table_candidates (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            phone varchar(20),
            location varchar(255),
            experience_years int(11),
            education varchar(255),
            skills longtext,
            resume_url varchar(500),
            linkedin_url varchar(500),
            portfolio_url varchar(500),
            availability varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        // Create employers table (extended profile information)
        $table_employers = $wpdb->prefix . 'sleeve_employers';
        $sql_employers = "CREATE TABLE IF NOT EXISTS $table_employers (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            company_name varchar(255) NOT NULL,
            company_description longtext,
            company_logo varchar(500),
            website varchar(500),
            phone varchar(20),
            location varchar(255),
            industry varchar(100),
            company_size varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        // Create payments table
        $table_payments = $wpdb->prefix . 'sleeve_payments';
        $sql_payments = "CREATE TABLE IF NOT EXISTS $table_payments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            job_id bigint(20) UNSIGNED,
            amount decimal(10,2) NOT NULL,
            currency varchar(3) DEFAULT 'USD',
            payment_method varchar(50),
            transaction_id varchar(255),
            status varchar(20) DEFAULT 'pending',
            payment_type varchar(50),
            description varchar(500),
            paid_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY job_id (job_id),
            KEY status (status),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        
        // Include WordPress upgrade library
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute table creation
        dbDelta($sql_jobs);
        dbDelta($sql_applications);
        dbDelta($sql_candidates);
        dbDelta($sql_employers);
        dbDelta($sql_payments);
        
        // Save database version
        update_option('sleeve_ke_db_version', SLEEVE_KE_VERSION);
    }
    
    /**
     * Drop all plugin tables (used during uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'sleeve_jobs',
            $wpdb->prefix . 'sleeve_applications',
            $wpdb->prefix . 'sleeve_candidates',
            $wpdb->prefix . 'sleeve_employers',
            $wpdb->prefix . 'sleeve_payments'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        // Remove database version option
        delete_option('sleeve_ke_db_version');
    }
}
