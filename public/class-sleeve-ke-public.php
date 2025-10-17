<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for public-facing site functionality.
 */
class Sleeve_KE_Public {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            SLEEVE_KE_PLUGIN_URL . 'assets/css/sleeve-ke-public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            SLEEVE_KE_PLUGIN_URL . 'assets/js/sleeve-ke-public.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }
}
