# เอกสารแบบทดสอบระบบ (Test Cases Specification)
## Lab04: ระบบบริหารจัดการข้อมูลนักศึกษาและสาขาวิชา (Node Express + MySQL)

เอกสารนี้รวบรวม **Test Cases** ทั้งหมดของระบบ โดยครอบคลุมทุก Scenario ตามข้อกำหนด ทั้งการทดสอบระดับ **API (Automated Tests)** และการทดสอบระดับ **UI/UX (Manual Tests)**

---

### สรุปผลการทดสอบรวม (Test Summary)

| กลุ่ม Scenario | จำนวน Test Cases | สถานะ |
|---|:---:|:---:|
| **Scenario 1: Authentication & Authorization** | 7 เคส |  Passed (100%) |
| **Scenario 2: การจัดการสาขาวิชา (`tb_major`)** | 6 เคส |  Passed (100%) |
| **Scenario 3: การจัดการนักศึกษา (`tb_student` 1:M)** | 6 เคส |  Passed (100%) |
| **Scenario 4: ระบบค้นหาและตัวกรอง (Search & Filter)** | 5 เคส |  Passed (100%) |
| **Scenario 5: การออกรายงาน PDF (Report PDF)** | 3 เคส |  Passed (100%) |
| **Scenario 6: การทำงานของ UI & Popup Modal** | 5 เคส |  Passed (100%) |
| **รวมทั้งสิ้น** | **32 เคส** | ** ผ่านทั้งหมด (32/32)** |

---

## Scenario 1: ระบบยืนยันตัวตนและการจัดการสิทธิ์ (Authentication & Authorization)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-AUTH-01** | เข้าสู่ระบบสำเร็จด้วยสิทธิ์ Admin | `username: "admin"`<br>`password: "admin123"` | HTTP 200, ได้รับ JWT Token, `user.role = 'admin'` | Passed |
| **TC-AUTH-02** | เข้าสู่ระบบสำเร็จด้วยสิทธิ์ Staff | `username: "staff"`<br>`password: "staff123"` | HTTP 200, ได้รับ JWT Token, `user.role = 'staff'` | Passed |
| **TC-AUTH-03** | เข้าสู่ระบบด้วยรหัสผ่านไม่ถูกต้อง | `username: "admin"`<br>`password: "wrong_pass"` | HTTP 401 Unauthorized, แจ้งเตือนรหัสผ่านไม่ถูกต้อง | Passed |
| **TC-AUTH-04** | เข้าสู่ระบบด้วยชื่อผู้ใช้ที่ไม่มีในระบบ | `username: "unknown_user"`<br>`password: "1234"` | HTTP 401 Unauthorized | Passed |
| **TC-AUTH-05** | ส่งข้อมูลล็อกอินโดยเว้นว่างฟิลด์บังคับ | `username: ""` หรือ `password: ""` | HTTP 400 Bad Request, แจ้งเตือนให้กรอกข้อมูลให้ครบ | Passed |
| **TC-AUTH-06** | เข้าถึง Protected Endpoint โดยไม่มี Token | `GET /api/majors`<br>(No Authorization Header) | HTTP 401 Unauthorized, ปฏิเสธการเข้าถึง | Passed |
| **TC-AUTH-07** | ตรวจสอบข้อมูลผู้ใช้ปัจจุบันจาก Token | `GET /api/auth/me`<br>(Bearer Valid Token) | HTTP 200, ส่งคืนข้อมูลผู้ใช้และ role ปัจจุบัน | Passed |

---

## Scenario 2: การจัดการสาขาวิชา (Major Management - `tb_major`)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-MAJ-01** | ดึงรายการสาขาวิชาทั้งหมดในระบบ | `GET /api/majors` | HTTP 200, Array สาขาวิชาทั้งหมด พร้อมจำนวนนักศึกษา (`student_count`) | Passed |
| **TC-MAJ-02** | ดึงข้อมูลสาขาวิชารายตัวตามรหัส | `GET /api/majors/CS` | HTTP 200, ข้อมูลสาขา CS พร้อม Array รายชื่อนักศึกษาในสังกัด | Passed |
| **TC-MAJ-03** | เพิ่มสาขาวิชาใหม่ข้อมูลถูกต้อง | `major_code: "TEST_AI"`<br>`major_name: "สาขาทดสอบ"` | HTTP 201 Created, เพิ่มข้อมูลลงในฐานข้อมูลสำเร็จ | Passed |
| **TC-MAJ-04** | ป้องกันการเพิ่มสาขาวิชารหัสซ้ำ (PK) | `major_code: "CS"` (มีอยู่แล้ว) | HTTP 409 Conflict, แจ้งเตือนรหัสสาขาวิชานี้มีอยู่แล้ว | Passed |
| **TC-MAJ-05** | แก้ไขข้อมูลสาขาวิชา | `PUT /api/majors/:code`<br>`major_name: "ชื่อใหม่"` | HTTP 200 OK, ข้อมูลได้รับการอัปเดตในฐานข้อมูล | Passed |
| **TC-MAJ-06** | ลบสาขาวิชา | `DELETE /api/majors/:code` | HTTP 200 OK, ลบข้อมูลสำเร็จ | Passed |

---

## Scenario 3: การจัดการข้อมูลนักศึกษา (Student Management - `tb_student` 1:M)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-STU-01** | ดึงรายชื่อนักศึกษาทั้งหมด พร้อม JOIN สาขา | `GET /api/students` | HTTP 200, ได้รายชื่อนักศึกษาพร้อม `major_name` จาก `tb_major` | Passed |
| **TC-STU-02** | เพิ่มนักศึกษาใหม่พร้อมผูกสาขาวิชา (1:M) | `StudentID: "68642206999-9"`<br>`Name: "สมชาย"`, `major_code: "CS"` | HTTP 201 Created, บันทึกความสัมพันธ์ 1:M ถูกต้อง | Passed |
| **TC-STU-03** | ป้องกันการเพิ่มรหัสนักศึกษาซ้ำ (Duplicate PK) | `StudentID: "67642206024-1"` (มีอยู่แล้ว) | HTTP 409 Conflict, ไม่อนุญาตให้บันทึกซ้ำ | Passed |
| **TC-STU-04** | ดึงข้อมูลนักศึกษารายคนตามรหัส | `GET /api/students/:id` | HTTP 200, ส่งคืนข้อมูลนักศึกษาตรงตาม ID | Passed |
| **TC-STU-05** | แก้ไขข้อมูลนักศึกษา | `PUT /api/students/:id`<br>`Student_Name: "ชื่อใหม่"` | HTTP 200 OK, ข้อมูลได้รับการปรับปรุงเรียบร้อย | Passed |
| **TC-STU-06** | ลบข้อมูลนักศึกษา | `DELETE /api/students/:id` | HTTP 200 OK, ข้อมูลถูกลบออกจากระบบ | Passed |

---

## Scenario 4: ระบบค้นหาและตัวกรองข้อมูล (Search & Filtering)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-SRCH-01** | ค้นหานักศึกษาด้วยชื่อจริงหรือนามสกุล | `?search=เจษฎา` | HTTP 200, พบนักศึกษา `68642206017-4 นายเจษฎา หมอยา` | Passed |
| **TC-SRCH-02** | ค้นหานักศึกษาด้วยรหัสนักศึกษา | `?search=67642206024-1` | HTTP 200, พบข้อมูลนักศึกษารหัสดังกล่าว 1 รายการ | Passed |
| **TC-SRCH-03** | กรองนักศึกษาตามสาขาวิชา (`major_code`) | `?major_code=AI` | HTTP 200, count = 5, ทุกแถวมี `major_code = 'AI'` | Passed |
| **TC-SRCH-04** | ค้นหาและกรองแบบผสม (Search + Filter) | `?search=นาย&major_code=IT` | HTTP 200, เฉพาะนักศึกษาชายในสาขา IT | Passed |
| **TC-SRCH-05** | ค้นหาด้วยคำค้นที่ไม่มีในฐานข้อมูล | `?search=NON_EXISTENT_KEY` | HTTP 200, `count: 0`, `data: []` | Passed |

---

## Scenario 5: การออกรายงาน PDF (Report PDF Generation)

| Test Case ID | วัตถุประสงค์ / เงื่อนไข | ข้อมูลนำเข้า (Input Data) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-REP-01** | ออกรายงาน PDF รายชื่อนักศึกษาทั้งหมด | `GET /api/reports/students-pdf` | HTTP 200, `Content-Type: application/pdf`, ไฟล์สมบูรณ์เปิดอ่านได้ | Passed |
| **TC-REP-02** | ออกรายงาน PDF เฉพาะสาขาวิชาที่เลือก | `GET /api/reports/students-pdf?major_code=SE` | HTTP 200, `Content-Type: application/pdf`, ข้อมูลเฉพาะสาขา SE | Passed |
| **TC-REP-03** | รองรับการดาวน์โหลดตรงผ่าน Browser Tab | `GET /api/reports/students-pdf?token={JWT}` | HTTP 200, Browser สามารถเปิดพรีวิวหรือดาวน์โหลด PDF ได้ทันที | Passed |

---

## Scenario 6: การทำงานของหน้าเว็บ UI & Popup Modal (User Experience)

| Test Case ID | วัตถุประสงค์ / การกระทำของผู้ใช้ | ขั้นตอนการทดสอบ (Steps) | ผลลัพธ์ที่คาดหวัง (Expected Result) | สถานะ |
|---|---|---|---|:---:|
| **TC-UI-01** | ปุ่มทดสอบเข้าสู่ระบบด่วน (Quick Login) | คลิกปุ่ม "Admin" หรือ "Staff" ที่หน้า Login | กรอก username/password ให้อัตโนมัติและเข้าสู่หน้า Dashboard ทันที | Passed |
| **TC-UI-02** | สลับหน้าแท็บ (Segmented Control Tabs) | คลิกสลับระหว่างแท็บ "นักศึกษา" และ "สาขาวิชา" | เปลี่ยนการแสดงผลและหัวข้อหน้าเว็บได้ลื่นไหล ไม่รีโหลดทั้งหน้า | Passed |
| **TC-UI-03** | เปิด Popup Modal เมื่อคลิกที่สาขาวิชา | คลิกที่ป้ายสาขาในแถวนักศึกษา เช่น `CS · วิทยาการคอมพิวเตอร์` | หน้าต่าง **Popup Modal** เด้งขึ้นมากลางจอ แสดงรหัส, ชื่อ, คำอธิบายหลักสูตร และรายชื่อนักศึกษาในสาขานั้นทันที | Passed |
| **TC-UI-04** | กรองข้อมูลนักศึกษาจาก Popup Modal | ใน Popup Modal คลิกปุ่ม "กรองดูนักศึกษาสาขานี้ในตาราง" | Modal ปิดลง และตารางหลักจะฟิลเตอร์แสดงเฉพาะนักศึกษาสาขานั้นทันที | Passed |
| **TC-UI-05** | ออกจากระบบ (Logout) | คลิกปุ่ม "ออกจากระบบ" (รูปประตูทางออก) ที่มุมขวาบน | ลบ Token ใน LocalStorage และนำผู้ใช้กลับไปยังหน้าเข้าสู่ระบบอย่างปลอดภัย | Passed |

---

## วิธีการรันชุดทดสอบอัตโนมัติ (How to Run Automated Tests)

เปิด Terminal และรันคำสั่ง:
```bash
cd src/node/lab04/backend
npm test
```
ผลลัพธ์จะรันตรวจสอบทั้ง 27 Automated Test Cases ครอบคลุมทุกสถานการณ์และแสดงผลลัพธ์แบบเรียลไทม์
