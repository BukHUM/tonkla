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
5. สร้างหน้า WordPress → Page Attributes เลือก Template **Warranty (IT Warranty Tracker)** → เลือกหน้านั้นในตั้งค่า (ใช้เป็น URL ใน QR — อย่าเปลี่ยนภายหลัง). Shortcode `[itwt_warranty_lookup]` ไม่บังคับเมื่อใช้เทมเพลตนี้

## หน้าเช็คประกัน (สาธารณะ)

ตั้งค่าใน **IT Warranty → ตั้งค่า → การแสดงผลหน้าเช็คประกัน**:

| รายการ | ความหมาย |
| :--- | :--- |
| ธีม | Soft / Minimal / Dense (`portal_theme`) + สี accent |
| วันซื้อ / หมดประกัน / แถบวันคงเหลือ / SLA | ติ๊กเปิด-ปิดการแสดง |
| สเปก / สัญญา / ประวัติบริการ / LINE / แจ้งซ่อม | ตามเดิม |

**เทมเพลตหน้า Warranty (v1.5.4+):** เลือกใน Page Attributes เพื่อให้ปลั๊กอินคุมเลย์เอาต์เต็มความกว้าง (ไม่ถูกกล่องหน้า Page ปกติของธีมบีบ)

**พฤติกรรมหน้าผลลัพธ์ (v1.5.3+):**
- สถานะ `upcoming` เมื่อสัญญาเริ่มในอนาคต (ไม่แสดงเป็นหมดประกัน)
- รายการสัญญาสาธารณะแสดงเฉพาะ **MA** — ประกันบริษัทอยู่ใน badge / วันที่คุ้มครอง
- สเปกเลือกไอคอนได้จากหลังบ้าน (CPU / Memory / Storage / …) ถ้าไม่เลือกจะเดาจากชื่อคีย์
- คำแนะนำต่อ MA อยู่ด้านล่าง ไม่แย่งความสนใจจากสถานะจริง

**ไม่แสดงหน้าเว็บ:** รายการอุปกรณ์/แหล่งซื้อ, หมายเหตุภายใน, เพดานต่อ MA, Serial เต็ม

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

## Admin list UI

หน้า list แอดมิน (ลูกค้า / ทรัพย์สิน / คำขอ ฯลฯ) ต้องจัดตามมาตรฐานเดียวกัน — ดู [`docs/admin-list-ui.md`](docs/admin-list-ui.md)

## รูปแบบวันที่

- **ข้อความอ่านอย่างเดียว:** `01 Nov 2029` (`\ITWT\Dates::format*`)
- **ช่องกรอกฟอร์ม:** `dd/mm/yyyy` ผ่าน `\ITWT\Dates::render_input()` (ห้าม `<input type="date">` ตรง ๆ)
- **เก็บใน DB / SQL:** `Y-m-d` เช่น `2029-11-01`

## ประกันบริษัท / อุปกรณ์ / เพดานต่อ MA

บนแท็บ **ข้อมูลหลัก** ของทรัพย์สิน:

| ฟิลด์ | ความหมาย |
| :--- | :--- |
| วันซื้อ / วันหมดประกัน | sync เป็นสัญญา `warranty` (ประกันบริษัท) อัตโนมัติ |
| เงื่อนไขต่อ MA | ติ๊กแยกได้: จำกัดปีรวมจากวันซื้อ และ/หรือ จำนวนครั้งที่ต่อ |

แท็บ **อุปกรณ์**: ตาราง `itwt_asset_components` — แหล่งซื้อ + วันหมดประกันต้นทาง (ข้อมูลภายใน ไม่แสดงหน้าเว็บ)

การเพิ่มสัญญา **MA** จะถูกตรวจตามเงื่อนไขด้านบน

## พัฒนา

```bash
phpcs --standard=WordPress wp-content/plugins/it-warranty-tracker
```
