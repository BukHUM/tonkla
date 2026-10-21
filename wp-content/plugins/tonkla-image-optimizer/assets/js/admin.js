/**
 * Admin JavaScript for Image Optimization Plugin
 *
 * @package Image_Optimization
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    // Wait for jQuery and DOM to be ready
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    
    jQuery(document).ready(function($) {
        console.log('Image Optimization admin script loaded');
        
        // Check if tab elements exist
        var $mainTabs = $('.io-main-tab');
        var $tabContents = $('.io-main-tab-content');
        console.log('Found main tabs:', $mainTabs.length);
        console.log('Found tab contents:', $tabContents.length);
        console.log('Tab contents IDs:', $tabContents.map(function() { return this.id; }).get());
        
        // Main tab switching function
        function switchMainTab(tab) {
            console.log('Switching to tab:', tab);
            
            // Update active tab
            $('.io-main-tab').removeClass('nav-tab-active');
            $('.io-main-tab[data-tab="' + tab + '"]').addClass('nav-tab-active');
            
            // Show/hide tab content
            $('.io-main-tab-content').hide();
            var $targetTab = $('#io-tab-' + tab);
            
            if ($targetTab.length === 0) {
                console.error('Tab content not found: #io-tab-' + tab);
                return false;
            }
            
            $targetTab.fadeIn(200);
            console.log('Tab switched to:', tab);
            
            // Update URL without page reload (for bookmarking)
            var baseUrl = window.location.pathname;
            var search = window.location.search;
            var newSearch = search.replace(/[&?]tab=[^&]*/, '').replace(/^&/, '?');
            if (newSearch === '?') newSearch = '';
            var separator = newSearch ? '&' : '?';
            var newUrl = baseUrl + newSearch + separator + (newSearch ? '' : 'page=tonkla-image-optimizer&') + 'tab=' + tab;
            
            if (window.history && window.history.pushState) {
                window.history.pushState({tab: tab}, '', newUrl);
            }
            
            return true;
        }
        
        // Main tab switching (Optimization <-> Cleanup) - Client-side navigation
        // Use both direct binding and delegated event
        $('.io-main-tab').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            var $tab = $(this);
            var tab = $tab.data('tab');
            
            console.log('Tab clicked (direct):', tab, 'Element:', $tab);
            
            if (!tab) {
                console.error('Tab data not found on element:', $tab);
                return false;
            }
            
            return switchMainTab(tab);
        });
        
        // Also use delegated event as fallback
        $(document).on('click', '.io-main-tab', function(e) {
            // This is a fallback - the direct binding above should handle it
            // But we'll prevent default just in case
            if ($(this).hasClass('io-main-tab')) {
                e.preventDefault();
                var tab = $(this).data('tab');
                console.log('Tab clicked (delegated):', tab);
                if (tab) {
                    switchMainTab(tab);
                }
                return false;
            }
        });
        
        // Tab switching for file lists (within cleanup tab)
        $(document).on('click', '.io-file-lists .nav-tab', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            
            // Update active tab
            $('.io-file-lists .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show/hide tab content
            $('.io-file-lists .tab-pane').removeClass('active').hide();
            $('#' + tab + '-tab').addClass('active').fadeIn(200);
        });
        
        // Store scan progress interval to clear it properly
        var scanProgressInterval = null;
        
        // Function to reset scan state
        function resetScanState() {
            var $btn = $('#io-scan-unused-images');
            var $progress = $('#io-scan-progress');
            var $progressBar = $('#io-scan-progress-bar');
            var $status = $('#io-scan-status');
            
            // Clear any running intervals
            if (scanProgressInterval !== null) {
                clearInterval(scanProgressInterval);
                scanProgressInterval = null;
            }
            
            // Reset UI elements
            $btn.prop('disabled', false);
            $progressBar.css('width', '0%');
            $status.html(''); // Clear status message
            $progress.hide(); // Hide progress section
        }
        
        // Scan for unused images
        $(document).on('click', '#io-scan-unused-images', function(e) {
            e.preventDefault();
            console.log('Scan button clicked');
            
            var $btn = $(this);
            var $progress = $('#io-scan-progress');
            var $progressBar = $('#io-scan-progress-bar');
            var $status = $('#io-scan-status');
            var $results = $('#io-scan-results');
            
            console.log('Button:', $btn.length, 'Progress:', $progress.length, 'Status:', $status.length);
            
            if ($btn.length === 0) {
                console.error('Scan button not found!');
                alert('Error: Scan button not found. Please refresh the page.');
                return;
            }
            
            // Reset any previous state first
            resetScanState();
            
            $btn.prop('disabled', true);
            $progress.show();
            $progressBar.css('width', '0%');
            $status.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; display: inline-block;"></span> ' + (ioAdmin.i18n.scanning || 'กำลังแสกน...'));
            $results.hide();
            
            console.log('Starting scan...');
            
            // Simulate progress (since we can't get real-time progress from AJAX)
            scanProgressInterval = setInterval(function() {
                var currentWidth = parseInt($progressBar.css('width')) || 0;
                var containerWidth = $progressBar.parent().width();
                var percent = (currentWidth / containerWidth) * 100;
                if (percent < 90) {
                    percent += Math.random() * 5;
                    $progressBar.css('width', Math.min(percent, 90) + '%');
                }
            }, 500);
            
            console.log('AJAX URL:', ioAdmin.ajaxurl);
            console.log('Nonce:', ioAdmin.nonce);
            
            var ajaxRequest = $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'io_scan_unused_images',
                    nonce: ioAdmin.nonce,
                    scan_type: 'all',
                    use_cache: true,
                    max_files: 0,
                    time_limit: 0
                },
                timeout: 300000, // 5 minutes timeout
                beforeSend: function() {
                    console.log('AJAX request sent');
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    
                    // Clear progress interval
                    if (scanProgressInterval !== null) {
                        clearInterval(scanProgressInterval);
                        scanProgressInterval = null;
                    }
                    
                    if (response.success) {
                        console.log('Scan completed successfully', response);
                        console.log('Response data:', response.data);
                        console.log('Response data type:', typeof response.data);
                        console.log('Response data keys:', response.data ? Object.keys(response.data) : 'null');
                        
                        // Validate response data structure
                        if (!response.data) {
                            console.error('Response data is null or undefined!');
                            alert('เกิดข้อผิดพลาด: ไม่ได้รับข้อมูลผลลัพธ์ กรุณาลองใหม่อีกครั้ง');
                            $btn.prop('disabled', false);
                            resetScanState();
                            return;
                        }
                        
                        // Ensure data has required structure
                        if (!response.data.hasOwnProperty('thumbnails')) {
                            response.data.thumbnails = [];
                        }
                        if (!response.data.hasOwnProperty('webp')) {
                            response.data.webp = [];
                        }
                        if (!response.data.hasOwnProperty('orphaned')) {
                            response.data.orphaned = [];
                        }
                        if (!response.data.hasOwnProperty('statistics')) {
                            response.data.statistics = {};
                        }
                        if (!response.data.hasOwnProperty('error_files')) {
                            response.data.error_files = [];
                        }
                        
                        $progressBar.css('width', '100%');
                        var scanTime = response.data.statistics && response.data.statistics.scan_time ? 
                            ' (' + response.data.statistics.scan_time + 's)' : '';
                        
                        // Check if there are errors
                        var hasErrors = response.data.error_files && response.data.error_files.length > 0;
                        var errorCount = hasErrors ? response.data.error_files.length : 0;
                        var totalScanned = response.data.statistics && response.data.statistics.total_scanned ? 
                            response.data.statistics.total_scanned : 0;
                        
                        if (hasErrors) {
                            $status.html(
                                '<span class="dashicons dashicons-warning" style="color: #dba617;"></span> ' + 
                                '<span style="color: #dba617; font-weight: 500;">แสกนเสร็จสิ้น</span>' + scanTime + 
                                ' <span style="color: #d63638;">(พบข้อผิดพลาด ' + errorCount + ' ไฟล์)</span>'
                            );
                        } else {
                            $status.html('<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ' + 
                                '<span style="color: #00a32a; font-weight: 500;">แสกนเสร็จสิ้น</span>' + scanTime);
                        }
                        
                        // Check if results element exists
                        if ($results.length === 0) {
                            console.error('Results element #io-scan-results not found!');
                            alert('เกิดข้อผิดพลาด: ไม่พบ element สำหรับแสดงผลลัพธ์ กรุณา refresh หน้าเว็บ');
                            $btn.prop('disabled', false);
                            return;
                        }
                        
                        // Display results (including error files)
                        console.log('Displaying scan results...', {
                            thumbnails: (response.data.thumbnails || []).length,
                            webp: (response.data.webp || []).length,
                            orphaned: (response.data.orphaned || []).length,
                            statistics: response.data.statistics,
                            hasResults: $results.length > 0
                        });
                        
                        // Always show results section, even if empty
                        try {
                            displayScanResults(response.data);
                        } catch (e) {
                            console.error('Error in displayScanResults:', e);
                            alert('เกิดข้อผิดพลาดในการแสดงผลลัพธ์: ' + e.message);
                        }
                        
                        // Force show results with animation and ensure visibility
                        $results.css({
                            'display': 'block',
                            'visibility': 'visible',
                            'opacity': '1'
                        }).slideDown(300);
                        console.log('Results element shown:', $results.is(':visible'), 'Display:', $results.css('display'), 'Height:', $results.height());
                        
                        // Double check - if still not visible, force it
                        setTimeout(function() {
                            if (!$results.is(':visible') || $results.height() === 0) {
                                console.warn('Results still not visible, forcing display');
                                $results.css({
                                    'display': 'block !important',
                                    'visibility': 'visible !important',
                                    'opacity': '1 !important',
                                    'height': 'auto !important'
                                }).show();
                                
                                // Try to scroll to it
                                $('html, body').animate({
                                    scrollTop: $results.offset().top - 100
                                }, 500);
                            }
                        }, 500);
                        
                        // Display error summary if there are errors
                        if (response.data.error_files && response.data.error_files.length > 0) {
                            displayErrorSummary(response.data.error_files);
                        } else {
                            $('#io-error-summary').hide();
                        }
                        
                        // Enable button and reset state
                        $btn.prop('disabled', false);
                        
                        // Scroll to results section smoothly
                        setTimeout(function() {
                            if ($results.length && $results.is(':visible')) {
                                $('html, body').animate({
                                    scrollTop: $results.offset().top - 100
                                }, 500);
                            }
                        }, 500);
                        
                        // Hide progress after a short delay
                        setTimeout(function() {
                            $progress.fadeOut(500);
                        }, 2000);
                    } else {
                        // Reset state on fatal error
                        resetScanState();
                        
                        // Show error message briefly
                        $progress.show();
                        $status.html('<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span> ' + 
                            '<span style="color: #d63638; font-weight: 500;">' + (response.data.message || 'เกิดข้อผิดพลาด') + '</span>');
                        
                        // Auto-hide error after 5 seconds
                        setTimeout(function() {
                            resetScanState();
                        }, 5000);
                    }
                    $btn.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr);
                    
                    // Clear progress interval
                    if (scanProgressInterval !== null) {
                        clearInterval(scanProgressInterval);
                        scanProgressInterval = null;
                    }
                    
                    var errorMsg = ioAdmin.i18n.scanError || 'เกิดข้อผิดพลาดในการแสกน';
                    
                    // Handle different error types
                    if (status === 'timeout') {
                        errorMsg = ioAdmin.i18n.scanTimeout || 'การแสกนใช้เวลานานเกินไป กรุณาลองใหม่อีกครั้ง';
                    } else if (status === 'abort') {
                        errorMsg = 'การแสกนถูกยกเลิก';
                        resetScanState();
                        return; // Don't show alert for abort
                    } else if (xhr.status === 0) {
                        errorMsg = 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต';
                    } else if (xhr.status === 403) {
                        errorMsg = 'คุณไม่มีสิทธิ์ในการดำเนินการนี้';
                    } else if (xhr.status === 500) {
                        errorMsg = 'เกิดข้อผิดพลาดที่เซิร์ฟเวอร์ กรุณาตรวจสอบ log หรือติดต่อผู้ดูแลระบบ';
                    } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        // Use the user-friendly error message from server
                        errorMsg = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        // Try to parse error from response
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.data && response.data.message) {
                                errorMsg = response.data.message;
                            }
                        } catch (e) {
                            // If parsing fails, use generic message
                            errorMsg = 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ กรุณาลองใหม่อีกครั้ง';
                        }
                    }
                    
                    // Reset state first
                    resetScanState();
                    
                    // Show error message briefly
                    $progress.show();
                    $progressBar.css('width', '0%');
                    $status.html(
                        '<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span> ' + 
                        '<span style="color: #d63638; font-weight: 500;">' + errorMsg + '</span>'
                    );
                    $btn.prop('disabled', false);
                    
                    // Don't auto-hide error - keep it visible so user can see the summary
                    // Show alert with helpful message (only once)
                    if (!window.ioScanErrorShown) {
                        window.ioScanErrorShown = true;
                        alert('เกิดข้อผิดพลาดในการแสกน\n\n' + errorMsg + '\n\nกรุณาตรวจสอบสรุปปัญหาด้านล่าง');
                        // Reset flag after 2 seconds
                        setTimeout(function() {
                            window.ioScanErrorShown = false;
                        }, 2000);
                    }
                    
                    // Try to show error summary if we have error data from previous scan
                    // This won't work for fatal errors, but we'll show what we can
                    $('#io-error-summary').show();
                    var summaryHtml = '<div style="background: #fcf0f1; padding: 15px; margin-bottom: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                        '<p style="margin: 0;"><strong>เกิดข้อผิดพลาดร้ายแรง:</strong></p>' +
                        '<p style="margin: 10px 0 0 0;">' + escapeHtml(errorMsg) + '</p>' +
                        '</div>';
                    
                    // Add memory limit info
                    summaryHtml += '<div style="background: #f0f6fc; padding: 15px; margin-bottom: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                        '<p style="margin: 0 0 10px 0;"><strong>การตั้งค่า Memory Limit:</strong></p>' +
                        '<p style="margin: 0; font-size: 12px; color: #646970; white-space: pre-line;">' +
                        'แนะนำ: memory_limit = 256M (หรือ 512M สำหรับเว็บไซต์ที่มีภาพจำนวนมาก)\n\n' +
                        'วิธีแก้ไข:\n' +
                        '1. เปิดไฟล์ php.ini (สำหรับ XAMPP: C:\\xampp\\php\\php.ini)\n' +
                        '2. หา memory_limit และเปลี่ยนเป็น: memory_limit = 256M\n' +
                        '3. บันทึกไฟล์และรีสตาร์ท Apache\n' +
                        '4. หรือเพิ่มใน wp-config.php: ini_set("memory_limit", "256M");' +
                        '</p>' +
                        '</div>';
                    
                    // Add log file locations
                    summaryHtml += '<div style="background: #f0f6fc; padding: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                        '<p style="margin: 0 0 10px 0;"><strong>ตำแหน่ง Log ไฟล์ที่ควรตรวจสอบ:</strong></p>' +
                        '<ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #646970;">' +
                        '<li style="margin-bottom: 8px;"><strong>WordPress Debug Log:</strong><br>' +
                        '<code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px; display: inline-block; margin-top: 4px;">wp-content/debug.log</code><br>' +
                        '<span style="font-size: 11px;">(ถ้าเปิด WP_DEBUG_LOG ใน wp-config.php)</span></li>' +
                        '<li style="margin-bottom: 8px;"><strong>XAMPP PHP Error Log:</strong><br>' +
                        '<code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px; display: inline-block; margin-top: 4px;">C:\\xampp\\php\\logs\\php_error_log</code></li>' +
                        '<li style="margin-bottom: 8px;"><strong>XAMPP Apache Error Log:</strong><br>' +
                        '<code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px; display: inline-block; margin-top: 4px;">C:\\xampp\\apache\\logs\\error.log</code></li>' +
                        '<li><strong>ตรวจสอบ PHP Error Log:</strong><br>' +
                        '<span style="font-size: 11px;">สร้างไฟล์ phpinfo.php ใน root และเปิดดูค่า error_log</span></li>' +
                        '</ul>' +
                        '</div>';
                    
                    $('#io-error-summary-content').html(summaryHtml);
                    $('#io-error-actions').html(
                        '<button type="button" id="io-retry-scan" class="button button-primary">' +
                        '<span class="dashicons dashicons-update"></span> ลองแสกนอีกครั้ง' +
                        '</button>'
                    );
                }
            });
            
            // Store ajax request for potential cancellation
            window.ioScanAjaxRequest = ajaxRequest;
        });
        
        // Display scan results
        function displayScanResults(data) {
            console.log('displayScanResults called with data:', data);
            
            var stats = data.statistics || {};
            var $status = $('#io-scan-status');
            
            // Update statistics
            $('#stat-thumbnails-count').text(stats.total_thumbnails || 0);
            $('#stat-thumbnails-size').text((stats.thumbnails_size_mb || 0).toFixed(2) + ' MB');
            $('#stat-webp-count').text(stats.total_webp || 0);
            $('#stat-webp-size').text((stats.webp_size_mb || 0).toFixed(2) + ' MB');
            $('#stat-orphaned-count').text(stats.total_orphaned || 0);
            $('#stat-orphaned-size').text((stats.orphaned_size_mb || 0).toFixed(2) + ' MB');
            $('#stat-total-size').text((stats.total_size_mb || 0).toFixed(2) + ' MB');
            $('#stat-protected-skipped').text(stats.protected_skipped || 0);
            
            console.log('Statistics updated:', {
                thumbnails: stats.total_thumbnails || 0,
                webp: stats.total_webp || 0,
                orphaned: stats.total_orphaned || 0,
                protected_skipped: stats.protected_skipped || 0,
                total_size: stats.total_size_mb || 0
            });
            
            // Show scan statistics if available
            if (stats.total_scanned) {
                var scanInfo = 'แสกนทั้งหมด: ' + stats.total_scanned + ' ไฟล์';
                if (stats.total_errors > 0) {
                    scanInfo += ' (พบข้อผิดพลาด: ' + stats.total_errors + ' ไฟล์)';
                }
                if (stats.protected_skipped > 0) {
                    scanInfo += ' | ข้าม protected: ' + stats.protected_skipped;
                }
                if (!$('#io-scan-info').length) {
                    $status.after('<p id="io-scan-info" style="color: #646970; font-size: 12px; margin-top: 5px;">' + scanInfo + '</p>');
                } else {
                    $('#io-scan-info').text(scanInfo);
                }
            }
            
            // Update tab counts
            $('#tab-thumbnails-count').text(stats.total_thumbnails || 0);
            $('#tab-webp-count').text(stats.total_webp || 0);
            $('#tab-orphaned-count').text(stats.total_orphaned || 0);
            
            // Display file lists
            displayFileList('thumbnails', data.thumbnails || []);
            displayFileList('webp', data.webp || []);
            displayFileList('orphaned', data.orphaned || []);
            
            // Display error files from scan (different from failed delete files)
            if (data.error_files && data.error_files.length > 0) {
                displayErrorFiles(data.error_files);
            } else {
                $('#error-files-tab-li').hide();
            }
            
            // Display failed delete files if any
            if (data.failed_files && data.failed_files.length > 0) {
                displayFailedFiles(data.failed_files);
            } else {
                $('#failed-files-tab-li').hide();
                $('#failed-list').html('<p style="color: #646970; text-align: center; padding: 20px;">' + 
                    (ioAdmin.i18n.noFailedFiles || 'ไม่มีไฟล์ที่ลบไม่สำเร็จ') + '</p>');
            }
            
            // Enable/disable delete buttons
            var totalFiles = (stats.total_thumbnails || 0) + (stats.total_webp || 0) + (stats.total_orphaned || 0);
            var deletableWithoutOrphaned = (stats.total_thumbnails || 0) + (stats.total_webp || 0);
            $('#io-delete-selected').prop('disabled', totalFiles === 0);
            var includeOrphanedChecked = $('#io-include-orphaned-in-delete-all').is(':checked');
            $('#io-delete-all').prop('disabled', includeOrphanedChecked ? totalFiles === 0 : deletableWithoutOrphaned === 0);
            
            // Update delete by type button counts and states
            var thumbnailsCount = stats.total_thumbnails || 0;
            var webpCount = stats.total_webp || 0;
            var orphanedCount = stats.total_orphaned || 0;
            
            $('#io-delete-thumbnails-count').text(thumbnailsCount);
            $('#io-delete-webp-count').text(webpCount);
            $('#io-delete-orphaned-count').text(orphanedCount);
            
            $('#io-delete-thumbnails').prop('disabled', thumbnailsCount === 0);
            $('#io-delete-webp').prop('disabled', webpCount === 0);
            $('#io-delete-orphaned').prop('disabled', orphanedCount === 0);
            
            // Show message if no files found
            if (totalFiles === 0 && (!data.error_files || data.error_files.length === 0)) {
                var $noResults = $('#io-no-results-message');
                if ($noResults.length === 0) {
                    $('#io-scan-results').prepend(
                        '<div id="io-no-results-message" style="background: #f0f6fc; padding: 15px; margin-bottom: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                        '<p style="margin: 0; color: #2271b1; font-weight: 500;">' +
                        '<span class="dashicons dashicons-info" style="vertical-align: middle;"></span> ' +
                        'ไม่พบไฟล์ที่ไม่ได้ใช้งาน</p>' +
                        '<p style="margin: 10px 0 0 0; color: #646970; font-size: 13px;">' +
                        'ระบบไม่พบไฟล์ภาพที่ไม่ได้ใช้งานในระบบของคุณ</p>' +
                        '</div>'
                    );
                }
            } else {
                $('#io-no-results-message').remove();
            }
        }
        
        // Display error summary with fix options
        function displayErrorSummary(errorFiles) {
            if (!errorFiles || errorFiles.length === 0) {
                $('#io-error-summary').hide();
                currentErrorFiles = [];
                return;
            }
            
            // Store error files globally
            currentErrorFiles = errorFiles;
            
            var $summary = $('#io-error-summary');
            var $content = $('#io-error-summary-content');
            var $actions = $('#io-error-actions');
            
            // Group errors by type
            var errorsByType = {};
            var fixableErrors = [];
            var nonFixableErrors = [];
            
            errorFiles.forEach(function(error) {
                var errorType = detectErrorType(error.error);
                if (!errorsByType[errorType]) {
                    errorsByType[errorType] = [];
                }
                errorsByType[errorType].push(error);
                
                // Check if error is fixable
                if (isErrorFixable(errorType)) {
                    fixableErrors.push({
                        type: errorType,
                        file: error.file || error.filename || '',
                        error: error
                    });
                } else {
                    nonFixableErrors.push({
                        type: errorType,
                        file: error.file || error.filename || '',
                        error: error
                    });
                }
            });
            
            // Build summary HTML
            var html = '<div style="background: #fff5f5; padding: 15px; margin-bottom: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
            html += '<p style="margin: 0 0 10px 0;"><strong>พบปัญหา ' + errorFiles.length + ' รายการ:</strong></p>';
            
            // Show error types summary
            var errorTypes = Object.keys(errorsByType);
            html += '<ul style="margin: 0; padding-left: 20px;">';
            errorTypes.forEach(function(type) {
                var typeLabel = getErrorTypeLabel(type);
                html += '<li>' + typeLabel + ': ' + errorsByType[type].length + ' รายการ</li>';
            });
            html += '</ul>';
            html += '</div>';
            
            // Show fixable errors
            if (fixableErrors.length > 0) {
                html += '<div style="background: #f0f6fc; padding: 15px; margin-bottom: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                html += '<p style="margin: 0 0 10px 0;"><strong>ปัญหาที่แก้ไขได้อัตโนมัติ:</strong></p>';
                html += '<ul style="margin: 0; padding-left: 20px;">';
                fixableErrors.forEach(function(item) {
                    var fileName = item.file ? item.file.split(/[\\/]/).pop() : 'N/A';
                    html += '<li>' + escapeHtml(fileName) + ' - ' + getErrorTypeLabel(item.type) + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            // Show non-fixable errors with hints
            if (nonFixableErrors.length > 0) {
                html += '<div style="background: #fcf0f1; padding: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                html += '<p style="margin: 0 0 10px 0;"><strong>ปัญหาที่ต้องแก้ไขด้วยตนเอง:</strong></p>';
                html += '<ul style="margin: 0; padding-left: 20px;">';
                nonFixableErrors.forEach(function(item) {
                    var fileName = item.file ? item.file.split(/[\\/]/).pop() : 'N/A';
                    var hint = getErrorHint(item.type);
                    html += '<li style="margin-bottom: 12px;">';
                    html += '<strong>' + escapeHtml(fileName) + '</strong><br>';
                    html += '<div style="font-size: 12px; color: #646970; margin-top: 5px; white-space: pre-line; background: #fff; padding: 8px; border-radius: 4px; border: 1px solid #e0e0e0;">' + escapeHtml(hint) + '</div>';
                    html += '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            // Add log file locations info
            html += '<div style="background: #f0f6fc; padding: 15px; margin-top: 15px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
            html += '<p style="margin: 0 0 10px 0;"><strong>ตำแหน่ง Log ไฟล์ที่ควรตรวจสอบ:</strong></p>';
            html += '<ul style="margin: 0; padding-left: 20px; font-size: 12px;">';
            html += '<li><strong>WordPress Debug Log:</strong><br>';
            html += '<code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px;">wp-content/debug.log</code><br>';
            html += '<span style="color: #646970;">(ถ้าเปิด WP_DEBUG_LOG ใน wp-config.php)</span></li>';
            html += '<li style="margin-top: 8px;"><strong>XAMPP PHP Error Log:</strong><br>';
            html += '<code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px;">C:\\xampp\\php\\logs\\php_error_log</code></li>';
            html += '<li style="margin-top: 8px;"><strong>XAMPP Apache Error Log:</strong><br>';
            html += '<code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px;">C:\\xampp\\apache\\logs\\error.log</code></li>';
            html += '<li style="margin-top: 8px;"><strong>ตรวจสอบ PHP Error Log:</strong><br>';
            html += '<span style="color: #646970;">สร้างไฟล์ phpinfo.php ใน root และเปิดดูค่า error_log</span></li>';
            html += '</ul>';
            html += '</div>';
            
            $content.html(html);
            
            // Build action buttons
            var actionsHtml = '';
            if (fixableErrors.length > 0) {
                actionsHtml += '<button type="button" id="io-fix-all-errors" class="button button-primary">';
                actionsHtml += '<span class="dashicons dashicons-admin-tools"></span> ';
                actionsHtml += 'แก้ไขปัญหาทั้งหมด (' + fixableErrors.length + ')';
                actionsHtml += '</button> ';
            }
            
            actionsHtml += '<button type="button" id="io-view-error-details" class="button button-secondary">';
            actionsHtml += '<span class="dashicons dashicons-visibility"></span> ';
            actionsHtml += 'ดูรายละเอียดทั้งหมด';
            actionsHtml += '</button>';
            
            $actions.html(actionsHtml);
            $summary.show();
        }
        
        // Detect error type from error message
        function detectErrorType(errorMessage) {
            if (!errorMessage) return 'unknown';
            
            var msg = errorMessage.toLowerCase();
            
            if (msg.indexOf('ไม่สามารถอ่าน') !== -1 || msg.indexOf('not readable') !== -1 || 
                msg.indexOf('permission') !== -1 || msg.indexOf('สิทธิ์') !== -1) {
                return 'permission';
            }
            if (msg.indexOf('ไม่พบ') !== -1 || msg.indexOf('not found') !== -1 || 
                msg.indexOf('no such file') !== -1) {
                return 'not_found';
            }
            if (msg.indexOf('database') !== -1 || msg.indexOf('ฐานข้อมูล') !== -1 || 
                msg.indexOf('mysql') !== -1 || msg.indexOf('query') !== -1) {
                return 'database';
            }
            if (msg.indexOf('memory') !== -1 || msg.indexOf('หน่วยความจำ') !== -1) {
                return 'memory';
            }
            if (msg.indexOf('timeout') !== -1 || msg.indexOf('ใช้เวลานาน') !== -1) {
                return 'timeout';
            }
            
            return 'unknown';
        }
        
        // Check if error is fixable
        function isErrorFixable(errorType) {
            var fixableTypes = ['permission', 'not_readable'];
            return fixableTypes.indexOf(errorType) !== -1;
        }
        
        // Get error type label
        function getErrorTypeLabel(errorType) {
            var labels = {
                'permission': 'ปัญหาสิทธิ์การเข้าถึง',
                'not_readable': 'ไม่สามารถอ่านไฟล์',
                'not_found': 'ไม่พบไฟล์/โฟลเดอร์',
                'database': 'ปัญหาฐานข้อมูล',
                'memory': 'หน่วยความจำไม่เพียงพอ',
                'timeout': 'ใช้เวลานานเกินไป',
                'unknown': 'ไม่ทราบสาเหตุ'
            };
            return labels[errorType] || labels['unknown'];
        }
        
        // Get error hint/instructions
        function getErrorHint(errorType) {
            var hints = {
                'permission': 'วิธีแก้ไข: ใช้ FTP/SSH เพื่อเปลี่ยนสิทธิ์โฟลเดอร์ uploads เป็น 755 หรือ 775',
                'not_readable': 'วิธีแก้ไข: ตรวจสอบสิทธิ์การเข้าถึงโฟลเดอร์ uploads และไฟล์ภายใน',
                'not_found': 'วิธีแก้ไข: ตรวจสอบว่าโฟลเดอร์ uploads มีอยู่จริง และ path ถูกต้อง',
                'database': 'วิธีแก้ไข: ตรวจสอบการเชื่อมต่อฐานข้อมูล และ wp-config.php',
                'memory': 'หน่วยความจำไม่เพียงพอ\n' +
                         'วิธีแก้ไข:\n' +
                         '1. เปิดไฟล์ php.ini (สำหรับ XAMPP: C:\\xampp\\php\\php.ini)\n' +
                         '2. หา memory_limit และเปลี่ยนเป็น: memory_limit = 256M (หรือ 512M สำหรับเว็บไซต์ที่มีภาพจำนวนมาก)\n' +
                         '3. บันทึกไฟล์และรีสตาร์ท Apache\n' +
                         '4. หรือเพิ่มใน wp-config.php: ini_set("memory_limit", "256M");',
                'timeout': 'การแสกนใช้เวลานานเกินไป\n' +
                          'วิธีแก้ไข:\n' +
                          '1. เปิดไฟล์ php.ini (สำหรับ XAMPP: C:\\xampp\\php\\php.ini)\n' +
                          '2. หา max_execution_time และเปลี่ยนเป็น: max_execution_time = 300 (5 นาที)\n' +
                          '3. บันทึกไฟล์และรีสตาร์ท Apache\n' +
                          '4. หรือเพิ่มใน wp-config.php: set_time_limit(300);',
                'unknown': 'วิธีแก้ไข: ตรวจสอบ log ไฟล์สำหรับรายละเอียดเพิ่มเติม'
            };
            return hints[errorType] || hints['unknown'];
        }
        
        // Store error files globally for fix function
        var currentErrorFiles = [];
        
        // Fix all fixable errors
        $(document).on('click', '#io-fix-all-errors', function() {
            var $btn = $(this);
            var originalText = $btn.html();
            
            if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการแก้ไขปัญหาทั้งหมด?')) {
                return;
            }
            
            $btn.prop('disabled', true);
            $btn.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> กำลังแก้ไข...');
            
            // Filter fixable errors
            var fixableErrors = currentErrorFiles.filter(function(error) {
                var errorType = detectErrorType(error.error);
                return isErrorFixable(errorType);
            });
            
            if (fixableErrors.length === 0) {
                alert('ไม่มีปัญหาที่แก้ไขได้อัตโนมัติ');
                $btn.prop('disabled', false);
                $btn.html(originalText);
                return;
            }
            
            // Fix errors one by one
            var fixedCount = 0;
            var failedCount = 0;
            var total = fixableErrors.length;
            var index = 0;
            
            function fixNextError() {
                if (index >= total) {
                    // All done
                    var message = 'แก้ไขเสร็จสิ้น: ' + fixedCount + ' รายการ';
                    if (failedCount > 0) {
                        message += ', ล้มเหลว: ' + failedCount + ' รายการ';
                    }
                    alert(message);
                    
                    // Re-scan to verify fixes
                    if (fixedCount > 0) {
                        if (confirm('ต้องการแสกนอีกครั้งเพื่อตรวจสอบผลการแก้ไขหรือไม่?')) {
                            $('#io-scan-unused-images').trigger('click');
                        }
                    }
                    
                    $btn.prop('disabled', false);
                    $btn.html(originalText);
                    return;
                }
                
                var error = fixableErrors[index];
                var errorType = detectErrorType(error.error);
                
                $.ajax({
                    url: ioAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'io_fix_scan_errors',
                        nonce: ioAdmin.nonce,
                        error_type: errorType,
                        file_path: error.file || error.filename || ''
                    },
                    success: function(response) {
                        if (response.success && response.data.fixed) {
                            fixedCount++;
                        } else {
                            failedCount++;
                        }
                        index++;
                        fixNextError();
                    },
                    error: function() {
                        failedCount++;
                        index++;
                        fixNextError();
                    }
                });
            }
            
            fixNextError();
        });
        
        // View error details
        $(document).on('click', '#io-view-error-details', function() {
            // Switch to error files tab
            if ($('#error-files-tab-li').is(':visible')) {
                $('#error-files-tab-li a').trigger('click');
                // Scroll to tab
                $('html, body').animate({
                    scrollTop: $('.io-file-lists').offset().top - 50
                }, 500);
            } else {
                alert('ไม่มีรายละเอียดข้อผิดพลาด กรุณาแสกนอีกครั้ง');
            }
        });
        
        // Retry scan
        $(document).on('click', '#io-retry-scan', function() {
            $('#io-scan-unused-images').trigger('click');
        });
        
        // Display error files from scan
        function displayErrorFiles(errorFiles) {
            var $list = $('#error-list');
            
            if ($list.length === 0) {
                // Tab should exist in HTML, but if not, create it
                console.warn('Error list container not found');
                return;
            }
            
            $list.empty();
            
            if (errorFiles.length === 0) {
                $list.html('<p style="color: #646970; text-align: center; padding: 20px;">ไม่มีไฟล์ที่มีปัญหา</p>');
                $('#error-files-tab-li').hide();
                return;
            }
            
            // Show error files tab
            $('#error-files-tab-li').show();
            $('#tab-error-count').text(errorFiles.length);
            
            var html = '<table class="wp-list-table widefat fixed striped" style="margin: 0;"><thead><tr>' +
                '<th style="width: 50px;">ประเภท</th>' +
                '<th>ไฟล์</th>' +
                '<th style="width: 300px;">ข้อผิดพลาด</th>' +
                '</tr></thead><tbody>';
            
            errorFiles.forEach(function(file) {
                var filename = file.filename || (file.file ? file.file.split(/[\\/]/).pop() : file.file || 'N/A');
                var errorMsg = file.error || 'Unknown error';
                var fileType = file.type || 'unknown';
                var typeLabel = {
                    'thumbnails': 'Thumbnail',
                    'webp': 'WebP',
                    'orphaned': 'Orphaned',
                    'system': 'System'
                }[fileType] || fileType;
                
                html += '<tr style="background: #fff5f5;">';
                html += '<td><span style="font-size: 11px; color: #646970;">' + escapeHtml(typeLabel) + '</span></td>';
                html += '<td><code style="font-size: 11px; word-break: break-all; color: #d63638;">' + escapeHtml(filename) + '</code></td>';
                html += '<td><span style="color: #d63638; font-size: 11px;" title="' + escapeHtml(errorMsg) + '">' + escapeHtml(errorMsg) + '</span></td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            $list.html(html);
        }
        
        // Display failed files
        function displayFailedFiles(failedFiles) {
            var $list = $('#failed-list');
            $list.empty();
            
            if (failedFiles.length === 0) {
                $list.html('<p style="color: #646970; text-align: center; padding: 20px;">' + 
                    (ioAdmin.i18n.noFailedFiles || 'ไม่มีไฟล์ที่ลบไม่สำเร็จ') + '</p>');
                $('#failed-files-tab-li').hide();
                return;
            }
            
            // Show failed files tab
            $('#failed-files-tab-li').show();
            $('#tab-failed-count').text(failedFiles.length);
            
            var html = '<table class="wp-list-table widefat fixed striped" style="margin: 0;"><thead><tr><th>File</th><th style="width: 100px;">Size</th><th style="width: 200px;">Error</th><th style="width: 150px;">Date</th></tr></thead><tbody>';
            
            failedFiles.forEach(function(file) {
                var size = formatFileSize(file.size || 0);
                var date = 'N/A';
                if (file.modified) {
                    var dateObj = new Date(file.modified * 1000);
                    date = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString();
                }
                var filename = file.filename || (file.path ? file.path.split(/[\\/]/).pop() : '');
                var errorMsg = file.error || 'Unknown error';
                
                html += '<tr style="background: #fff5f5;">';
                html += '<td><code style="font-size: 11px; word-break: break-all; color: #d63638;">' + escapeHtml(filename) + '</code></td>';
                html += '<td>' + size + '</td>';
                html += '<td><span style="color: #d63638; font-size: 11px;" title="' + escapeHtml(errorMsg) + '">' + escapeHtml(errorMsg) + '</span></td>';
                html += '<td style="font-size: 11px;">' + date + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            $list.html(html);
        }
        
        // Display file list
        function displayFileList(type, files) {
            var $list = $('#' + type + '-list');
            $list.empty();
            
            if (files.length === 0) {
                $list.html('<p style="color: #646970; text-align: center; padding: 20px;">' + (ioAdmin.i18n.noFiles || 'ไม่มีไฟล์') + '</p>');
                return;
            }
            
            var html = '<table class="wp-list-table widefat fixed striped io-cleanup-file-table" style="margin: 0;"><thead><tr><th style="width: 30px;"><input type="checkbox" class="select-all-' + type + '"></th><th>File</th><th style="width: 100px;">Size</th><th style="width: 150px;">Date</th></tr></thead><tbody>';
            
            files.forEach(function(file) {
                var size = formatFileSize(file.size || 0);
                var date = 'N/A';
                if (file.modified) {
                    var dateObj = new Date(file.modified * 1000);
                    date = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString();
                }
                var filename = file.filename || (file.path ? file.path.split(/[\\/]/).pop() : '');
                var previewUrl = file.url || '';
                var isImage = /\.(jpe?g|png|gif|webp|bmp|ico)$/i.test(filename);
                html += '<tr>';
                html += '<td><input type="checkbox" class="file-checkbox" data-path="' + escapeHtml(file.path) + '" data-type="' + type + '"></td>';
                html += '<td><div class="io-file-cell">';
                if (isImage && previewUrl) {
                    html += '<span class="io-file-preview"><img src="' + escapeHtml(previewUrl) + '" alt="" loading="lazy" decoding="async" onerror="this.parentNode.classList.add(\'is-broken\');" /></span>';
                } else {
                    html += '<span class="io-file-preview io-file-preview--empty" aria-hidden="true"></span>';
                }
                html += '<code class="io-file-name">' + escapeHtml(filename) + '</code>';
                html += '</div></td>';
                html += '<td>' + size + '</td>';
                html += '<td style="font-size: 11px;">' + date + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            $list.html(html);
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Escape HTML
        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // Select all checkbox
        $(document).on('change', '.select-all-thumbnails, .select-all-webp, .select-all-orphaned', function() {
            var type = $(this).hasClass('select-all-thumbnails') ? 'thumbnails' : 
                      $(this).hasClass('select-all-webp') ? 'webp' : 'orphaned';
            var checked = $(this).is(':checked');
            $('#' + type + '-list .file-checkbox[data-type="' + type + '"]').prop('checked', checked);
            updateDeleteButton();
        });
        
        // File checkbox change
        $(document).on('change', '.file-checkbox', function() {
            updateDeleteButton();
        });
        
        // Update delete button state
        function updateDeleteButton() {
            var checked = $('.file-checkbox:checked').length;
            $('#io-delete-selected').prop('disabled', checked === 0);
        }
        
        // Toggle whether Delete All includes orphaned
        $(document).on('change', '#io-include-orphaned-in-delete-all', function() {
            var thumbnailsCount = parseInt($('#io-delete-thumbnails-count').text(), 10) || 0;
            var webpCount = parseInt($('#io-delete-webp-count').text(), 10) || 0;
            var orphanedCount = parseInt($('#io-delete-orphaned-count').text(), 10) || 0;
            var includeOrphaned = $(this).is(':checked');
            var deletable = thumbnailsCount + webpCount + (includeOrphaned ? orphanedCount : 0);
            $('#io-delete-all').prop('disabled', deletable === 0);
        });

        // Delete selected files
        $(document).on('click', '#io-delete-selected', function() {
            var selectedPaths = [];
            $('.file-checkbox:checked').each(function() {
                selectedPaths.push($(this).data('path'));
            });
            
            if (selectedPaths.length === 0) {
                alert(ioAdmin.i18n.noFilesSelected || 'กรุณาเลือกไฟล์ที่ต้องการลบ');
                return;
            }
            
            if (!confirm(ioAdmin.i18n.confirmDeleteSelected || 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ที่เลือก ' + selectedPaths.length + ' ไฟล์? การลบไม่สามารถยกเลิกได้')) {
                return;
            }
            
            deleteFiles(selectedPaths, false);
        });
        
        // Delete all files (orphaned excluded unless checkbox checked)
        $(document).on('click', '#io-delete-all', function() {
            var includeOrphaned = $('#io-include-orphaned-in-delete-all').is(':checked');
            var thumbnailsCount = parseInt($('#io-delete-thumbnails-count').text(), 10) || 0;
            var webpCount = parseInt($('#io-delete-webp-count').text(), 10) || 0;
            var orphanedCount = parseInt($('#io-delete-orphaned-count').text(), 10) || 0;
            var total = thumbnailsCount + webpCount + (includeOrphaned ? orphanedCount : 0);

            if (total === 0) {
                alert(ioAdmin.i18n.noFilesToDelete || 'ไม่มีไฟล์ที่จะลบ (ถ้าต้องการรวม Orphaned ให้ติ๊กตัวเลือกด้านบน)');
                return;
            }

            var confirmMsg = ioAdmin.i18n.confirmDeleteAll || 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ทั้งหมด? การลบไม่สามารถยกเลิกได้';
            confirmMsg += ' (' + total + ' ไฟล์';
            if (!includeOrphaned && orphanedCount > 0) {
                confirmMsg += ', ไม่รวม Orphaned ' + orphanedCount + ' ไฟล์';
            }
            confirmMsg += ')';
            if (includeOrphaned && orphanedCount > 0) {
                confirmMsg += '\n\n' + (ioAdmin.i18n.confirmDeleteAllWithOrphaned || 'รวม Orphaned ด้วย — ตรวจรายการให้รอบคอบก่อนลบ');
            }

            if (!confirm(confirmMsg)) {
                return;
            }

            deleteFiles([], true, null, total, includeOrphaned);
        });
        
        // Delete by type: Thumbnails
        $(document).on('click', '#io-delete-thumbnails', function() {
            var count = parseInt($('#io-delete-thumbnails-count').text()) || 0;
            if (count === 0) {
                alert('ไม่มี Thumbnails ที่จะลบ');
                return;
            }
            
            if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ Thumbnails ทั้งหมด (' + count + ' ไฟล์)? การลบไม่สามารถยกเลิกได้')) {
                return;
            }
            
            deleteFilesByType('thumbnails');
        });
        
        // Delete by type: WebP
        $(document).on('click', '#io-delete-webp', function() {
            var count = parseInt($('#io-delete-webp-count').text()) || 0;
            if (count === 0) {
                alert('ไม่มี WebP ที่จะลบ');
                return;
            }
            
            if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ WebP ทั้งหมด (' + count + ' ไฟล์)? การลบไม่สามารถยกเลิกได้')) {
                return;
            }
            
            deleteFilesByType('webp');
        });
        
        // Delete by type: Orphaned Images
        $(document).on('click', '#io-delete-orphaned', function() {
            var count = parseInt($('#io-delete-orphaned-count').text(), 10) || 0;
            if (count === 0) {
                alert('ไม่มี Orphaned Images ที่จะลบ');
                return;
            }

            var orphanedMsg = (ioAdmin.i18n.confirmDeleteOrphaned || 'คุณกำลังจะลบ Orphaned — ตรวจให้แน่ใจว่าไม่ใช่ไฟล์สำคัญ') +
                '\n\n(' + count + ' ไฟล์)';
            if (!confirm(orphanedMsg)) {
                return;
            }

            deleteFilesByType('orphaned');
        });
        
        // Delete files by type
        function deleteFilesByType(type) {
            var count = parseInt($('#io-delete-' + type + '-count').text(), 10) || 0;
            
            if (count === 0) {
                alert('ไม่พบไฟล์ประเภท ' + type + ' ที่จะลบ');
                return;
            }
            
            console.log('Starting batch deletion for type: ' + type);
            deleteFiles([], false, type, count, false);
        }
        
        // Delete files
        function deleteFiles(filePaths, deleteAll, deleteType, totalFiles, includeOrphaned) {
            var $progress = $('#io-delete-progress');
            var $progressBar = $('#io-delete-progress-bar');
            var $status = $('#io-delete-status');

            // Persist includeOrphaned across batch recursion
            if (typeof includeOrphaned === 'undefined' && typeof deleteFiles.includeOrphaned !== 'undefined') {
                includeOrphaned = deleteFiles.includeOrphaned;
            }
            deleteFiles.includeOrphaned = !!includeOrphaned;
            
            // Initialize progress UI
            if (!$progress.is(':visible')) {
                $progress.show();
                $progressBar.css('width', '0%');
                $status.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; display: inline-block;"></span> ' + (ioAdmin.i18n.deleting || 'กำลังลบ...'));
            }
            
            var ajaxData = {
                action: 'io_delete_unused_images',
                nonce: ioAdmin.nonce,
                dry_run: false
            };
            
            if (deleteAll) {
                ajaxData.delete_all = true;
                ajaxData.include_orphaned = deleteFiles.includeOrphaned ? '1' : '0';
            } else if (deleteType) {
                ajaxData.delete_type = deleteType;
            } else {
                ajaxData.files = filePaths;
            }
            
            // Track accumulated stats for batch processing
            if (typeof deleteFiles.stats === 'undefined') {
                deleteFiles.stats = {
                    deleted: 0,
                    failed: 0,
                    total: totalFiles || 0
                };
            }
            
            $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        var currentDeleted = response.data.deleted || 0;
                        var currentFailed = response.data.failed || 0;
                        var remaining = response.data.remaining || 0;
                        
                        // Update stats
                        deleteFiles.stats.deleted += currentDeleted;
                        deleteFiles.stats.failed += currentFailed;
                        
                        // Calculate progress
                        var percent = 100;
                        if (deleteFiles.stats.total > 0 && remaining > 0) {
                            // Start + Remaining = Total? No, Total decreases.
                            // Better: (TotalOriginal - Remaining) / TotalOriginal * 100
                            // We passed totalFiles as TotalOriginal
                            var processed = deleteFiles.stats.total - remaining;
                            percent = Math.min(95, Math.max(5, (processed / deleteFiles.stats.total) * 100));
                        }
                        
                        $progressBar.css('width', percent + '%');
                        
                        // Update status message
                        var statusMsg = (ioAdmin.i18n.deleting || 'กำลังลบ...') + ' ' + deleteFiles.stats.deleted + ' ไฟล์';
                        if (remaining > 0) {
                            statusMsg += ' (เหลือ ' + remaining + ')';
                        }
                        $status.html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite; display: inline-block;"></span> ' + statusMsg);
                        
                        // Check if we need to continue (Batch processing)
                        if (remaining > 0) {
                            // Continue next batch
                            console.log('Batch completed. Deleted: ' + currentDeleted + '. Remaining: ' + remaining + '. Continuing...');
                            deleteFiles(filePaths, deleteAll, deleteType, deleteFiles.stats.total, deleteFiles.includeOrphaned);
                        } else {
                            // All done
                            finalizeDeletion(response.data.failed_files);
                        }
                    } else {
                        $status.html('<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span> ' + (response.data.message || 'เกิดข้อผิดพลาด'));
                        // Reset stats
                        delete deleteFiles.stats;
                    }
                },
                error: function() {
                    $status.html('<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span> ' + (ioAdmin.i18n.deleteError || 'เกิดข้อผิดพลาดในการลบ'));
                    delete deleteFiles.stats;
                }
            });
            
            function finalizeDeletion(failedFiles) {
                $progressBar.css('width', '100%');
                
                var statusMsg = (ioAdmin.i18n.deleteComplete || 'ลบเสร็จสิ้น') + ': ' + deleteFiles.stats.deleted + ' ไฟล์';
                if (deleteFiles.stats.failed > 0) {
                    statusMsg += ', <span style="color: #d63638;">ลบไม่สำเร็จ: ' + deleteFiles.stats.failed + ' ไฟล์</span>';
                }
                $status.html('<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ' + statusMsg);
                
                // Display failed files if any
                if (failedFiles && failedFiles.length > 0) {
                    displayFailedFiles(failedFiles);
                    setTimeout(function() {
                        if ($('#failed-files-tab-li').is(':visible')) {
                            $('#failed-files-tab-li a').trigger('click');
                        }
                    }, 500);
                }
                
                // Hide progress after delay
                setTimeout(function() {
                    $progress.fadeOut(500);
                }, 3000);
                
                // Refresh scan results automatically
                setTimeout(function() {
                    console.log('Auto-refreshing scan results after deletion...');
                    resetScanState();
                    $('#io-scan-unused-images').trigger('click');
                }, 2000);
                
                // Reset stats
                delete deleteFiles.stats;
            }
        }
        
        // Download report
        $(document).on('click', '#io-download-report', function() {
            // This would generate a CSV file - implementation can be added later
            alert(ioAdmin.i18n.reportFeature || 'ฟีเจอร์นี้จะเพิ่มในอนาคต');
        });
        
        // Add spin animation
        if (!$('#io-spin-animation').length) {
            $('head').append('<style id="io-spin-animation">@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>');
        }
        
        // Regenerate Images
        var regenerateState = {
            total: 0,
            processed: 0,
            success: 0,
            failed: 0,
            startTime: null,
            cancelled: false
        };
        
        // Count images before regenerate
        // Clear dates button
        $(document).on('click', '#io-regenerate-clear-dates', function() {
            $('#io-regenerate-date-from').val('');
            $('#io-regenerate-date-to').val('');
            updateRegenerateStats();
        });
        
        // Update stats when options change
        function updateRegenerateStats() {
            var regenerateType = $('#io-regenerate-type').val();
            var dateFrom = $('#io-regenerate-date-from').val();
            var dateTo = $('#io-regenerate-date-to').val();
            var skipProcessed = $('#io-regenerate-skip-processed').is(':checked');
            
            $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'io_count_images_for_regenerate',
                    nonce: ioAdmin.nonce,
                    regenerate_type: regenerateType,
                    date_from: dateFrom,
                    date_to: dateTo,
                    skip_processed: skipProcessed
                },
                success: function(response) {
                    if (response.success) {
                        $('#io-regenerate-stats-total').text(response.data.total || 0);
                        $('#io-regenerate-stats-selected').text(response.data.selected || 0);
                        $('#io-regenerate-stats-skipped').text(response.data.skipped || 0);
                        $('#io-regenerate-stats').fadeIn(200);
                    }
                }
            });
        }
        
        // Watch for option changes
        $('#io-regenerate-type, #io-regenerate-skip-processed').on('change', function() {
            updateRegenerateStats();
        });
        
        // Watch for date inputs - use both change and input events for better responsiveness
        $('#io-regenerate-date-from, #io-regenerate-date-to').on('change input blur', function() {
            // Small delay to avoid too many requests while typing
            clearTimeout(window.regenerateStatsTimeout);
            window.regenerateStatsTimeout = setTimeout(function() {
                updateRegenerateStats();
            }, 300);
        });
        
        // Load stats on page load if regenerate tab is active
        if ($('#io-tab-regenerate').is(':visible')) {
            updateRegenerateStats();
        }
        
        // Also load stats when switching to regenerate tab
        $(document).on('click', '.io-main-tab[data-tab="regenerate"]', function() {
            setTimeout(function() {
                updateRegenerateStats();
            }, 100);
        });
        
        $(document).on('click', '#io-regenerate-images', function() {
            var $btn = $(this);
            $btn.prop('disabled', true);
            
            // Get options
            var regenerateType = $('#io-regenerate-type').val();
            var dateFrom = $('#io-regenerate-date-from').val();
            var dateTo = $('#io-regenerate-date-to').val();
            var skipProcessed = $('#io-regenerate-skip-processed').is(':checked');
            var regenerateThumbnails = $('#io-regenerate-thumbnails').is(':checked');
            var batchSize = parseInt($('#io-regenerate-batch-size').val()) || 10;
            
            // Store options in regenerateState
            regenerateState.options = {
                regenerate_type: regenerateType,
                date_from: dateFrom,
                date_to: dateTo,
                skip_processed: skipProcessed,
                regenerate_thumbnails: regenerateThumbnails,
                batch_size: batchSize
            };
            
            $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'io_count_images_for_regenerate',
                    nonce: ioAdmin.nonce,
                    regenerate_type: regenerateType,
                    date_from: dateFrom,
                    date_to: dateTo,
                    skip_processed: skipProcessed
                },
                success: function(response) {
                    if (response.success) {
                        var total = response.data.total || 0;
                        var selected = response.data.selected || 0;
                        var skipped = response.data.skipped || 0;
                        
                        if (selected > 0) {
                            regenerateState.total = selected;
                            $('#io-regenerate-total-count').text(selected);
                            $('#io-regenerate-preview').fadeIn(200);
                        } else {
                            var message = 'ไม่พบภาพที่ต้อง Regenerate';
                            if (total > 0 && skipped > 0) {
                                message += '\n\nพบภาพทั้งหมด ' + total + ' ภาพ แต่ถูกข้ามทั้งหมด ' + skipped + ' ภาพ';
                                message += '\nกรุณายกเลิกการเลือก "ข้ามภาพที่ Regenerate แล้ว" เพื่อ regenerate ใหม่';
                            } else if (total === 0) {
                                message += '\n\nไม่พบภาพในระบบ';
                            }
                            alert(message);
                        }
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + (response.data.message || 'ไม่ทราบสาเหตุ'));
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการนับจำนวนภาพ');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
        
        // Cancel preview
        $(document).on('click', '#io-regenerate-cancel-preview', function() {
            $('#io-regenerate-preview').fadeOut(200);
        });
        
        // Confirm regenerate
        $(document).on('click', '#io-regenerate-confirm', function() {
            $('#io-regenerate-preview').fadeOut(200);
            $('#io-regenerate-controls').hide();
            $('#io-regenerate-progress').fadeIn(200);
            
            // Reset state
            regenerateState.processed = 0;
            regenerateState.success = 0;
            regenerateState.failed = 0;
            regenerateState.startTime = Date.now();
            regenerateState.cancelled = false;
            
            // Clear cancel flag
            // Clear cancel flag by setting it to false
            $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'io_cancel_regenerate',
                    nonce: ioAdmin.nonce,
                    cancel: 0
                }
            });
            
            // Start regeneration
            processRegenerateBatch(0);
        });
        
        // Cancel regenerate
        $(document).on('click', '#io-regenerate-cancel', function() {
            if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกการ Regenerate?')) {
                return;
            }
            
            // Set cancelled flag immediately
            regenerateState.cancelled = true;
            
            // Abort current AJAX request
            if (currentRegenerateRequest) {
                currentRegenerateRequest.abort();
                currentRegenerateRequest = null;
            }
            
            // Set cancel flag on server
            $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'io_cancel_regenerate',
                    nonce: ioAdmin.nonce
                },
                success: function() {
                    handleRegenerateCancelled();
                },
                error: function() {
                    // Even if server request fails, show cancelled state
                    handleRegenerateCancelled();
                }
            });
        });
        
        // Store current AJAX request for cancellation
        var currentRegenerateRequest = null;
        
        // Process regenerate batch
        function processRegenerateBatch(offset) {
            // Check if cancelled before starting
            if (regenerateState.cancelled) {
                handleRegenerateCancelled();
                return;
            }
            
            var batchSize = regenerateState.options ? regenerateState.options.batch_size : 10;
            
            // Cancel any pending request
            if (currentRegenerateRequest) {
                currentRegenerateRequest.abort();
            }
            
            var ajaxData = {
                action: 'io_regenerate_images',
                nonce: ioAdmin.nonce,
                batch_size: batchSize,
                offset: offset,
                total: regenerateState.total
            };
            
            // Add options if available
            if (regenerateState.options) {
                ajaxData.regenerate_type = regenerateState.options.regenerate_type;
                ajaxData.date_from = regenerateState.options.date_from;
                ajaxData.date_to = regenerateState.options.date_to;
                ajaxData.skip_processed = regenerateState.options.skip_processed;
                ajaxData.regenerate_thumbnails = regenerateState.options.regenerate_thumbnails;
            }
            
            currentRegenerateRequest = $.ajax({
                url: ioAdmin.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    currentRegenerateRequest = null;
                    
                    // Check if cancelled again after response
                    if (regenerateState.cancelled) {
                        handleRegenerateCancelled();
                        return;
                    }
                    
                    if (response.success) {
                        var data = response.data;
                        
                        regenerateState.processed = data.processed || 0;
                        regenerateState.success += data.success || 0;
                        regenerateState.failed += data.failed || 0;
                        
                        // Update UI
                        updateRegenerateProgress();
                        
                        if (data.done || data.cancelled) {
                            // Finished or cancelled
                            if (data.cancelled) {
                                handleRegenerateCancelled();
                            } else {
                                handleRegenerateComplete();
                            }
                        } else {
                            // Continue with next batch (only if not cancelled)
                            if (!regenerateState.cancelled) {
                                setTimeout(function() {
                                    processRegenerateBatch(data.processed);
                                }, 500); // Small delay to prevent overwhelming the server
                            } else {
                                handleRegenerateCancelled();
                            }
                        }
                    } else {
                        $('#io-regenerate-status').html(
                            '<span style="color: #d63638;">' + 
                            (response.data.message || ioAdmin.i18n.regenerateError || 'เกิดข้อผิดพลาด') + 
                            '</span>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    currentRegenerateRequest = null;
                    
                    // Don't show error if it was cancelled
                    if (status === 'abort' || regenerateState.cancelled) {
                        handleRegenerateCancelled();
                        return;
                    }
                    
                    $('#io-regenerate-status').html(
                        '<span style="color: #d63638;">' + 
                        (ioAdmin.i18n.regenerateError || 'เกิดข้อผิดพลาดในการ Regenerate') + 
                        '</span>'
                    );
                }
            });
        }
        
        // Handle regenerate cancelled
        function handleRegenerateCancelled() {
            // Stop any pending requests
            if (currentRegenerateRequest) {
                currentRegenerateRequest.abort();
                currentRegenerateRequest = null;
            }
            
            // Update UI immediately
            $('#io-regenerate-status').html(
                '<span style="color: #d63638; font-weight: 600; display: block; padding: 10px; background: #fcf0f1; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' + 
                '<span class="dashicons dashicons-dismiss" style="vertical-align: middle;"></span> ' +
                (ioAdmin.i18n.regenerateCancelled || 'Regenerate ถูกยกเลิก') + 
                '</span>'
            );
            $('#io-regenerate-cancel').hide();
            
            // Stop progress animation
            $('#io-regenerate-progress .dashicons-update').css('animation', 'none');
            
            // Update ETA to show cancelled
            $('#io-regenerate-eta').text('-');
            
            // Show restart button after 2 seconds
            setTimeout(function() {
                $('#io-regenerate-controls').fadeIn(200);
                $('#io-regenerate-progress').fadeOut(200);
            }, 2000);
        }
        
        // Handle regenerate complete
        function handleRegenerateComplete() {
            $('#io-regenerate-status').html(
                '<span style="color: #00a32a; font-weight: 600;">' + 
                (ioAdmin.i18n.regenerateComplete || 'Regenerate เสร็จสิ้น') + 
                '</span>'
            );
            $('#io-regenerate-cancel').hide();
            
            // Show restart button after 3 seconds
            setTimeout(function() {
                $('#io-regenerate-controls').fadeIn(200);
                $('#io-regenerate-progress').fadeOut(200);
            }, 3000);
        }
        
        // Update regenerate progress
        function updateRegenerateProgress() {
            // Don't update if cancelled
            if (regenerateState.cancelled) {
                return;
            }
            
            var processed = regenerateState.processed;
            var total = regenerateState.total;
            var success = regenerateState.success;
            var failed = regenerateState.failed;
            var percentage = total > 0 ? Math.round((processed / total) * 100) : 0;
            
            // Update progress bar
            $('#io-regenerate-progress-bar').css('width', percentage + '%').text(percentage + '%');
            
            // Update stats
            $('#io-regenerate-processed').text(processed);
            $('#io-regenerate-total').text(total);
            $('#io-regenerate-success').text(success);
            $('#io-regenerate-failed').text(failed);
            
            // Calculate time
            if (regenerateState.startTime) {
                var elapsed = Math.floor((Date.now() - regenerateState.startTime) / 1000);
                var minutes = Math.floor(elapsed / 60);
                var seconds = elapsed % 60;
                $('#io-regenerate-time').text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
                
                // Calculate ETA (only if not cancelled)
                if (!regenerateState.cancelled && processed > 0 && processed < total) {
                    var avgTimePerImage = elapsed / processed;
                    var remaining = total - processed;
                    var etaSeconds = Math.floor(avgTimePerImage * remaining);
                    var etaMinutes = Math.floor(etaSeconds / 60);
                    var etaSecs = etaSeconds % 60;
                    $('#io-regenerate-eta').text(etaMinutes + ':' + (etaSecs < 10 ? '0' : '') + etaSecs);
                } else if (processed >= total || regenerateState.cancelled) {
                    $('#io-regenerate-eta').text('-');
                }
            }
            
            // Update status (only if not cancelled)
            if (!regenerateState.cancelled) {
                var remaining = total - processed;
                $('#io-regenerate-status').html(
                    '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> ' +
                    'กำลังประมวลผล... เหลืออีก ' + remaining + ' ไฟล์'
                );
            }
        }
    });
})();
