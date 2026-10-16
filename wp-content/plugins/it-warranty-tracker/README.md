# IT Warranty Tracker

Custom WordPress plugin สำหรับติดตามทรัพย์สินไอที ตรวจสอบประกัน/MA และแจ้งเตือนทาง LINE Messaging API

- Plugin slug: `it-warranty-tracker`
- Prefix: `itwt_` / namespace `ITWT\`
- Text domain: `it-warranty-tracker`
- Requires: WordPress 6.0+, PHP 8.1+

## ติดตั้ง

1. เปิดใช้งาน plugin จาก **Plugins** ใน WP Admin
2. ใส่ค่าใน `wp-config.php`:

```php
define( 'ITWT_LINE_CHANNEL_SECRET', 'your-channel-secret' );
define( 'ITWT_LINE_CHANNEL_TOKEN', 'your-channel-access-token' );
// optional:
// define( 'ITWT_TRUST_CLOUDFLARE', true );
```

3. ตั้ง Webhook URL ของ LINE Messaging API เป็น:
   `https://{โดเมน}/wp-json/itwt/v1/line/webhook`
4. เปิด **Allow bot to join group chats** แล้วเชิญ bot เข้ากลุ่มช่าง จากนั้นยืนยัน group ID ใน **IT Warranty → ตั้งค่า**
5. สร้างหน้า WordPress ใส่ shortcode `[itwt_warranty_lookup]` แล้วเลือกหน้านั้นในตั้งค่า (ใช้เป็น URL ใน QR — อย่าเปลี่ยนภายหลัง)

## Vendor libraries

| ไฟล์ | ไลบรารี | เวอร์ชัน |
| :--- | :--- | :--- |
| `admin/js/vendor/qrcode-generator.js` | [qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator) | 1.4.4 |

QR สร้างฝั่ง browser เท่านั้น ไม่เรียกบริการภายนอก

## สิทธิ์

| Capability | Administrator | Role `itwt_technician` |
| :--- | :---: | :---: |
| `itwt_manage_assets` | ✓ | |
| `itwt_log_service` | ✓ | ✓ |
| `itwt_manage_settings` | ✓ | |

## CSV Import columns

`service_tag`, `brand`, `serial_number`, `asset_type`, `model_name`, `customer_id`, `install_location`, `status`  
Optional contract: `contract_type`, `start_date`, `end_date`, `sla_tier`, `contract_no`

## พัฒนา

```bash
phpcs --standard=WordPress wp-content/plugins/it-warranty-tracker
```
