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
        // Apply filters if any
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $experience_filter = isset( $_GET['experience_level'] ) ? sanitize_text_field( $_GET['experience_level'] ) : '';

        // Mock data - in real implementation, this would fetch from database
        $all_candidates = array(
            array(
                'id' => 1,
                'full_name' => 'John Kamau',
                'email' => 'john.kamau@email.com',
                'phone' => '+254 700 123 456',
                'location' => 'Nairobi, Kenya',
                'experience_level' => 'senior',
                'skills' => 'PHP, Laravel, JavaScript, MySQL, Git, Docker',
                'bio' => 'Experienced software developer with 8+ years in web development. Passionate about creating scalable applications and mentoring junior developers.',
                'education' => 'Bachelor of Computer Science - University of Nairobi',
                'work_experience' => 'Senior PHP Developer at TechCorp (2020-Present), Software Developer at WebSolutions (2017-2020)',
                'languages' => 'English (Fluent), Swahili (Native), French (Basic)',
                'availability' => 'available',
                'salary_expectation_min' => 180000,
                'salary_expectation_max' => 350000,
                'currency' => 'KES',
                'remote_work_preference' => 'hybrid',
                'status' => 'approved',
                'applications_count' => 5,
                'registered_date' => '2025-09-15',
                'last_active' => '2025-10-16'
            ),
            array(
                'id' => 2,
                'full_name' => 'Mary Wanjiku',
                'email' => 'mary.wanjiku@email.com',
                'phone' => '+254 722 654 321',
                'location' => 'Mombasa, Kenya',
                'experience_level' => 'mid',
                'skills' => 'React, JavaScript, CSS3, HTML5, Node.js, MongoDB',
                'bio' => 'Creative frontend developer with strong UX/UI sense. Love building responsive and interactive web applications.',
                'education' => 'Diploma in Software Engineering - Mombasa Technical University',
                'work_experience' => 'Frontend Developer at DigitalAgency (2021-Present), Junior Developer at StartupHub (2019-2021)',
                'languages' => 'English (Fluent), Swahili (Native)',
                'availability' => 'available',
                'salary_expectation_min' => 120000,
                'salary_expectation_max' => 200000,
                'currency' => 'KES',
                'remote_work_preference' => 'full',
                'status' => 'active',
                'applications_count' => 8,
                'registered_date' => '2025-09-20',
                'last_active' => '2025-10-17'
            ),
            array(
                'id' => 3,
                'full_name' => 'David Ochieng',
                'email' => 'david.ochieng@email.com',
                'phone' => '+254 733 987 654',
                'location' => 'Kisumu, Kenya',
                'experience_level' => 'entry',
                'skills' => 'Python, Django, PostgreSQL, HTML, CSS, JavaScript',
                'bio' => 'Recent computer science graduate eager to start career in software development. Quick learner with strong problem-solving skills.',
                'education' => 'Bachelor of Science in Computer Science - Maseno University (2025)',
                'work_experience' => 'Intern Software Developer at LocalTech (3 months)',
                'languages' => 'English (Fluent), Swahili (Native), Luo (Native)',
                'availability' => 'available',
                'salary_expectation_min' => 60000,
                'salary_expectation_max' => 100000,
                'currency' => 'KES',
                'remote_work_preference' => 'no',
                'status' => 'pending',
                'applications_count' => 12,
                'registered_date' => '2025-10-01',
                'last_active' => '2025-10-17'
            ),
            array(
                'id' => 4,
                'full_name' => 'Grace Mutua',
                'email' => 'grace.mutua@email.com',
                'phone' => '+254 712 345 678',
                'location' => 'Nakuru, Kenya',
                'experience_level' => 'expert',
                'skills' => 'Project Management, Agile, Scrum, Leadership, Strategic Planning, Budgeting',
                'bio' => 'Experienced project manager with 15+ years leading cross-functional teams in technology and business transformation projects.',
                'education' => 'MBA - Strathmore University, PMP Certified',
                'work_experience' => 'Senior Project Manager at GlobalCorp (2015-Present), Project Manager at BusinessSolutions (2010-2015)',
                'languages' => 'English (Fluent), Swahili (Native), German (Intermediate)',
                'availability' => 'available',
                'salary_expectation_min' => 400000,
                'salary_expectation_max' => 600000,
                'currency' => 'KES',
                'remote_work_preference' => 'hybrid',
                'status' => 'approved',
                'applications_count' => 3,
                'registered_date' => '2025-08-25',
                'last_active' => '2025-10-15'
            ),
            array(
                'id' => 5,
                'full_name' => 'Ahmed Hassan',
                'email' => 'ahmed.hassan@email.com',
                'phone' => '+254 701 987 654',
                'location' => 'Eldoret, Kenya',
                'experience_level' => 'junior',
                'skills' => 'Digital Marketing, SEO, Social Media, Content Creation, Analytics',
                'bio' => 'Digital marketing enthusiast with 3 years experience in social media management and content marketing.',
                'education' => 'Bachelor of Business Administration - Moi University',
                'work_experience' => 'Digital Marketing Specialist at MarketingPro (2022-Present), Marketing Assistant at LocalBusiness (2021-2022)',
                'languages' => 'English (Fluent), Swahili (Native), Arabic (Basic)',
                'availability' => 'available',
                'salary_expectation_min' => 80000,
                'salary_expectation_max' => 150000,
                'currency' => 'KES',
                'remote_work_preference' => 'full',
                'status' => 'active',
                'applications_count' => 6,
                'registered_date' => '2025-09-10',
                'last_active' => '2025-10-16'
            )
        );

        // Apply filters
        $filtered_candidates = $all_candidates;

        if ( ! empty( $search ) ) {
            $filtered_candidates = array_filter( $filtered_candidates, function( $candidate ) use ( $search ) {
                return stripos( $candidate['full_name'], $search ) !== false || 
                       stripos( $candidate['email'], $search ) !== false ||
                       stripos( $candidate['skills'], $search ) !== false;
            });
        }

        if ( ! empty( $status_filter ) ) {
            $filtered_candidates = array_filter( $filtered_candidates, function( $candidate ) use ( $status_filter ) {
                return $candidate['status'] === $status_filter;
            });
        }

        if ( ! empty( $experience_filter ) ) {
            $filtered_candidates = array_filter( $filtered_candidates, function( $candidate ) use ( $experience_filter ) {
                return $candidate['experience_level'] === $experience_filter;
            });
        }

        return $filtered_candidates;
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
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Add New Candidate', 'sleeve-ke' ) . '</h1>';
        echo '<p>' . esc_html__( 'Form for adding new candidates will be implemented here.', 'sleeve-ke' ) . '</p>';
        echo '</div>';
    }

    /**
     * Display edit candidate form.
     */
    private function display_edit_candidate_form( $candidate_id ) {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Edit Candidate', 'sleeve-ke' ) . '</h1>';
        echo '<p>' . esc_html__( 'Form for editing candidate with ID: ', 'sleeve-ke' ) . esc_html( $candidate_id ) . '</p>';
        echo '</div>';
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
        // Handle form submissions and bulk actions
        if ( isset( $_POST['apply_bulk_action'] ) && isset( $_POST['bulk_action'] ) && isset( $_POST['candidate_ids'] ) ) {
            $this->handle_bulk_actions();
        }
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