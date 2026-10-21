<?php
/**
 * Admin Menu
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Menu Class
 */
class IO_Admin_Menu {
    
    /**
     * Instance
     *
     * @var IO_Admin_Menu
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return IO_Admin_Menu
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Tonkla Image Optimizer', 'tonkla-image-optimizer' ), // Page title
            __( 'TK IMG Optimizer', 'tonkla-image-optimizer' ), // Menu title
            'manage_options',
            'tonkla-image-optimizer',
            array( $this, 'render_settings_page' ),
            'dashicons-images-alt2', // Menu icon
            30 // Position (after Media)
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'tonkla-image-optimizer' ) );
        }
        
        // Load settings page class
        require_once IO_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
        $settings_page = IO_Settings_Page::get_instance();
        $settings_page->render();
    }
}
