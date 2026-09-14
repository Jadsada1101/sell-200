/**
 * Automated Test Cases Runner for Lab04
 * ครอบคลุมทุก Scenario ตามข้อกำหนดระบบ:
 * - Scenario 1: Authentication & Authorization
 * - Scenario 2: Major Management (tb_major)
 * - Scenario 3: Student Management (tb_student - 1:M)
 * - Scenario 4: Search & Filtering
 * - Scenario 5: PDF Report Export
 */

const assert = require("assert");

const BASE_URL = process.env.TEST_URL || "http://localhost:5001";
let adminToken = "";
let staffToken = "";

// Helper ส่ง HTTP Request
async function apiRequest(endpoint, options = {}) {
  const url = `${BASE_URL}${endpoint}`;
  const response = await fetch(url, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...(options.headers || {}),
    },
  });

  const contentType = response.headers.get("content-type") || "";
  let body = null;
  if (contentType.includes("application/json")) {
    body = await response.json();
  } else if (contentType.includes("application/pdf")) {
    body = await response.arrayBuffer();
  } else {
    body = await response.text();
  }

  return {
    status: response.status,
    headers: response.headers,
    body,
  };
}

// สีแสดงผลใน Terminal
const green = (text) => `\x1b[32m${text}\x1b[0m`;
const red = (text) => `\x1b[31m${text}\x1b[0m`;
const cyan = (text) => `\x1b[36m${text}\x1b[0m`;

let passedCount = 0;
let failedCount = 0;

async function runTestCase(id, name, testFn) {
  try {
    await testFn();
    console.log(`  ${green("✔")} [${id}] ${name}`);
    passedCount++;
  } catch (error) {
    console.log(`  ${red("✖")} [${id}] ${name}`);
    console.error(`     Error: ${error.message}`);
    failedCount++;
  }
}

async function runAllTests() {
  console.log(cyan("\n========================================================"));
  console.log(cyan("🧪 เริ่มต้นการรัน Test Cases ระบบ Lab04 (Node + MySQL)"));
  console.log(cyan("========================================================\n"));

  // -----------------------------------------------------------
  // SCENARIO 1: Authentication & Authorization
  // -----------------------------------------------------------
  console.log(cyan("📁 Scenario 1: Authentication & Authorization"));

  await runTestCase("TC-AUTH-01", "เข้าสู่ระบบด้วยบัญชี Admin (admin / admin123)", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "admin", password: "admin123" }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.user.role, "admin");
    assert.ok(res.body.token, "ต้องได้รับ JWT Token");
    adminToken = res.body.token;
  });

  await runTestCase("TC-AUTH-02", "เข้าสู่ระบบด้วยบัญชี Staff (staff / staff123)", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "staff", password: "staff123" }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.user.role, "staff");
    assert.ok(res.body.token, "ต้องได้รับ JWT Token");
    staffToken = res.body.token;
  });

  await runTestCase("TC-AUTH-03", "เข้าสู่ระบบด้วยรหัสผ่านไม่ถูกต้อง", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "admin", password: "wrong_password_999" }),
    });
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-04", "เข้าสู่ระบบด้วย Username ที่ไม่มีในระบบ", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "ghost_user", password: "some_password" }),
    });
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-05", "เข้าสู่ระบบโดยไม่กรอกฟิลด์บังคับ", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "", password: "" }),
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-06", "เรียกใช้งาน Protected Endpoint โดยไม่ส่ง Token", async () => {
    const res = await apiRequest("/api/majors");
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-07", "ตรวจสอบข้อมูลผู้ใช้ปัจจุบันจาก Token (GET /api/auth/me)", async () => {
    const res = await apiRequest("/api/auth/me", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.user.username, "admin");
  });

  // -----------------------------------------------------------
  // SCENARIO 2: Major Management (tb_major)
  // -----------------------------------------------------------
  console.log(cyan("\n📁 Scenario 2: Major Management (tb_major)"));

  const testMajorCode = `TEST_${Date.now().toString().slice(-4)}`;

  await runTestCase("TC-MAJ-01", "ดึงรายการสาขาวิชาทั้งหมด พร้อมจำนวนนักศึกษา", async () => {
    const res = await apiRequest("/api/majors", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(Array.isArray(res.body.data), "ข้อมูลต้องเป็น Array");
    assert.ok(res.body.data.length > 0, "ต้องมีสาขาวิชาอย่างน้อย 1 รายการ");
    assert.ok("student_count" in res.body.data[0], "ต้องมีฟิลด์ student_count");
  });

  await runTestCase("TC-MAJ-02", "ดึงข้อมูลสาขาวิชารายตัวตามรหัส (GET /api/majors/CS)", async () => {
    const res = await apiRequest("/api/majors/CS", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.data.major_code, "CS");
    assert.ok(Array.isArray(res.body.data.students), "ต้องมี Array รายชื่อนักศึกษาในสังกัด");
  });

  await runTestCase("TC-MAJ-03", "เพิ่มสาขาวิชาใหม่ (POST /api/majors)", async () => {
    const res = await apiRequest("/api/majors", {
      method: "POST",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        major_code: testMajorCode,
        major_name: "สาขาทดสอบอัตโนมัติ",
        remark: "สร้างโดย Test Suite",
      }),
    });
    assert.strictEqual(res.status, 201);
    assert.strictEqual(res.body.success, true);
  });

  await runTestCase("TC-MAJ-04", "ป้องกันการเพิ่มสาขาวิชารหัสซ้ำ (Duplicate PK)", async () => {
    const res = await apiRequest("/api/majors", {
      method: "POST",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        major_code: testMajorCode,
        major_name: "สาขาซ้ำ",
      }),
    });
    assert.strictEqual(res.status, 409);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-MAJ-05", "แก้ไขข้อมูลสาขาวิชา (PUT /api/majors/:code)", async () => {
    const res = await apiRequest(`/api/majors/${testMajorCode}`, {
      method: "PUT",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        major_name: "สาขาทดสอบ (แก้ไขแล้ว)",
        remark: "ปรับปรุงข้อมูล",
      }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
  });

  await runTestCase("TC-MAJ-06", "ลบสาขาวิชา (DELETE /api/majors/:code)", async () => {
    const res = await apiRequest(`/api/majors/${testMajorCode}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
  });

  // -----------------------------------------------------------
  // SCENARIO 3: Student Management (tb_student - 1:M Relationship)
  // -----------------------------------------------------------
  console.log(cyan("\n📁 Scenario 3: Student Management (tb_student)"));

  const testStudentId = `99999999999-${Math.floor(Math.random() * 9)}`;

  await runTestCase("TC-STU-01", "ดึงรายชื่อนักศึกษาทั้งหมด พร้อม JOIN ชื่อสาขา", async () => {
    const res = await apiRequest("/api/students", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(Array.isArray(res.body.data));
    assert.ok(res.body.data.length > 0);
    assert.ok("major_name" in res.body.data[0], "ต้องมีฟิลด์ชื่อสาขา major_name จากการ JOIN");
  });

  await runTestCase("TC-STU-02", "เพิ่มนักศึกษาใหม่พร้อมผูกกับสาขาวิชา (1:M)", async () => {
    const res = await apiRequest("/api/students", {
      method: "POST",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        StudentID: testStudentId,
        Student_Name: "ทดสอบ",
        Student_Surname: "ระบบงาน",
        Student_Website: "https://test.dev",
        major_code: "CS",
      }),
    });
    assert.strictEqual(res.status, 201);
    assert.strictEqual(res.body.success, true);
  });

  await runTestCase("TC-STU-03", "ป้องกันการเพิ่มรหัสนักศึกษาซ้ำ (Duplicate PK)", async () => {
    const res = await apiRequest("/api/students", {
      method: "POST",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        StudentID: testStudentId,
        Student_Name: "ซ้ำ",
        Student_Surname: "ซ้ำ",
      }),
    });
    assert.strictEqual(res.status, 409);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-STU-04", "ดึงข้อมูลนักศึกษารายคน (GET /api/students/:id)", async () => {
    const res = await apiRequest(`/api/students/${testStudentId}`, {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.data.StudentID, testStudentId);
    assert.strictEqual(res.body.data.major_code, "CS");
  });

  await runTestCase("TC-STU-05", "แก้ไขข้อมูลนักศึกษา (PUT /api/students/:id)", async () => {
    const res = await apiRequest(`/api/students/${testStudentId}`, {
      method: "PUT",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        Student_Name: "ทดสอบ (แก้ไข)",
        Student_Surname: "เรียบร้อย",
        Student_Website: "https://updated.dev",
        major_code: "SE",
      }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
  });

  await runTestCase("TC-STU-06", "ลบข้อมูลนักศึกษา (DELETE /api/students/:id)", async () => {
    const res = await apiRequest(`/api/students/${testStudentId}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
  });

  // -----------------------------------------------------------
  // SCENARIO 4: Search & Filtering
  // -----------------------------------------------------------
  console.log(cyan("\n📁 Scenario 4: Search & Filtering"));

  await runTestCase("TC-SRCH-01", "ค้นหานักศึกษาด้วยชื่อ (Search: 'เจษฎา')", async () => {
    const res = await apiRequest("/api/students?search=เจษฎา", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.ok(res.body.data.length >= 1);
    assert.ok(res.body.data[0].Student_Name.includes("เจษฎา"));
  });

  await runTestCase("TC-SRCH-02", "ค้นหานักศึกษาด้วยรหัส (Search: '67642206024-1')", async () => {
    const res = await apiRequest("/api/students?search=67642206024-1", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.data.length, 1);
    assert.strictEqual(res.body.data[0].StudentID, "67642206024-1");
  });

  await runTestCase("TC-SRCH-03", "กรองนักศึกษาตามสาขาวิชา (Filter: major_code='AI')", async () => {
    const res = await apiRequest("/api/students?major_code=AI", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.count, 5);
    res.body.data.forEach((s) => assert.strictEqual(s.major_code, "AI"));
  });

  await runTestCase("TC-SRCH-04", "ค้นหาและกรองแบบผสม (Search: 'นาย' + Filter: major_code='IT')", async () => {
    const res = await apiRequest("/api/students?search=นาย&major_code=IT", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    res.body.data.forEach((s) => {
      assert.strictEqual(s.major_code, "IT");
      assert.ok(s.Student_Name.includes("นาย"));
    });
  });

  await runTestCase("TC-SRCH-05", "ค้นหาด้วยคำที่ไม่มีในระบบ ผลลัพธ์ต้องว่าง", async () => {
    const res = await apiRequest("/api/students?search=XYZ_NON_EXISTING_KEYWORD", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.count, 0);
    assert.strictEqual(res.body.data.length, 0);
  });

  // -----------------------------------------------------------
  // SCENARIO 5: PDF Report Generation
  // -----------------------------------------------------------
  console.log(cyan("\n📁 Scenario 5: PDF Report Export"));

  await runTestCase("TC-REP-01", "สร้างรายงาน PDF นักศึกษาทั้งหมด (Content-Type: application/pdf)", async () => {
    const res = await apiRequest("/api/reports/students-pdf", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.ok(res.headers.get("content-type").includes("application/pdf"));
    assert.ok(res.body.byteLength > 1000, "ขนาดไฟล์ PDF ต้องสมบูรณ์ (> 1KB)");
  });

  await runTestCase("TC-REP-02", "สร้างรายงาน PDF เฉพาะสาขา (major_code='SE')", async () => {
    const res = await apiRequest("/api/reports/students-pdf?major_code=SE", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.ok(res.headers.get("content-type").includes("application/pdf"));
  });

  await runTestCase("TC-REP-03", "ดาวน์โหลด PDF ผ่าน Query Token (?token=...)", async () => {
    const res = await apiRequest(`/api/reports/students-pdf?token=${adminToken}`);
    assert.strictEqual(res.status, 200);
    assert.ok(res.headers.get("content-type").includes("application/pdf"));
  });

  // -----------------------------------------------------------
  // สรุปผลการทดสอบ
  // -----------------------------------------------------------
  console.log(cyan("\n========================================================"));
  console.log(`📊 สรุปผลการทดสอบ: ${green(`ผ่าน ${passedCount} รายการ`)} | ${failedCount > 0 ? red(`ไม่ผ่าน ${failedCount} รายการ`) : "ไม่ผ่าน 0 รายการ"}`);
  console.log(cyan("========================================================\n"));

  if (failedCount > 0) {
    process.exit(1);
  } else {
    process.exit(0);
  }
}

// ตรวจสอบว่าเซิร์ฟเวอร์รันอยู่หรือไม่ก่อนเริ่ม
apiRequest("/api/health")
  .then(() => runAllTests())
  .catch((err) => {
    console.error(red("❌ เซิร์ฟเวอร์ Backend ยังไม่ทำงาน กรุณารัน `npm start` ก่อน"));
    process.exit(1);
  });
