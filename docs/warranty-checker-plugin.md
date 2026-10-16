# System Specification: IT Asset & Warranty Lookup Plugin (v2)

ระบบติดตามทรัพย์สินและตรวจสอบสถานะการรับประกันสำหรับธุรกิจจำหน่ายอุปกรณ์ไอที พัฒนาซอฟต์แวร์ และวางระบบ
พัฒนาเป็น **Custom WordPress Plugin** ใช้ Custom Database Tables แยกจาก `wp_posts` / `wp_postmeta`

- Plugin slug: `it-warranty-tracker`
- Prefix: function / hook / option / transient ใช้ `itwt_`, class ใช้ namespace `ITWT\`
- ตาราง: `{$wpdb->prefix}itwt_*` (เช่น `wp_itwt_assets`)
- Text domain: `it-warranty-tracker`
- PHP 8.1+, ผ่าน `phpcs --standard=WordPress`

### การตัดสินใจหลัก (สรุปจาก v1)
| เรื่อง | ข้อสรุป |
| :--- | :--- |
| การต่อ MA | เปิดให้ต่อ MA ได้ แม้ประกันหมดไปแล้ว → แยกตาราง `contracts` เก็บทุกช่วงความคุ้มครอง |
| ข้อมูลลูกค้าบนหน้าสาธารณะ | **ไม่แสดง** ชื่อบริษัท ผู้ติดต่อ และสถานที่ติดตั้ง (PDPA) |
| แจ้งเตือนประกันใกล้หมด | ส่งหาลูกค้าผ่าน LINE OA (Messaging API) ลูกค้าต้องกดสมัครรับแจ้งเตือนเอง |
| Login ลูกค้า | ไม่มี ทุกอย่างหน้าบ้านเป็น public + rate limit |
| ค้นหาด้วย Serial Number | เปิดให้ค้นได้ (exact match) |
| สถานะ expired | **ไม่เก็บใน DB** คำนวณจากวันที่ของ contracts ตอน query |
| แจ้งทีมช่างเมื่อมีคำขอใหม่ | push เข้า LINE group ของช่าง (bot ของ OA บริษัท) + จัดการใน WP Admin |
| LINE OA | OA ของบริษัท แพ็กเกจฟรี 300 ข้อความ/เดือน (อัปเกรดภายหลังถ้าไม่พอ) |
| QR Code | อนุมัติ `qrcode-generator` (JS) |
| Milestones แจ้งเตือน | 60 / 30 / 7 / 0 วัน |
| เก็บคำขอแจ้งซ่อม | 24 เดือน แล้วลบอัตโนมัติ |

---

## 1. โครงสร้างฐานข้อมูล (Database Schema)

สร้าง/แก้ schema ด้วย `dbDelta()` ใน `ITWT\Installer` เก็บเวอร์ชันใน option `itwt_db_version` (autoload = false)
และตรวจทุกครั้งที่ `plugins_loaded` เพื่อ migrate อัตโนมัติ ทุกตารางใช้ `$wpdb->get_charset_collate()` (utf8mb4)

> dbDelta ไม่รองรับ Foreign Key — ความถูกต้องของความสัมพันธ์ต้องตรวจใน Repository (ดู 1.9)

### 1.1 `itwt_customers` — ลูกค้า (ใช้ภายในเท่านั้น)
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `customer_code` | `VARCHAR(50)` | `UNIQUE` | รหัสลูกค้า เช่น `CUST-2026-001` |
| `company_name` | `VARCHAR(255)` | `INDEX` | ชื่อบริษัท / หน่วยงาน |
| `contact_name` | `VARCHAR(150)` | - | ผู้ติดต่อหลัก |
| `phone` | `VARCHAR(50)` | - | |
| `email` | `VARCHAR(150)` | - | |
| `created_at` | `DATETIME` | - | |
| `updated_at` | `DATETIME` | - | |

> ตัด `sites_json` ออก (ซ้ำกับ `install_location`) ถ้าภายหลังต้องรายงานแยกสาขา ให้เพิ่มตาราง `itwt_customer_sites` แล้วอ้าง `site_id`

### 1.2 `itwt_assets` — ทรัพย์สิน
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `service_tag` | `VARCHAR(20)` | `UNIQUE` | รหัสสุ่ม (ดู 2.1) ใช้เช็คประกัน/QR |
| `brand` | `VARCHAR(50)` | `UNIQUE (brand, serial_number)` | ยี่ห้อผู้ผลิต |
| `serial_number` | `VARCHAR(100)` NULL | `INDEX` | S/N ผู้ผลิต (NULL ได้สำหรับ software/system) |
| `asset_type` | `VARCHAR(30)` | `INDEX (status, asset_type)` | `hardware`, `system`, `software` |
| `model_name` | `VARCHAR(255)` | - | ชื่อรุ่น / ชื่อโปรเจกต์ |
| `specs_json` | `LONGTEXT` | - | `[{"key":"CPU","value":"Xeon 4314","public":true}, ...]` |
| `customer_id` | `BIGINT UNSIGNED` | `INDEX` | → `itwt_customers.id` |
| `install_location` | `VARCHAR(255)` | - | ภายในเท่านั้น ไม่ส่งออก API |
| `status` | `VARCHAR(20)` | (ร่วมกับ asset_type) | lifecycle: `active`, `void`, `decommissioned` |
| `internal_note` | `TEXT` | - | ภายในเท่านั้น |
| `created_by` | `BIGINT UNSIGNED` | - | WP user ID |
| `updated_by` | `BIGINT UNSIGNED` | - | WP user ID |
| `created_at` | `DATETIME` | - | |
| `updated_at` | `DATETIME` | - | |

หมายเหตุ
- `service_tag` และ `serial_number` เก็บแบบ normalize (ตัวพิมพ์ใหญ่, ตัดช่องว่าง) ทั้งตอนบันทึกและตอนค้นหา
- `asset_type = ma_contract` ของ v1 ถูกย้ายไปเป็น `contracts.contract_type = ma`
- สเปกแต่ละรายการมี flag `public` — สเปกระบบบางอย่าง (IP, URL ภายใน, ชื่อ server) ห้ามแสดงหน้าเว็บ

### 1.3 `itwt_contracts` — ช่วงความคุ้มครอง (ประกัน / MA)
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `asset_id` | `BIGINT UNSIGNED` | `UNIQUE (asset_id, contract_type, start_date)`, `INDEX (asset_id, end_date)` | |
| `contract_type` | `VARCHAR(20)` | - | `manufacturer` (ประกันผู้ผลิต), `warranty` (ประกันบริษัท), `ma` |
| `contract_no` | `VARCHAR(50)` | - | เลขที่สัญญา / ใบเสนอราคา (ภายใน) |
| `start_date` | `DATE` | - | |
| `end_date` | `DATE` | `INDEX (status, end_date)` | |
| `sla_tier` | `VARCHAR(100)` | - | `On-Site 24x7`, `NBD`, `Carry-in` |
| `status` | `VARCHAR(20)` | (ร่วมกับ end_date) | `active`, `cancelled` |
| `created_by` | `BIGINT UNSIGNED` | - | |
| `created_at` | `DATETIME` | - | |
| `updated_at` | `DATETIME` | - | |

กติกา
- ต่อ MA = **เพิ่มแถวใหม่** ไม่แก้ทับแถวเดิม ประวัติจึงครบ และอนุญาตให้มีช่วงว่าง (gap) ระหว่างสัญญาได้
- สัญญาประเภทเดียวกันของเครื่องเดียวกันห้ามมีช่วงวันที่ทับกัน (ตรวจใน Repository ก่อนบันทึก)
- สถานะความคุ้มครอง (คำนวณ ไม่เก็บ):
  - `covered` — วันนี้อยู่ในช่วงของสัญญา `active` อย่างน้อย 1 ฉบับ
  - `expiring` — covered และ `end_date` ล่าสุดเหลือ ≤ `itwt_expiring_days` (ค่าเริ่มต้น 30 วัน)
  - `expired` — ไม่มีสัญญาครอบคลุมวันนี้
  - `void` / `decommissioned` — ตาม `assets.status` (มีผลเหนือกว่าวันที่)
- วันที่ทั้งหมดคำนวณตามเขตเวลาของเว็บ (Asia/Bangkok) ด้วย `current_time()` / `wp_date()`

### 1.4 `itwt_service_logs` — ประวัติซ่อมบำรุง
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `asset_id` | `BIGINT UNSIGNED` | `INDEX (asset_id, service_date)` | |
| `service_date` | `DATE` | (ร่วม) | |
| `service_type` | `VARCHAR(30)` | - | `claim`, `pm`, `upgrade`, `incident` |
| `symptom` | `TEXT` | - | ภายใน |
| `resolution` | `TEXT` | - | ภายใน |
| `public_summary` | `VARCHAR(500)` NULL | - | ข้อความที่แสดงหน้าเว็บ — **NULL = ไม่แสดงรายการนี้** |
| `technician_id` | `BIGINT UNSIGNED` | - | WP user ID ของช่าง (ไม่แสดงหน้าเว็บ) |
| `created_at` | `DATETIME` | - | |
| `updated_at` | `DATETIME` | - | |

> ตัด `renew` ออกจาก service_type เพราะการต่ออายุอยู่ใน `itwt_contracts` แล้ว

### 1.5 `itwt_support_requests` — คำขอแจ้งซ่อม / ขอต่อ MA จากหน้าเว็บ
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `asset_id` | `BIGINT UNSIGNED` | `INDEX` | |
| `request_type` | `VARCHAR(20)` | - | `repair`, `renew_ma` |
| `contact_name` | `VARCHAR(150)` | - | |
| `phone` | `VARCHAR(50)` | - | |
| `detail` | `TEXT` | - | |
| `status` | `VARCHAR(20)` | `INDEX (status, created_at)` | `new`, `in_progress`, `done`, `spam` |
| `dedupe_hash` | `CHAR(64)` | `UNIQUE` | sha256(asset_id + phone + detail + วันที่) กันกดส่งซ้ำ |
| `consent_at` | `DATETIME` | - | เวลาที่ยอมรับเงื่อนไขเก็บข้อมูล (PDPA) |
| `created_at` | `DATETIME` | - | |
| `updated_at` | `DATETIME` | - | |

> ลบอัตโนมัติเมื่อเกินระยะเก็บรักษา (`itwt_request_retention_months`) ผ่าน WP-Cron

### 1.6 `itwt_line_subscriptions` — ผู้รับแจ้งเตือนทาง LINE
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `asset_id` | `BIGINT UNSIGNED` | `UNIQUE (asset_id, line_user_id)` | |
| `line_user_id` | `VARCHAR(64)` | `INDEX (line_user_id, status)` | userId จาก LINE webhook |
| `status` | `VARCHAR(20)` | (ร่วม) | `active`, `unsubscribed` |
| `consent_at` | `DATETIME` | - | |
| `created_at` | `DATETIME` | - | |
| `updated_at` | `DATETIME` | - | |

### 1.7 `itwt_notification_log` — บันทึกการส่งแจ้งเตือน (กันส่งซ้ำ)
| Column | Type | Key / Index | Description |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | `PRIMARY KEY` | |
| `subscription_id` | `BIGINT UNSIGNED` | `UNIQUE (subscription_id, contract_id, milestone)` | |
| `contract_id` | `BIGINT UNSIGNED` | - | |
| `milestone` | `SMALLINT` | - | จำนวนวันก่อนหมด เช่น 60, 30, 7, 0 |
| `result` | `VARCHAR(20)` | - | `sent`, `failed` |
| `sent_at` | `DATETIME` | `INDEX` | ใช้ลบข้อมูลเก่า |

### 1.8 Options
| Option | Autoload | Description |
| :--- | :--- | :--- |
| `itwt_db_version` | no | เวอร์ชัน schema |
| `itwt_settings` | no | โลโก้, เบอร์ศูนย์บริการ, page ID หน้าเช็คประกัน, `expiring_days` (30), milestones (60/30/7/0), retention (24 เดือน), `line_tech_group_id`, `line_monthly_quota` (300), `line_customer_reserve` (ดูข้อ 4.1) |
| `itwt_spec_templates` | no | ชุด key สเปกเริ่มต้นตาม asset_type |

Secret อ่านจาก `wp-config.php` เท่านั้น: `ITWT_LINE_CHANNEL_SECRET`, `ITWT_LINE_CHANNEL_TOKEN`

### 1.9 กติกาความสัมพันธ์และการลบ
- ลบลูกค้าไม่ได้ ถ้ายังมี asset ผูกอยู่
- ลบ asset จริงได้เฉพาะเมื่อไม่มี contract / log / request — ถ้ามี ให้เปลี่ยน status เป็น `decommissioned`
- การบันทึกที่แตะหลายตาราง (เช่น asset + contract แรก) ใช้ transaction (`START TRANSACTION` / `COMMIT` / `ROLLBACK`)

---

## 2. ระบบหลังบ้าน (WP Admin)

### 2.1 สิทธิ์ (Capabilities)
เพิ่มตอน activate และลบตอน uninstall

| Capability | Administrator | Role `itwt_technician` |
| :--- | :---: | :---: |
| `itwt_manage_assets` (asset, ลูกค้า, contract, import/export) | ✓ | - |
| `itwt_log_service` (บันทึก service log, ดู request) | ✓ | ✓ |
| `itwt_manage_settings` | ✓ | - |

ทุก action ที่เปลี่ยนข้อมูล: nonce (`check_admin_referer`) + `current_user_can()`

### 2.2 Asset & Contract
- ตารางรายการใช้ `WP_List_Table` (ค้นหา, กรองตาม asset_type / สถานะความคุ้มครอง, แบ่งหน้า 20–50 รายการ)
- ปุ่มสร้าง Service Tag: สุ่ม 7 ตัวจาก base32 ที่ตัด `O 0 I 1` ออก (เช่น `7KQ3XTM`) ถ้าชน UNIQUE ให้สุ่มใหม่ (สูงสุด 5 ครั้ง)
- Dynamic Spec Builder: key-value + checkbox "แสดงหน้าเว็บ" โหลด template ตาม asset_type
- ส่วนสัญญาในฟอร์ม asset: รายการ contracts ทั้งหมด + ปุ่ม "ต่อ MA" (เลือกวันเริ่ม + ปุ่มลัด 1/2/3 ปี หรือกำหนดเอง)
- Service log: เพิ่ม/แก้ไข พร้อมช่อง `public_summary`

### 2.3 QR Code Sticker
- หน้าพิมพ์ขนาด 50×30 มม. และ 60×40 มม. (CSS `@page`) พิมพ์ทีละเครื่องหรือเลือกหลายเครื่อง
- แสดงโลโก้, เบอร์ศูนย์บริการ, Service Tag, QR
- QR ฝัง URL ถาวร `{หน้าเช็คประกัน}/?tag=XXXX` (หน้าอ้างจาก page ID ใน settings)
- สร้าง QR ฝั่ง browser — **ห้ามใช้บริการภายนอก** (เช่น Google Charts) เพราะ URL ที่มี tag จะรั่วออกไป
- ไลบรารี: `qrcode-generator` (JS, อนุมัติแล้ว) เก็บไฟล์ไว้ใน `admin/js/vendor/` ระบุเวอร์ชันใน README และโหลดเฉพาะหน้า print-label

### 2.4 Bulk CSV Import / Export
- Import: ตรวจ `wp_check_filetype_and_ext()`, จำกัด 5 MB, upsert ตาม `service_tag` (`INSERT ... ON DUPLICATE KEY UPDATE`)
- ประมวลผลเป็น batch ละ 200 แถวผ่าน WP-Cron แสดงความคืบหน้าและรายงานแถวที่ผิดพลาด
- Export: ป้องกัน CSV injection (เซลล์ที่ขึ้นต้นด้วย `= + - @` ให้นำหน้าด้วย `'`)

### 2.5 Dashboard
- จำนวน asset ตามสถานะความคุ้มครอง
- รายการใกล้หมดภายใน 30 / 60 วัน (จาก `MAX(end_date)` ต่อ asset — ตรวจ `EXPLAIN` ก่อนส่งงาน)
- คำขอแจ้งซ่อมสถานะ `new`
- โควตา LINE เดือนนี้: ใช้ไป / 300 (ดึงจาก API `GET /v2/bot/message/quota/consumption` cache 1 ชั่วโมง) แจ้งเตือน admin notice เมื่อเกิน 80%

---

## 3. หน้าบ้าน (Public Portal)

### 3.1 Shortcode `[itwt_warranty_lookup]`
- enqueue CSS/JS เฉพาะเมื่อ shortcode ถูกเรนเดอร์ (`strategy => 'defer'`, ไม่ใช้ jQuery)
- ช่องค้นหาเดียว รับ Service Tag หรือ Serial Number (exact match หลัง normalize)
- อ่าน `?tag=` จาก URL แล้วค้นหาทันที (กรณีสแกน QR)
- Serial ตรงหลายเครื่อง (คนละยี่ห้อ) → แสดงรายการให้เลือก (ยี่ห้อ + รุ่น)

### 3.2 ข้อมูลที่แสดง (Allowlist — ส่งออกเฉพาะ field เหล่านี้)
| แสดง | ไม่แสดง |
| :--- | :--- |
| Service Tag, ยี่ห้อ, รุ่น, ประเภท | ชื่อลูกค้า, ผู้ติดต่อ, เบอร์, อีเมล |
| S/N เฉพาะ 4 ตัวท้าย (`••••A7F2`) | `install_location` |
| สถานะความคุ้มครอง + จำนวนวันคงเหลือ | `internal_note`, `contract_no` |
| ประวัติสัญญา (ประเภท, ช่วงวันที่, SLA) | สเปกที่ `public = false` |
| สเปกที่ `public = true` | `symptom`, `resolution`, ชื่อช่าง |
| ประวัติบริการที่มี `public_summary` (วันที่, ประเภท, ข้อความ) | |

### 3.3 Warranty Status Card
- ป้ายสถานะต้องมี **ข้อความ + ไอคอน SVG** ไม่ใช้สีอย่างเดียว เช่น "อยู่ในประกัน", "เหลือ 25 วัน", "หมดประกัน — ต่อ MA ได้"
- Progress bar มี `role="progressbar"` + `aria-valuenow/min/max`
- สี contrast ≥ 4.5:1 (ระวังสีเหลือง)
- ทุกสถานะ: loading, ไม่พบข้อมูล, ค้นหาบ่อยเกินไป (429), error, void/decommissioned
- ใช้ UX ลักษณะหน้าเช็คประกันของผู้ผลิตเป็นแนวทาง แต่ไม่ใช้ชื่อ โลโก้ หรือสีแบรนด์ของผู้ผลิตรายใด
- CSS ใช้ CSS variables ของ theme (ไม่ใช้ Tailwind), เส้นขอบเส้นเดียวต่อองค์ประกอบ

### 3.4 แจ้งซ่อม / ขอต่อ MA
- ปุ่มบนการ์ด เปิดฟอร์มที่เติม Service Tag ให้อัตโนมัติ
- ฟิลด์: ประเภทคำขอ, ชื่อ, เบอร์, รายละเอียด, checkbox ยินยอมเก็บข้อมูล (บังคับ)
- ปุ่ม "ขอต่อ MA" แสดงเด่นเมื่อสถานะ `expiring` หรือ `expired`
- กันสแปม: honeypot + เวลากรอกขั้นต่ำ + rate limit + `dedupe_hash`
- บันทึกลง `itwt_support_requests` ก่อน แล้วจึงแจ้งทีมช่างเป็น background (`wp_schedule_single_event`) ดูข้อ 4.2
- WP Admin คือแหล่งข้อมูลหลัก — ถ้าส่ง LINE ไม่สำเร็จหรือโควตาหมด คำขอยังอยู่ครบในหน้า request-list

### 3.5 สมัครรับแจ้งเตือนทาง LINE
ไม่มี login จึงผูก LINE กับเครื่องด้วยรหัสครั้งเดียว:

1. ลูกค้ากด "รับแจ้งเตือนประกันทาง LINE" บนการ์ด
2. Server สร้างรหัส 6 ตัว เก็บใน transient `itwt_link_{code}` → `asset_id` (หมดอายุ 15 นาที)
3. เปิด deep link `https://line.me/R/oaMessage/{OA_ID}/?ผูกแจ้งเตือน%20{code}` (ลูกค้าต้องเพิ่มเพื่อน OA)
4. Webhook รับข้อความ → ตรวจรหัส → บันทึก `itwt_line_subscriptions` + ตอบกลับยืนยันพร้อมข้อความ PDPA
   (ใช้ **Reply API** ซึ่งไม่นับโควตา — ข้อความตอบกลับใน webhook ทุกกรณีต้องใช้ reply ไม่ใช้ push)
5. ยกเลิก: พิมพ์ "ยกเลิก" หรือ block OA (event `unfollow`) → เปลี่ยนทุก subscription ของ user นั้นเป็น `unsubscribed`

---

## 4. แจ้งเตือนประกันใกล้หมด (LINE Messaging API)

- WP-Cron รายวัน (เช้า) หา contract ที่ `end_date - วันนี้` ตรงกับ milestone (ค่าเริ่มต้น 60 / 30 / 7 / 0 วัน)
  และเป็นสัญญาล่าสุดของเครื่องนั้น (ถ้าต่อ MA ไว้แล้ว ไม่ต้องเตือน)
- **รวมเป็น 1 ข้อความต่อ LINE user ต่อวัน** (ลูกค้าที่มีหลายเครื่องจะไม่ถูกส่งรัว ๆ และประหยัดโควตา)
- เนื้อหาใช้เฉพาะข้อมูลในข้อ 3.2 + ลิงก์ไปหน้าเช็คประกัน/ขอต่อ MA
- กันส่งซ้ำด้วย UNIQUE ใน `itwt_notification_log`, ประมวลผลครั้งละไม่เกิน 200 รายการ ที่เหลือต่อรอบถัดไป
- ส่งไม่สำเร็จ → บันทึก `failed` และลองใหม่รอบถัดไป ห้าม log token หรือ line_user_id แบบเต็มลง error log
- ข้อควรรู้: LINE Notify ปิดบริการแล้ว ต้องใช้ Messaging API และ push message นับโควตาตามแพ็กเกจ OA

### 4.1 การจัดการโควตา (แพ็กเกจฟรี 300 ข้อความ/เดือน)
- **push เข้า group นับตามจำนวนสมาชิกในกลุ่ม** (กลุ่มช่าง 5 คน = 5 ข้อความต่อการแจ้ง 1 ครั้ง)
- ก่อน push ทุกครั้ง ตรวจยอดใช้ไป (จาก quota API ที่ cache ไว้ + ตัวนับในเครื่อง)
- กันโควตาไว้ให้แจ้งเตือนลูกค้า (`line_customer_reserve` ค่าเริ่มต้น 150): เมื่อยอดใช้ไปถึง `quota - reserve` ให้หยุด push เข้ากลุ่มช่าง
  (ช่างยังเห็นคำขอใน WP Admin และมี admin notice แจ้ง) ส่วนการแจ้งลูกค้าส่งต่อจนเต็มโควตา
- โควตาหมด → ไม่ส่ง บันทึก `failed` พร้อมเหตุผล `quota` และไม่ retry จนกว่าจะขึ้นเดือนใหม่
- ถ้าภายหลังอัปเกรดแพ็กเกจ แก้แค่ค่า `line_monthly_quota` ใน settings

### 4.2 แจ้งทีมช่าง (LINE group)
- ตั้งค่า OA ให้ bot เข้ากลุ่มได้ แล้วเชิญ bot เข้ากลุ่มช่าง
- Webhook รับ event `join` → แสดง groupId ใน settings ให้ admin กดยืนยันเป็น `line_tech_group_id`
  (รับเฉพาะกลุ่มที่ admin ยืนยัน กลุ่มอื่นที่เชิญ bot เข้าไปให้ bot ออกจากกลุ่มอัตโนมัติ)
- ข้อความ: ประเภทคำขอ, Service Tag, รุ่น, ชื่อและเบอร์ผู้แจ้ง, ลิงก์ไปหน้าคำขอใน WP Admin
  (ข้อมูลส่วนบุคคลส่งเข้ากลุ่มภายในได้ เพราะเป็นการใช้เพื่อให้บริการตามที่ลูกค้าขอ — ระบุไว้ในข้อความยินยอม)
- ส่งเป็น background ห้ามทำให้ request ของลูกค้าช้า

---

## 5. REST API (`itwt/v1`)

| Method | Route | `permission_callback` | Rate limit |
| :--- | :--- | :--- | :--- |
| GET | `/lookup?q=` | public (ข้อมูลผ่าน allowlist เท่านั้น) | 15 ครั้ง/นาที/IP |
| POST | `/support-request` | public + honeypot/dedupe | 3 ครั้ง/ชั่วโมง/IP |
| POST | `/line/link-code` | public | 5 ครั้ง/ชั่วโมง/IP |
| POST | `/line/webhook` | ตรวจ `X-Line-Signature` (HMAC-SHA256 + `hash_equals`) | - |

- Rate limiter: ใช้ `wp_cache_*` ถ้ามี persistent object cache ไม่งั้นใช้ transient, key เป็น hash ของ IP
- IP: ใช้ `REMOTE_ADDR`, เชื่อ `CF-Connecting-IP` เฉพาะเมื่อ `ITWT_TRUST_CLOUDFLARE` เป็น true
- Cache ผล lookup ด้วย `wp_cache_set( ..., 'itwt', HOUR_IN_SECONDS )` และล้างเมื่อแก้ asset / contract / log
- Error ตอบเป็นข้อความทั่วไป ไม่เปิดเผย stack trace หรือรายละเอียด DB

---

## 6. โครงสร้างไฟล์

```text
wp-content/plugins/it-warranty-tracker/
├── it-warranty-tracker.php           # Plugin header, autoload, bootstrap
├── uninstall.php                     # ลบตาราง, options, transients, caps, role
├── phpcs.xml
├── languages/
├── includes/
│   ├── class-plugin.php              # ลงทะเบียน hooks ทั้งหมด
│   ├── class-installer.php           # dbDelta, db version, caps/role
│   ├── class-coverage.php            # คำนวณสถานะความคุ้มครองจาก contracts
│   ├── class-service-tag.php         # สุ่ม/normalize Service Tag
│   ├── class-rate-limiter.php
│   ├── class-public-presenter.php    # allowlist field ที่ส่งออกหน้าเว็บ
│   ├── repositories/
│   │   ├── class-customer-repo.php
│   │   ├── class-asset-repo.php
│   │   ├── class-contract-repo.php
│   │   ├── class-service-log-repo.php
│   │   ├── class-support-request-repo.php
│   │   └── class-subscription-repo.php
│   ├── line/
│   │   ├── class-line-client.php     # เรียก Messaging API
│   │   ├── class-line-webhook.php    # ตรวจ signature, ผูก/ยกเลิก, join group
│   │   ├── class-line-quota.php      # ตรวจ/สงวนโควตาก่อน push
│   │   ├── class-expiry-notifier.php # cron แจ้งเตือนลูกค้า
│   │   └── class-tech-notifier.php   # แจ้งกลุ่มช่างเมื่อมีคำขอใหม่
│   ├── class-csv-importer.php
│   ├── class-rest-api.php
│   └── class-shortcode.php
├── admin/
│   ├── class-admin-menu.php
│   ├── class-asset-list-table.php    # extends WP_List_Table
│   ├── class-request-list-table.php
│   ├── views/
│   │   ├── dashboard.php
│   │   ├── asset-list.php
│   │   ├── asset-form.php            # asset + specs + contracts + service logs
│   │   ├── customer-list.php
│   │   ├── request-list.php
│   │   ├── settings.php
│   │   └── print-label.php
│   ├── css/admin.css
│   └── js/
│       ├── admin.js
│       └── vendor/qrcode-generator.js
└── public/
    ├── views/lookup-portal.php
    ├── css/warranty-portal.css
    └── js/warranty-lookup.js
```

ทุกไฟล์ PHP ขึ้นต้นด้วย `defined( 'ABSPATH' ) || exit;`

---

## 7. สิ่งที่ต้องเตรียมก่อนเริ่มพัฒนา

1. สร้าง Messaging API channel ของ LINE OA บริษัท → ใส่ `ITWT_LINE_CHANNEL_SECRET` / `ITWT_LINE_CHANNEL_TOKEN` ใน `wp-config.php`
2. เปิด "Allow bot to join group chats" และตั้ง Webhook URL เป็น `https://{โดเมน}/wp-json/itwt/v1/line/webhook`
3. ข้อความยินยอม (PDPA) สำหรับฟอร์มแจ้งซ่อมและการสมัครรับแจ้งเตือน — ให้ฝ่ายที่ดูแล PDPA ตรวจ
4. สร้างหน้าเช็คประกันที่มี shortcode `[itwt_warranty_lookup]` (URL นี้จะถูกพิมพ์ลง QR ห้ามเปลี่ยนภายหลัง)
