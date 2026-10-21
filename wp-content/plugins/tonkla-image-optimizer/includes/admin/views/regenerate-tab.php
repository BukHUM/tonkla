<?php
/**
 * Regenerate Tab View
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
        <span class="dashicons dashicons-update"></span>
        <?php _e( 'Regenerate Images', 'tonkla-image-optimizer' ); ?>
    </h2>
    <p class="io-section-description">
        <?php _e( 'ปรับขนาดและแปลงเป็น WebP สำหรับภาพที่มีอยู่แล้วทั้งหมด', 'tonkla-image-optimizer' ); ?>
    </p>
    
    <div class="io-image-regenerate-section">
        <!-- Options Section -->
        <div class="io-regenerate-options" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 16px; font-weight: 600;">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php _e( 'ตัวเลือกการ Regenerate', 'tonkla-image-optimizer' ); ?>
            </h3>
            
            <!-- Regenerate Type -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e( 'ประเภทการ Regenerate:', 'tonkla-image-optimizer' ); ?>
                </label>
                <select id="io-regenerate-type" style="width: 100%; max-width: 400px;">
                    <option value="all"><?php _e( 'ทั้งหมด (Resize + WebP)', 'tonkla-image-optimizer' ); ?></option>
                    <option value="resize"><?php _e( 'เฉพาะ Resize', 'tonkla-image-optimizer' ); ?></option>
                    <option value="webp"><?php _e( 'เฉพาะ WebP', 'tonkla-image-optimizer' ); ?></option>
                </select>
            </div>
            
            <!-- Date Range -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e( 'ช่วงวันที่ (ไม่บังคับ):', 'tonkla-image-optimizer' ); ?>
                </label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <input type="date" id="io-regenerate-date-from" style="max-width: 200px;" />
                    <span><?php _e( 'ถึง', 'tonkla-image-optimizer' ); ?></span>
                    <input type="date" id="io-regenerate-date-to" style="max-width: 200px;" />
                    <button type="button" id="io-regenerate-clear-dates" class="button button-small">
                        <?php _e( 'ล้าง', 'tonkla-image-optimizer' ); ?>
                    </button>
                </div>
                <p class="description" style="margin-top: 5px;">
                    <?php _e( 'ถ้าไม่ระบุ จะ regenerate ภาพทั้งหมด', 'tonkla-image-optimizer' ); ?>
                </p>
            </div>
            
            <!-- Skip Already Regenerated -->
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="io-regenerate-skip-processed" />
                    <span><?php _e( 'ข้ามภาพที่ Regenerate แล้ว', 'tonkla-image-optimizer' ); ?></span>
                </label>
                <p class="description" style="margin-top: 5px; margin-left: 28px;">
                    <?php _e( 'ข้ามภาพที่มี metadata บ่งชี้ว่า regenerate แล้ว (แนะนำให้ปิดไว้ถ้าต้องการ regenerate ใหม่)', 'tonkla-image-optimizer' ); ?>
                </p>
            </div>
            
            <!-- Regenerate Thumbnails -->
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="io-regenerate-thumbnails" />
                    <span><?php _e( 'Regenerate Thumbnails ด้วย', 'tonkla-image-optimizer' ); ?></span>
                </label>
                <p class="description" style="margin-top: 5px; margin-left: 28px;">
                    <?php _e( 'สร้าง thumbnail sizes ใหม่ทั้งหมด', 'tonkla-image-optimizer' ); ?>
                </p>
            </div>
            
            <!-- Batch Size -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e( 'Batch Size (จำนวนภาพต่อรอบ):', 'tonkla-image-optimizer' ); ?>
                </label>
                <input type="number" id="io-regenerate-batch-size" value="10" min="1" max="100" style="width: 100px;" />
                <p class="description" style="margin-top: 5px;">
                    <?php _e( 'แนะนำ: 10-20 สำหรับเซิร์ฟเวอร์ทั่วไป, 5-10 สำหรับเซิร์ฟเวอร์ที่ช้า', 'tonkla-image-optimizer' ); ?>
                </p>
            </div>
        </div>
        
        <!-- Statistics Preview -->
        <div id="io-regenerate-stats" style="display: none; background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <h4 style="margin-top: 0; margin-bottom: 10px; font-size: 14px; font-weight: 600;">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e( 'สถิติภาพที่จะ Regenerate', 'tonkla-image-optimizer' ); ?>
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <div>
                    <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'ภาพทั้งหมด:', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="io-regenerate-stats-total" style="font-size: 18px; font-weight: 700; color: #2271b1;">0</span>
                </div>
                <div>
                    <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'ภาพที่เลือก:', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="io-regenerate-stats-selected" style="font-size: 18px; font-weight: 700; color: #00a32a;">0</span>
                </div>
                <div>
                    <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'จะข้าม:', 'tonkla-image-optimizer' ); ?></strong>
                    <span id="io-regenerate-stats-skipped" style="font-size: 18px; font-weight: 700; color: #646970;">0</span>
                </div>
            </div>
        </div>
        
        <div id="io-regenerate-controls">
            <button type="button" id="io-regenerate-images" class="button button-primary">
                <span class="dashicons dashicons-search"></span>
                <?php _e( 'ตรวจสอบภาพ', 'tonkla-image-optimizer' ); ?>
            </button>
            <p class="description">
                <?php _e( 'คลิกเพื่อตรวจสอบและนับจำนวนภาพที่จะ Regenerate ตามตัวเลือกที่กำหนด', 'tonkla-image-optimizer' ); ?>
            </p>
            <div id="io-regenerate-preview" style="display: none; margin-top: 10px; padding: 15px; background: #f0f6fc; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <p style="margin: 0 0 10px; font-weight: 600; color: #1d2327;">
                    <span class="dashicons dashicons-info"></span>
                    <?php _e( 'พบภาพทั้งหมด:', 'tonkla-image-optimizer' ); ?>
                    <span id="io-regenerate-total-count" style="color: #2271b1; font-size: 18px;">0</span>
                    <?php _e( 'ไฟล์', 'tonkla-image-optimizer' ); ?>
                </p>
                <p style="margin: 0; font-size: 13px; color: #646970;">
                    <?php _e( 'กระบวนการนี้อาจใช้เวลานาน คุณต้องการดำเนินการต่อหรือไม่?', 'tonkla-image-optimizer' ); ?>
                </p>
                <div style="margin-top: 10px;">
                    <button type="button" id="io-regenerate-confirm" class="button button-primary">
                        <?php _e( 'เริ่ม Regenerate', 'tonkla-image-optimizer' ); ?>
                    </button>
                    <button type="button" id="io-regenerate-cancel-preview" class="button">
                        <?php _e( 'ยกเลิก', 'tonkla-image-optimizer' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <div id="io-regenerate-progress" style="display: none; margin-top: 15px;">
            <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #1d2327;">
                        <span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span>
                        <?php _e( 'กำลัง Regenerate Images...', 'tonkla-image-optimizer' ); ?>
                    </h4>
                    <button type="button" id="io-regenerate-cancel" class="button button-small">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php _e( 'ยกเลิก', 'tonkla-image-optimizer' ); ?>
                    </button>
                </div>
                <div class="io-progress-container" style="margin-bottom: 15px;">
                    <div id="io-regenerate-progress-bar" class="io-progress-bar"></div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; font-size: 13px;">
                    <div>
                        <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'ทำเสร็จแล้ว:', 'tonkla-image-optimizer' ); ?></strong>
                        <span id="io-regenerate-processed" style="font-size: 20px; font-weight: 700; color: #00a32a;">0</span>
                        <span style="color: #646970;">/</span>
                        <span id="io-regenerate-total" style="color: #646970;">0</span>
                    </div>
                    <div>
                        <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'สำเร็จ:', 'tonkla-image-optimizer' ); ?></strong>
                        <span id="io-regenerate-success" style="font-size: 20px; font-weight: 700; color: #00a32a;">0</span>
                    </div>
                    <div>
                        <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'ล้มเหลว:', 'tonkla-image-optimizer' ); ?></strong>
                        <span id="io-regenerate-failed" style="font-size: 20px; font-weight: 700; color: #d63638;">0</span>
                    </div>
                    <div>
                        <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'เวลาที่ใช้:', 'tonkla-image-optimizer' ); ?></strong>
                        <span id="io-regenerate-time" style="font-size: 20px; font-weight: 700; color: #2271b1;">0:00</span>
                    </div>
                    <div>
                        <strong style="color: #646970; display: block; margin-bottom: 5px;"><?php _e( 'เวลาที่เหลือ (ประมาณ):', 'tonkla-image-optimizer' ); ?></strong>
                        <span id="io-regenerate-eta" style="font-size: 20px; font-weight: 700; color: #646970;">-</span>
                    </div>
                </div>
                <div id="io-regenerate-status" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <p style="margin: 0; font-size: 13px; color: #646970;">
                        <?php _e( 'กำลังเตรียมการ...', 'tonkla-image-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
