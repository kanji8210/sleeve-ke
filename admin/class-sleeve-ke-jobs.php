<?php
/**
 * Jobs management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Jobs management class.
 *
 * Handles all functionality related to job postings management
 * including display, creation, editing, and deletion.
 * Supports different user roles: admin, sleve_admin, and employer.
 */
class Sleeve_KE_Jobs {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor can be used for initialization if needed
    }

    /**
     * Display the jobs management page.
     */
    public function display_page() {
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['sleeve_nonce'], 'sleeve_jobs' ) ) {
            $this->handle_job_actions();
        }
        
        // Check if we're adding/editing a job
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
        $job_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        
        switch ( $action ) {
            case 'add':
                $this->display_add_job_form();
                break;
            case 'edit':
                $this->display_edit_job_form( $job_id );
                break;
            case 'view':
                $this->display_job_view( $job_id );
                break;
            default:
                $this->display_jobs_list();
                break;
        }
    }

    /**
     * Display the jobs list page.
     */
    private function display_jobs_list() {
        // Get jobs data
        $jobs = $this->get_jobs_data();
        $statuses = $this->get_status_options();
        $current_user = wp_get_current_user();
        $can_add_jobs = $this->user_can_add_jobs();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( 'Job Postings', 'sleeve-ke' ); ?>
                <?php if ( $can_add_jobs ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=add' ) ); ?>" class="page-title-action">
                        <?php esc_html_e( 'Add New Job', 'sleeve-ke' ); ?>
                    </a>
                <?php endif; ?>
            </h1>
            
            <!-- Filter and Search Section -->
            <div class="sleeve-ke-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="sleeve-ke-jobs" />
                    
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="<?php esc_attr_e( 'Search by job title or company...', 'sleeve-ke' ); ?>" 
                               value="<?php echo esc_attr( isset( $_GET['search'] ) ? $_GET['search'] : '' ); ?>" />
                        
                        <select name="status">
                            <option value=""><?php esc_html_e( 'All Statuses', 'sleeve-ke' ); ?></option>
                            <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                <option value="<?php echo esc_attr( $status_key ); ?>" 
                                        <?php selected( isset( $_GET['status'] ) ? $_GET['status'] : '', $status_key ); ?>>
                                    <?php echo esc_html( $status_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="job_type">
                            <option value=""><?php esc_html_e( 'All Job Types', 'sleeve-ke' ); ?></option>
                            <?php
                            $job_types = $this->get_job_types();
                            foreach ( $job_types as $type_key => $type_label ) :
                            ?>
                                <option value="<?php echo esc_attr( $type_key ); ?>" 
                                        <?php selected( isset( $_GET['job_type'] ) ? $_GET['job_type'] : '', $type_key ); ?>>
                                    <?php echo esc_html( $type_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="sector">
                            <option value=""><?php esc_html_e( 'All Sectors', 'sleeve-ke' ); ?></option>
                            <?php
                            $sectors = $this->get_sectors();
                            foreach ( $sectors as $sector_key => $sector_label ) :
                            ?>
                                <option value="<?php echo esc_attr( $sector_key ); ?>" 
                                        <?php selected( isset( $_GET['sector'] ) ? $_GET['sector'] : '', $sector_key ); ?>>
                                    <?php echo esc_html( $sector_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button( __( 'Filter', 'sleeve-ke' ), 'secondary', 'filter', false ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs' ) ); ?>" class="button">
                            <?php esc_html_e( 'Clear', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Jobs Table -->
            <?php if ( $this->user_can_manage_all_jobs() ) : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'sleeve_jobs', 'sleeve_nonce' ); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value=""><?php esc_html_e( 'Bulk Actions', 'sleeve-ke' ); ?></option>
                            <option value="publish"><?php esc_html_e( 'Publish', 'sleeve-ke' ); ?></option>
                            <option value="draft"><?php esc_html_e( 'Move to Draft', 'sleeve-ke' ); ?></option>
                            <option value="archive"><?php esc_html_e( 'Archive', 'sleeve-ke' ); ?></option>
                            <option value="delete"><?php esc_html_e( 'Delete', 'sleeve-ke' ); ?></option>
                        </select>
                        <?php submit_button( __( 'Apply', 'sleeve-ke' ), 'action', 'apply_bulk_action', false ); ?>
                    </div>
                </div>
            <?php endif; ?>

                <table class="wp-list-table widefat fixed striped sleeve-ke-jobs-table">
                    <thead>
                        <tr>
                            <?php if ( $this->user_can_manage_all_jobs() ) : ?>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all" />
                            </td>
                            <?php endif; ?>
                            <th class="manage-column"><?php esc_html_e( 'Job Title', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Company', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Sector', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Type', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Salary', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Applications', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Posted', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Actions', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $jobs ) ) : ?>
                            <tr>
                                <td colspan="<?php echo $this->user_can_manage_all_jobs() ? '11' : '10'; ?>" class="no-items">
                                    <?php esc_html_e( 'No jobs found.', 'sleeve-ke' ); ?>
                                    <?php if ( $can_add_jobs ) : ?>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=add' ) ); ?>">
                                            <?php esc_html_e( 'Add your first job', 'sleeve-ke' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $jobs as $job ) : ?>
                                <tr>
                                    <?php if ( $this->user_can_manage_all_jobs() ) : ?>
                                    <th class="check-column">
                                        <input type="checkbox" name="job_ids[]" value="<?php echo esc_attr( $job['id'] ); ?>" />
                                    </th>
                                    <?php endif; ?>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=view&id=' . $job['id'] ) ); ?>">
                                                <?php echo esc_html( $job['title'] ); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=view&id=' . $job['id'] ) ); ?>"><?php esc_html_e( 'View', 'sleeve-ke' ); ?></a> | </span>
                                            <?php if ( $this->user_can_edit_job( $job ) ) : ?>
                                                <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'sleeve-ke' ); ?></a> | </span>
                                            <?php endif; ?>
                                            <?php if ( $this->user_can_delete_job( $job ) ) : ?>
                                                <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=delete&id=' . $job['id'] ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'sleeve-ke' ); ?>')" class="delete"><?php esc_html_e( 'Delete', 'sleeve-ke' ); ?></a></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo esc_html( $job['company'] ); ?>
                                        <div class="employer-type-indicator">
                                            <?php echo esc_html( $job['employer_type'] === 'individual' ? __( '(Individual)', 'sleeve-ke' ) : __( '(Org)', 'sleeve-ke' ) ); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $sectors = $this->get_sectors();
                                        $sector_display = isset( $sectors[ $job['sector'] ] ) ? $sectors[ $job['sector'] ] : ucfirst( $job['sector'] );
                                        echo esc_html( $sector_display );
                                        ?>
                                    </td>
                                    <td><?php echo esc_html( $job['location'] ); ?></td>
                                    <td><?php echo esc_html( $job['job_type'] ); ?></td>
                                    <td>
                                        <?php if ( ! empty( $job['salary_min'] ) && ! empty( $job['salary_max'] ) ) : ?>
                                            <?php echo esc_html( number_format( $job['salary_min'] ) . ' - ' . number_format( $job['salary_max'] ) . ' ' . $job['currency'] ); ?>
                                        <?php elseif ( ! empty( $job['salary_min'] ) ) : ?>
                                            <?php echo esc_html( number_format( $job['salary_min'] ) . '+ ' . $job['currency'] ); ?>
                                        <?php else : ?>
                                            <span class="no-salary"><?php esc_html_e( 'Not specified', 'sleeve-ke' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $job['status'] ); ?>">
                                            <?php echo esc_html( $statuses[ $job['status'] ] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications&job_id=' . $job['id'] ) ); ?>">
                                            <?php echo esc_html( $job['applications_count'] ); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html( $job['posted_date'] ); ?></td>
                                    <td>
                                        <?php if ( $this->user_can_edit_job( $job ) ) : ?>
                                            <select class="status-select" data-job-id="<?php echo esc_attr( $job['id'] ); ?>">
                                                <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                                    <option value="<?php echo esc_attr( $status_key ); ?>" 
                                                            <?php selected( $job['status'], $status_key ); ?>>
                                                        <?php echo esc_html( $status_label ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php if ( $this->user_can_manage_all_jobs() ) : ?>
            </form>
            <?php endif; ?>
            
            <!-- Statistics Section -->
            <div class="sleeve-ke-jobs-stats">
                <h3><?php esc_html_e( 'Job Statistics', 'sleeve-ke' ); ?></h3>
                <div class="stats-grid">
                    <?php
                    $stats = $this->get_job_stats();
                    foreach ( $stats as $stat ) :
                    ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo esc_html( $stat['count'] ); ?></div>
                            <div class="stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Handle status change
            $('.status-select').on('change', function() {
                var jobId = $(this).data('job-id');
                var newStatus = $(this).val();
                
                $.post(ajaxurl, {
                    action: 'update_job_status',
                    job_id: jobId,
                    status: newStatus,
                    nonce: '<?php echo wp_create_nonce( 'update_job_status' ); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating status');
                    }
                });
            });
            
            // Handle select all checkbox
            $('#cb-select-all').on('change', function() {
                $('input[name="job_ids[]"]').prop('checked', this.checked);
            });
        });
        </script>
        <?php
    }

    /**
     * Display add job form.
     */
    private function display_add_job_form() {
        if ( ! $this->user_can_add_jobs() ) {
            wp_die( __( 'You do not have permission to add jobs.', 'sleeve-ke' ) );
        }

        $this->display_job_form();
    }

    /**
     * Display edit job form.
     */
    private function display_edit_job_form( $job_id ) {
        $job = $this->get_job_by_id( $job_id );
        
        if ( ! $job ) {
            wp_die( __( 'Job not found.', 'sleeve-ke' ) );
        }

        if ( ! $this->user_can_edit_job( $job ) ) {
            wp_die( __( 'You do not have permission to edit this job.', 'sleeve-ke' ) );
        }

        $this->display_job_form( $job );
    }

    /**
     * Display job form (add/edit).
     */
    private function display_job_form( $job = null ) {
        $is_edit = ! empty( $job );
        $form_title = $is_edit ? __( 'Edit Job', 'sleeve-ke' ) : __( 'Add New Job', 'sleeve-ke' );
        $submit_text = $is_edit ? __( 'Update Job', 'sleeve-ke' ) : __( 'Add Job', 'sleeve-ke' );
        
        // Default values
        $defaults = array(
            'id' => 0,
            'title' => '',
            'sector' => '',
            'description' => '',
            'requirements' => '',
            'company' => '',
            'employer_type' => 'organization',
            'location' => '',
            'job_type' => 'full-time',
            'experience_level' => '',
            'salary_min' => '',
            'salary_max' => '',
            'currency' => 'KES',
            'status' => 'draft',
            'expires_at' => '',
            'remote_work' => 'no',
            'benefits' => ''
        );

        $job_data = $is_edit ? array_merge( $defaults, $job ) : $defaults;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( $form_title ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs' ) ); ?>" class="button">
                <?php esc_html_e( '← Back to Jobs', 'sleeve-ke' ); ?>
            </a>
            
            <form method="post" action="" class="sleeve-ke-job-form">
                <?php wp_nonce_field( 'sleeve_job_form', 'job_form_nonce' ); ?>
                <input type="hidden" name="job_action" value="<?php echo $is_edit ? 'update' : 'create'; ?>" />
                <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_data['id'] ); ?>" />
                
                <div class="sleeve-ke-form-container">
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Job Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="job_title"><?php esc_html_e( 'Job Title', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr( $job_data['title'] ); ?>" class="regular-text" required />
                                    <p class="description"><?php esc_html_e( 'Include sector in title (e.g., "Senior Developer - Technology Sector")', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="sector"><?php esc_html_e( 'Sector', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <select id="sector" name="sector" required>
                                        <option value=""><?php esc_html_e( 'Select Sector', 'sleeve-ke' ); ?></option>
                                        <?php
                                        $sectors = $this->get_sectors();
                                        foreach ( $sectors as $sector_key => $sector_label ) :
                                        ?>
                                            <option value="<?php echo esc_attr( $sector_key ); ?>" <?php selected( $job_data['sector'], $sector_key ); ?>>
                                                <?php echo esc_html( $sector_label ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="company"><?php esc_html_e( 'Employer Name', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="company" name="company" value="<?php echo esc_attr( $job_data['company'] ); ?>" class="regular-text" required />
                                    <p class="description"><?php esc_html_e( 'Organization name or Individual name (e.g., "Tech Corp" or "Dr. John Doe")', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="employer_type"><?php esc_html_e( 'Employer Type', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <select id="employer_type" name="employer_type">
                                        <option value="organization" <?php selected( $job_data['employer_type'], 'organization' ); ?>><?php esc_html_e( 'Organization/Company', 'sleeve-ke' ); ?></option>
                                        <option value="individual" <?php selected( $job_data['employer_type'], 'individual' ); ?>><?php esc_html_e( 'Individual', 'sleeve-ke' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="location"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="location" name="location" value="<?php echo esc_attr( $job_data['location'] ); ?>" class="regular-text" required />
                                    <p class="description"><?php esc_html_e( 'e.g., Nairobi, Kenya or Remote', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="job_type"><?php esc_html_e( 'Job Type', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <select id="job_type" name="job_type">
                                        <?php
                                        $job_types = $this->get_job_types();
                                        foreach ( $job_types as $type_key => $type_label ) :
                                        ?>
                                            <option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $job_data['job_type'], $type_key ); ?>>
                                                <?php echo esc_html( $type_label ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            

                            
                            <tr>
                                <th scope="row">
                                    <label for="experience_level"><?php esc_html_e( 'Experience Level', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <select id="experience_level" name="experience_level">
                                        <option value=""><?php esc_html_e( 'Select Experience Level', 'sleeve-ke' ); ?></option>
                                        <?php
                                        $experience_levels = array(
                                            'entry' => __( 'Entry Level (0-2 years)', 'sleeve-ke' ),
                                            'mid' => __( 'Mid Level (3-5 years)', 'sleeve-ke' ),
                                            'senior' => __( 'Senior Level (6-10 years)', 'sleeve-ke' ),
                                            'executive' => __( 'Executive Level (10+ years)', 'sleeve-ke' )
                                        );
                                        foreach ( $experience_levels as $level_key => $level_label ) :
                                        ?>
                                            <option value="<?php echo esc_attr( $level_key ); ?>" <?php selected( $job_data['experience_level'], $level_key ); ?>>
                                                <?php echo esc_html( $level_label ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="remote_work"><?php esc_html_e( 'Remote Work', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <select id="remote_work" name="remote_work">
                                        <option value="no" <?php selected( $job_data['remote_work'], 'no' ); ?>><?php esc_html_e( 'No Remote Work', 'sleeve-ke' ); ?></option>
                                        <option value="hybrid" <?php selected( $job_data['remote_work'], 'hybrid' ); ?>><?php esc_html_e( 'Hybrid', 'sleeve-ke' ); ?></option>
                                        <option value="full" <?php selected( $job_data['remote_work'], 'full' ); ?>><?php esc_html_e( 'Fully Remote', 'sleeve-ke' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Salary Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="salary_min"><?php esc_html_e( 'Minimum Salary', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="salary_min" name="salary_min" value="<?php echo esc_attr( $job_data['salary_min'] ); ?>" class="regular-text" />
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="salary_max"><?php esc_html_e( 'Maximum Salary', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="salary_max" name="salary_max" value="<?php echo esc_attr( $job_data['salary_max'] ); ?>" class="regular-text" />
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="currency"><?php esc_html_e( 'Currency', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <select id="currency" name="currency">
                                        <option value="KES" <?php selected( $job_data['currency'], 'KES' ); ?>>KES (Kenyan Shilling)</option>
                                        <option value="USD" <?php selected( $job_data['currency'], 'USD' ); ?>>USD (US Dollar)</option>
                                        <option value="EUR" <?php selected( $job_data['currency'], 'EUR' ); ?>>EUR (Euro)</option>
                                        <option value="UGX" <?php selected( $job_data['currency'], 'UGX' ); ?>>UGX (Ugandan Shilling)</option>
                                        <option value="TZS" <?php selected( $job_data['currency'], 'TZS' ); ?>>TZS (Tanzanian Shilling)</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Job Description', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="description"><?php esc_html_e( 'Description', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <textarea id="description" name="description" rows="8" class="large-text" required><?php echo esc_textarea( $job_data['description'] ); ?></textarea>
                                    <p class="description"><?php esc_html_e( 'Detailed description of the job role and responsibilities.', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="requirements"><?php esc_html_e( 'Requirements', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="requirements" name="requirements" rows="6" class="large-text"><?php echo esc_textarea( $job_data['requirements'] ); ?></textarea>
                                    <p class="description"><?php esc_html_e( 'Skills, qualifications, and experience required.', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="benefits"><?php esc_html_e( 'Benefits', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="benefits" name="benefits" rows="4" class="large-text"><?php echo esc_textarea( $job_data['benefits'] ); ?></textarea>
                                    <p class="description"><?php esc_html_e( 'Benefits and perks offered with this position.', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Publication Settings', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="status"><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <select id="status" name="status">
                                        <?php
                                        $statuses = $this->get_status_options();
                                        foreach ( $statuses as $status_key => $status_label ) :
                                        ?>
                                            <option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $job_data['status'], $status_key ); ?>>
                                                <?php echo esc_html( $status_label ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="expires_at"><?php esc_html_e( 'Expires On', 'sleeve-ke' ); ?></label>
                                </th>
                                <td>
                                    <input type="date" id="expires_at" name="expires_at" value="<?php echo esc_attr( $job_data['expires_at'] ); ?>" />
                                    <p class="description"><?php esc_html_e( 'Optional: Set when this job posting should expire.', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p class="submit">
                    <?php submit_button( $submit_text, 'primary', 'submit_job' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs' ) ); ?>" class="button">
                        <?php esc_html_e( 'Cancel', 'sleeve-ke' ); ?>
                    </a>
                </p>
            </form>
        </div>
        
        <style>
        .sleeve-ke-job-form .form-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sleeve-ke-job-form .form-section h2 {
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .sleeve-ke-job-form .required {
            color: #d63638;
        }
        </style>
        <?php
    }

    /**
     * Get status options for jobs
     */
    public function get_status_options() {
        return array(
            'draft' => __( 'Draft', 'sleeve-ke' ),
            'published' => __( 'Published', 'sleeve-ke' ),
            'archived' => __( 'Archived', 'sleeve-ke' ),
            'expired' => __( 'Expired', 'sleeve-ke' )
        );
    }

    /**
     * Get job types
     */
    public function get_job_types() {
        return array(
            'full-time' => __( 'Full-Time', 'sleeve-ke' ),
            'part-time' => __( 'Part-Time', 'sleeve-ke' ),
            'contract' => __( 'Contract', 'sleeve-ke' ),
            'temporary' => __( 'Temporary', 'sleeve-ke' ),
            'internship' => __( 'Internship', 'sleeve-ke' ),
            'freelance' => __( 'Freelance', 'sleeve-ke' )
        );
    }

    /**
     * Get sectors
     */
    public function get_sectors() {
        return array(
            'technology' => __( 'Technology & IT', 'sleeve-ke' ),
            'healthcare' => __( 'Healthcare & Medical', 'sleeve-ke' ),
            'finance' => __( 'Finance & Banking', 'sleeve-ke' ),
            'education' => __( 'Education & Training', 'sleeve-ke' ),
            'manufacturing' => __( 'Manufacturing & Production', 'sleeve-ke' ),
            'retail' => __( 'Retail & Sales', 'sleeve-ke' ),
            'hospitality' => __( 'Hospitality & Tourism', 'sleeve-ke' ),
            'agriculture' => __( 'Agriculture & Farming', 'sleeve-ke' ),
            'construction' => __( 'Construction & Real Estate', 'sleeve-ke' ),
            'telecommunications' => __( 'Telecommunications & Media', 'sleeve-ke' ),
            'legal' => __( 'Legal & Professional Services', 'sleeve-ke' ),
            'marketing' => __( 'Marketing & Advertising', 'sleeve-ke' ),
            'business' => __( 'Business & Consulting', 'sleeve-ke' ),
            'nonprofit' => __( 'Non-Profit & NGO', 'sleeve-ke' ),
            'government' => __( 'Government & Public Sector', 'sleeve-ke' ),
            'transport' => __( 'Transportation & Logistics', 'sleeve-ke' ),
            'energy' => __( 'Energy & Environment', 'sleeve-ke' ),
            'arts' => __( 'Arts & Creative', 'sleeve-ke' ),
            'sports' => __( 'Sports & Recreation', 'sleeve-ke' ),
            'other' => __( 'Other', 'sleeve-ke' )
        );
    }

    /**
     * Get jobs data (mock data for demonstration)
     */
    public function get_jobs_data() {
        // Apply filters if any
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $type_filter = isset( $_GET['job_type'] ) ? sanitize_text_field( $_GET['job_type'] ) : '';
        $sector_filter = isset( $_GET['sector'] ) ? sanitize_text_field( $_GET['sector'] ) : '';

        // Mock data - in real implementation, this would fetch from database
        $all_jobs = array(
            array(
                'id' => 1,
                'title' => 'Senior PHP Developer - Technology Sector',
                'sector' => 'technology',
                'description' => 'We are seeking a highly skilled Senior PHP Developer to join our dynamic technology team. You will be responsible for developing and maintaining web applications, collaborating with cross-functional teams, and mentoring junior developers.',
                'company' => 'Tech Solutions Ltd',
                'employer_type' => 'organization',
                'location' => 'Nairobi, Kenya',
                'job_type' => 'Full-Time',
                'experience_level' => 'senior',
                'requirements' => 'Bachelor\'s degree in Computer Science, 5+ years PHP experience, Laravel framework expertise, MySQL database skills, Git version control knowledge.',
                'benefits' => 'Health insurance, Flexible working hours, Remote work options, Professional development budget, Annual bonus',
                'salary_min' => 150000,
                'salary_max' => 300000,
                'currency' => 'KES',
                'remote_work' => 'hybrid',
                'status' => 'published',
                'applications_count' => 24,
                'posted_date' => '2025-10-10',
                'expires_at' => '2025-11-10',
                'employer_id' => 1
            ),
            array(
                'id' => 2,
                'title' => 'Frontend Developer - Creative Technology',
                'sector' => 'technology',
                'description' => 'Join our creative team as a Frontend Developer where you\'ll build beautiful, responsive user interfaces and collaborate with designers to bring innovative digital experiences to life.',
                'company' => 'Creative Agency',
                'employer_type' => 'organization',
                'location' => 'Remote',
                'job_type' => 'Full-Time',
                'experience_level' => 'mid',
                'requirements' => 'Strong JavaScript, HTML5, CSS3 skills, React or Vue.js experience, Responsive design expertise, UI/UX collaboration experience.',
                'benefits' => 'Fully remote work, Creative freedom, Latest equipment, Team retreats, Learning stipend',
                'salary_min' => 120000,
                'salary_max' => 250000,
                'currency' => 'KES',
                'remote_work' => 'full',
                'status' => 'published',
                'applications_count' => 18,
                'posted_date' => '2025-10-08',
                'expires_at' => '2025-11-08',
                'employer_id' => 2
            ),
            array(
                'id' => 3,
                'title' => 'Project Manager - Business Development',
                'sector' => 'business',
                'description' => 'Seeking an experienced Project Manager to lead cross-functional teams, manage project timelines, and ensure successful delivery of business initiatives.',
                'company' => 'Business Corp',
                'employer_type' => 'organization',
                'location' => 'Kampala, Uganda',
                'job_type' => 'Full-Time',
                'experience_level' => 'senior',
                'requirements' => 'PMP certification preferred, 5+ years project management experience, Agile/Scrum methodology, Strong communication skills.',
                'benefits' => 'Competitive salary, Health coverage, Professional certifications, Performance bonuses, Career advancement',
                'salary_min' => 200000,
                'salary_max' => 400000,
                'currency' => 'UGX',
                'remote_work' => 'no',
                'status' => 'published',
                'applications_count' => 12,
                'posted_date' => '2025-10-05',
                'expires_at' => '2025-12-05',
                'employer_id' => 3
            ),
            array(
                'id' => 4,
                'title' => 'Marketing Intern - Digital Marketing',
                'sector' => 'marketing',
                'description' => 'Great opportunity for a Marketing student or recent graduate to gain hands-on experience in digital marketing, social media management, and campaign development.',
                'company' => 'StartUp Inc',
                'employer_type' => 'organization',
                'location' => 'Dar es Salaam, Tanzania',
                'job_type' => 'Internship',
                'experience_level' => 'entry',
                'requirements' => 'Currently studying Marketing/Business or recent graduate, Social media knowledge, Basic graphic design skills, Eager to learn.',
                'benefits' => 'Mentorship program, Certificate of completion, Networking opportunities, Potential full-time offer',
                'salary_min' => 50000,
                'salary_max' => 80000,
                'currency' => 'TZS',
                'remote_work' => 'hybrid',
                'status' => 'draft',
                'applications_count' => 0,
                'posted_date' => '2025-10-15',
                'expires_at' => '2025-11-30',
                'employer_id' => 1
            ),
            array(
                'id' => 5,
                'title' => 'Freelance Graphic Designer - Healthcare Sector',
                'sector' => 'healthcare',
                'description' => 'Independent healthcare consultant seeking a talented graphic designer for ongoing branding and marketing materials for medical practices.',
                'company' => 'Dr. Sarah Kimani (Individual)',
                'employer_type' => 'individual',
                'location' => 'Mombasa, Kenya',
                'job_type' => 'Freelance',
                'experience_level' => 'mid',
                'requirements' => 'Portfolio of healthcare/medical designs, Adobe Creative Suite proficiency, Brand development experience, Professional communication.',
                'benefits' => 'Flexible schedule, Project-based pay, Creative autonomy, Potential long-term partnership',
                'salary_min' => 25000,
                'salary_max' => 50000,
                'currency' => 'KES',
                'remote_work' => 'full',
                'status' => 'published',
                'applications_count' => 8,
                'posted_date' => '2025-10-12',
                'expires_at' => '2025-11-12',
                'employer_id' => 4
            )
        );

        // Filter by current user if they're an employer
        if ( $this->is_employer() ) {
            $current_user_id = get_current_user_id();
            $all_jobs = array_filter( $all_jobs, function( $job ) use ( $current_user_id ) {
                return $job['employer_id'] === $current_user_id;
            });
        }

        // Apply filters
        $filtered_jobs = $all_jobs;

        if ( ! empty( $search ) ) {
            $filtered_jobs = array_filter( $filtered_jobs, function( $job ) use ( $search ) {
                return stripos( $job['title'], $search ) !== false || 
                       stripos( $job['company'], $search ) !== false;
            });
        }

        if ( ! empty( $status_filter ) ) {
            $filtered_jobs = array_filter( $filtered_jobs, function( $job ) use ( $status_filter ) {
                return $job['status'] === $status_filter;
            });
        }

        if ( ! empty( $type_filter ) ) {
            $filtered_jobs = array_filter( $filtered_jobs, function( $job ) use ( $type_filter ) {
                return strtolower( str_replace( '-', '-', $job['job_type'] ) ) === $type_filter;
            });
        }

        if ( ! empty( $sector_filter ) ) {
            $filtered_jobs = array_filter( $filtered_jobs, function( $job ) use ( $sector_filter ) {
                return $job['sector'] === $sector_filter;
            });
        }

        return $filtered_jobs;
    }

    /**
     * Get job statistics
     */
    public function get_job_stats() {
        $jobs = $this->get_jobs_data();
        $statuses = $this->get_status_options();
        
        $stats = array();
        $stats[] = array( 'count' => count( $jobs ), 'label' => __( 'Total Jobs', 'sleeve-ke' ) );
        
        foreach ( $statuses as $status_key => $status_label ) {
            $count = count( array_filter( $jobs, function( $job ) use ( $status_key ) {
                return $job['status'] === $status_key;
            }));
            $stats[] = array( 'count' => $count, 'label' => $status_label );
        }
        
        return $stats;
    }

    /**
     * Check if current user can add jobs
     */
    public function user_can_add_jobs() {
        return current_user_can( 'manage_options' ) || 
               current_user_can( 'manage_jobs' ) || 
               in_array( 'employer', wp_get_current_user()->roles ) ||
               in_array( 'sleve_admin', wp_get_current_user()->roles );
    }

    /**
     * Check if current user can manage all jobs
     */
    public function user_can_manage_all_jobs() {
        return current_user_can( 'manage_options' ) || 
               in_array( 'sleve_admin', wp_get_current_user()->roles );
    }

    /**
     * Check if current user can edit specific job
     */
    public function user_can_edit_job( $job ) {
        // Admins and sleve_admins can edit all jobs
        if ( $this->user_can_manage_all_jobs() ) {
            return true;
        }
        
        // Employers can only edit their own jobs
        if ( $this->is_employer() ) {
            return $job['employer_id'] === get_current_user_id();
        }
        
        return false;
    }

    /**
     * Check if current user can delete specific job
     */
    public function user_can_delete_job( $job ) {
        return $this->user_can_edit_job( $job );
    }

    /**
     * Check if current user is an employer
     */
    private function is_employer() {
        return in_array( 'employer', wp_get_current_user()->roles );
    }

    /**
     * Get job by ID
     */
    public function get_job_by_id( $job_id ) {
        $jobs = $this->get_jobs_data();
        foreach ( $jobs as $job ) {
            if ( $job['id'] == $job_id ) {
                return $job;
            }
        }
        return null;
    }

    /**
     * Display job view page
     */
    public function display_job_view( $job_id ) {
        $job = $this->get_job_by_id( $job_id );
        
        if ( ! $job ) {
            echo '<div class="wrap"><h1>' . __( 'Job Not Found', 'sleeve-ke' ) . '</h1></div>';
            return;
        }

        $statuses = $this->get_status_options();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Job Details', 'sleeve-ke' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs' ) ); ?>" class="button">
                <?php esc_html_e( '← Back to Jobs', 'sleeve-ke' ); ?>
            </a>
            
            <div class="sleeve-ke-job-details">
                <div class="job-header">
                    <h2><?php echo esc_html( $job['title'] ); ?></h2>
                    <div class="job-meta">
                        <span class="status-badge status-<?php echo esc_attr( $job['status'] ); ?>">
                            <?php echo esc_html( $statuses[ $job['status'] ] ); ?>
                        </span>
                        <?php if ( $this->user_can_edit_job( $job ) ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job['id'] ) ); ?>" class="button button-primary">
                                <?php esc_html_e( 'Edit Job', 'sleeve-ke' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="job-details-grid">
                    <div class="job-details-main">
                        <h3><?php esc_html_e( 'Job Information', 'sleeve-ke' ); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Sector', 'sleeve-ke' ); ?></th>
                                <td>
                                    <?php 
                                    $sectors = $this->get_sectors();
                                    echo esc_html( isset( $sectors[ $job['sector'] ] ) ? $sectors[ $job['sector'] ] : $job['sector'] );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Employer', 'sleeve-ke' ); ?></th>
                                <td>
                                    <?php echo esc_html( $job['company'] ); ?>
                                    <span class="employer-type">
                                        (<?php echo esc_html( $job['employer_type'] === 'individual' ? __( 'Individual', 'sleeve-ke' ) : __( 'Organization', 'sleeve-ke' ) ); ?>)
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Location', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $job['location'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Job Type', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $job['job_type'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Experience Level', 'sleeve-ke' ); ?></th>
                                <td>
                                    <?php 
                                    $levels = array(
                                        'entry' => __( 'Entry Level', 'sleeve-ke' ),
                                        'mid' => __( 'Mid Level', 'sleeve-ke' ),
                                        'senior' => __( 'Senior Level', 'sleeve-ke' ),
                                        'executive' => __( 'Executive Level', 'sleeve-ke' )
                                    );
                                    echo esc_html( isset( $levels[ $job['experience_level'] ] ) ? $levels[ $job['experience_level'] ] : $job['experience_level'] );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Remote Work', 'sleeve-ke' ); ?></th>
                                <td>
                                    <?php 
                                    $remote_options = array(
                                        'no' => __( 'No Remote Work', 'sleeve-ke' ),
                                        'hybrid' => __( 'Hybrid', 'sleeve-ke' ),
                                        'full' => __( 'Fully Remote', 'sleeve-ke' )
                                    );
                                    echo esc_html( isset( $remote_options[ $job['remote_work'] ] ) ? $remote_options[ $job['remote_work'] ] : $job['remote_work'] );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Salary Range', 'sleeve-ke' ); ?></th>
                                <td>
                                    <?php if ( ! empty( $job['salary_min'] ) && ! empty( $job['salary_max'] ) ) : ?>
                                        <?php echo esc_html( number_format( $job['salary_min'] ) . ' - ' . number_format( $job['salary_max'] ) . ' ' . $job['currency'] ); ?>
                                    <?php elseif ( ! empty( $job['salary_min'] ) ) : ?>
                                        <?php echo esc_html( number_format( $job['salary_min'] ) . '+ ' . $job['currency'] ); ?>
                                    <?php else : ?>
                                        <em><?php esc_html_e( 'Not specified', 'sleeve-ke' ); ?></em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Posted Date', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( date( 'F j, Y', strtotime( $job['posted_date'] ) ) ); ?></td>
                            </tr>
                            <?php if ( ! empty( $job['expires_at'] ) ) : ?>
                            <tr>
                                <th><?php esc_html_e( 'Expires On', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( date( 'F j, Y', strtotime( $job['expires_at'] ) ) ); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th><?php esc_html_e( 'Applications', 'sleeve-ke' ); ?></th>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications&job_id=' . $job['id'] ) ); ?>" class="button button-secondary">
                                        <span class="dashicons dashicons-groups"></span>
                                        <?php echo esc_html( $job['applications_count'] ); ?> <?php esc_html_e( 'applications', 'sleeve-ke' ); ?>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="job-details-content">
                        <div class="job-section">
                            <h3><?php esc_html_e( 'Job Description', 'sleeve-ke' ); ?></h3>
                            <div class="job-content">
                                <?php echo wp_kses_post( wpautop( $job['description'] ) ); ?>
                            </div>
                        </div>
                        
                        <?php if ( ! empty( $job['requirements'] ) ) : ?>
                        <div class="job-section">
                            <h3><?php esc_html_e( 'Requirements', 'sleeve-ke' ); ?></h3>
                            <div class="job-content">
                                <?php echo wp_kses_post( wpautop( $job['requirements'] ) ); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $job['benefits'] ) ) : ?>
                        <div class="job-section">
                            <h3><?php esc_html_e( 'Benefits', 'sleeve-ke' ); ?></h3>
                            <div class="job-content">
                                <?php echo wp_kses_post( wpautop( $job['benefits'] ) ); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle job actions
     */
    public function handle_job_actions() {
        if ( isset( $_POST['job_action'] ) && wp_verify_nonce( $_POST['job_form_nonce'], 'sleeve_job_form' ) ) {
            $action = sanitize_text_field( $_POST['job_action'] );
            
            switch ( $action ) {
                case 'create':
                    $this->create_job();
                    break;
                case 'update':
                    $this->update_job();
                    break;
            }
        }
        
        if ( isset( $_POST['apply_bulk_action'] ) && isset( $_POST['bulk_action'] ) && isset( $_POST['job_ids'] ) ) {
            $this->handle_bulk_actions();
        }
    }

    /**
     * Create new job
     */
    private function create_job() {
        if ( ! $this->user_can_add_jobs() ) {
            wp_die( __( 'You do not have permission to create jobs.', 'sleeve-ke' ) );
        }

        // Here you would normally insert into database
        // For now, just show success message
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__( 'Job created successfully!', 'sleeve-ke' ) . 
                 '</p></div>';
        });

        // Redirect to jobs list
        wp_redirect( admin_url( 'admin.php?page=sleeve-ke-jobs' ) );
        exit;
    }

    /**
     * Update existing job
     */
    private function update_job() {
        $job_id = intval( $_POST['job_id'] );
        $job = $this->get_job_by_id( $job_id );
        
        if ( ! $job || ! $this->user_can_edit_job( $job ) ) {
            wp_die( __( 'You do not have permission to edit this job.', 'sleeve-ke' ) );
        }

        // Here you would normally update the database
        // For now, just show success message
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__( 'Job updated successfully!', 'sleeve-ke' ) . 
                 '</p></div>';
        });

        // Redirect to jobs list
        wp_redirect( admin_url( 'admin.php?page=sleeve-ke-jobs' ) );
        exit;
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        if ( ! $this->user_can_manage_all_jobs() ) {
            wp_die( __( 'You do not have permission to perform bulk actions.', 'sleeve-ke' ) );
        }

        $action = sanitize_text_field( $_POST['bulk_action'] );
        $job_ids = array_map( 'intval', $_POST['job_ids'] );
        
        // Here you would normally update the database
        // For now, just show success message
        $message = '';
        switch ( $action ) {
            case 'publish':
                $message = __( 'Jobs published successfully.', 'sleeve-ke' );
                break;
            case 'draft':
                $message = __( 'Jobs moved to draft.', 'sleeve-ke' );
                break;
            case 'archive':
                $message = __( 'Jobs archived.', 'sleeve-ke' );
                break;
            case 'delete':
                $message = __( 'Jobs deleted.', 'sleeve-ke' );
                break;
        }

        if ( $message ) {
            add_action( 'admin_notices', function() use ( $message ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     esc_html( $message ) . 
                     '</p></div>';
            });
        }
    }

    /**
     * Handle AJAX request to update job status
     */
    public function ajax_update_job_status() {
        // Check nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'update_job_status' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'sleeve-ke' ) ) );
        }
        
        $job_id = intval( $_POST['job_id'] );
        $status = sanitize_text_field( $_POST['status'] );
        $job = $this->get_job_by_id( $job_id );
        
        // Check permissions
        if ( ! $job || ! $this->user_can_edit_job( $job ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'sleeve-ke' ) ) );
        }
        
        // Validate status
        $valid_statuses = array_keys( $this->get_status_options() );
        if ( ! in_array( $status, $valid_statuses ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid status', 'sleeve-ke' ) ) );
        }
        
        // Here you would normally update the database
        // For now, we'll just simulate success
        
        wp_send_json_success( array( 
            'message' => __( 'Job status updated successfully', 'sleeve-ke' ),
            'job_id' => $job_id,
            'new_status' => $status
        ) );
    }
}