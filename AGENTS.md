# AGENTS.md — กฎการทำงานสำหรับ AI (WordPress Web App)

ไฟล์นี้คือกฎหลัก (single source of truth) ที่ AI ทุกตัวต้องทำตาม
ถ้ากฎข้อใดขัดกับคำสั่งผู้ใช้ ให้ถามก่อน ห้ามเดาเอง

---

## 0. บริบทโปรเจกต์
- เป็นเว็บแอปที่พัฒนาบน WordPress (theme / plugin แบบ custom)
- PHP 8.1+ , ใช้ WordPress Coding Standards (WPCS) และ PHPCS
- ทุก function / class / hook / option / table ต้องมี prefix ของโปรเจกต์ เช่น `myapp_` หรือใช้ namespace `MyApp\`
- ห้ามแก้ไฟล์ใน WordPress core และ plugin ของบุคคลที่สามโดยตรง ให้ใช้ hook / filter / child theme แทน
- ห้ามติดตั้ง plugin หรือ library เพิ่มโดยไม่ขออนุญาตก่อน

---

## 1. Performance
### PHP / Query
- ห้าม query ฐานข้อมูลภายใน loop (N+1) ให้ดึงข้อมูลรวมครั้งเดียวแล้วค่อยวน
- `WP_Query` ต้องระบุเฉพาะที่ใช้:
  - ไม่ต้องการ pagination → `'no_found_rows' => true`
  - ต้องการแค่ ID → `'fields' => 'ids'`
  - ไม่ใช้ meta/term → `'update_post_meta_cache' => false`, `'update_post_term_cache' => false`
  - ห้ามใช้ `'posts_per_page' => -1` กับข้อมูลที่โตได้ ให้กำหนด limit เสมอ
- ห้ามใช้ `query_posts()`
- ข้อมูลที่คำนวณหนักหรือเรียก API ภายนอก ให้ cache ด้วย Transients / Object Cache (`wp_cache_*`) พร้อมกำหนดเวลาหมดอายุและจุด invalidate
- งานหนักที่ไม่ต้องตอบทันที ให้ทำเป็น background (WP-Cron / Action Scheduler) ไม่ทำใน request ของผู้ใช้

### Front-end
- `wp_enqueue_script` / `wp_enqueue_style` เฉพาะหน้าที่ใช้จริง (ตรวจเงื่อนไขก่อน enqueue)
- Script ใช้ `defer` หรือ `strategy => 'defer'`, ห้ามโหลด jQuery ถ้าไม่จำเป็น
- รูปภาพใช้ `wp_get_attachment_image()` (มี srcset + lazy loading), ใช้ WebP/AVIF, ระบุ width/height กัน layout shift
- ห้ามใส่ CSS/JS inline ขนาดใหญ่ ห้ามโหลด font เกิน 2 family
- เป้าหมาย Core Web Vitals: LCP < 2.5s, INP < 200ms, CLS < 0.1

---

## 2. Security (อ้างอิง OWASP Top 10)
### Input / Output
- **Sanitize ทุก input** ตอนรับเข้า: `sanitize_text_field`, `sanitize_email`, `absint`, `sanitize_key`, `wp_unslash` ก่อน sanitize
- **Escape ทุก output ตอนแสดงผล (escape late)**: `esc_html`, `esc_attr`, `esc_url`, `esc_js`, `wp_kses_post` / `wp_kses`
- ห้าม `echo` ตัวแปรตรง ๆ โดยไม่ escape

### Access Control
- ทุก action ที่เปลี่ยนแปลงข้อมูลต้องมีทั้ง
  1. Nonce: `wp_nonce_field` / `check_admin_referer` / `check_ajax_referer`
  2. Capability: `current_user_can()` ตามสิทธิ์ที่เหมาะสม
- REST API ทุก route ต้องมี `permission_callback` ที่ตรวจสิทธิ์จริง ห้ามใช้ `__return_true` กับ endpoint ที่เขียน/ลบ/อ่านข้อมูลส่วนตัว
- ตรวจ ownership เสมอ (ผู้ใช้แก้ได้เฉพาะข้อมูลของตัวเอง) ป้องกัน IDOR

### Injection
- SQL ทุกคำสั่งที่มีตัวแปรต้องใช้ `$wpdb->prepare()` ห้ามต่อ string เอง
- ห้ามใช้ `eval`, `extract`, `unserialize` กับข้อมูลจากผู้ใช้, `shell_exec` / `exec`
- ชื่อไฟล์ / path จากผู้ใช้ต้อง validate กัน path traversal

### อื่น ๆ
- ไฟล์ PHP ทุกไฟล์เริ่มด้วย `defined( 'ABSPATH' ) || exit;`
- Upload ต้องตรวจด้วย `wp_check_filetype_and_ext()` และจำกัดชนิด/ขนาด
- ห้าม hardcode secret / API key ในโค้ด ให้อ่านจาก `wp-config.php` หรือ environment
- ห้าม log password, token, ข้อมูลส่วนบุคคล และห้ามแสดง error / stack trace ต่อผู้ใช้
- Redirect ใช้ `wp_safe_redirect()` ตามด้วย `exit;`
- Form สำคัญ (login, contact, reset password) ต้องมี rate limit
- Library ภายนอกต้องเป็นเวอร์ชันที่ยังได้รับ security update

---

## 3. โค้ดไม่ซ้ำซ้อน (DRY)
- **ก่อนเขียน function ใหม่ ต้องค้นหาในโปรเจกต์ก่อน** ว่ามีของเดิมหรือ WordPress มี API ให้อยู่แล้วหรือไม่
- Logic ที่ใช้ซ้ำ ≥ 2 ที่ ให้แยกเป็น helper / class ในโฟลเดอร์ `inc/` หรือ `src/`
- HTML ที่ใช้ซ้ำให้ใช้ `get_template_part()` หรือ block/pattern
- 1 function ทำ 1 หน้าที่ ความยาวไม่ควรเกิน ~50 บรรทัด
- ห้าม copy-paste แล้วแก้นิดหน่อย ให้ refactor เป็นพารามิเตอร์แทน
- ลบโค้ดที่ไม่ได้ใช้, ห้ามทิ้งโค้ดที่ comment ไว้
- ค่าคงที่ (ชื่อ option, meta key, table) ประกาศเป็น constant ที่เดียว

---

## 4. Database — ไม่ซ้ำ ไม่ช้า
### ป้องกันข้อมูลซ้ำ
- ตารางที่สร้างเองต้องมี `PRIMARY KEY` และ `UNIQUE KEY` บนคอลัมน์ที่ต้องไม่ซ้ำ
- การบันทึกใช้แนวคิด upsert (`INSERT ... ON DUPLICATE KEY UPDATE` หรือเช็คก่อน insert) ห้าม insert ซ้ำเมื่อ request ถูกส่งสองครั้ง
- `add_post_meta` / `add_user_meta` ให้ใช้ `$unique = true` หรือใช้ `update_*_meta` แทน
- ออกแบบให้เป็น normalized ไม่เก็บข้อมูลเดียวกันหลายที่

### ป้องกันความช้า
- ข้อมูลที่เยอะ / ต้อง filter / ต้อง report ให้ใช้ **custom table** แทนการยัดลง `postmeta` หรือ `options`
- สร้าง index บนคอลัมน์ที่ใช้ใน `WHERE`, `JOIN`, `ORDER BY`
- ห้าม `SELECT *` ให้ระบุคอลัมน์ที่ใช้ และต้องมี `LIMIT` / pagination
- หลีกเลี่ยง `meta_query` / `LIKE '%...%'` บนข้อมูลจำนวนมาก
- option ขนาดใหญ่หรือไม่ได้ใช้ทุกหน้าให้ตั้ง `autoload = false`
- ใช้ `dbDelta()` สำหรับสร้าง/แก้ schema พร้อมเก็บ schema version เพื่อ migrate
- query ใหม่ที่ซับซ้อนต้องตรวจด้วย `EXPLAIN` (หรือ Query Monitor) ก่อนส่งงาน
- ต้องมีการลบข้อมูลชั่วคราว / transient ที่หมดอายุ และ uninstall ต้องลบตาราง/option ของตัวเอง

---

## 5. UX/UI — ต้องไม่ดูเหมือน AI ทำ
### เส้นขอบ (สำคัญ)
- **องค์ประกอบหนึ่งใช้เส้นขอบได้แค่เส้นเดียว** เลือกอย่างใดอย่างหนึ่ง: `border` หรือ `outline` หรือ `box-shadow` แบบเส้น
- ห้ามใช้ `border` + `ring` (Tailwind) + `outline` + `shadow` ซ้อนกันบนองค์ประกอบเดียว
- ห้ามกล่องซ้อนกล่องที่มีขอบทุกชั้น (card ในการ์ด ในการ์ด)
- สถานะ focus ให้ **เปลี่ยนสีของเส้นเดิม** แทนการเพิ่ม ring อีกเส้น แต่ต้องยังเห็นชัดตาม accessibility
- ความหนาเส้น 1px เป็นค่าเริ่มต้น สีเส้นใช้ token เดียวทั้งระบบ

### หลีกเลี่ยงลักษณะ "AI-generated"
- ห้าม gradient ม่วง-น้ำเงิน, glassmorphism, glow, เงาฟุ้งทุกกล่อง
- ห้ามใช้ emoji เป็นไอคอน ให้ใช้ชุดไอคอน SVG ชุดเดียวทั้งเว็บ
- ห้าม layout สำเร็จรูป hero + 3 การ์ดไอคอน + CTA ซ้ำทุกหน้า
- ห้าม border-radius ใหญ่ทุกอย่าง กำหนด radius ไม่เกิน 2 ค่าทั้งระบบ
- ห้ามข้อความ placeholder แบบ "Unlock your potential", "Seamless experience" ให้ใช้เนื้อหาจริงหรือถามผู้ใช้

### หลักการออกแบบ
- ใช้ design token ที่เดียว (สี, spacing, font, radius) ใน `theme.json` หรือ CSS variables
- Spacing ใช้สเกลคงที่ (4 / 8 / 12 / 16 / 24 / 32 / 48 / 64)
- สีหลักไม่เกิน 1 สี + สีกลาง (neutral) + สีสถานะ (success/warning/error)
- แยกลำดับความสำคัญด้วย ขนาดตัวอักษร / น้ำหนัก / ระยะห่าง ก่อนใช้เส้นหรือกล่อง
- รองรับมือถือก่อน (mobile-first), ภาษาไทยต้องใช้ font ที่รองรับวรรณยุกต์ถูกต้องและ line-height ≥ 1.6
- ผ่าน WCAG 2.2 AA: contrast ≥ 4.5:1, ใช้ keyboard ได้ครบ, มี label ทุก input
- ทุก action ต้องมีสถานะ loading / empty / error / success

---

## 6. ขั้นตอนการทำงานของ AI
1. อ่านโค้ดที่เกี่ยวข้องก่อนแก้ และค้นหาของเดิมที่ใช้ซ้ำได้
2. งานที่แก้หลายไฟล์ ให้สรุปแผนสั้น ๆ ก่อนลงมือ
3. แก้ทีละส่วนเล็ก ๆ ไม่ refactor สิ่งที่ไม่เกี่ยวข้องกับงาน
4. รัน `phpcs --standard=WordPress` และแก้ให้ผ่านก่อนส่ง
5. สรุปสิ่งที่เปลี่ยน, ผลต่อ database (ถ้ามี) และสิ่งที่ผู้ใช้ต้องทดสอบ

### Checklist ก่อนส่งงาน
- [ ] ไม่มี query ใน loop, query มี limit และ index รองรับ
- [ ] Input sanitize, output escape, มี nonce + capability check
- [ ] SQL ใช้ `$wpdb->prepare()`
- [ ] ไม่มีโค้ดซ้ำกับของเดิมในโปรเจกต์
- [ ] ข้อมูลใหม่มี UNIQUE key / ไม่ insert ซ้ำ
- [ ] UI ใช้เส้นขอบเส้นเดียว ไม่มี border + ring ซ้อน
- [ ] ผ่าน PHPCS และไม่มี PHP warning/notice
