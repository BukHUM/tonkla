<?php
/**
 * AJAX Handlers
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX Handlers Class
 */
class IO_Ajax_Handlers {
    
    /**
     * Instance
     *
     * @var IO_Ajax_Handlers
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return IO_Ajax_Handlers
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
        // Scan unused images
        add_action( 'wp_ajax_io_scan_unused_images', array( $this, 'scan_unused_images' ) );
        
        // Delete unused images
        add_action( 'wp_ajax_io_delete_unused_images', array( $this, 'delete_unused_images' ) );
        
        // Fix scan errors
        add_action( 'wp_ajax_io_fix_scan_errors', array( $this, 'fix_scan_errors' ) );
        
        // Regenerate images
        add_action( 'wp_ajax_io_count_images_for_regenerate', array( $this, 'count_images_for_regenerate' ) );
        add_action( 'wp_ajax_io_regenerate_images', array( $this, 'regenerate_images' ) );
        add_action( 'wp_ajax_io_cancel_regenerate', array( $this, 'cancel_regenerate' ) );

        // Tonkla Suite aliases (Phase 2 bridge) — same handlers, new action names.
        foreach ( array( 'scan_unused_images', 'delete_unused_images', 'fix_scan_errors', 'count_images_for_regenerate', 'regenerate_images', 'cancel_regenerate' ) as $tonkla_img_action ) {
            add_action( 'wp_ajax_tonkla_img_' . $tonkla_img_action, array( $this, $tonkla_img_action ) );
        }
    }
    
    /**
     * Scan unused images AJAX handler
     */
    public function scan_unused_images() {
        check_ajax_referer( 'io_settings_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'คุณไม่มีสิทธิ์ในการดำเนินการนี้', 'tonkla-image-optimizer' ),
            ) );
        }
        
        // Increase time limit and memory limit for scanning
        @set_time_limit( 600 ); // 10 minutes for large scans
        @ini_set( 'memory_limit', '512M' );
        
        // Try to increase memory limit more if possible
        $current_memory = ini_get( 'memory_limit' );
        if ( $current_memory ) {
            $current_bytes = $this->convert_to_bytes( $current_memory );
            $target_bytes = $this->convert_to_bytes( '512M' );
            if ( $current_bytes < $target_bytes ) {
                // Try to set to 1GB if 512M doesn't work
                @ini_set( 'memory_limit', '1024M' );
            }
        }
        
        $scan_type = isset( $_POST['scan_type'] ) ? sanitize_text_field( $_POST['scan_type'] ) : 'all';
        $use_cache = isset( $_POST['use_cache'] ) ? (bool) $_POST['use_cache'] : true;
        $max_files = isset( $_POST['max_files'] ) ? absint( $_POST['max_files'] ) : 0;
        $time_limit = isset( $_POST['time_limit'] ) ? absint( $_POST['time_limit'] ) : 0;
        
        try {
            // Validate prerequisites before starting
            $validation = $this->validate_scan_prerequisites();
            if ( ! $validation['valid'] ) {
                wp_send_json_error( array(
                    'message' => $validation['message'],
                ) );
            }
            
            // Validate scan_type
            $valid_scan_types = array( 'all', 'thumbnails', 'webp', 'orphaned', 'statistics_only' );
            if ( ! in_array( $scan_type, $valid_scan_types, true ) ) {
                $scan_type = 'all';
            }
            
            $cleanup = new IO_Image_Cleanup();
            if ( ! $cleanup ) {
                throw new Exception( __( 'ไม่สามารถเริ่มต้นระบบทำความสะอาดภาพได้ กรุณาลองใหม่อีกครั้ง', 'tonkla-image-optimizer' ) );
            }
            
            $results = $cleanup->scan_unused_images( array(
                'scan_type' => $scan_type,
                'use_cache' => $use_cache,
                'max_files' => $max_files,
                'time_limit' => $time_limit,
            ) );
            
            if ( ! is_array( $results ) ) {
                throw new Exception( __( 'ผลการแสกนไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง', 'tonkla-image-optimizer' ) );
            }
            
            // Check if there was a fatal error (only send error if no files were scanned at all)
            $has_results = ! empty( $results['thumbnails'] ) || ! empty( $results['webp'] ) || ! empty( $results['orphaned'] );
            $has_fatal_error = isset( $results['error'] ) && ! empty( $results['error'] ) && ! $has_results;
            
            if ( $has_fatal_error ) {
                $error_message = $this->translate_error_message( $results['error'] );
                wp_send_json_error( array(
                    'message' => $error_message,
                ) );
            }
            
            // If we have results (even with some errors), send success with warnings
            // Store results in transient for later use
            $this->save_scan_results( $results, HOUR_IN_SECONDS );
            
            wp_send_json_success( $results );
        } catch ( Exception $e ) {
            $error_message = $this->translate_error_message( $e->getMessage() );
            error_log( '[Image Optimization] Scan error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString() );
            wp_send_json_error( array(
                'message' => $error_message,
            ) );
        } catch ( Error $e ) {
            error_log( '[Image Optimization] Scan fatal error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString() );
            wp_send_json_error( array(
                'message' => __( 'เกิดข้อผิดพลาดร้ายแรงในการแสกน กรุณาตรวจสอบ log หรือติดต่อผู้ดูแลระบบ', 'tonkla-image-optimizer' ),
            ) );
        }
    }
    
    /**
     * Validate prerequisites before scanning
     *
     * @return array Validation result with 'valid' and 'message' keys.
     */
    private function validate_scan_prerequisites() {
        // Check if uploads directory exists
        $upload_dir = wp_upload_dir();
        if ( empty( $upload_dir['basedir'] ) ) {
            return array(
                'valid' => false,
                'message' => __( 'ไม่พบโฟลเดอร์ uploads กรุณาตรวจสอบการตั้งค่า WordPress', 'tonkla-image-optimizer' ),
            );
        }
        
        $uploads_dir = $upload_dir['basedir'];
        
        // Check if directory exists
        if ( ! is_dir( $uploads_dir ) ) {
            return array(
                'valid' => false,
                'message' => __( 'โฟลเดอร์ uploads ไม่มีอยู่จริง กรุณาตรวจสอบสิทธิ์การเข้าถึง', 'tonkla-image-optimizer' ),
            );
        }
        
        // Check if directory is readable
        if ( ! is_readable( $uploads_dir ) ) {
            return array(
                'valid' => false,
                'message' => __( 'ไม่สามารถอ่านโฟลเดอร์ uploads ได้ กรุณาตรวจสอบสิทธิ์การเข้าถึง (ควรเป็น 755 หรือ 775)', 'tonkla-image-optimizer' ),
            );
        }
        
        // Check database connection
        global $wpdb;
        if ( ! $wpdb ) {
            return array(
                'valid' => false,
                'message' => __( 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า', 'tonkla-image-optimizer' ),
            );
        }
        
        // Check memory limit
        $memory_limit = ini_get( 'memory_limit' );
        $memory_limit_bytes = $this->convert_to_bytes( $memory_limit );
        if ( $memory_limit_bytes < 64 * 1024 * 1024 ) { // Less than 64MB
            return array(
                'valid' => false,
                'message' => sprintf( 
                    __( 'หน่วยความจำไม่เพียงพอ (ปัจจุบัน: %s) กรุณาเพิ่ม memory_limit ใน php.ini เป็นอย่างน้อย 128M', 'tonkla-image-optimizer' ),
                    $memory_limit
                ),
            );
        }
        
        // Check if required PHP functions exist
        $required_functions = array( 'get_posts', 'wp_upload_dir', 'get_attached_file' );
        foreach ( $required_functions as $func ) {
            if ( ! function_exists( $func ) ) {
                return array(
                    'valid' => false,
                    'message' => sprintf( 
                        __( 'ฟังก์ชัน PHP ที่จำเป็นไม่พร้อมใช้งาน: %s', 'tonkla-image-optimizer' ),
                        $func
                    ),
                );
            }
        }
        
        return array(
            'valid' => true,
            'message' => '',
        );
    }
    
    /**
     * Convert memory limit string to bytes
     *
     * @param string $memory_limit Memory limit string (e.g., '128M', '256M').
     * @return int Memory limit in bytes.
     */
    private function convert_to_bytes( $memory_limit ) {
        $memory_limit = trim( $memory_limit );
        $last = strtolower( $memory_limit[ strlen( $memory_limit ) - 1 ] );
        $value = (int) $memory_limit;
        
        switch ( $last ) {
            case 'g':
                $value *= 1024;
                // Fall through
            case 'm':
                $value *= 1024;
                // Fall through
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Translate technical error messages to user-friendly Thai messages
     *
     * @param string $error_message Original error message.
     * @return string Translated error message.
     */
    private function translate_error_message( $error_message ) {
        $error_message = trim( $error_message );
        
        // Common error patterns and their translations
        $error_patterns = array(
            // File system errors
            '/permission denied/i' => 'ไม่มีสิทธิ์เข้าถึงไฟล์ กรุณาตรวจสอบสิทธิ์การเข้าถึงโฟลเดอร์ uploads',
            '/failed to open stream/i' => 'ไม่สามารถเปิดไฟล์ได้ กรุณาตรวจสอบสิทธิ์การเข้าถึง',
            '/no such file or directory/i' => 'ไม่พบไฟล์หรือโฟลเดอร์ที่ระบุ',
            '/directory not found/i' => 'ไม่พบโฟลเดอร์ที่ระบุ',
            '/uploads directory not found/i' => 'ไม่พบโฟลเดอร์ uploads',
            
            // Database errors
            '/database error/i' => 'เกิดข้อผิดพลาดจากฐานข้อมูล กรุณาตรวจสอบการเชื่อมต่อ',
            '/mysql/i' => 'เกิดข้อผิดพลาดจากฐานข้อมูล กรุณาตรวจสอบการเชื่อมต่อ',
            '/query failed/i' => 'การค้นหาข้อมูลล้มเหลว กรุณาลองใหม่อีกครั้ง',
            
            // Memory errors
            '/memory/i' => 'หน่วยความจำไม่เพียงพอ กรุณาเพิ่ม memory_limit ใน php.ini',
            '/allowed memory size/i' => 'หน่วยความจำไม่เพียงพอ กรุณาเพิ่ม memory_limit ใน php.ini',
            
            // Timeout errors
            '/timeout/i' => 'การแสกนใช้เวลานานเกินไป กรุณาลองใหม่อีกครั้งหรือลดจำนวนไฟล์',
            '/maximum execution time/i' => 'การแสกนใช้เวลานานเกินไป กรุณาเพิ่ม max_execution_time ใน php.ini',
            
            // Class/function errors
            '/class.*not found/i' => 'ไม่พบคลาสที่จำเป็น กรุณาตรวจสอบการติดตั้งปลั๊กอิน',
            '/function.*not found/i' => 'ไม่พบฟังก์ชันที่จำเป็น กรุณาตรวจสอบการติดตั้งปลั๊กอิน',
            '/call to undefined/i' => 'เรียกใช้ฟังก์ชันที่ไม่ถูกต้อง กรุณาตรวจสอบการติดตั้งปลั๊กอิน',
            
            // Generic errors
            '/invalid/i' => 'ข้อมูลไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
            '/failed/i' => 'การดำเนินการล้มเหลว กรุณาลองใหม่อีกครั้ง',
        );
        
        // Check if error matches any pattern
        foreach ( $error_patterns as $pattern => $translation ) {
            if ( preg_match( $pattern, $error_message ) ) {
                return $translation;
            }
        }
        
        // If no pattern matches, return a generic message with the original error for debugging
        // But only show a simple message to the user
        return __( 'เกิดข้อผิดพลาดในการแสกน: ', 'tonkla-image-optimizer' ) . 
               __( 'กรุณาลองใหม่อีกครั้ง หรือตรวจสอบ log สำหรับรายละเอียดเพิ่มเติม', 'tonkla-image-optimizer' );
    }
    
    /**
     * Fix scan errors AJAX handler
     */
    public function fix_scan_errors() {
        check_ajax_referer( 'io_settings_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'คุณไม่มีสิทธิ์ในการดำเนินการนี้', 'tonkla-image-optimizer' ),
            ) );
        }
        
        $error_type = isset( $_POST['error_type'] ) ? sanitize_text_field( $_POST['error_type'] ) : '';
        $file_path = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
        
        $results = array(
            'fixed' => false,
            'message' => '',
            'errors' => array(),
        );
        
        try {
            // Handle different error types
            switch ( $error_type ) {
                case 'permission':
                case 'not_readable':
                    // Try to fix directory permissions
                    if ( ! empty( $file_path ) && is_dir( $file_path ) ) {
                        // Try to make directory readable
                        $old_perms = fileperms( $file_path );
                        $new_perms = 0755; // Standard directory permissions
                        
                        if ( @chmod( $file_path, $new_perms ) ) {
                            $results['fixed'] = true;
                            $results['message'] = sprintf( 
                                __( 'แก้ไขสิทธิ์การเข้าถึงโฟลเดอร์ %s สำเร็จ', 'tonkla-image-optimizer' ),
                                basename( $file_path )
                            );
                        } else {
                            $results['message'] = sprintf( 
                                __( 'ไม่สามารถแก้ไขสิทธิ์ได้ กรุณาตรวจสอบสิทธิ์ของ PHP user หรือใช้ FTP/SSH เพื่อแก้ไข', 'tonkla-image-optimizer' ),
                                basename( $file_path )
                            );
                        }
                    } else {
                        $results['message'] = __( 'ไม่พบโฟลเดอร์ที่ระบุ', 'tonkla-image-optimizer' );
                    }
                    break;
                    
                case 'not_found':
                    // Can't fix - file/directory doesn't exist
                    $results['message'] = __( 'ไม่สามารถแก้ไขได้: ไฟล์หรือโฟลเดอร์ไม่มีอยู่จริง', 'tonkla-image-optimizer' );
                    break;
                    
                case 'database':
                    // Can't auto-fix database errors
                    $results['message'] = __( 'ไม่สามารถแก้ไขได้อัตโนมัติ กรุณาตรวจสอบการเชื่อมต่อฐานข้อมูล', 'tonkla-image-optimizer' );
                    break;
                    
                default:
                    $results['message'] = __( 'ไม่ทราบประเภทของปัญหา', 'tonkla-image-optimizer' );
            }
            
            wp_send_json_success( $results );
        } catch ( Exception $e ) {
            error_log( '[Image Optimization] Fix error: ' . $e->getMessage() );
            wp_send_json_error( array(
                'message' => __( 'เกิดข้อผิดพลาดในการแก้ไข: ', 'tonkla-image-optimizer' ) . $e->getMessage(),
            ) );
        }
    }
    
    /**
     * Delete unused images AJAX handler
     */
    public function delete_unused_images() {
        check_ajax_referer( 'io_settings_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to perform this action.', 'tonkla-image-optimizer' ),
            ) );
        }
        
        // Increase time limit and memory limit for batch processing
        @set_time_limit( 300 );
        @ini_set( 'memory_limit', '512M' );
        
        $files = isset( $_POST['files'] ) ? $_POST['files'] : array();
        // Handle dry_run: can be boolean false, string 'false', or not set
        $dry_run = false;
        if ( isset( $_POST['dry_run'] ) ) {
            $dry_run_value = $_POST['dry_run'];
            // Convert string 'false' to boolean false
            if ( $dry_run_value === 'false' || $dry_run_value === false || $dry_run_value === 0 || $dry_run_value === '0' ) {
                $dry_run = false;
            } else {
                $dry_run = (bool) $dry_run_value;
            }
        }
        
        // Check for delete_all (handle string 'true' from JS or boolean)
        $delete_all = false;
        if ( isset( $_POST['delete_all'] ) ) {
            if ( $_POST['delete_all'] === 'true' || $_POST['delete_all'] === '1' || $_POST['delete_all'] === true ) {
                $delete_all = true;
            }
        }
        
        $delete_type = isset( $_POST['delete_type'] ) ? sanitize_text_field( $_POST['delete_type'] ) : '';
        
        if ( empty( $files ) && ! $delete_all && empty( $delete_type ) ) {
            wp_send_json_error( array(
                'message' => __( 'No files specified for deletion.', 'tonkla-image-optimizer' ),
            ) );
        }
        
        try {
            $cleanup = new IO_Image_Cleanup();
            if ( ! $cleanup ) {
                throw new Exception( __( 'Failed to initialize Image Cleanup class.', 'tonkla-image-optimizer' ) );
            }
            
            $batch_size = 500;
            $remaining_files = 0;
            $save_transient = false;
            
            // If delete_all or delete_type, get files from scan results with batching
            if ( $delete_all || ! empty( $delete_type ) ) {
                $scan_results = $this->get_scan_results();
                if ( ! $scan_results || ! is_array( $scan_results ) ) {
                    wp_send_json_error( array(
                        'message' => __( 'No scan results found. Please scan first.', 'tonkla-image-optimizer' ),
                    ) );
                }
                
                $files = array();
                $types_to_process = array();
                
                if ( $delete_all ) {
                    // Orphaned excluded by default — only include when explicitly requested.
                    $include_orphaned = false;
                    if ( isset( $_POST['include_orphaned'] ) ) {
                        $include_orphaned = (
                            'true' === $_POST['include_orphaned']
                            || '1' === $_POST['include_orphaned']
                            || true === $_POST['include_orphaned']
                        );
                    }
                    $types_to_process = array( 'thumbnails', 'webp' );
                    if ( $include_orphaned ) {
                        $types_to_process[] = 'orphaned';
                    }
                } elseif ( ! empty( $delete_type ) ) {
                    $types_to_process = array( $delete_type );
                }
                
                // Process each type
                foreach ( $types_to_process as $type ) {
                    if ( ! empty( $scan_results[ $type ] ) && is_array( $scan_results[ $type ] ) ) {
                        // Calculate how many more files we need for this batch
                        $needed = $batch_size - count( $files );
                        
                        if ( $needed <= 0 ) {
                            break;
                        }
                        
                        // Take a chunk of files from the beginning of the array
                        // array_splice removes these elements from $scan_results[$type]
                        // This ensures we make progress even if some items are invalid
                        $chunk = array_splice( $scan_results[ $type ], 0, $needed );
                        
                        // Log batch processing details
                        error_log( sprintf( '[Image Optimization] Batch splice: Type=%s, Count=%d, RemainingInType=%d', $type, count($chunk), count($scan_results[$type]) ) );
                        
                        foreach ( $chunk as $file ) {
                            if ( isset( $file['path'] ) && is_string( $file['path'] ) ) {
                                // Normalize path: Fix Windows path format issues
                                $path = $file['path'];
                                // Replace mixed slashes (C:\/path) with forward slashes
                                $path = str_replace( array( '\\/', '\/' ), '/', $path );
                                // Replace backslashes with forward slashes
                                $path = str_replace( '\\', '/', $path );
                                // Use wp_normalize_path for final normalization
                                $normalized_path = wp_normalize_path( $path );
                                // Only add if path exists or is valid
                                if ( ! empty( $normalized_path ) ) {
                                    $files[] = $normalized_path;
                                } else {
                                    error_log( sprintf( '[Image Optimization] Invalid path in chunk: %s', $file['path'] ) );
                                }
                            } else {
                                error_log( sprintf( '[Image Optimization] Invalid file structure in chunk: %s', print_r($file, true) ) );
                            }
                        }
                        
                        // Log extracted files for debugging
                        if ( ! empty( $files ) ) {
                            error_log( sprintf( '[Image Optimization] Extracted %d files from chunk. First file: %s', count($files), $files[0] ) );
                        }
                        
                        $save_transient = true;
                    }
                }
                
                // Recount remaining files
                foreach ( $types_to_process as $type ) {
                    if ( ! empty( $scan_results[ $type ] ) ) {
                        $remaining_files += count( $scan_results[ $type ] );
                    }
                }
                
                if ( empty( $files ) && $remaining_files === 0 ) {
                     wp_send_json_error( array(
                        'message' => __( 'No files found to delete.', 'tonkla-image-optimizer' ),
                    ) );
                }
            } else {
                // Manual selection: Validate and sanitize file paths
                if ( ! is_array( $files ) ) {
                    $files = array();
                }
                $files = array_map( 'sanitize_text_field', $files );
                $files = array_filter( $files, 'strlen' ); // Remove empty strings
                
                if ( empty( $files ) ) {
                    wp_send_json_error( array(
                        'message' => __( 'No files specified for deletion.', 'tonkla-image-optimizer' ),
                    ) );
                }
            }
            
            // Log deletion attempt with sample paths
            $sample_paths = array_slice( $files, 0, 3 );
            error_log( sprintf( '[Image Optimization] Starting deletion: Count=%d, Type=%s, DryRun=%s, Sample paths: %s', 
                count($files), 
                $delete_type ?: ($delete_all ? 'all' : 'manual'),
                $dry_run ? 'true' : 'false',
                implode(', ', $sample_paths)
            ) );
            
            // Perform deletion
            $results = $cleanup->delete_unused_images( $files, array( 'dry_run' => $dry_run ) );
            
            if ( ! is_array( $results ) ) {
                throw new Exception( __( 'Invalid deletion results returned.', 'tonkla-image-optimizer' ) );
            }
            
            // Log deletion results
            error_log( sprintf( '[Image Optimization] Deletion complete: Deleted=%d, Failed=%d, Remaining=%d', $results['deleted'], $results['failed'], $remaining_files ) );
            
            // Format response for JavaScript
            $response_data = array(
                'deleted'      => $results['deleted'],
                'failed'       => $results['failed'],
                'failed_files' => array(),
                'remaining'    => $remaining_files,
            );
            
            // Format failed files for display
            if ( ! empty( $results['errors'] ) ) {
                foreach ( $results['errors'] as $error ) {
                    $file_info = array(
                        'path'     => $error['file'],
                        'filename' => basename( $error['file'] ),
                        'error'    => $error['error'],
                        'size'     => file_exists( $error['file'] ) ? filesize( $error['file'] ) : 0,
                        'modified' => file_exists( $error['file'] ) ? filemtime( $error['file'] ) : 0,
                    );
                    $response_data['failed_files'][] = $file_info;
                }
            }
            
            // Update scan results logic
            if ( ! $dry_run ) {
                if ( $save_transient ) {
                    // Batch mode: Save updated transient
                     foreach ( array( 'thumbnails', 'webp', 'orphaned' ) as $type ) {
                        if ( isset( $scan_results[ $type ] ) ) {
                            $scan_results[ $type ] = array_values( $scan_results[ $type ] );
                        }
                    }
                    // Update stats
                    $scan_results['statistics']['total_thumbnails'] = count( $scan_results['thumbnails'] ?? [] );
                    $scan_results['statistics']['total_webp'] = count( $scan_results['webp'] ?? [] );
                    $scan_results['statistics']['total_orphaned'] = count( $scan_results['orphaned'] ?? [] );
                    
                    if ( $remaining_files === 0 ) {
                        // All done, clear transient
                        $this->delete_scan_results();
                    } else {
                        // Save progress
                        $saved = $this->save_scan_results( $scan_results, DAY_IN_SECONDS );
                        if ( ! $saved ) {
                             error_log( '[Image Optimization] Critical: Failed to save updated scan results. Aborting to prevent loop.' );
                             $response_data['remaining'] = 0; // Tell client to stop
                             $this->delete_scan_results(); // Nuke stale data
                        }
                    }
                } elseif ( $results['deleted'] > 0 ) {
                    // Manual mode: Clear to force rescan
                    $this->delete_scan_results();
                }
            }
            
            wp_send_json_success( $response_data );
            
        } catch ( Exception $e ) {
            error_log( '[Image Optimization] Delete error: ' . $e->getMessage() );
            wp_send_json_error( array(
                'message' => $e->getMessage(),
            ) );
        } catch ( Error $e ) {
            error_log( '[Image Optimization] Delete fatal error: ' . $e->getMessage() );
            wp_send_json_error( array(
                'message' => __( 'A fatal error occurred during deletion.', 'tonkla-image-optimizer' ),
            ) );
        }
    }
    
    /**
     * Count images for regenerate
     */
    public function count_images_for_regenerate() {
        check_ajax_referer( 'io_settings_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to perform this action.', 'tonkla-image-optimizer' ),
            ) );
        }
        
        try {
            // Get options
            $regenerate_type = isset( $_POST['regenerate_type'] ) ? sanitize_text_field( $_POST['regenerate_type'] ) : 'all';
            $date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( $_POST['date_from'] ) : '';
            $date_to = isset( $_POST['date_to'] ) ? sanitize_text_field( $_POST['date_to'] ) : '';
            $skip_processed = isset( $_POST['skip_processed'] ) ? (bool) $_POST['skip_processed'] : false;
            
            // Build query args (lang => '' = all languages for Polylang)
            $query_args = array(
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => -1,
                'post_status'    => 'inherit',
                'fields'         => 'ids',
                'no_found_rows'  => true,
                'lang'           => '',
            );
            
            // Date range filter
            if ( ! empty( $date_from ) || ! empty( $date_to ) ) {
                $date_query = array();
                if ( ! empty( $date_from ) ) {
                    $date_query['after'] = $date_from . ' 00:00:00';
                }
                if ( ! empty( $date_to ) ) {
                    $date_query['before'] = $date_to . ' 23:59:59';
                }
                $date_query['inclusive'] = true;
                $query_args['date_query'] = array( $date_query );
            }
            
            // Get all image attachments
            $attachments = get_posts( $query_args );
            $total = count( $attachments );
            
            // Filter by skip processed
            $selected = $total;
            $skipped = 0;
            if ( $skip_processed ) {
                $selected = 0;
                foreach ( $attachments as $attachment_id ) {
                    // Check if already processed
                    $processed = get_post_meta( $attachment_id, '_io_regenerated', true );
                    if ( ! $processed ) {
                        $selected++;
                    } else {
                        $skipped++;
                    }
                }
            }
            
            // Log for debugging
            error_log( sprintf( '[Image Optimization] Count regenerate: Total=%d, Selected=%d, Skipped=%d, SkipProcessed=%s', 
                $total, $selected, $skipped, $skip_processed ? 'true' : 'false' ) );
            
            wp_send_json_success( array(
                'total' => $total,
                'selected' => $selected,
                'skipped' => $skipped,
            ) );
        } catch ( Exception $e ) {
            error_log( '[Image Optimization] Count error: ' . $e->getMessage() );
            wp_send_json_error( array(
                'message' => $e->getMessage(),
            ) );
        }
    }
    
    /**
     * Regenerate images AJAX handler (batch processing)
     */
    public function regenerate_images() {
        check_ajax_referer( 'io_settings_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to perform this action.', 'tonkla-image-optimizer' ),
            ) );
        }
        
        // Increase time limit and memory limit
        @set_time_limit( 60 );
        @ini_set( 'memory_limit', '256M' );
        
        $batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 10;
        $offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
        $total = isset( $_POST['total'] ) ? absint( $_POST['total'] ) : 0;
        
        // Get options
        $regenerate_type = isset( $_POST['regenerate_type'] ) ? sanitize_text_field( $_POST['regenerate_type'] ) : 'all';
        $date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( $_POST['date_from'] ) : '';
        $date_to = isset( $_POST['date_to'] ) ? sanitize_text_field( $_POST['date_to'] ) : '';
        $skip_processed = isset( $_POST['skip_processed'] ) ? (bool) $_POST['skip_processed'] : false;
        $regenerate_thumbnails = isset( $_POST['regenerate_thumbnails'] ) ? (bool) $_POST['regenerate_thumbnails'] : false;
        
        // Validate regenerate_type
        if ( ! in_array( $regenerate_type, array( 'all', 'resize', 'webp' ), true ) ) {
            $regenerate_type = 'all';
        }
        
        // Check if cancelled
        $cancelled = get_transient( 'io_regenerate_cancelled' );
        if ( $cancelled ) {
            wp_send_json_error( array(
                'message' => __( 'Regeneration was cancelled.', 'tonkla-image-optimizer' ),
                'cancelled' => true,
            ) );
        }
        
        try {
            require_once IO_PLUGIN_DIR . 'includes/class-image-optimizer.php';
            $optimizer = IO_Image_Optimizer::get_instance();
            
            // Build query args (lang => '' = all languages for Polylang)
            $query_args = array(
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => $batch_size * 2, // Get more to account for skipped
                'post_status'    => 'inherit',
                'fields'         => 'ids',
                'offset'         => $offset,
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'no_found_rows'  => true,
                'lang'           => '',
            );
            
            // Date range filter
            if ( ! empty( $date_from ) || ! empty( $date_to ) ) {
                $date_query = array();
                if ( ! empty( $date_from ) ) {
                    $date_query['after'] = $date_from . ' 00:00:00';
                }
                if ( ! empty( $date_to ) ) {
                    $date_query['before'] = $date_to . ' 23:59:59';
                }
                $date_query['inclusive'] = true;
                $query_args['date_query'] = array( $date_query );
            }
            
            // Get batch of attachments
            $attachments = get_posts( $query_args );
            
            // Filter by skip processed if needed
            if ( $skip_processed ) {
                $filtered = array();
                foreach ( $attachments as $attachment_id ) {
                    $processed = get_post_meta( $attachment_id, '_io_regenerated', true );
                    if ( ! $processed ) {
                        $filtered[] = $attachment_id;
                        if ( count( $filtered ) >= $batch_size ) {
                            break;
                        }
                    }
                }
                $attachments = $filtered;
            } else {
                $attachments = array_slice( $attachments, 0, $batch_size );
            }
            
            if ( empty( $attachments ) ) {
                // All done
                delete_transient( 'io_regenerate_cancelled' );
                wp_send_json_success( array(
                    'done' => true,
                    'processed' => $offset,
                    'total' => $total,
                ) );
            }
            
            $processed = 0;
            $success = 0;
            $failed = 0;
            
            foreach ( $attachments as $attachment_id ) {
                // Check if cancelled
                $cancelled = get_transient( 'io_regenerate_cancelled' );
                if ( $cancelled ) {
                    break;
                }
                
                try {
                    $options = array(
                        'regenerate_type' => $regenerate_type,
                        'regenerate_thumbnails' => $regenerate_thumbnails,
                    );
                    $result = $optimizer->regenerate_image( $attachment_id, $options );
                    if ( $result && ! is_wp_error( $result ) ) {
                        $success++;
                        // Mark as processed
                        update_post_meta( $attachment_id, '_io_regenerated', time() );
                    } else {
                        $failed++;
                        if ( is_wp_error( $result ) ) {
                            error_log( '[Image Optimization] Regenerate error for attachment ' . $attachment_id . ': ' . $result->get_error_message() );
                        }
                    }
                } catch ( Exception $e ) {
                    error_log( '[Image Optimization] Regenerate error for attachment ' . $attachment_id . ': ' . $e->getMessage() );
                    $failed++;
                }
                
                $processed++;
            }
            
            // Check cancelled again after processing
            $cancelled = get_transient( 'io_regenerate_cancelled' );
            
            // Calculate new offset - use actual processed count, not batch size
            // because we may have skipped some images
            $new_offset = $offset + count( $attachments );
            $remaining = max( 0, $total - ( $offset + $processed ) );
            
            // If cancelled, clear the flag and return cancelled status
            if ( $cancelled ) {
                wp_send_json_success( array(
                    'processed' => $new_offset,
                    'total' => $total,
                    'success' => $success,
                    'failed' => $failed,
                    'remaining' => $remaining,
                    'done' => true,
                    'cancelled' => true,
                    'message' => __( 'Regeneration was cancelled.', 'tonkla-image-optimizer' ),
                ) );
            }
            
            wp_send_json_success( array(
                'processed' => $new_offset,
                'total' => $total,
                'success' => $success,
                'failed' => $failed,
                'remaining' => $remaining,
                'done' => $remaining === 0,
                'cancelled' => false,
            ) );
        } catch ( Exception $e ) {
            error_log( '[Image Optimization] Regenerate error: ' . $e->getMessage() );
            wp_send_json_error( array(
                'message' => $e->getMessage(),
            ) );
        }
    }
    
    /**
     * Cancel regenerate
     */
    public function cancel_regenerate() {
        check_ajax_referer( 'io_settings_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to perform this action.', 'tonkla-image-optimizer' ),
            ) );
        }
        
        // Check if we're clearing the cancel flag
        if ( isset( $_POST['cancel'] ) && ( $_POST['cancel'] === '0' || $_POST['cancel'] === false ) ) {
            delete_transient( 'io_regenerate_cancelled' );
            wp_send_json_success( array(
                'message' => __( 'Regeneration flag cleared.', 'tonkla-image-optimizer' ),
            ) );
        } else {
            // Set cancel flag
            set_transient( 'io_regenerate_cancelled', true, 300 );
            wp_send_json_success( array(
                'message' => __( 'Regeneration cancelled.', 'tonkla-image-optimizer' ),
            ) );
        }
    }
    
    /**
     * Get scan results file path
     * 
     * @return string File path
     */
    private function get_scan_results_file() {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/io_data';
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
            // Secure directory
            @file_put_contents( $dir . '/.htaccess', 'Deny from all' );
            @file_put_contents( $dir . '/index.php', '<?php // Silence is golden' );
        }
        return $dir . '/scan_results.json';
    }

    /**
     * Get scan results
     * 
     * @return array|false Scan results or false
     */
    private function get_scan_results() {
        $file = $this->get_scan_results_file();
        if ( file_exists( $file ) ) {
            $content = @file_get_contents( $file );
            if ( ! empty( $content ) ) {
                $data = json_decode( $content, true );
                if ( is_array( $data ) ) {
                    return $data;
                }
            }
        }
        return get_transient( 'io_scan_results' );
    }

    /**
     * Save scan results
     * 
     * @param array $data Scan results
     * @param int $expiration Expiration in seconds (unused for file, kept for compatibility)
     * @return bool Success
     */
    private function save_scan_results( $data, $expiration = 0 ) {
        $file = $this->get_scan_results_file();
        if ( $file ) {
            $json = json_encode( $data );
            if ( $json ) {
                // Use exclusive lock to prevent race conditions
                $result = @file_put_contents( $file, $json, LOCK_EX );
                
                if ( $result !== false ) {
                    return true;
                }
                
                // If write failed, log error and try to remove the file to prevent stale data
                error_log( '[Image Optimization] Failed to write scan results to file: ' . $file );
                @unlink( $file );
            } else {
                 error_log( '[Image Optimization] Failed to encode scan results JSON: ' . json_last_error_msg() );
            }
        }
        
        // Fallback to transient if file storage fails
        return set_transient( 'io_scan_results', $data, $expiration );
    }

    /**
     * Delete scan results
     * 
     * @return bool Success
     */
    private function delete_scan_results() {
        $file = $this->get_scan_results_file();
        if ( file_exists( $file ) ) {
            @unlink( $file );
        }
        return delete_transient( 'io_scan_results' );
    }
}
