# Lab04: ระบบบริหารจัดการข้อมูลนักศึกษา (Node Express + MySQL)

ระบบแยกสถาปัตยกรรม **Frontend** และ **Backend** ตามโจทย์:
- **Backend**: Node.js Express REST API + MySQL (mysql2) + JWT Authentication + PDFKit Report
- **Frontend**: Vanilla HTML5 + Tailwind CSS + JavaScript (ES6) + Axios

---

## โครงสร้างโปรเจกต์ (Project Structure)

```
src/node/lab04/
├── backend/                  # ฝั่ง Backend (Express API)
│   ├── .env                  # การตั้งค่าพอร์ตและฐานข้อมูล
│   ├── package.json          # Dependencies ของ backend
│   ├── server.js             # เซิร์ฟเวอร์ Express หลัก (พอร์ต 5001)
│   ├── assets/fonts/         # ฟอนต์ Prompt สำหรับออกรายงาน PDF
│   ├── config/
│   │   └── database.js       # เชื่อมต่อ MySQL Connection Pool
│   ├── controllers/
│   │   ├── authController.js   # ล็อกอิน, ตรวจสอบผู้ใช้
│   │   ├── majorController.js  # CRUD ตาราง tb_major
│   │   ├── studentController.js# CRUD ตาราง tb_student + ระบบค้นหา
│   │   └── reportController.js # ออกรายงาน PDF ภาษาไทย
│   ├── middlewares/
│   │   └── authMiddleware.js # ตรวจสอบ JWT Token
│   └── routes/
│       ├── authRoutes.js
│       ├── majorRoutes.js
│       ├── studentRoutes.js
│       └── reportRoutes.js
│
├── frontend/                 # ฝั่ง Frontend (Vanilla HTML + Tailwind + Axios)
│   ├── index.html            # หน้าเว็บหลัก (SPA UI สวยงาม)
│   ├── package.json
│   └── assets/
│       ├── css/
│       │   └── style.css     # CSS ปรับแต่งเพิ่มเติม
│       └── js/
│           ├── api.js        # Axios Client จัดการ Token และต่อ API
│           └── app.js        # ตรรกะการทำงาน UI, ฟอร์ม, ตาราง, และค้นหา
│
└── README.md
```

---

## บัญชีเข้าสู่ระบบ (Default Credentials)

| สิทธิ์ (Role) | ชื่อผู้ใช้ (Username) | รหัสผ่าน (Password) |
|---|---|---|
| **ผู้ดูแลระบบ (Admin)** | `admin` | `admin123` |
| **เจ้าหน้าที่ (Staff)** | `staff` | `staff123` |

*(มีปุ่มทางลัด Quick Login สำหรับกดทดสอบได้ทันทีในหน้า Login)*

---

## วิธีการเริ่มใช้งาน (How to Run)

### 1. ตรวจสอบ MySQL ใน Docker
ตรวจสอบให้แน่ใจว่าคอนเทนเนอร์ฐานข้อมูลกำลังทำงานอยู่:
```bash
docker ps
```
(พอร์ตของ MySQL คือ `3307`, ฐานข้อมูลชื่อ `db_student`)

### 2. รัน Backend เซิร์ฟเวอร์
```bash
cd src/node/lab04/backend
npm start
# หรือรันแบบ nodemon/watch: npm run dev
```

### 3. เปิดใช้งาน Frontend
สามารถเข้าใช้งานได้ 2 รูปแบบตามสะดวก:
- **วิธีที่ 1 (สะดวกที่สุด):** เข้าผ่านเบราว์เซอร์ที่ [http://localhost:5001](http://localhost:5001) ได้ทันที (Backend ให้บริการไฟล์ Static ของ Frontend ให้โดยตรง)
- **วิธีที่ 2:** เปิดไฟล์ [index.html](file:///Users/jadsadamaoya/Desktop/JS-Project/sell-200/src/node/lab04/frontend/index.html) ผ่าน VS Code Live Server หรือรัน `npx serve src/node/lab04/frontend`

---

## คุณสมบัติหลักของระบบ (Features)

1. **ระบบ Login & Authentication:**
   - ตรวจสอบรหัสผ่านด้วย `bcrypt` และออก Token ด้วย `JWT`
   - จัดการสิทธิ์ `admin` และ `staff`
2. **การจัดการสาขาวิชา (`tb_major`):**
   - แสดงรายชื่อสาขา พร้อมนับจำนวนนักศึกษาในแต่ละสาขา
   - เพิ่ม, แก้ไข, และลบสาขาวิชา
3. **การจัดการข้อมูลนักศึกษา (`tb_student` - 1:M Relationship):**
   - แสดงรายชื่อนักศึกษาพร้อม `JOIN tb_major` เพื่อแสดงชื่อสาขา
   - มี Dropdown เลือกสาขาวิชาจากฐานข้อมูลแบบ Dynamic
   - เพิ่ม, แก้ไข, และลบข้อมูลนักศึกษา
4. **ระบบค้นหาและกรองข้อมูล (Search & Filter):**
   - ค้นหาทันทีจากรหัสนักศึกษา, ชื่อ, หรือนามสกุล
   - ตัวกรองแยกตามสาขาวิชา
5. **ระบบออกรายงาน PDF (Report PDF):**
   - สรุปตารางรายชื่อนักศึกษาพร้อมฟอนต์ภาษาไทย (Prompt) สวยงาม
   - กรองพิมพ์เฉพาะสาขาที่เลือก หรือพิมพ์ทั้งหมด
