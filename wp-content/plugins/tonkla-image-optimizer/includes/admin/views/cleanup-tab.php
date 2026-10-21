<?php
/**
 * Cleanup Tab View
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="io-settings-section">
    <h2 class="io-section-title">
        <span class="dashicons dashicons-trash"></span>
        <?php _e( 'Unused Image Cleaner', 'tonkla-image-optimizer' ); ?>
    </h2>
    <p class="io-section-description">
        <?php _e( 'แสกนและลบภาพที่ไม่ได้ใช้ รวมถึง Thumbnail ขยะ, WebP ที่ไม่ได้ใช้, และไฟล์ที่ไม่มีใน Media Library', 'tonkla-image-optimizer' ); ?>
    </p>
    <div class="io-alert io-alert-info" style="margin-bottom: 20px;">
        <p style="margin: 0;">
            <strong><?php _e( 'หมายเหตุความปลอดภัย:', 'tonkla-image-optimizer' ); ?></strong>
            <?php _e( 'ระบบจะไม่ลบโลโก้, favicon (Site Icon), และรูปที่ตั้งในธีม/Carbon Fields โดยอัตโนมัติ', 'tonkla-image-optimizer' ); ?>
            <?php _e( 'Orphaned ไม่ได้หมายถึง “ไม่ได้ใช้ในโพสต์/หน้า” แต่หมายถึงไฟล์ใน uploads ที่ไม่มี record ใน Media Library', 'tonkla-image-optimizer' ); ?>
        </p>
    </div>
    
    <!-- Scan Section -->
    <div class="io-image-cleanup-section">
        <h3>
            <span class="dashicons dashicons-search"></span>
            <?php _e( 'Scan for Unused Images', 'tonkla-image-optimizer' ); ?>
        </h3>
        <p class="description">
            <?php _e( 'คลิกปุ่มด้านล่างเพื่อเริ่มแสกนหาภาพที่ไม่ได้ใช้ กระบวนการนี้อาจใช้เวลาสักครู่', 'tonkla-image-optimizer' ); ?>
        </p>
        <button type="button" id="io-scan-unused-images" class="button button-primary">
            <span class="dashicons dashicons-search"></span>
            <?php _e( 'Scan for Unused Images', 'tonkla-image-optimizer' ); ?>
        </button>
        
        <!-- Progress Indicator -->
        <div id="io-scan-progress" style="display: none;">
            <div class="io-progress-container">
                <div id="io-scan-progress-bar" class="io-progress-bar"></div>
            </div>
            <p id="io-scan-status" class="io-progress-status">
                <span class="dashicons dashicons-update"></span>
                <?php _e( 'กำลังแสกน...', 'tonkla-image-optimizer' ); ?>
            </p>
        </div>
        
        <!-- Error Summary Box -->
        <div id="io-error-summary" style="display: none;" class="io-error-summary-box">
            <h4 style="margin-top: 0;">
                <span class="dashicons dashicons-warning" style="color: #dba617;"></span>
                <?php _e( 'สรุปปัญหา', 'tonkla-image-optimizer' ); ?>
            </h4>
            <div id="io-error-summary-content"></div>
            <div id="io-error-actions" style="margin-top: 15px;"></div>
        </div>
    </div>
    
    <!-- Results Section -->
    <div id="io-scan-results" style="display: none;">
        <!-- Statistics Box -->
        <div class="io-stats-box">
            <h3>
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e( 'Scan Results', 'tonkla-image-optimizer' ); ?>
            </h3>
            <div class="io-stats-grid">
                <div class="io-stat-item">
                    <strong><?php _e( 'Unused Thumbnails:', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="stat-thumbnails-count" class="io-stat-value">0</span>
                    <small id="stat-thumbnails-size" class="io-stat-subtext">0 MB</small>
                </div>
                <div class="io-stat-item">
                    <strong><?php _e( 'Unused WebP:', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="stat-webp-count" class="io-stat-value">0</span>
                    <small id="stat-webp-size" class="io-stat-subtext">0 MB</small>
                </div>
                <div class="io-stat-item">
                    <strong><?php _e( 'Orphaned (ไม่ได้อยู่ใน Media):', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="stat-orphaned-count" class="io-stat-value">0</span>
                    <small id="stat-orphaned-size" class="io-stat-subtext">0 MB</small>
                </div>
                <div class="io-stat-item">
                    <strong><?php _e( 'Total Reclaimable:', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="stat-total-size" class="io-stat-value success">0 MB</span>
                </div>
                <div class="io-stat-item">
                    <strong><?php _e( 'ข้าม (protected):', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="stat-protected-skipped" class="io-stat-value">0</span>
                    <small class="io-stat-subtext"><?php _e( 'โลโก้ / favicon / รูปธีม', 'tonkla-image-optimizer' ); ?></small>
                </div>
            </div>
            <p id="io-orphaned-help" class="description" style="margin: 0;">
                <?php _e( 'Orphaned = ไฟล์ในโฟลเดอร์ uploads ที่ไม่มีใน Media Library — ไม่ใช่รูปที่ “ไม่ได้ใส่ในโพสต์”', 'tonkla-image-optimizer' ); ?>
            </p>
        </div>
        
        <!-- File Lists Tabs -->
        <div class="io-file-lists">
            <ul class="nav-tabs">
                <li>
                    <a href="#thumbnails-tab" class="nav-tab nav-tab-active" data-tab="thumbnails">
                        <span class="dashicons dashicons-images-alt2"></span>
                        <?php _e( 'Thumbnails', 'tonkla-image-optimizer' ); ?>
                        <span id="tab-thumbnails-count" class="count-badge">0</span>
                    </a>
                </li>
                <li>
                    <a href="#webp-tab" class="nav-tab" data-tab="webp">
                        <span class="dashicons dashicons-format-image"></span>
                        <?php _e( 'Unused WebP', 'tonkla-image-optimizer' ); ?>
                        <span id="tab-webp-count" class="count-badge">0</span>
                    </a>
                </li>
                <li>
                    <a href="#orphaned-tab" class="nav-tab" data-tab="orphaned">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e( 'Orphaned Images', 'tonkla-image-optimizer' ); ?>
                        <span id="tab-orphaned-count" class="count-badge">0</span>
                    </a>
                </li>
                <li id="error-files-tab-li" style="display: none;">
                    <a href="#error-tab" class="nav-tab" data-tab="error">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e( 'ไฟล์ที่มีปัญหา', 'tonkla-image-optimizer' ); ?>
                        <span id="tab-error-count" class="count-badge">0</span>
                    </a>
                </li>
                <li id="failed-files-tab-li" style="display: none;">
                    <a href="#failed-tab" class="nav-tab" data-tab="failed">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php _e( 'Failed to Delete', 'tonkla-image-optimizer' ); ?>
                        <span id="tab-failed-count" class="count-badge">0</span>
                    </a>
                </li>
            </ul>
            
            <!-- Tab Contents -->
            <div class="tab-content">
                <!-- Thumbnails Tab -->
                <div id="thumbnails-tab" class="tab-pane active">
                    <div id="thumbnails-list" class="file-list">
                        <p class="file-list-empty">
                            <?php _e( 'ไม่มี thumbnail ที่ไม่ได้ใช้', 'tonkla-image-optimizer' ); ?>
                        </p>
                    </div>
                </div>
                
                <!-- WebP Tab -->
                <div id="webp-tab" class="tab-pane">
                    <div id="webp-list" class="file-list">
                        <p class="file-list-empty">
                            <?php _e( 'ไม่มี WebP ที่ไม่ได้ใช้', 'tonkla-image-optimizer' ); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Orphaned Tab -->
                <div id="orphaned-tab" class="tab-pane">
                    <div id="orphaned-list" class="file-list">
                        <p class="file-list-empty">
                            <?php _e( 'ไม่มี orphaned images', 'tonkla-image-optimizer' ); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Error Files Tab (from scan) -->
                <div id="error-tab" class="tab-pane">
                    <div id="error-list" class="file-list">
                        <p class="file-list-empty">
                            <?php _e( 'ไม่มีไฟล์ที่มีปัญหา', 'tonkla-image-optimizer' ); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Failed Files Tab (from delete) -->
                <div id="failed-tab" class="tab-pane">
                    <div id="failed-list" class="file-list">
                        <p class="file-list-empty">
                            <?php _e( 'ไม่มีไฟล์ที่ลบไม่สำเร็จ', 'tonkla-image-optimizer' ); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="io-cleanup-actions">
            <h3>
                <span class="dashicons dashicons-trash"></span>
                <?php _e( 'Delete Files', 'tonkla-image-optimizer' ); ?>
            </h3>
            <p class="description">
                <?php _e( 'เลือกไฟล์ที่ต้องการลบ หรือลบตามประเภท ระวัง: การลบไฟล์ไม่สามารถยกเลิกได้', 'tonkla-image-optimizer' ); ?>
            </p>

            <!-- Delete by Type Buttons -->
            <div style="margin-bottom: 15px; padding: 15px; background: #f0f6fc; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h4 style="margin-top: 0; margin-bottom: 10px;">
                    <span class="dashicons dashicons-filter"></span>
                    <?php _e( 'ลบตามประเภท', 'tonkla-image-optimizer' ); ?>
                </h4>
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #646970;">
                    <?php _e( 'ลบไฟล์ทั้งหมดในแต่ละประเภทโดยไม่ต้องเลือกทีละไฟล์', 'tonkla-image-optimizer' ); ?>
                </p>
                <div>
                    <button type="button" id="io-delete-thumbnails" class="button button-secondary" style="margin-right: 10px;">
                        <span class="dashicons dashicons-images-alt2"></span>
                        <?php _e( 'ลบ Thumbnails ทั้งหมด', 'tonkla-image-optimizer' ); ?>
                        <span id="io-delete-thumbnails-count" class="count-badge" style="margin-left: 5px;">0</span>
                    </button>
                    <button type="button" id="io-delete-webp" class="button button-secondary" style="margin-right: 10px;">
                        <span class="dashicons dashicons-format-image"></span>
                        <?php _e( 'ลบ WebP ทั้งหมด', 'tonkla-image-optimizer' ); ?>
                        <span id="io-delete-webp-count" class="count-badge" style="margin-left: 5px;">0</span>
                    </button>
                    <button type="button" id="io-delete-orphaned" class="button button-secondary">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e( 'ลบ Orphaned ทั้งหมด', 'tonkla-image-optimizer' ); ?>
                        <span id="io-delete-orphaned-count" class="count-badge" style="margin-left: 5px;">0</span>
                    </button>
                </div>
            </div>

            <!-- Manual Selection Buttons -->
            <div style="margin-bottom: 15px;">
                <h4 style="margin-bottom: 10px;">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e( 'ลบแบบเลือกเอง', 'tonkla-image-optimizer' ); ?>
                </h4>
                <p class="io-include-orphaned-option" style="margin: 0 0 12px 0;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px;">
                        <input type="checkbox" id="io-include-orphaned-in-delete-all" value="1" />
                        <span><?php _e( 'รวม Orphaned ในการลบทั้งหมด (Delete All)', 'tonkla-image-optimizer' ); ?></span>
                    </label>
                    <span class="description" style="display: block; margin: 4px 0 0 28px;">
                        <?php _e( 'ค่าเริ่มต้นปิด เพื่อลดความเสี่ยงลบไฟล์ที่อยู่นอก Media Library โดยไม่ตั้งใจ', 'tonkla-image-optimizer' ); ?>
                    </span>
                </p>
                <div>
                    <button type="button" id="io-delete-selected" class="button button-secondary" disabled>
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e( 'Delete Selected', 'tonkla-image-optimizer' ); ?>
                    </button>
                    <button type="button" id="io-delete-all" class="button button-danger">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e( 'Delete All', 'tonkla-image-optimizer' ); ?>
                    </button>
                    <button type="button" id="io-download-report" class="button button-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e( 'Download Report (CSV)', 'tonkla-image-optimizer' ); ?>
                    </button>
                </div>
            </div>
            
            <!-- Delete Progress -->
            <div id="io-delete-progress" style="display: none;">
                <div class="io-progress-container">
                    <div id="io-delete-progress-bar" class="io-progress-bar"></div>
                </div>
                <p id="io-delete-status" class="io-progress-status"></p>
            </div>
        </div>
    </div>
</div>
