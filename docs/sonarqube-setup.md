# คู่มือตั้งค่า SonarQube — โปรเจกต์ Tonkla

เอกสารนี้ครอบคลุม: ออก token, เชื่อม Cursor (SonarQube for IDE), สแกนโค้ดด้วย Docker, และแก้ปัญหาที่พบบ่อย

| รายการ | ค่า |
| :--- | :--- |
| Server | https://sonarqube.tonkla.co |
| Project key | `tonkla` |
| Config สแกน | `sonar-project.properties` (ที่ root ของ repo) |
| สคริปต์ตัวอย่าง | `sonarqube/scan.bat.example`, `sonarqube/export-sonar.bat.example` |

---

## 1. ออก User token (ใช้กับ Cursor / IDE)

Connected Mode และคำสั่งใน Cursor ต้องใช้ **User token** ไม่ใช่ Project token

### ทางที่ถูกต้อง (แนะนำ)

1. เปิด https://sonarqube.tonkla.co แล้วล็อกอิน
2. คลิก **ไอคอนโปรไฟล์มุมขวาบน** (ไม่ใช่เมนู Administration)
3. เลือก **My Account**
4. แท็บ **Security**
5. ส่วน **Generate Tokens**
   - Name: เช่น `cursor-tonkla`
   - Type: **User Token**
   - Expires: ตามนโยบายทีม (หรือ No expiration ถ้าอนุญาต)
6. กด **Generate** แล้ว **คัดลอกทันที** (แสดงครั้งเดียว)
7. Token จะขึ้นต้นด้วย `squ_...`

### อย่าใช้เมนูเหล่านี้สำหรับ IDE

| เมนู | เหตุผล |
| :--- | :--- |
| Administration → System | ดูสถานะเซิร์ฟเวอร์ ไม่มีปุ่มสร้าง token |
| Administration → Security → Users | จัดการผู้ใช้อื่นได้ แต่สร้าง token สำหรับ IDE ควรจาก My Account ของตัวเอง |
| หน้า Project → Analysis Method / Project token | ได้ `sqp_...` ใช้สแกน CI ได้บางกรณี แต่ **Connected Mode ใน Cursor มักใช้ไม่ได้** |

### สรุปชนิด token

| Prefix | ชนิด | ใช้กับ |
| :--- | :--- | :--- |
| `squ_` | User token | Cursor Connected Mode, API ส่วนใหญ่, `scan.bat` |
| `sqp_` | Project token | สแกนโปรเจกต์เฉพาะ (ไม่แนะนำสำหรับ IDE) |
| `sqa_` | Global analysis | CI ระดับองค์กร |

---

## 2. เชื่อม Cursor — SonarQube for IDE

### สิ่งที่มีใน repo แล้ว

- `.vscode/settings.json` bind โปรเจกต์แล้ว:

```json
{
  "sonarlint.connectedMode.project": {
    "connectionId": "https-sonarqube-tonkla-co",
    "projectKey": "tonkla"
  }
}
```

- User settings อาจมี connection URL อยู่แล้ว (`sonarlint.connectedMode.connections.sonarqube`)

### ขั้นตอนเชื่อมต่อ

1. ติดตั้ง extension **SonarQube for IDE** (SonarSource)
2. `Ctrl+Shift+P` → **SonarQube: Connect to SonarQube Server**
3. กรอก:
   - Server URL: `https://sonarqube.tonkla.co`
   - Token: วาง `squ_...` จากข้อ 1
4. ถ้ายังไม่ bind โฟลเดอร์: **SonarQube: Bind all workspace folders to SonarQube**
   - เลือก connection เดิม
   - Project key: `tonkla`
5. เปิดไฟล์ใน `wp-content/themes/tonkla/` แล้วดูแท็บ **SonarQube** ด้านล่าง

### ตรวจว่าเชื่อมสำเร็จ

- แท็บ SonarQube ไม่ค้างไอคอนนาฬิกา
- Output → **SonarQube for IDE** ไม่ขึ้น `Not authorized` / `Could not initialize SonarLint Backend`
- เปิด PHP ใน theme แล้วมี findings (หรือว่างถ้าไม่มี issue จริง)

---

## 3. สแกนโค้ดขึ้นเซิร์ฟเวอร์ (Docker)

ต้องมี Docker Desktop เปิดอยู่

```bat
cd C:\laragon\www\tonkla\sonarqube
copy scan.bat.example scan.bat
```

แก้ `scan.bat` ให้ใส่ token ตอนรัน (หรือตั้ง env `SONAR_TOKEN`) — **ห้าม commit token จริง**

```bat
scan.bat
```

สคริปต์จะรัน `sonarsource/sonar-scanner-cli` อ่าน `sonar-project.properties` ที่ root

หลังสแกนเสร็จ เปิด https://sonarqube.tonkla.co/dashboard?id=tonkla

### ส่งออก issue เป็นรายงาน

```bat
copy export-sonar.bat.example export-sonar.bat
export-sonar.bat
```

ผลอยู่ที่โฟลเดอร์ `sonar-reports/` (ถูก gitignore แล้ว)

---

## 4. ขอบเขตที่ Sonar วิเคราะห์

จาก `sonar-project.properties` ปัจจุบัน:

- **รวม:** `wp-content/themes/tonkla`
- **ไม่รวม (ตัวอย่าง):** `node_modules`, `vendor`, `main.css` ที่ build แล้ว, `*.min.js`, โฟลเดอร์ `sonarqube/`, รายงาน

ถ้าต้องการวิเคราะห์ plugin `it-warranty-tracker` ด้วย ต้องเพิ่มใน `sonar.sources` (คั่นด้วย comma) แล้วสแกนใหม่

---

## 5. แก้ปัญหาที่พบบ่อย

### `command 'SonarLint.ConnectToSonarQube' not found`

Extension โผล่ใน palette แต่ backend ยังไม่ขึ้น

1. `Ctrl+Shift+P` → **Developer: Reload Window**
2. Extensions → SonarQube for IDE → Disable → Enable → Reload
3. ถ้ายังพัง: Uninstall แล้วติดตั้งใหม่

### `Could not initialize SonarLint Backend` / H2 corrupted

ฐานข้อมูล local ของ IDE เสีย (เคยเกิดที่ `%USERPROFILE%\.sonarlint\storage\h2\`)

1. ปิด Cursor ให้หมด (หรือหยุด process `java` ของ sonarlint)
2. ลบโฟลเดอร์:

```text
%USERPROFILE%\.sonarlint\storage\h2
%USERPROFILE%\.sonarlint\work
```

3. เปิด Cursor ใหม่ / Reload Window
4. เชื่อม connection + token อีกครั้ง

### `Not authorized`

- Token หมดอายุ / ถูกลบ
- ใช้ `sqp_` แทน `squ_` → ออก User token ใหม่จาก My Account → Security

### แท็บ Findings ว่างตลอด

- ยังไม่เปิดไฟล์ใน path ที่ถูกวิเคราะห์
- ยังไม่ bind project key `tonkla`
- ยังไม่เคยสแกนขึ้นเซิร์ฟเวอร์ (Connected Mode ดึงกฎ/issue จากเซิร์ฟเวอร์เป็นหลักในบางโหมด)

### สแกนด้วย Docker ล้ม

- Docker ไม่ได้เปิด
- `SONAR_TOKEN` ว่าง
- เซิร์ฟเวอร์ล่ม — เช็ค https://sonarqube.tonkla.co/api/system/status ควรได้ `"status":"UP"`

---

## 6. Checklist ตั้งค่าเครื่องใหม่

- [ ] ล็อกอิน SonarQube ได้
- [ ] สร้าง User token (`squ_...`) จาก **My Account → Security**
- [ ] ติดตั้ง SonarQube for IDE ใน Cursor
- [ ] Connect to Server + bind `tonkla`
- [ ] (ถ้าต้องการขึ้น dashboard) มี Docker + รัน `sonarqube/scan.bat`
- [ ] ไม่ commit `scan.bat` / `export-sonar.bat` ที่มี token จริง

---

## 7. อ้างอิงใน repo

| ไฟล์ | หน้าที่ |
| :--- | :--- |
| `sonar-project.properties` | project key, sources, exclusions |
| `sonarqube/scan.bat.example` | สแกนด้วย Docker |
| `sonarqube/export-sonar.bat.example` | ดึง issues เป็นรายงาน |
| `sonarqube/export-sonar-issues.ps1` | สคริปต์ export |
| `.vscode/settings.json` | bind Connected Mode |
| `.gitignore` | ไม่ track token scripts / reports / `.sonar/` |
