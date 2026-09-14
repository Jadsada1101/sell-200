# Lab05: ระบบบริหารจัดการข้อมูลนักศึกษา (Next.js + use client & router.js)

ระบบพัฒนาต่อยอดมาจาก **Lab04** โดยเปลี่ยนสถาปัตยกรรมมาเป็น **Next.js (App Router)** พร้อมแบ่งโครงสร้าง **Frontend | Backend** อย่างชัดเจนตามโจทย์:
- **Frontend (Next.js App)**: ใช้งาน `"use client"`, `useRouter` จาก `next/navigation`, `lib/router.js` และ Tailwind CSS
- **Backend**: มีทั้ง **Next.js Route Handlers (`app/api/`)** สำหรับรันแบบ All-in-One และโฟลเดอร์ **`backend/` (Express API + `router.js`)** สำหรับรันแยกเซิร์ฟเวอร์อิสระ
- **Database**: MySQL (`db_student`) เชื่อมต่อผ่านพอร์ต 3307 ใน Docker
- **PDF Export**: ออกรายงานรายชื่อนักศึกษาด้วยฟอนต์ภาษาไทย (Prompt)

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```
src/node/lab05/
├── backend/                      # [Backend ฝั่ง Express + router.js]
│   ├── .env                      # การตั้งค่าพอร์ต (5001) และฐานข้อมูล
│   ├── package.json
│   ├── server.js                 # เซิร์ฟเวอร์ Express หลัก
│   ├── router.js                 # Central Router รวมทุก Routes ตามโจทย์
│   ├── config/database.js        # เชื่อมต่อ MySQL Connection Pool
│   ├── controllers/              # Controllers จัดการ Business Logic
│   │   ├── authController.js
│   │   ├── studentController.js
│   │   ├── majorController.js
│   │   └── reportController.js
│   ├── middlewares/authMiddleware.js
│   └── routes/                   # Sub-routers สำหรับแต่ละโมดูล
│
├── app/                          # [Frontend ฝั่ง Next.js & API Handlers]
│   ├── layout.js                 # Root Layout + Prompt Thai Font + Navbar
│   ├── page.js                   # หน้า Login / เข้าสู่ระบบ ("use client")
│   ├── globals.css               # สไตล์ Tailwind CSS
│   ├── students/
│   │   └── page.js               # หน้าจัดการนักศึกษา CRUD + Search ("use client")
│   ├── majors/
│   │   └── page.js               # หน้าจัดการสาขาวิชา CRUD ("use client")
│   ├── reports/
│   │   └── page.js               # หน้าออกรายงาน PDF พร้อมตัวกรอง ("use client")
│   └── api/                      # [Backend ฝั่ง Next.js Route Handlers]
│       ├── auth/login/route.js   # API เข้าสู่ระบบ (JWT)
│       ├── auth/me/route.js      # API ดึงข้อมูล User ปัจจุบัน
│       ├── students/route.js     # API นักศึกษา (GET, POST)
│       ├── students/[id]/route.js# API นักศึกษารายคน (GET, PUT, DELETE)
│       ├── majors/route.js       # API สาขาวิชา (GET, POST)
│       ├── majors/[code]/route.js# API สาขารายสาขา (GET, PUT, DELETE)
│       └── reports/students-pdf/route.js # API สร้างไฟล์ PDF ภาษาไทย
│
├── components/                   # Reusable UI Components ("use client")
│   ├── Navbar.js                 # แถบเมนูด้านบน แสดง User และปุ่ม Logout
│   └── Modal.js                  # กล่อง Popup สำหรับฟอร์มเพิ่ม/แก้ไข/ลบ
│
├── lib/                          # Services & Routing Helpers
│   ├── router.js                 # ประกาศเส้นทางและ Helper จัดการ Routing ตามโจทย์
│   ├── api.js                    # Client API Service จัดการ Token อัตโนมัติ
│   ├── auth.js                   # ตรวจสอบ JWT Token ฝั่ง Server
│   └── db.js                     # เชื่อมต่อ MySQL Pool ฝั่ง Next.js
│
├── assets/fonts/                 # ฟอนต์ Prompt ภาษาไทยสำหรับ PDF
├── .env.local                    # ตั้งค่า Environment Variables ของ Next.js
├── package.json
└── README.md
```

---

## 🔑 ข้อมูลเข้าสู่ระบบ (Default Credentials)

| บทบาท (Role) | บัญชีผู้ใช้ (Username) | รหัสผ่าน (Password) | สิทธิ์การใช้งาน |
|---|---|---|---|
| **ผู้ดูแลระบบ (Admin)** | `admin` | `admin123` | จัดการนักศึกษาได้ทั้งหมด, เพิ่ม/แก้ไข/ลบสาขาวิชาได้ |
| **เจ้าหน้าที่ (Staff)** | `staff` | `staff123` | จัดการนักศึกษาได้, ดูสาขาวิชาได้ (ไม่อนุญาตให้ลบสาขา) |

*(มีปุ่มทางลัด **Quick Login** ในหน้า Login ให้กดทดสอบได้ทันที)*

---

## 🚀 วิธีการเปิดใช้งาน (How to Run)

### 1. เปิด Next.js Web App (แนะนำและสะดวกที่สุด)
```bash
cd src/node/lab05
npm run dev
```
- เปิดเบราว์เซอร์ไปที่: **[http://localhost:3000](http://localhost:3000)**
- Next.js จะให้บริการทั้ง Frontend UI และ Backend API Route Handlers ในพอร์ตเดียวโดยอัตโนมัติ

### 2. (ทางเลือก) รัน Backend Express แยกพอร์ต 5001
หากต้องการรันเซิร์ฟเวอร์ Express แยกต่างหาก:
```bash
cd src/node/lab05/backend
npm start
```
- API จะทำงานที่: `http://localhost:5001/api`

---

## ✨ คุณสมบัติเด่นของ Lab05 (Key Features)

1. **โครงสร้าง Next.js Client Component (`"use client"`):**
   - ทุกหน้าใช้งาน React Hooks (`useState`, `useEffect`, `useCallback`)
   - ใช้ `useRouter` จาก `next/navigation` สำหรับการนำทางระหว่างหน้า
2. **ระบบจัดการ Routing (`router.js`):**
   - รวมการประกาศ Path ทั้งฝั่ง Frontend และ Backend ในที่เดียว
   - ฝั่ง Backend Express รวม Routes ทั้งหมดผ่าน `backend/router.js`
3. **ระบบความปลอดภัย (JWT Authentication):**
   - รหัสผ่านเข้ารหัสด้วย `bcrypt`
   - มีระบบตรวจสอบสิทธิ์ของ `admin` และ `staff`
4. **การจัดการข้อมูลนักศึกษา (CRUD & Search):**
   - เพิ่ม, แก้ไข, ลบข้อมูลนักศึกษาแบบ Real-time
   - ค้นหาได้ทันทีตาม รหัส, ชื่อ หรือ นามสกุล
   - ตัวกรองแยกตามสาขาวิชา
5. **การจัดการข้อมูลสาขาวิชา (Majors):**
   - แสดงรายชื่อสาขาพร้อมจำนวนนักศึกษาแบบ Dynamic (JOIN `tb_student`)
   - ป้องกันการลบสาขาวิชาหากยังมีนักศึกษาสังกัดอยู่
6. **การออกรายงาน PDF (Thai PDF Report):**
   - ฟอนต์ Prompt สวยงาม รองรับสระวรรณยุกต์ภาษาไทย 100%
   - กรองพิมพ์เฉพาะสาขาที่ต้องการได้
