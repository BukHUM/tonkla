# Tonkla WordPress — site overlay

เก็บเฉพาะโค้ดที่เราพัฒนาเอง (theme + custom plugin + docs) **ไม่รวม WordPress core**

โครงสร้างโฟลเดอร์ตรงกับ root ของ WordPress เพื่อให้ `git pull` ลงไซต์ปลายทางแล้วไฟล์ไปอยู่ตำแหน่งเดิม

```text
/
├── wp-content/
│   ├── themes/tonkla/              ← theme
│   └── plugins/it-warranty-tracker/ ← custom plugin
├── docs/
├── sonarqube/*.example
├── AGENTS.md
└── README.md
```

## ติดตั้งบนเซิร์ฟเวอร์ / เครื่องใหม่

1. ติดตั้ง WordPress ปกติ (core + `wp-config.php`)
2. Clone หรือ pull repo นี้ทับ root ของ WordPress:

```bash
cd /path/to/wordpress
git clone https://github.com/BukHUM/tonkla-site.git .
# หรือถ้ามี WordPress อยู่แล้ว:
git init
git remote add origin https://github.com/BukHUM/tonkla-site.git
git fetch origin
git checkout -f main
```

3. ติดตั้ง plugin ที่ theme ต้องการเพิ่มเอง (เช่น Carbon Fields, Polylang)
4. เปิดใช้งาน theme `tonkla` และ plugin `IT Warranty Tracker`
5. ใส่ LINE secrets ใน `wp-config.php` ตาม `docs/warranty-checker-plugin.md`

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

## หมายเหตุเรื่อง repo theme เดิม

Repo theme เดี่ยว [BukHUM/tonkla](https://github.com/BukHUM/tonkla) ยังมีอยู่ได้ แต่แหล่งหลักของการพัฒนาต่อจากนี้คือ repo นี้ (theme อยู่ใน `wp-content/themes/tonkla` โดยไม่มี `.git` ซ้อน)
