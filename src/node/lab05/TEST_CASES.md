# เอกสารชุดทดสอบระบบ (Test Cases Specification)
## Lab05: ระบบบริหารจัดการข้อมูลนักศึกษา (Next.js + use client & router.js)

เอกสารนี้รวบรวม **Test Cases** ทั้งหมดของระบบ Lab05 โดยครอบคลุมทุก Scenario ตามข้อกำหนด ทั้งการทดสอบระดับ **API (Automated Tests)** และการทดสอบระดับ **UI/UX (Manual Tests)**

---

### สรุปผลการทดสอบรวม (Test Summary)

| กลุ่ม Scenario | จำนวน Test Cases | ประเภทการทดสอบ | ผลการทดสอบ |
|---|:---:|:---:|:---:|
| **Scenario 1: Authentication & Authorization** | 7 เคส | Automated API |  ผ่านทั้งหมด (7/7) |
| **Scenario 2: การจัดการนักศึกษา (`tb_student` 1:M)** | 6 เคส | Automated API |  ผ่านทั้งหมด (6/6) |
| **Scenario 3: ระบบค้นหาและตัวกรอง (Search & Filter)** | 4 เคส | Automated API |  ผ่านทั้งหมด (4/4) |
| **Scenario 4: การจัดการสาขาวิชาและสิทธิ์ (`tb_major`)** | 8 เคส | Automated API |  ผ่านทั้งหมด (8/8) |
| **Scenario 5: การออกรายงาน PDF (Report PDF)** | 4 เคส | Automated API |  ผ่านทั้งหมด (4/4) |
| **Scenario 6: การทำงานของ UI & Routing (`use client`)** | 6 เคส | Manual UI/UX |  ผ่านทั้งหมด (6/6) |
| **รวมทั้งสิ้น** | **35 เคส** | **API + UI** | ** ผ่านทั้งหมด (35/35)** |

---

## Scenario 1: ระบบยืนยันตัวตนและการจัดการสิทธิ์ (Authentication & Authorization)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-AUTH-01** | เข้าสู่ระบบสำเร็จด้วยสิทธิ์ Admin | `username: "admin"`<br>`password: "admin123"` | HTTP 200, ได้รับ JWT Token, `user.role = 'admin'` |  Passed |
| **TC-AUTH-02** | เข้าสู่ระบบสำเร็จด้วยสิทธิ์ Staff | `username: "staff"`<br>`password: "staff123"` | HTTP 200, ได้รับ JWT Token, `user.role = 'staff'` |  Passed |
| **TC-AUTH-03** | เข้าสู่ระบบด้วยรหัสผ่านไม่ถูกต้อง | `username: "admin"`<br>`password: "wrong_pass"` | HTTP 401 Unauthorized, แจ้งเตือนรหัสผ่านไม่ถูกต้อง |  Passed |
| **TC-AUTH-04** | เข้าสู่ระบบด้วยชื่อผู้ใช้ที่ไม่มีในระบบ | `username: "unknown_user"`<br>`password: "1234"` | HTTP 401 Unauthorized |  Passed |
| **TC-AUTH-05** | ส่งข้อมูลล็อกอินโดยเว้นว่างฟิลด์บังคับ | `username: ""` หรือ `password: ""` | HTTP 400 Bad Request, แจ้งเตือนให้กรอกข้อมูลให้ครบ |  Passed |
| **TC-AUTH-06** | เข้าถึง Protected Endpoint โดยไม่มี Token | `GET /api/students`<br>(No Authorization Header) | HTTP 401 Unauthorized, ปฏิเสธการเข้าถึง |  Passed |
| **TC-AUTH-07** | ตรวจสอบข้อมูลผู้ใช้ปัจจุบันจาก Token | `GET /api/auth/me`<br>(Bearer Valid Token) | HTTP 200, ส่งคืนข้อมูลผู้ใช้และ role ปัจจุบัน |  Passed |

---

## Scenario 2: การจัดการข้อมูลนักศึกษา (Student Management - `tb_student` 1:M)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-STU-01** | ดึงรายชื่อนักศึกษาทั้งหมด พร้อม JOIN สาขา | `GET /api/students` | HTTP 200, ได้รายชื่อนักศึกษาพร้อม `major_name` จาก `tb_major` |  Passed |
| **TC-STU-02** | เพิ่มนักศึกษาใหม่พร้อมผูกสาขาวิชา (1:M) | `StudentID: "68999999999-9"`<br>`Name: "ทดสอบ"`, `major_code: "CS"` | HTTP 201 Created, บันทึกความสัมพันธ์ 1:M ถูกต้อง |  Passed |
| **TC-STU-03** | ป้องกันการเพิ่มรหัสนักศึกษาซ้ำ (Duplicate PK) | `StudentID: "68642206017-4"` (มีอยู่แล้ว) | HTTP 409 Conflict, ไม่อนุญาตให้บันทึกซ้ำ |  Passed |
| **TC-STU-04** | ดึงข้อมูลนักศึกษารายคนตาม StudentID | `GET /api/students/:id` | HTTP 200, ส่งคืนข้อมูลนักศึกษาตรงตาม ID |  Passed |
| **TC-STU-05** | แก้ไขข้อมูลนักศึกษา | `PUT /api/students/:id`<br>`Student_Name: "ชื่อใหม่"` | HTTP 200 OK, ข้อมูลได้รับการปรับปรุงเรียบร้อย |  Passed |
| **TC-STU-06** | ลบข้อมูลนักศึกษา | `DELETE /api/students/:id` | HTTP 200 OK, ข้อมูลถูกลบออกจากระบบ |  Passed |

---

## Scenario 3: ระบบค้นหาและตัวกรองข้อมูล (Search & Filtering)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-SRCH-01** | ค้นหานักศึกษาด้วยชื่อจริงภาษาไทย | `?search=เจษฎา` | HTTP 200, พบนักศึกษา `68642206017-4 นายเจษฎา หมอยา` |  Passed |
| **TC-SRCH-02** | ค้นหานักศึกษาด้วยรหัสนักศึกษา | `?search=68642206017-4` | HTTP 200, พบข้อมูลนักศึกษารหัสดังกล่าว 1 รายการ |  Passed |
| **TC-SRCH-03** | กรองนักศึกษาตามสาขาวิชา (`major_code`) | `?major_code=AI` | HTTP 200, ทุกแถวที่คืนมามี `major_code = 'AI'` |  Passed |
| **TC-SRCH-04** | ค้นหาด้วยคำค้นที่ไม่มีในฐานข้อมูล | `?search=XYZ_UNKNOWN_NOT_FOUND` | HTTP 200, `count: 0`, `data: []` |  Passed |

---

## Scenario 4: การจัดการสาขาวิชาและสิทธิ์ (Major Management & Role Permissions)

> **เงื่อนไขสำคัญ:** Staff สามารถดูได้อย่างเดียว ห้ามเพิ่ม แก้ไข หรือลบสาขาวิชา

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | สิทธิ์ (Role) | ข้อมูลนำเข้า (Input) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|:---:|---|---|:---:|
| **TC-MAJ-01** | ดูรายการสาขาวิชาและจำนวนนักศึกษา | Staff & Admin | `GET /api/majors` | HTTP 200, แสดงรายชื่อสาขาและ `student_count` |  Passed |
| **TC-MAJ-02** | Staff พยายามเพิ่มสาขาวิชาใหม่ | **Staff** | `POST /api/majors` | **HTTP 403 Forbidden** (ปฏิเสธสิทธิ์) |  Passed |
| **TC-MAJ-03** | Staff พยายามแก้ไขข้อมูลสาขาวิชา | **Staff** | `PUT /api/majors/:code` | **HTTP 403 Forbidden** (ปฏิเสธสิทธิ์) |  Passed |
| **TC-MAJ-04** | Staff พยายามลบสาขาวิชา | **Staff** | `DELETE /api/majors/:code` | **HTTP 403 Forbidden** (ปฏิเสธสิทธิ์) |  Passed |
| **TC-MAJ-05** | Admin เพิ่มสาขาวิชาใหม่ | **Admin** | `POST /api/majors`<br>`code: "TEST_MAJOR"` | HTTP 201 Created, เพิ่มสาขาสำเร็จ |  Passed |
| **TC-MAJ-06** | Admin แก้ไขข้อมูลสาขาวิชา | **Admin** | `PUT /api/majors/:code` | HTTP 200 OK, อัปเดตข้อมูลสำเร็จ |  Passed |
| **TC-MAJ-07** | ป้องกันการลบสาขาที่มีนักศึกษาอยู่ | **Admin** | `DELETE /api/majors/CS` | HTTP 400 Bad Request, แจ้งเตือนยังมีนักศึกษาสังกัดอยู่ |  Passed |
| **TC-MAJ-08** | Admin ลบสาขาวิชาที่ไม่มีนักศึกษา | **Admin** | `DELETE /api/majors/TEST_MAJOR` | HTTP 200 OK, ลบข้อมูลสาขาสำเร็จ |  Passed |

---

## Scenario 5: การออกรายงาน PDF (PDF Report Export)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-REP-01** | ออกรายงาน PDF รายชื่อนักศึกษาทั้งหมด | `GET /api/reports/students-pdf`<br>(Header: Authorization) | HTTP 200, `Content-Type: application/pdf`, ฟอนต์ภาษาไทย Prompt สมบูรณ์ |  Passed |
| **TC-REP-02** | ออกรายงาน PDF เฉพาะสาขาวิชาที่เลือก | `GET /api/reports/students-pdf?major_code=SE` | HTTP 200, `Content-Type: application/pdf`, รายการเฉพาะสาขา SE |  Passed |
| **TC-REP-03** | ดาวน์โหลด PDF ตรงผ่าน Browser Tab | `GET /api/reports/students-pdf?token={JWT}` | HTTP 200, เบราว์เซอร์เปิดพรีวิวหรือเซฟไฟล์ได้ทันที |  Passed |
| **TC-REP-04** | ป้องกันการเข้าถึงรายงานหากไม่มี Token | `GET /api/reports/students-pdf` (No Token) | HTTP 401 Unauthorized |  Passed |

---

## Scenario 6: การทำงานของหน้าเว็บ UI & การจัดสิทธิ์ (Next.js Client & UX)

| Test Case ID | วัตถุประสงค์ / การกระทำของผู้ใช้ | ขั้นตอนการทดสอบ (Steps) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-UI-01** | การนำทางแบบ Client-side Router (`useRouter`) | คลิกเมนูระหว่าง "จัดการนักศึกษา", "สาขาวิชา", "ออกรายงาน" | สลับหน้าได้ทันทีโดยไม่ต้องโหลดหน้าเว็บใหม่ทั้งหน้า (SPA) |  Passed |
| **TC-UI-02** | ปุ่มเข้าสู่ระบบด่วน (Quick Login) | คลิกปุ่ม "Admin" หรือ "Staff" ที่หน้า Login | เข้าสู่ระบบสำเร็จและสลับไปหน้า `/students` อัตโนมัติ |  Passed |
| **TC-UI-03** | การแสดงผลหน้าสาขาวิชาสำหรับ **Staff** | เข้าสู่ระบบด้วย Staff แล้วไปที่หน้า `/majors` | ซ่อนปุ่ม "+ เพิ่มสาขาวิชา", ซ่อนคอลัมน์ "จัดการ" (ไม่มีปุ่มแก้ไข/ลบ) |  Passed |
| **TC-UI-04** | การแสดงผลหน้าสาขาวิชาสำหรับ **Admin** | เข้าสู่ระบบด้วย Admin แล้วไปที่หน้า `/majors` | แสดงปุ่ม "+ เพิ่มสาขาวิชา", แสดงคอลัมน์ "จัดการ" พร้อมปุ่มแก้ไขและลบ |  Passed |
| **TC-UI-05** | กรอบปุ่มและกล่องข้อความ Popup Modal | คลิกปุ่ม "แก้ไข" หรือ "ลบ" ในตารางข้อมูล | ปุ่มมีกรอบชัดเจน (Border) และเปิด Modal ยืนยันการทำงานถูกต้อง |  Passed |
| **TC-UI-06** | ออกจากระบบ (Logout) | คลิกปุ่มมีกรอบ "ออกจากระบบ" บนแถบ Navbar | ล้าง Token ในระบบ และนำกลับมาหน้าแรกอย่างปลอดภัย |  Passed |

---

## วิธีการรันชุดทดสอบอัตโนมัติ (How to Run Automated Tests)

รันคำสั่งในเทอร์มินัล:
```bash
cd src/node/lab05
npm test
```
คำสั่งจะรันชุดทดสอบ API ทั้งหมด 29 ข้อ และแสดงผลลัพธ์ผ่าน (Passed) 100% แบบเรียลไทม์
