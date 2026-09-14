import { NextResponse } from "next/server";
import pool from "@/lib/db";
import { authenticateRequest } from "@/lib/auth";

/**
 * GET /api/majors
 * ดึงรายการสาขาวิชาทั้งหมด พร้อมนับจำนวนนักศึกษาในแต่ละสาขา
 */
export async function GET(request) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  try {
    const query = `
      SELECT 
        m.major_code,
        m.major_name,
        m.remark,
        COUNT(s.StudentID) AS student_count
      FROM tb_major m
      LEFT JOIN tb_student s ON m.major_code = s.major_code
      GROUP BY m.major_code, m.major_name, m.remark
      ORDER BY m.major_code ASC
    `;

    const [majors] = await pool.query(query);

    return NextResponse.json({
      success: true,
      data: majors,
    });
  } catch (error) {
    console.error("GET Majors Error:", error);
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการดึงข้อมูลสาขาวิชา" },
      { status: 500 }
    );
  }
}

/**
 * POST /api/majors
 * เพิ่มสาขาวิชาใหม่
 */
export async function POST(request) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  // เฉพาะ Admin เท่านั้นที่สามารถเพิ่มสาขาวิชาได้
  if (user.role !== "admin") {
    return NextResponse.json(
      { success: false, message: "เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถเพิ่มสาขาวิชาได้" },
      { status: 403 }
    );
  }

  try {
    const body = await request.json().catch(() => ({}));
    const { major_code, major_name } = body;

    if (!major_code || !major_name) {
      return NextResponse.json(
        { success: false, message: "กรุณากรอกรหัสสาขาและชื่อสาขาให้ครบถ้วน" },
        { status: 400 }
      );
    }

    // ตรวจสอบว่ารหัสสาขาซ้ำหรือไม่
    const [existing] = await pool.execute(
      "SELECT major_code FROM tb_major WHERE major_code = ? LIMIT 1",
      [major_code.trim()]
    );

    if (existing.length > 0) {
      return NextResponse.json(
        { success: false, message: "รหัสสาขาวิชานี้มีอยู่ในระบบแล้ว" },
        { status: 409 }
      );
    }

    await pool.execute(
      "INSERT INTO tb_major (major_code, major_name) VALUES (?, ?)",
      [major_code.trim(), major_name.trim()]
    );

    return NextResponse.json(
      {
        success: true,
        message: "เพิ่มข้อมูลสาขาวิชาเรียบร้อยแล้ว",
        data: { major_code: major_code.trim(), major_name: major_name.trim() },
      },
      { status: 201 }
    );
  } catch (error) {
    console.error("Create Major Error:", error);
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการเพิ่มสาขาวิชา" },
      { status: 500 }
    );
  }
}
