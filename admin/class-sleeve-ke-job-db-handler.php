<?php
/**
 * Database handler for job operations.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Job database handler class.
 *
 * Handles all database operations for job postings.
 */
class Sleeve_KE_Job_DB_Handler {

    /**
     * The database table name.
     */
    private $table_name;

    /**
     * Initialize the class and set the table name.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'sleeve_jobs';
    }

    /**
     * Create the jobs table.
     */
    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(200) NOT NULL,
            description longtext NOT NULL,
            requirements longtext NOT NULL,
            benefits longtext,
            company varchar(200) NOT NULL,
            sector varchar(100) NOT NULL,
            location varchar(200) NOT NULL,
            job_type varchar(50) NOT NULL,
            experience_level varchar(50) NOT NULL,
            remote_work varchar(50) NOT NULL,
            salary_min int(11) DEFAULT NULL,
            salary_max int(11) DEFAULT NULL,
            currency varchar(10) DEFAULT 'KES',
            status varchar(20) NOT NULL DEFAULT 'draft',
            applications_count int(11) NOT NULL DEFAULT 0,
            posted_date datetime NOT NULL,
            expires_at date NOT NULL,
            employer_id bigint(20) NOT NULL,
            employer_type varchar(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employer_id (employer_id),
            KEY status (status),
            KEY sector (sector),
            KEY job_type (job_type),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get job by ID.
     */
    public function get_job_by_id($job_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d", $job_id
        ), ARRAY_A);
    }

    /**
     * Get jobs with filters.
     */
    public function get_jobs_with_filters($filters = array()) {
        global $wpdb;

        $where_conditions = array();
        $query_params = array();

        // Base query
        $query = "SELECT * FROM {$this->table_name}";

        // Apply filters
        if (!empty($filters['search'])) {
            $where_conditions[] = "(title LIKE %s OR company LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $query_params[] = $search_term;
            $query_params[] = $search_term;
        }

        if (!empty($filters['status'])) {
            $where_conditions[] = "status = %s";
            $query_params[] = $filters['status'];
        }

        if (!empty($filters['job_type'])) {
            $where_conditions[] = "job_type = %s";
            $query_params[] = $filters['job_type'];
        }

        if (!empty($filters['sector'])) {
            $where_conditions[] = "sector = %s";
            $query_params[] = $filters['sector'];
        }

        // If the user is an employer, only show their jobs
        if (current_user_can('employer')) {
            $where_conditions[] = "employer_id = %d";
            $query_params[] = get_current_user_id();
        }

        // Build WHERE clause
        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(" AND ", $where_conditions);
        }

        // Order by most recent first
        $query .= " ORDER BY created_at DESC";

        // Prepare and execute query
        if (!empty($query_params)) {
            $query = $wpdb->prepare($query, $query_params);
        }

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Insert a new job.
     */
    public function insert_job($data) {
        global $wpdb;

        $defaults = array(
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );

        $data = wp_parse_args($data, $defaults);

        $result = $wpdb->insert($this->table_name, $data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update an existing job.
     */
    public function update_job($job_id, $data) {
        global $wpdb;

        $data['updated_at'] = current_time('mysql');

        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $job_id)
        );

        return $result !== false;
    }

    /**
     * Update job status.
     */
    public function update_job_status($job_id, $status) {
        return $this->update_job($job_id, array('status' => $status));
    }

    /**
     * Delete a job.
     */
    public function delete_job($job_id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, array('id' => $job_id));
    }

    /**
     * Get job statistics.
     */
    public function get_job_stats() {
        global $wpdb;

        $current_user_id = get_current_user_id();
        $where_condition = "";

        // If user is employer, only count their jobs
        if (current_user_can('employer')) {
            $where_condition = $wpdb->prepare("WHERE employer_id = %d", $current_user_id);
        }

        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired
            FROM {$this->table_name} {$where_condition}
        ";

        $stats = $wpdb->get_row($query, ARRAY_A);
        
        // Handle null results
        if (!$stats || !is_array($stats)) {
            $stats = array(
                'total' => 0,
                'draft' => 0,
                'active' => 0,
                'closed' => 0,
                'expired' => 0
            );
        }

        return array(
            array('count' => intval($stats['total'] ?? 0), 'label' => __('Total Jobs', 'sleeve-ke')),
            array('count' => intval($stats['draft'] ?? 0), 'label' => __('Draft', 'sleeve-ke')),
            array('count' => intval($stats['active'] ?? 0), 'label' => __('Active', 'sleeve-ke')),
            array('count' => intval($stats['closed'] ?? 0), 'label' => __('Closed', 'sleeve-ke')),
            array('count' => intval($stats['expired'] ?? 0), 'label' => __('Expired', 'sleeve-ke'))
        );
    }
}