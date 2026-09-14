/**
 * Automated Test Runner for Lab05 (Next.js + MySQL)
 * ทดสอบครอบคลุมทุก Scenario ตามข้อกำหนด:
 * - Scenario 1: Authentication & Authorization (Admin & Staff)
 * - Scenario 2: Student Management (tb_student CRUD 1:M)
 * - Scenario 3: Search & Filtering (Name, ID, Major)
 * - Scenario 4: Major Management & Role Permissions (Admin vs Staff)
 * - Scenario 5: PDF Report Export (Thai Prompt Font)
 */

const assert = require("assert");

const BASE_URL = process.env.TEST_URL || "http://localhost:3000";
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

const green = (text) => `\x1b[32m${text}\x1b[0m`;
const red = (text) => `\x1b[31m${text}\x1b[0m`;
const cyan = (text) => `\x1b[36m${text}\x1b[0m`;
const bold = (text) => `\x1b[1m${text}\x1b[0m`;

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

async function main() {
  console.log(`\n${bold(cyan("==============================================================="))}`);
  console.log(`${bold(cyan("  🧪 Lab05 Test Cases Suite (Next.js Architecture)"))}`);
  console.log(`  Target: ${BASE_URL}`);
  console.log(`${bold(cyan("==============================================================="))}\n`);

  // ==============================================================
  // Scenario 1: Authentication & Authorization
  // ==============================================================
  console.log(bold("\n📂 Scenario 1: Authentication & Authorization"));

  await runTestCase("TC-AUTH-01", "เข้าสู่ระบบสำเร็จด้วยสิทธิ์ Admin", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "admin", password: "admin123" }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.user.role, "admin");
    assert.ok(res.body.token);
    adminToken = res.body.token;
  });

  await runTestCase("TC-AUTH-02", "เข้าสู่ระบบสำเร็จด้วยสิทธิ์ Staff", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "staff", password: "staff123" }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.user.role, "staff");
    assert.ok(res.body.token);
    staffToken = res.body.token;
  });

  await runTestCase("TC-AUTH-03", "ปฏิเสธการเข้าสู่ระบบเมื่อรหัสผ่านไม่ถูกต้อง (401)", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "admin", password: "wrongpassword" }),
    });
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-04", "ปฏิเสธเมื่อชื่อผู้ใช้ไม่มีในระบบ (401)", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "not_exists", password: "123" }),
    });
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-05", "ตรวจสอบฟิลด์ว่างในข้อมูลล็อกอิน (400)", async () => {
    const res = await apiRequest("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({ username: "", password: "" }),
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-06", "ปฏิเสธ Protected Endpoint หากไม่มี Token (401)", async () => {
    const res = await apiRequest("/api/students");
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-AUTH-07", "ดึงข้อมูลผู้ใช้ปัจจุบันจาก Token (/api/auth/me)", async () => {
    const res = await apiRequest("/api/auth/me", {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.user.username, "admin");
  });

  // ==============================================================
  // Scenario 2: Student Management (tb_student - 1:M)
  // ==============================================================
  console.log(bold("\n📂 Scenario 2: Student Management (tb_student 1:M)"));

  const testStudentId = "68999999999-9";

  await runTestCase("TC-STU-01", "ดึงรายชื่อนักศึกษาทั้งหมด พร้อม JOIN tb_major", async () => {
    const res = await apiRequest("/api/students", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(Array.isArray(res.body.data));
    assert.ok(res.body.data.length > 0);
    assert.ok("major_name" in res.body.data[0]);
  });

  await runTestCase("TC-STU-02", "เพิ่มข้อมูลนักศึกษาใหม่ (รองรับทั้ง Staff และ Admin)", async () => {
    // ลบตัวเก่าออกก่อนหากมีค้างอยู่
    await apiRequest(`/api/students/${testStudentId}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${staffToken}` },
    });

    const res = await apiRequest("/api/students", {
      method: "POST",
      headers: { Authorization: `Bearer ${staffToken}` },
      body: JSON.stringify({
        StudentID: testStudentId,
        Student_Name: "ทดสอบชื่อ",
        Student_Surname: "ทดสอบสกุล",
        Student_Website: "https://teststudent.org",
        major_code: "CS",
      }),
    });
    assert.strictEqual(res.status, 201);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.data.StudentID, testStudentId);
  });

  await runTestCase("TC-STU-03", "ป้องกันการเพิ่มรหัสนักศึกษาซ้ำ (Duplicate PK 409)", async () => {
    const res = await apiRequest("/api/students", {
      method: "POST",
      headers: { Authorization: `Bearer ${staffToken}` },
      body: JSON.stringify({
        StudentID: testStudentId,
        Student_Name: "ชื่อซ้ำ",
        Student_Surname: "สกุลซ้ำ",
        major_code: "CS",
      }),
    });
    assert.strictEqual(res.status, 409);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-STU-04", "ดึงข้อมูลนักศึกษารายคนตาม StudentID", async () => {
    const res = await apiRequest(`/api/students/${testStudentId}`, {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.data.StudentID, testStudentId);
    assert.strictEqual(res.body.data.Student_Name, "ทดสอบชื่อ");
  });

  await runTestCase("TC-STU-05", "แก้ไขข้อมูลนักศึกษา (Update)", async () => {
    const res = await apiRequest(`/api/students/${testStudentId}`, {
      method: "PUT",
      headers: { Authorization: `Bearer ${staffToken}` },
      body: JSON.stringify({
        Student_Name: "ทดสอบแก้ไขแล้ว",
        Student_Surname: "สกุลแก้ไข",
        major_code: "IT",
        Student_Website: "https://updated.org",
      }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);

    const verify = await apiRequest(`/api/students/${testStudentId}`, {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(verify.body.data.Student_Name, "ทดสอบแก้ไขแล้ว");
    assert.strictEqual(verify.body.data.major_code, "IT");
  });

  await runTestCase("TC-STU-06", "ลบข้อมูลนักศึกษา (Delete)", async () => {
    const res = await apiRequest(`/api/students/${testStudentId}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);

    const check = await apiRequest(`/api/students/${testStudentId}`, {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(check.status, 404);
  });

  // ==============================================================
  // Scenario 3: Search & Filtering
  // ==============================================================
  console.log(bold("\n📂 Scenario 3: Search & Filtering"));

  await runTestCase("TC-SRCH-01", "ค้นหานักศึกษาด้วยชื่อจริงภาษาไทย (?search=เจษฎา)", async () => {
    const res = await apiRequest("/api/students?search=" + encodeURIComponent("เจษฎา"), {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(res.body.data.length >= 1);
    assert.ok(res.body.data[0].Student_Name.includes("เจษฎา"));
  });

  await runTestCase("TC-SRCH-02", "ค้นหาด้วยรหัสนักศึกษา (?search=68642206017-4)", async () => {
    const res = await apiRequest("/api/students?search=68642206017-4", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.data.length, 1);
    assert.strictEqual(res.body.data[0].StudentID, "68642206017-4");
  });

  await runTestCase("TC-SRCH-03", "กรองตามสาขาวิชา (?major_code=AI)", async () => {
    const res = await apiRequest("/api/students?major_code=AI", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.ok(res.body.data.length > 0);
    res.body.data.forEach((s) => assert.strictEqual(s.major_code, "AI"));
  });

  await runTestCase("TC-SRCH-04", "ค้นหาแบบคำค้นไม่มีในระบบ (Empty Result)", async () => {
    const res = await apiRequest("/api/students?search=XYZ_UNKNOWN_NOT_FOUND", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.data.length, 0);
  });

  // ==============================================================
  // Scenario 4: Major Management & Role-based Access Control
  // ==============================================================
  console.log(bold("\n📂 Scenario 4: Major Management & Role Permissions (Admin vs Staff)"));

  const testMajorCode = "TEST_MAJOR";

  await runTestCase("TC-MAJ-01", "Staff และ Admin สามารถดูรายการสาขาวิชาได้ (Read-only for Staff)", async () => {
    const res = await apiRequest("/api/majors", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(Array.isArray(res.body.data));
    assert.ok("student_count" in res.body.data[0]);
  });

  await runTestCase("TC-MAJ-02", "Staff ไม่สามารถเพิ่มสาขาวิชาได้ (403 Forbidden)", async () => {
    const res = await apiRequest("/api/majors", {
      method: "POST",
      headers: { Authorization: `Bearer ${staffToken}` },
      body: JSON.stringify({ major_code: "HACK", major_name: "Staff Try" }),
    });
    assert.strictEqual(res.status, 403);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-MAJ-03", "Staff ไม่สามารถแก้ไขสาขาวิชาได้ (403 Forbidden)", async () => {
    const res = await apiRequest("/api/majors/CS", {
      method: "PUT",
      headers: { Authorization: `Bearer ${staffToken}` },
      body: JSON.stringify({ major_name: "Staff Hack Name" }),
    });
    assert.strictEqual(res.status, 403);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-MAJ-04", "Staff ไม่สามารถลบสาขาวิชาได้ (403 Forbidden)", async () => {
    const res = await apiRequest("/api/majors/CS", {
      method: "DELETE",
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 403);
    assert.strictEqual(res.body.success, false);
  });

  await runTestCase("TC-MAJ-05", "Admin สามารถเพิ่มสาขาวิชาใหม่ได้ (201 Created)", async () => {
    // ลบตัวเก่าออกก่อนหากมีค้างอยู่
    await apiRequest(`/api/majors/${testMajorCode}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${adminToken}` },
    });

    const res = await apiRequest("/api/majors", {
      method: "POST",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        major_code: testMajorCode,
        major_name: "สาขาทดสอบสิทธิ์ Admin",
      }),
    });
    assert.strictEqual(res.status, 201);
    assert.strictEqual(res.body.success, true);
  });

  await runTestCase("TC-MAJ-06", "Admin สามารถแก้ไขข้อมูลสาขาวิชาได้ (200 OK)", async () => {
    const res = await apiRequest(`/api/majors/${testMajorCode}`, {
      method: "PUT",
      headers: { Authorization: `Bearer ${adminToken}` },
      body: JSON.stringify({
        major_name: "สาขาทดสอบแก้ไขสำเร็จ",
      }),
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
  });

  await runTestCase("TC-MAJ-07", "ป้องกันการลบสาขาวิชาที่มีนักศึกษาสังกัดอยู่ (400 Bad Request)", async () => {
    const res = await apiRequest("/api/majors/CS", {
      method: "DELETE",
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.body.success, false);
    assert.ok(res.body.message.includes("ไม่สามารถลบสาขานี้ได้"));
  });

  await runTestCase("TC-MAJ-08", "Admin สามารถลบสาขาวิชาที่ไม่มีนักศึกษาได้ (200 OK)", async () => {
    const res = await apiRequest(`/api/majors/${testMajorCode}`, {
      method: "DELETE",
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
  });

  // ==============================================================
  // Scenario 5: PDF Report Export
  // ==============================================================
  console.log(bold("\n📂 Scenario 5: PDF Report Export"));

  await runTestCase("TC-REP-01", "สร้างรายงาน PDF นักศึกษาทั้งหมด (Content-Type: application/pdf)", async () => {
    const res = await apiRequest("/api/reports/students-pdf", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    const contentType = res.headers.get("content-type") || "";
    assert.ok(contentType.includes("application/pdf"));
    assert.ok(res.body.byteLength > 1000); // ขนาดไฟล์อย่างน้อย 1KB
  });

  await runTestCase("TC-REP-02", "สร้างรายงาน PDF เฉพาะสาขาที่เลือก (?major_code=SE)", async () => {
    const res = await apiRequest("/api/reports/students-pdf?major_code=SE", {
      headers: { Authorization: `Bearer ${staffToken}` },
    });
    assert.strictEqual(res.status, 200);
    const contentType = res.headers.get("content-type") || "";
    assert.ok(contentType.includes("application/pdf"));
  });

  await runTestCase("TC-REP-03", "ดาวน์โหลด PDF ผ่าน Query Token (?token=...)", async () => {
    const res = await apiRequest(`/api/reports/students-pdf?token=${staffToken}`);
    assert.strictEqual(res.status, 200);
    const contentType = res.headers.get("content-type") || "";
    assert.ok(contentType.includes("application/pdf"));
  });

  await runTestCase("TC-REP-04", "ปฏิเสธการดาวน์โหลด PDF หากไม่มี Token (401)", async () => {
    const res = await apiRequest("/api/reports/students-pdf");
    assert.strictEqual(res.status, 401);
  });

  // ==============================================================
  // Test Summary
  // ==============================================================
  console.log(`\n${bold(cyan("==============================================================="))}`);
  console.log(`  ${bold("📊 Test Results Summary:")}`);
  console.log(`  - Passed: ${green(passedCount)}`);
  console.log(`  - Failed: ${failedCount > 0 ? red(failedCount) : green(0)}`);
  console.log(`  - Total:  ${passedCount + failedCount}`);
  console.log(`${bold(cyan("==============================================================="))}\n`);

  if (failedCount > 0) {
    process.exit(1);
  }
}

main().catch((err) => {
  console.error("Fatal Test Runner Error:", err);
  process.exit(1);
});
