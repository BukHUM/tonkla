# ต้นกล้า ไอที — WordPress site overlay

**โดย [ต้นกล้า ไอที](https://tonkla.co)** · Repo: [BukHUM/tonkla](https://github.com/BukHUM/tonkla)

เก็บเฉพาะโค้ดที่พัฒนาเอง (theme + custom plugin + docs) **ไม่รวม WordPress core**

โครงสร้างโฟลเดอร์ตรงกับ root ของ WordPress เพื่อให้ `git pull` ลงไซต์ปลายทางแล้วไฟล์ไปอยู่ตำแหน่งเดิม

```text
/
├── wp-content/
│   ├── themes/tonkla/               ← theme
│   └── plugins/it-warranty-tracker/ ← custom plugin
├── docs/
├── sonarqube/*.example
├── AGENTS.md
└── README.md
```

## สิ่งที่รวมใน repo

| ส่วน | path |
| :--- | :--- |
| Theme | `wp-content/themes/tonkla/` |
| IT Warranty Tracker | `wp-content/plugins/it-warranty-tracker/` |
| สเปก / เอกสาร | `docs/` (เช่น `warranty-checker-plugin.md`) |

## ติดตั้งบนเซิร์ฟเวอร์ / เครื่องใหม่

1. ติดตั้ง WordPress ปกติ (core + `wp-config.php`)
2. Clone หรือ pull repo นี้ทับ root ของ WordPress:

```bash
cd /path/to/wordpress
git clone https://github.com/BukHUM/tonkla.git .
# หรือถ้ามี WordPress อยู่แล้ว:
git remote add origin https://github.com/BukHUM/tonkla.git
git fetch origin
git checkout -f main
```

3. ติดตั้ง plugin ที่ theme ต้องการเพิ่มเอง (เช่น Carbon Fields, Polylang)
4. เปิดใช้งาน theme `tonkla` และ plugin **IT Warranty Tracker**
5. ใส่ LINE secrets ใน `wp-config.php` ตาม `docs/warranty-checker-plugin.md`

รายละเอียด plugin: ดู `wp-content/plugins/it-warranty-tracker/README.md`

## สิ่งที่ไม่ถูก track

- WordPress core (`wp-admin`, `wp-includes`, ไฟล์ PHP ที่ root)
- Plugin ของบุคคลที่สาม
- `wp-config.php`, uploads, cache, backups

## เพิ่ม custom plugin ใหม่

แก้ `.gitignore` ให้ allowlist slug ของ plugin เช่น:

```gitignore
!/wp-content/plugins/my-plugin/
!/wp-content/plugins/my-plugin/**
```

## ลิงก์

- เว็บไซต์: [https://tonkla.co](https://tonkla.co)
- GitHub: [https://github.com/BukHUM/tonkla](https://github.com/BukHUM/tonkla)
