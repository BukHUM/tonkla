<?php
/**
 * Admin
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Class
 */
class IO_Admin {
    
    /**
     * Instance
     *
     * @var IO_Admin
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return IO_Admin
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
        // Load admin menu
        require_once IO_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
        IO_Admin_Menu::get_instance();
        
        // Enqueue admin assets
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        
        // Show migration notice if needed
        add_action( 'admin_notices', array( $this, 'show_migration_notice' ) );

        // Plugins list meta link
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
    }

    /**
     * Add developer credit link on the Plugins screen.
     *
     * @param array  $links Plugin row meta links.
     * @param string $file  Plugin basename.
     * @return array
     */
    public function plugin_row_meta( $links, $file ) {
        if ( IO_PLUGIN_BASENAME !== $file ) {
            return $links;
        }

        $url = add_query_arg(
            array(
                'utm_source'   => 'tonkla-suite',
                'utm_medium'   => 'credit',
                'utm_campaign' => 'image-optimizer',
                'utm_content'  => 'plugins',
            ),
            'https://tonkla.co'
        );

        $links[] = sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url( $url ),
            esc_html__( 'ต้นกล้า ไอที', 'tonkla-image-optimizer' )
        );

        return $links;
    }
    
    /**
     * Show migration notice if theme options exist
     */
    public function show_migration_notice() {
        // Only show on our settings page
        $screen = get_current_screen();
        if ( ! $screen || 'toplevel_page_tonkla-image-optimizer' !== $screen->id ) {
            return;
        }
        
        require_once IO_PLUGIN_DIR . 'includes/helpers/class-settings-migration.php';
        $migration = IO_Settings_Migration::get_instance();
        $status = $migration->get_migration_status();
        
        // Show notice if theme options exist but not migrated
        if ( ! $status['migrated'] && $status['theme_options_exist'] > 0 ) {
            $migrate_url = wp_nonce_url(
                add_query_arg( 'migrate', '1', admin_url( 'admin.php?page=tonkla-image-optimizer' ) ),
                'io_settings_nonce',
                '_wpnonce'
            );
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php _e( 'Image Optimization:', 'tonkla-image-optimizer' ); ?></strong>
                    <?php
                    printf(
                        /* translators: %d: Number of theme options found */
                        esc_html__( 'Found %d settings from theme. Would you like to migrate them?', 'tonkla-image-optimizer' ),
                        esc_html( $status['theme_options_exist'] )
                    );
                    ?>
                    <a href="<?php echo esc_url( $migrate_url ); ?>" class="button button-primary" style="margin-left: 10px;">
                        <?php _e( 'Migrate Settings', 'tonkla-image-optimizer' ); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on our settings page
        if ( 'toplevel_page_tonkla-image-optimizer' !== $hook ) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style(
            'io-admin',
            IO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            IO_VERSION
        );
        
        // Enqueue scripts
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script(
            'io-admin',
            IO_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            IO_VERSION,
            true
        );
        
        // Tonkla Suite alias object (Phase 2 bridge) - same nonce/ajaxurl for new JS.
        wp_localize_script( 'io-admin', 'tonklaImgAdmin', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'io_settings_nonce' ) ) );

        // Localize script
        wp_localize_script( 'io-admin', 'ioAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'io_settings_nonce' ),
            'i18n'    => array(
                'scanning'              => __( 'กำลังแสกน...', 'tonkla-image-optimizer' ),
                'scanComplete'          => __( 'แสกนเสร็จสิ้น', 'tonkla-image-optimizer' ),
                'scanError'             => __( 'เกิดข้อผิดพลาดในการแสกน', 'tonkla-image-optimizer' ),
                'noFiles'               => __( 'ไม่มีไฟล์', 'tonkla-image-optimizer' ),
                'noFilesSelected'       => __( 'กรุณาเลือกไฟล์ที่ต้องการลบ', 'tonkla-image-optimizer' ),
                'confirmDeleteSelected'  => __( 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ที่เลือก? การลบไม่สามารถยกเลิกได้', 'tonkla-image-optimizer' ),
                'confirmDeleteAll'       => __( 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ทั้งหมด? การลบไม่สามารถยกเลิกได้', 'tonkla-image-optimizer' ),
                'confirmDeleteOrphaned'  => __( 'คุณกำลังจะลบ Orphaned (ไฟล์ที่ไม่มีใน Media Library) — ตรวจให้แน่ใจว่าไม่ใช่โลโก้/favicon/ไฟล์สำคัญ การลบไม่สามารถยกเลิกได้', 'tonkla-image-optimizer' ),
                'confirmDeleteAllWithOrphaned' => __( 'รวม Orphaned ด้วย — ตรวจรายการให้รอบคอบก่อนลบ การลบไม่สามารถยกเลิกได้', 'tonkla-image-optimizer' ),
                'deleting'               => __( 'กำลังลบ...', 'tonkla-image-optimizer' ),
                'deleteComplete'         => __( 'ลบเสร็จสิ้น', 'tonkla-image-optimizer' ),
                'deleteError'            => __( 'เกิดข้อผิดพลาดในการลบ', 'tonkla-image-optimizer' ),
                'reportFeature'          => __( 'ฟีเจอร์นี้จะเพิ่มในอนาคต', 'tonkla-image-optimizer' ),
                'scanTimeout'            => __( 'การแสกนใช้เวลานานเกินไป กรุณาลองใหม่อีกครั้ง', 'tonkla-image-optimizer' ),
                'noFailedFiles'          => __( 'ไม่มีไฟล์ที่ลบไม่สำเร็จ', 'tonkla-image-optimizer' ),
                'regenerating'           => __( 'กำลัง Regenerate...', 'tonkla-image-optimizer' ),
                'regenerateComplete'     => __( 'Regenerate เสร็จสิ้น', 'tonkla-image-optimizer' ),
                'regenerateError'        => __( 'เกิดข้อผิดพลาดในการ Regenerate', 'tonkla-image-optimizer' ),
                'regenerateCancelled'    => __( 'Regenerate ถูกยกเลิก', 'tonkla-image-optimizer' ),
                'noFilesToDelete'        => __( 'ไม่มีไฟล์ที่จะลบ (ถ้าต้องการรวม Orphaned ให้ติ๊กตัวเลือกด้านบน)', 'tonkla-image-optimizer' ),
            ),
        ) );
    }
}
