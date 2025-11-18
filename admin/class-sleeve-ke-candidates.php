<?php
/**
 * Candidates management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Candidates management class.
 *
 * Handles all functionality related to candidate management
 * including registration, profile management, and job applications.
 * Candidates can register themselves to apply for jobs.
 */
class Sleeve_KE_Candidates {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor can be used for initialization if needed
    }

    /**
     * Display the candidates management page.
     */
    public function display_page() {
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['sleeve_nonce'], 'sleeve_candidates' ) ) {
            $this->handle_candidate_actions();
        }
        
        // Check if we're adding/editing/viewing a candidate
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
        $candidate_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        
        switch ( $action ) {
            case 'add':
                $this->display_add_candidate_form();
                break;
            case 'edit':
                $this->display_edit_candidate_form( $candidate_id );
                break;
            case 'view':
                $this->display_candidate_view( $candidate_id );
                break;
            default:
                $this->display_candidates_list();
                break;
        }
    }

    /**
     * Display the candidates list page.
     */
    private function display_candidates_list() {
        // Get candidates data
        $candidates = $this->get_candidates_data();
        $statuses = $this->get_status_options();
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( 'Candidates', 'sleeve-ke' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates&action=add' ) ); ?>" class="page-title-action">
                    <?php esc_html_e( 'Add New Candidate', 'sleeve-ke' ); ?>
                </a>
            </h1>
            
            <?php if ( isset( $_GET['candidate_created'] ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Candidate created successfully!', 'sleeve-ke' ); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ( isset( $_GET['candidate_updated'] ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Candidate updated successfully!', 'sleeve-ke' ); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Filter and Search Section -->
            <div class="sleeve-ke-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="sleeve-ke-candidates" />
                    
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="<?php esc_attr_e( 'Search by name, email, or skills...', 'sleeve-ke' ); ?>" 
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
                        
                        <select name="experience_level">
                            <option value=""><?php esc_html_e( 'All Experience Levels', 'sleeve-ke' ); ?></option>
                            <?php
                            $experience_levels = $this->get_experience_levels();
                            foreach ( $experience_levels as $level_key => $level_label ) :
                            ?>
                                <option value="<?php echo esc_attr( $level_key ); ?>" 
                                        <?php selected( isset( $_GET['experience_level'] ) ? $_GET['experience_level'] : '', $level_key ); ?>>
                                    <?php echo esc_html( $level_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button( __( 'Filter', 'sleeve-ke' ), 'secondary', 'filter', false ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates' ) ); ?>" class="button">
                            <?php esc_html_e( 'Clear', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Candidates Table -->
            <form method="post" action="">
                <?php wp_nonce_field( 'sleeve_candidates', 'sleeve_nonce' ); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value=""><?php esc_html_e( 'Bulk Actions', 'sleeve-ke' ); ?></option>
                            <option value="approve"><?php esc_html_e( 'Approve', 'sleeve-ke' ); ?></option>
                            <option value="pending"><?php esc_html_e( 'Set Pending', 'sleeve-ke' ); ?></option>
                            <option value="suspend"><?php esc_html_e( 'Suspend', 'sleeve-ke' ); ?></option>
                            <option value="delete"><?php esc_html_e( 'Delete', 'sleeve-ke' ); ?></option>
                        </select>
                        <?php submit_button( __( 'Apply', 'sleeve-ke' ), 'action', 'apply_bulk_action', false ); ?>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped sleeve-ke-candidates-table">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all" />
                            </td>
                            <th class="manage-column"><?php esc_html_e( 'Candidate', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Email', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Phone', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Experience', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Skills', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Applications', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Registered', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Actions', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $candidates ) ) : ?>
                            <tr>
                                <td colspan="11" class="no-items">
                                    <?php esc_html_e( 'No candidates found.', 'sleeve-ke' ); ?>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates&action=add' ) ); ?>">
                                        <?php esc_html_e( 'Add first candidate', 'sleeve-ke' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $candidates as $candidate ) : ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="candidate_ids[]" value="<?php echo esc_attr( $candidate['id'] ); ?>" />
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates&action=view&id=' . $candidate['id'] ) ); ?>">
                                                <?php echo esc_html( $candidate['full_name'] ); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates&action=view&id=' . $candidate['id'] ) ); ?>"><?php esc_html_e( 'View', 'sleeve-ke' ); ?></a> | </span>
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates&action=edit&id=' . $candidate['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'sleeve-ke' ); ?></a> | </span>
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates&action=delete&id=' . $candidate['id'] ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'sleeve-ke' ); ?>')" class="delete"><?php esc_html_e( 'Delete', 'sleeve-ke' ); ?></a></span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html( $candidate['email'] ); ?></td>
                                    <td><?php echo esc_html( $candidate['phone'] ); ?></td>
                                    <td><?php echo esc_html( $candidate['location'] ); ?></td>
                                    <td>
                                        <?php 
                                        $experience_levels = $this->get_experience_levels();
                                        echo esc_html( isset( $experience_levels[ $candidate['experience_level'] ] ) ? $experience_levels[ $candidate['experience_level'] ] : $candidate['experience_level'] );
                                        ?>
                                    </td>
                                    <td>
                                        <div class="skills-preview">
                                            <?php 
                                            $skills = array_slice( explode( ', ', $candidate['skills'] ), 0, 3 );
                                            echo esc_html( implode( ', ', $skills ) );
                                            if ( count( explode( ', ', $candidate['skills'] ) ) > 3 ) {
                                                echo '...';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $candidate['status'] ); ?>">
                                            <?php echo esc_html( $statuses[ $candidate['status'] ] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications&candidate_id=' . $candidate['id'] ) ); ?>">
                                            <?php echo esc_html( $candidate['applications_count'] ); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html( $candidate['registered_date'] ); ?></td>
                                    <td>
                                        <select class="status-select" data-candidate-id="<?php echo esc_attr( $candidate['id'] ); ?>">
                                            <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                                <option value="<?php echo esc_attr( $status_key ); ?>" 
                                                        <?php selected( $candidate['status'], $status_key ); ?>>
                                                    <?php echo esc_html( $status_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
            
            <!-- Statistics Section -->
            <div class="sleeve-ke-candidates-stats">
                <h3><?php esc_html_e( 'Candidate Statistics', 'sleeve-ke' ); ?></h3>
                <div class="stats-grid">
                    <?php
                    $stats = $this->get_candidate_stats();
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
                var candidateId = $(this).data('candidate-id');
                var newStatus = $(this).val();
                
                $.post(ajaxurl, {
                    action: 'update_candidate_status',
                    candidate_id: candidateId,
                    status: newStatus,
                    nonce: '<?php echo wp_create_nonce( 'update_candidate_status' ); ?>'
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
                $('input[name="candidate_ids[]"]').prop('checked', this.checked);
            });
        });
        </script>
        <?php
    }

    /**
     * Get status options for candidates
     */
    public function get_status_options() {
        return array(
            'pending' => __( 'Pending Review', 'sleeve-ke' ),
            'approved' => __( 'Approved', 'sleeve-ke' ),
            'active' => __( 'Active', 'sleeve-ke' ),
            'suspended' => __( 'Suspended', 'sleeve-ke' ),
            'inactive' => __( 'Inactive', 'sleeve-ke' )
        );
    }

    /**
     * Get experience levels
     */
    public function get_experience_levels() {
        return array(
            'entry' => __( 'Entry Level (0-2 years)', 'sleeve-ke' ),
            'junior' => __( 'Junior Level (2-4 years)', 'sleeve-ke' ),
            'mid' => __( 'Mid Level (4-7 years)', 'sleeve-ke' ),
            'senior' => __( 'Senior Level (7-12 years)', 'sleeve-ke' ),
            'expert' => __( 'Expert Level (12+ years)', 'sleeve-ke' ),
            'executive' => __( 'Executive Level', 'sleeve-ke' )
        );
    }

    /**
     * Get candidates data (mock data for demonstration)
     */
    public function get_candidates_data() {
        global $wpdb;
        
        // Apply filters if any
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $experience_filter = isset( $_GET['experience_level'] ) ? sanitize_text_field( $_GET['experience_level'] ) : '';

        // Query real data from wp_sleeve_candidates table
        $table_name = $wpdb->prefix . 'sleeve_candidates';
        
        $sql = "SELECT c.*, 
                u.user_login, 
                u.user_email,
                u.display_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}sleeve_applications WHERE candidate_id = c.user_id) as applications_count
                FROM $table_name c
                LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
                WHERE 1=1";
        
        // Apply search filter
        if (!empty($search)) {
            $sql .= $wpdb->prepare(" AND (u.display_name LIKE %s OR u.user_email LIKE %s OR c.skills LIKE %s OR c.location LIKE %s)", 
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        // Apply experience filter
        if (!empty($experience_filter)) {
            if ($experience_filter === 'entry') {
                $sql .= " AND c.experience_years <= 2";
            } elseif ($experience_filter === 'junior') {
                $sql .= " AND c.experience_years BETWEEN 2 AND 4";
            } elseif ($experience_filter === 'mid') {
                $sql .= " AND c.experience_years BETWEEN 4 AND 7";
            } elseif ($experience_filter === 'senior') {
                $sql .= " AND c.experience_years BETWEEN 7 AND 12";
            } elseif ($experience_filter === 'expert') {
                $sql .= " AND c.experience_years >= 12";
            }
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        // Format data for display
        $all_candidates = array();
        
        foreach ($results as $row) {
            // Map experience years to experience level
            $experience_level = 'entry';
            if ($row['experience_years'] >= 12) {
                $experience_level = 'expert';
            } elseif ($row['experience_years'] >= 7) {
                $experience_level = 'senior';
            } elseif ($row['experience_years'] >= 4) {
                $experience_level = 'mid';
            } elseif ($row['experience_years'] >= 2) {
                $experience_level = 'junior';
            }
            
            // Get status from user_meta
            $status = get_user_meta($row['user_id'], 'account_status', true);
            $status = !empty($status) ? $status : 'pending';
            
            $all_candidates[] = array(
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'full_name' => $row['display_name'],
                'email' => $row['user_email'],
                'phone' => $row['phone'] ?? __('N/A', 'sleeve-ke'),
                'location' => $row['location'] ?? __('N/A', 'sleeve-ke'),
                'experience_level' => $experience_level,
                'skills' => $row['skills'] ?? __('No skills listed', 'sleeve-ke'),
                'education' => $row['education'] ?? '',
                'resume_url' => $row['resume_url'] ?? '',
                'linkedin_url' => $row['linkedin_url'] ?? '',
                'portfolio_url' => $row['portfolio_url'] ?? '',
                'availability' => $row['availability'] ?? 'available',
                'status' => $status,
                'applications_count' => $row['applications_count'] ?? 0,
                'registered_date' => date('Y-m-d', strtotime($row['created_at'])),
                'last_active' => date('Y-m-d', strtotime($row['updated_at']))
            );
        }

        return $all_candidates;
    }

    /**
     * Get candidate statistics
     */
    public function get_candidate_stats() {
        $candidates = $this->get_candidates_data();
        $statuses = $this->get_status_options();
        
        $stats = array();
        $stats[] = array( 'count' => count( $candidates ), 'label' => __( 'Total Candidates', 'sleeve-ke' ) );
        
        foreach ( $statuses as $status_key => $status_label ) {
            $count = count( array_filter( $candidates, function( $candidate ) use ( $status_key ) {
                return $candidate['status'] === $status_key;
            }));
            if ( $count > 0 ) {
                $stats[] = array( 'count' => $count, 'label' => $status_label );
            }
        }
        
        return $stats;
    }

    /**
     * Display add candidate form.
     */
    private function display_add_candidate_form() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Add New Candidate', 'sleeve-ke' ); ?></h1>
            
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates' ) ); ?>" class="button">
                    &larr; <?php esc_html_e( 'Back to Candidates', 'sleeve-ke' ); ?>
                </a>
            </p>

            <form method="post" action="" class="sleeve-ke-candidate-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'sleeve_candidates', 'sleeve_nonce' ); ?>
                <input type="hidden" name="action" value="create_candidate" />
                
                <div class="form-container">
                    <!-- Account Information -->
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Account Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="username"><?php esc_html_e( 'Username', 'sleeve-ke' ); ?> *</label></th>
                                <td>
                                    <input type="text" id="username" name="username" required class="regular-text" />
                                    <p class="description"><?php esc_html_e( 'Used for login', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="email"><?php esc_html_e( 'Email Address', 'sleeve-ke' ); ?> *</label></th>
                                <td>
                                    <input type="email" id="email" name="email" required class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="password"><?php esc_html_e( 'Password', 'sleeve-ke' ); ?> *</label></th>
                                <td>
                                    <input type="password" id="password" name="password" required class="regular-text" />
                                    <p class="description"><?php esc_html_e( 'Minimum 8 characters', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Personal Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="first_name"><?php esc_html_e( 'First Name', 'sleeve-ke' ); ?> *</label></th>
                                <td><input type="text" id="first_name" name="first_name" required class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><label for="last_name"><?php esc_html_e( 'Last Name', 'sleeve-ke' ); ?> *</label></th>
                                <td><input type="text" id="last_name" name="last_name" required class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><label for="phone"><?php esc_html_e( 'Phone Number', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="tel" id="phone" name="phone" class="regular-text" placeholder="+254 700 123 456" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="location"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?> *</label></th>
                                <td>
                                    <input type="text" id="location" name="location" required class="regular-text" placeholder="City, Country" />
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Professional Information -->
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Professional Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="experience_years"><?php esc_html_e( 'Years of Experience', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="number" id="experience_years" name="experience_years" min="0" max="50" value="0" class="small-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="education"><?php esc_html_e( 'Education', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="text" id="education" name="education" class="regular-text" placeholder="e.g., Bachelor of Computer Science" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="skills"><?php esc_html_e( 'Skills', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <textarea id="skills" name="skills" rows="3" class="large-text" placeholder="e.g., PHP, JavaScript, Project Management"></textarea>
                                    <p class="description"><?php esc_html_e( 'Comma-separated list of skills', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="resume_file"><?php esc_html_e( 'Resume/CV', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="file" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx" />
                                    <p class="description"><?php esc_html_e( 'Upload PDF or Word document (Max 5MB)', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="linkedin_url"><?php esc_html_e( 'LinkedIn Profile', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="url" id="linkedin_url" name="linkedin_url" class="regular-text" placeholder="https://linkedin.com/in/username" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="portfolio_url"><?php esc_html_e( 'Portfolio URL', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="url" id="portfolio_url" name="portfolio_url" class="regular-text" placeholder="https://yourportfolio.com" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="availability"><?php esc_html_e( 'Availability', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <select id="availability" name="availability" class="regular-text">
                                        <option value="available"><?php esc_html_e( 'Available', 'sleeve-ke' ); ?></option>
                                        <option value="employed"><?php esc_html_e( 'Currently Employed', 'sleeve-ke' ); ?></option>
                                        <option value="not_looking"><?php esc_html_e( 'Not Looking', 'sleeve-ke' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <?php submit_button( __( 'Create Candidate Account', 'sleeve-ke' ) ); ?>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Display edit candidate form.
     */
    private function display_edit_candidate_form( $candidate_id ) {
        global $wpdb;
        
        // Get candidate data
        $table_name = $wpdb->prefix . 'sleeve_candidates';
        $candidate = $wpdb->get_row( $wpdb->prepare( 
            "SELECT c.*, u.user_login, u.user_email, u.display_name 
            FROM $table_name c 
            LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID 
            WHERE c.id = %d", 
            $candidate_id 
        ), ARRAY_A );
        
        if ( ! $candidate ) {
            wp_die( __( 'Candidate not found.', 'sleeve-ke' ) );
        }
        
        $user_data = get_userdata( $candidate['user_id'] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Edit Candidate', 'sleeve-ke' ); ?></h1>
            
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-candidates' ) ); ?>" class="button">
                    &larr; <?php esc_html_e( 'Back to Candidates', 'sleeve-ke' ); ?>
                </a>
            </p>

            <form method="post" action="" class="sleeve-ke-candidate-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'sleeve_candidates', 'sleeve_nonce' ); ?>
                <input type="hidden" name="action" value="update_candidate" />
                <input type="hidden" name="candidate_id" value="<?php echo esc_attr( $candidate_id ); ?>" />
                <input type="hidden" name="user_id" value="<?php echo esc_attr( $candidate['user_id'] ); ?>" />
                
                <div class="form-container">
                    <!-- Account Information -->
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Account Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label><?php esc_html_e( 'Username', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <strong><?php echo esc_html( $candidate['user_login'] ); ?></strong>
                                    <p class="description"><?php esc_html_e( 'Username cannot be changed', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="email"><?php esc_html_e( 'Email Address', 'sleeve-ke' ); ?> *</label></th>
                                <td>
                                    <input type="email" id="email" name="email" required class="regular-text" 
                                           value="<?php echo esc_attr( $candidate['user_email'] ); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="password"><?php esc_html_e( 'New Password', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="password" id="password" name="password" class="regular-text" />
                                    <p class="description"><?php esc_html_e( 'Leave blank to keep current password', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Personal Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="first_name"><?php esc_html_e( 'First Name', 'sleeve-ke' ); ?> *</label></th>
                                <td><input type="text" id="first_name" name="first_name" required class="regular-text" 
                                           value="<?php echo esc_attr( $user_data->first_name ); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label for="last_name"><?php esc_html_e( 'Last Name', 'sleeve-ke' ); ?> *</label></th>
                                <td><input type="text" id="last_name" name="last_name" required class="regular-text" 
                                           value="<?php echo esc_attr( $user_data->last_name ); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label for="phone"><?php esc_html_e( 'Phone Number', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="tel" id="phone" name="phone" class="regular-text" 
                                           value="<?php echo esc_attr( $candidate['phone'] ); ?>" placeholder="+254 700 123 456" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="location"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?> *</label></th>
                                <td>
                                    <input type="text" id="location" name="location" required class="regular-text" 
                                           value="<?php echo esc_attr( $candidate['location'] ); ?>" placeholder="City, Country" />
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Professional Information -->
                    <div class="form-section">
                        <h2><?php esc_html_e( 'Professional Information', 'sleeve-ke' ); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="experience_years"><?php esc_html_e( 'Years of Experience', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="number" id="experience_years" name="experience_years" min="0" max="50" 
                                           value="<?php echo esc_attr( $candidate['experience_years'] ); ?>" class="small-text" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="education"><?php esc_html_e( 'Education', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="text" id="education" name="education" class="regular-text" 
                                           value="<?php echo esc_attr( $candidate['education'] ); ?>" placeholder="e.g., Bachelor of Computer Science" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="skills"><?php esc_html_e( 'Skills', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <textarea id="skills" name="skills" rows="3" class="large-text" placeholder="e.g., PHP, JavaScript, Project Management"><?php echo esc_textarea( $candidate['skills'] ); ?></textarea>
                                    <p class="description"><?php esc_html_e( 'Comma-separated list of skills', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="resume_file"><?php esc_html_e( 'Resume/CV', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <?php if ( ! empty( $candidate['resume_url'] ) ) : ?>
                                        <p>
                                            <strong><?php esc_html_e( 'Current:', 'sleeve-ke' ); ?></strong> 
                                            <a href="<?php echo esc_url( $candidate['resume_url'] ); ?>" target="_blank">
                                                <?php esc_html_e( 'View Resume', 'sleeve-ke' ); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    <input type="file" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx" />
                                    <p class="description"><?php esc_html_e( 'Upload new resume to replace current one (PDF or Word, Max 5MB)', 'sleeve-ke' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="linkedin_url"><?php esc_html_e( 'LinkedIn Profile', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="url" id="linkedin_url" name="linkedin_url" class="regular-text" 
                                           value="<?php echo esc_attr( $candidate['linkedin_url'] ); ?>" placeholder="https://linkedin.com/in/username" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="portfolio_url"><?php esc_html_e( 'Portfolio URL', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <input type="url" id="portfolio_url" name="portfolio_url" class="regular-text" 
                                           value="<?php echo esc_attr( $candidate['portfolio_url'] ); ?>" placeholder="https://yourportfolio.com" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="availability"><?php esc_html_e( 'Availability', 'sleeve-ke' ); ?></label></th>
                                <td>
                                    <select id="availability" name="availability" class="regular-text">
                                        <option value="available" <?php selected( $candidate['availability'], 'available' ); ?>><?php esc_html_e( 'Available', 'sleeve-ke' ); ?></option>
                                        <option value="employed" <?php selected( $candidate['availability'], 'employed' ); ?>><?php esc_html_e( 'Currently Employed', 'sleeve-ke' ); ?></option>
                                        <option value="not_looking" <?php selected( $candidate['availability'], 'not_looking' ); ?>><?php esc_html_e( 'Not Looking', 'sleeve-ke' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <?php submit_button( __( 'Update Candidate', 'sleeve-ke' ) ); ?>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Display candidate view page.
     */
    public function display_candidate_view( $candidate_id ) {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Candidate Details', 'sleeve-ke' ) . '</h1>';
        echo '<p>' . esc_html__( 'Detailed view for candidate with ID: ', 'sleeve-ke' ) . esc_html( $candidate_id ) . '</p>';
        echo '</div>';
    }

    /**
     * Handle candidate actions
     */
    public function handle_candidate_actions() {
        $action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
        
        // Handle candidate creation
        if ( $action === 'create_candidate' ) {
            $this->create_candidate();
            return;
        }
        
        // Handle candidate update
        if ( $action === 'update_candidate' ) {
            $this->update_candidate();
            return;
        }
        
        // Handle form submissions and bulk actions
        if ( isset( $_POST['apply_bulk_action'] ) && isset( $_POST['bulk_action'] ) && isset( $_POST['candidate_ids'] ) ) {
            $this->handle_bulk_actions();
        }
    }
    
    /**
     * Create new candidate from admin
     */
    private function create_candidate() {
        global $wpdb;
        
        error_log('=== ADMIN CANDIDATE CREATION START ===');
        
        // Sanitize input
        $username = sanitize_user( $_POST['username'] );
        $email = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];
        $first_name = sanitize_text_field( $_POST['first_name'] );
        $last_name = sanitize_text_field( $_POST['last_name'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $location = sanitize_text_field( $_POST['location'] );
        $experience_years = intval( $_POST['experience_years'] );
        $education = sanitize_text_field( $_POST['education'] );
        $skills = sanitize_textarea_field( $_POST['skills'] );
        $linkedin_url = esc_url_raw( $_POST['linkedin_url'] );
        $portfolio_url = esc_url_raw( $_POST['portfolio_url'] );
        $availability = sanitize_text_field( $_POST['availability'] );
        
        // Validate
        if ( username_exists( $username ) ) {
            wp_die( __( 'Username already exists.', 'sleeve-ke' ) );
        }
        
        if ( email_exists( $email ) ) {
            wp_die( __( 'Email already exists.', 'sleeve-ke' ) );
        }
        
        // Create WordPress user
        $user_id = wp_create_user( $username, $password, $email );
        
        if ( is_wp_error( $user_id ) ) {
            error_log('Failed to create user: ' . $user_id->get_error_message());
            wp_die( $user_id->get_error_message() );
        }
        
        error_log('User created with ID: ' . $user_id);
        
        // Set user role
        $user = new WP_User( $user_id );
        $user->set_role( 'candidate' );
        
        // Update user profile
        wp_update_user( array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ) );
        
        // Handle resume upload
        $resume_url = null;
        if ( ! empty( $_FILES['resume_file']['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            
            $upload = wp_handle_upload( $_FILES['resume_file'], array( 'test_form' => false ) );
            
            if ( isset( $upload['file'] ) && ! isset( $upload['error'] ) ) {
                $resume_url = $upload['url'];
                error_log('Resume uploaded: ' . $resume_url);
            } else {
                error_log('Resume upload failed: ' . $upload['error']);
            }
        }
        
        // Insert into wp_sleeve_candidates
        $table_name = $wpdb->prefix . 'sleeve_candidates';
        
        $candidate_data = array(
            'user_id' => $user_id,
            'phone' => $phone,
            'location' => $location,
            'experience_years' => $experience_years,
            'education' => $education,
            'skills' => $skills,
            'resume_url' => $resume_url,
            'linkedin_url' => $linkedin_url,
            'portfolio_url' => $portfolio_url,
            'availability' => $availability
        );
        
        error_log('Candidate data: ' . print_r($candidate_data, true));
        
        $insert_result = $wpdb->insert( $table_name, $candidate_data );
        
        if ( $insert_result === false ) {
            error_log('Database insert FAILED: ' . $wpdb->last_error);
            wp_delete_user( $user_id );
            wp_die( __( 'Failed to create candidate profile: ', 'sleeve-ke' ) . $wpdb->last_error );
        }
        
        error_log('Candidate created successfully. ID: ' . $wpdb->insert_id);
        
        // Set user meta
        update_user_meta( $user_id, 'account_status', 'active' );
        
        error_log('=== ADMIN CANDIDATE CREATION END ===');
        
        // Redirect to candidates list with success message
        wp_redirect( add_query_arg( 'candidate_created', 1, admin_url( 'admin.php?page=sleeve-ke-candidates' ) ) );
        exit;
    }

    /**
     * Update existing candidate
     */
    private function update_candidate() {
        global $wpdb;
        
        error_log('=== ADMIN CANDIDATE UPDATE START ===');
        
        // Get and validate candidate ID
        $candidate_id = intval( $_POST['candidate_id'] );
        $user_id = intval( $_POST['user_id'] );
        
        $table_name = $wpdb->prefix . 'sleeve_candidates';
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $candidate_id ), ARRAY_A );
        
        if ( ! $existing ) {
            wp_die( __( 'Candidate not found.', 'sleeve-ke' ) );
        }
        
        error_log('Updating candidate ID: ' . $candidate_id . ', User ID: ' . $user_id);
        
        // Sanitize input
        $email = sanitize_email( $_POST['email'] );
        $password = isset( $_POST['password'] ) && ! empty( $_POST['password'] ) ? $_POST['password'] : '';
        $first_name = sanitize_text_field( $_POST['first_name'] );
        $last_name = sanitize_text_field( $_POST['last_name'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $location = sanitize_text_field( $_POST['location'] );
        $experience_years = intval( $_POST['experience_years'] );
        $education = sanitize_text_field( $_POST['education'] );
        $skills = sanitize_textarea_field( $_POST['skills'] );
        $linkedin_url = esc_url_raw( $_POST['linkedin_url'] );
        $portfolio_url = esc_url_raw( $_POST['portfolio_url'] );
        $availability = sanitize_text_field( $_POST['availability'] );
        
        // Check if email changed and if it exists for another user
        $user = get_userdata( $user_id );
        if ( $user->user_email !== $email && email_exists( $email ) ) {
            wp_die( __( 'Email already exists for another user.', 'sleeve-ke' ) );
        }
        
        // Update WordPress user data
        $user_data = array(
            'ID' => $user_id,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        );
        
        if ( ! empty( $password ) ) {
            $user_data['user_pass'] = $password;
            error_log('Password will be updated');
        }
        
        $update_user_result = wp_update_user( $user_data );
        
        if ( is_wp_error( $update_user_result ) ) {
            error_log('Failed to update user: ' . $update_user_result->get_error_message());
            wp_die( $update_user_result->get_error_message() );
        }
        
        error_log('User data updated successfully');
        
        // Handle resume upload if new file provided
        $resume_url = $existing['resume_url']; // Keep existing by default
        
        if ( ! empty( $_FILES['resume_file']['name'] ) ) {
            error_log('New resume file uploaded, processing...');
            
            $upload = wp_handle_upload( 
                $_FILES['resume_file'], 
                array( 
                    'test_form' => false,
                    'mimes' => array(
                        'pdf' => 'application/pdf',
                        'doc' => 'application/msword',
                        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    )
                ) 
            );
            
            if ( ! isset( $upload['error'] ) ) {
                // Delete old resume if exists
                if ( ! empty( $existing['resume_url'] ) ) {
                    $old_file = str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $existing['resume_url'] );
                    if ( file_exists( $old_file ) ) {
                        unlink( $old_file );
                        error_log('Old resume deleted: ' . $old_file);
                    }
                }
                
                $resume_url = $upload['url'];
                error_log('New resume uploaded: ' . $resume_url);
            } else {
                error_log('Resume upload failed: ' . $upload['error']);
                wp_die( __( 'Resume upload failed: ', 'sleeve-ke' ) . $upload['error'] );
            }
        }
        
        // Update candidate data
        $candidate_data = array(
            'phone' => $phone,
            'location' => $location,
            'experience_years' => $experience_years,
            'education' => $education,
            'skills' => $skills,
            'resume_url' => $resume_url,
            'linkedin_url' => $linkedin_url,
            'portfolio_url' => $portfolio_url,
            'availability' => $availability,
            'updated_at' => current_time( 'mysql' )
        );
        
        error_log('Candidate data to update: ' . print_r($candidate_data, true));
        
        $update_result = $wpdb->update( 
            $table_name, 
            $candidate_data, 
            array( 'id' => $candidate_id ),
            array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
            array( '%d' )
        );
        
        if ( $update_result === false ) {
            error_log('Database update FAILED: ' . $wpdb->last_error);
            wp_die( __( 'Failed to update candidate profile: ', 'sleeve-ke' ) . $wpdb->last_error );
        }
        
        error_log('Candidate updated successfully. Rows affected: ' . $update_result);
        error_log('=== ADMIN CANDIDATE UPDATE END ===');
        
        // Redirect to candidates list with success message
        wp_redirect( add_query_arg( 'candidate_updated', 1, admin_url( 'admin.php?page=sleeve-ke-candidates' ) ) );
        exit;
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        $action = sanitize_text_field( $_POST['bulk_action'] );
        $candidate_ids = array_map( 'intval', $_POST['candidate_ids'] );
        
        // Here you would normally update the database
        $message = '';
        switch ( $action ) {
            case 'approve':
                $message = __( 'Candidates approved successfully.', 'sleeve-ke' );
                break;
            case 'pending':
                $message = __( 'Candidates set to pending review.', 'sleeve-ke' );
                break;
            case 'suspend':
                $message = __( 'Candidates suspended.', 'sleeve-ke' );
                break;
            case 'delete':
                $message = __( 'Candidates deleted.', 'sleeve-ke' );
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
     * Handle AJAX request to update candidate status
     */
    public function ajax_update_candidate_status() {
        // Check nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'update_candidate_status' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'sleeve-ke' ) ) );
        }
        
        $candidate_id = intval( $_POST['candidate_id'] );
        $status = sanitize_text_field( $_POST['status'] );
        
        // Validate status
        $valid_statuses = array_keys( $this->get_status_options() );
        if ( ! in_array( $status, $valid_statuses ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid status', 'sleeve-ke' ) ) );
        }
        
        // Here you would normally update the database
        // For now, we'll just simulate success
        
        wp_send_json_success( array( 
            'message' => __( 'Candidate status updated successfully', 'sleeve-ke' ),
            'candidate_id' => $candidate_id,
            'new_status' => $status
        ) );
    }
}