<?php
/**
 * The core plugin class.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Sleeve_KE {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version     = defined( 'SLEEVE_KE_VERSION' ) ? SLEEVE_KE_VERSION : '1.0.0';
        $this->plugin_name = 'sleeve-ke';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        
        /**
         * The class responsible for orchestrating the actions and filters of the core plugin.
         */
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-loader.php';

        /**
         * The class responsible for defining internationalization functionality.
         */
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once SLEEVE_KE_PLUGIN_DIR . 'admin/class-sleeve-ke-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing side.
         */
        require_once SLEEVE_KE_PLUGIN_DIR . 'public/class-sleeve-ke-public.php';

        $this->loader = new Sleeve_KE_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new Sleeve_KE_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Sleeve_KE_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new Sleeve_KE_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }
}
