<?php
/**
 * Settings Migration Class
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings Migration Class
 */
class IO_Settings_Migration {
    
    /**
     * Instance
     *
     * @var IO_Settings_Migration
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return IO_Settings_Migration
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
        // Add backward compatibility hooks
        add_filter( 'option_io_image_auto_resize', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_max_width', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_max_height', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_maintain_aspect', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_jpeg_quality', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_max_file_size', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_webp_enabled', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_webp_quality', array( $this, 'backward_compat_option' ), 10, 2 );
        add_filter( 'option_io_image_strip_exif', array( $this, 'backward_compat_option' ), 10, 2 );
    }
    
    /**
     * Migrate settings from theme
     *
     * @return array Migration results
     */
    public function migrate_from_theme() {
        $theme_options = array(
            'trendtoday_image_auto_resize'     => 'io_image_auto_resize',
            'trendtoday_image_max_width'       => 'io_image_max_width',
            'trendtoday_image_max_height'      => 'io_image_max_height',
            'trendtoday_image_maintain_aspect' => 'io_image_maintain_aspect',
            'trendtoday_image_jpeg_quality'    => 'io_image_jpeg_quality',
            'trendtoday_image_max_file_size'   => 'io_image_max_file_size',
            'trendtoday_image_webp_enabled'    => 'io_image_webp_enabled',
            'trendtoday_image_webp_quality'    => 'io_image_webp_quality',
            'trendtoday_image_strip_exif'      => 'io_image_strip_exif',
        );
        
        $migrated = get_option( 'io_settings_migrated', false );
        
        if ( $migrated ) {
            return array(
                'success' => true,
                'message' => __( 'Settings have already been migrated.', 'tonkla-image-optimizer' ),
                'migrated_count' => 0,
            );
        }
        
        $migrated_count = 0;
        $migrated_options = array();
        
        foreach ( $theme_options as $old_option => $new_option ) {
            $value = get_option( $old_option );
            if ( $value !== false ) {
                // Only migrate if new option doesn't exist or is default
                $new_value = get_option( $new_option );
                if ( $new_value === false ) {
                    update_option( $new_option, $value );
                    $migrated_count++;
                    $migrated_options[] = $old_option;
                }
            }
        }
        
        // Mark as migrated
        update_option( 'io_settings_migrated', true );
        update_option( 'io_settings_migrated_at', current_time( 'mysql' ) );
        
        return array(
            'success' => true,
            'message' => sprintf(
                /* translators: %d: Number of options migrated */
                __( 'Successfully migrated %d settings from theme.', 'tonkla-image-optimizer' ),
                $migrated_count
            ),
            'migrated_count' => $migrated_count,
            'migrated_options' => $migrated_options,
        );
    }
    
    /**
     * Backward compatibility for options
     * If plugin option doesn't exist, check theme option
     *
     * @param mixed  $value  Option value.
     * @param string $option Option name.
     * @return mixed Option value.
     */
    public function backward_compat_option( $value, $option ) {
        // If value already exists, return it
        if ( $value !== false ) {
            return $value;
        }
        
        // Map plugin options to theme options
        $option_map = array(
            'io_image_auto_resize'     => 'trendtoday_image_auto_resize',
            'io_image_max_width'       => 'trendtoday_image_max_width',
            'io_image_max_height'      => 'trendtoday_image_max_height',
            'io_image_maintain_aspect' => 'trendtoday_image_maintain_aspect',
            'io_image_jpeg_quality'    => 'trendtoday_image_jpeg_quality',
            'io_image_max_file_size'   => 'trendtoday_image_max_file_size',
            'io_image_webp_enabled'    => 'trendtoday_image_webp_enabled',
            'io_image_webp_quality'    => 'trendtoday_image_webp_quality',
            'io_image_strip_exif'      => 'trendtoday_image_strip_exif',
        );
        
        if ( isset( $option_map[ $option ] ) ) {
            $theme_value = get_option( $option_map[ $option ] );
            if ( $theme_value !== false ) {
                // Auto-migrate on first access
                update_option( $option, $theme_value );
                return $theme_value;
            }
        }
        
        return $value;
    }
    
    /**
     * Get migration status
     *
     * @return array Migration status
     */
    public function get_migration_status() {
        $migrated = get_option( 'io_settings_migrated', false );
        $migrated_at = get_option( 'io_settings_migrated_at', '' );
        
        $theme_options = array(
            'trendtoday_image_auto_resize',
            'trendtoday_image_max_width',
            'trendtoday_image_max_height',
            'trendtoday_image_maintain_aspect',
            'trendtoday_image_jpeg_quality',
            'trendtoday_image_max_file_size',
            'trendtoday_image_webp_enabled',
            'trendtoday_image_webp_quality',
            'trendtoday_image_strip_exif',
        );
        
        $theme_options_exist = 0;
        foreach ( $theme_options as $option ) {
            if ( get_option( $option ) !== false ) {
                $theme_options_exist++;
            }
        }
        
        return array(
            'migrated' => $migrated,
            'migrated_at' => $migrated_at,
            'theme_options_exist' => $theme_options_exist,
            'total_theme_options' => count( $theme_options ),
        );
    }
    
    /**
     * Clear migration flag (for testing)
     *
     * @return void
     */
    public function clear_migration_flag() {
        delete_option( 'io_settings_migrated' );
        delete_option( 'io_settings_migrated_at' );
    }
}
