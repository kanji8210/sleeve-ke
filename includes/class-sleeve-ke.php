<?php
/**
 * The core plugin class
 *
 * @package Sleeve_KE
 */

class Sleeve_KE {
    
    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;
    
    /**
     * The current version of the plugin
     */
    protected $version;
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        $this->version = SLEEVE_KE_VERSION;
        $this->plugin_name = 'sleeve-ke';
        
        $this->load_dependencies();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load database handler
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-database.php';
        
        // Load roles handler
        require_once SLEEVE_KE_PLUGIN_DIR . 'includes/class-sleeve-ke-roles.php';
    }
    
    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is ready
    }
    
    /**
     * Get plugin name
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return $this->version;
    }
}
