# ITWT Admin List UI Standard

มาตรฐานหน้า list แอดมินของปลั๊กอิน IT Warranty Tracker  
อ้างอิงจากหน้า **ลูกค้า / คู่ค้า** — หน้า list ใหม่หรือแก้ของเดิมต้องจัดโครงตามนี้

## โครงหน้า (ลำดับบน→ล่าง)

1. **`.wrap.itwt-wrap`**
2. **`.itwt-page-header`** — eyebrow + ชื่อหน้า + `.itwt-page-header__actions` (CTA หลัก)
3. **Toast** ผ่าน `\ITWT\Toast::push()` (ไม่ใช้ `admin_notice` ดิบ)
4. **`.itwt-panel.itwt-list-panel`**
   - `.itwt-panel__head` — ชื่อรายการ + `.itwt-panel__count`
   - `.itwt-panel__body`
     - `.itwt-panel__toolbar`
     - ตาราง (custom `.itwt-table` หรือ `WP_List_Table`)
     - partial `admin/views/partials/list-footer.php`

## Toolbar

```
.itwt-panel__toolbar
  ├─ .itwt-bulk-bar          (ซ้าย — มีเมื่อมีรายการ + มี bulk)
  ├─ .itwt-panel__toolbar-spacer
  └─ .itwt-toolbar-filters   (ขวา — filter / search แบบ GET)
```

- Bulk: select `.itwt-bulk-action` + ปุ่ม «ทำรายการ» + hint `.itwt-bulk-selected-hint`
- Form bulk: class `.itwt-bulk-form` (JS ใช้ class นี้ร่วมกันทุกหน้า)
- Checkbox แถว: class `.itwt-cb-row`
- Select-all: class `.itwt-cb-select-all` (หรือ thead `.check-column` ของ WP_List_Table)
- Filter form ต้องส่งต่อ `per_page` เป็น hidden

## ตาราง

| องค์ประกอบ | กฎ |
| :--- | :--- |
| คอลัมน์ «จัดการ» | บังคับ — ใช้ `.itwt-row-actions` + `.itwt-row-action` (ลิงก์/ปุ่มข้อความ ไม่ใช้ `button button-small` ดิบ) |
| รหัส / Service Tag | ห่อด้วย `<code>` + `.itwt-tag-link` ถ้าคลิกได้ |
| ลบแถว | `.itwt-row-action--danger` + `data-itwt-confirm="delete"` |
| Empty state | `.itwt-empty` ในแถว colspan หรือข้อความว่างชัด |
| Checkbox | มี label screen-reader ต่อแถว |

ถ้าใช้ `WP_List_Table`:

- override `display_tablenav()` ให้ว่าง — อย่าใช้ tablenav ของ WP
- ย้าย bulk / filter / pagination ไป toolbar + `list-footer.php`

## Footer

ใช้ partial เดียว:

```php
$list_page  = 'itwt-assets'; // ค่า page= ใน admin.php
$per_page   = ...;
$page       = ...;
$total      = ...;
$query_args = array( 's' => $search, /* filters */ );
include ITWT_PLUGIN_DIR . 'admin/views/partials/list-footer.php';
```

- ตัวเลือกต่อหน้า: **20 / 50 / 100** เท่านั้น — ผ่าน `\ITWT\Admin_List`
- Pagination อยู่ขวา, ตัวเลือกต่อหน้าอยู่ซ้าย

## Helpers ที่ต้องใช้ซ้ำ

| Class / file | หน้าที่ |
| :--- | :--- |
| `\ITWT\Admin_List` | `per_page_options()`, `normalize_per_page()`, `request_per_page()` |
| `\ITWT\Dates` | แสดงวันที่ — ดูหัวข้อด้านล่าง |
| `admin/views/partials/list-footer.php` | footer รวม |
| `admin.js` → `initListBulk()` | select-all + hint + ยืนยัน delete bulk |
| `\ITWT\Toast` | แจ้งผล |
| Confirm dialog (`data-itwt-confirm` / `itwtConfirm`) | ห้าม `window.confirm` |

## รูปแบบวันที่ (บังคับ)

| ชั้น | รูปแบบ |
| :--- | :--- |
| ข้อความอ่านอย่างเดียว (แอดมิน + หน้าบ้าน) | `d M Y` เช่น **01 Nov 2029** |
| วันเวลา | `d M Y H:i` เช่น **15 Oct 2026 14:30** |
| ช่วงวันที่ | `01 Nov 2026 → 01 Nov 2029` ผ่าน `Dates::format_range()` |
| ช่องกรอกฟอร์ม | **`dd/mm/yyyy`** ผ่าน `Dates::render_input()` — ห้าม `<input type="date">` ตรง ๆ |
| เก็บใน DB / SQL / ค่า submit | ISO **`Y-m-d`** |

ใช้ `\ITWT\Dates::format()` / `format_datetime()` / `format_range()` ทุกครั้งที่แสดงวันที่ — ห้าม `echo` ค่า `2029-11-01` ดิบ

## สิ่งที่ห้าม

- แสดง tablenav / bulk / pagination ดิบของ WordPress นอกมาตรฐานนี้
- ใส่ action ใต้คอลัมน์หลักแบบ `row_actions()` ของ WP (ให้รวมในคอลัมน์ «จัดการ»)
- Hardcode รายการ per_page นอก `Admin_List`
- โหลด UI framework เพิ่มเพื่อทำ list

## Checklist ก่อน merge หน้า list ใหม่

- [ ] โครง page-header → panel → toolbar → table → list-footer
- [ ] คอลัมน์ «จัดการ» ใช้ `.itwt-row-actions`
- [ ] per_page ผ่าน `Admin_List` และ footer partial
- [ ] มี empty / success (toast) / error
- [ ] Input sanitize + output escape + nonce + capability
- [ ] PHPCS WordPress ผ่าน
