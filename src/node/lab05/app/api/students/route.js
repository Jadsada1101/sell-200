import { NextResponse } from "next/server";
import pool from "@/lib/db";
import { authenticateRequest } from "@/lib/auth";

/**
 * GET /api/students
 * ดึงรายการนักศึกษา พร้อมรองรับ ?search= และ ?major_code=
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
    const { searchParams } = new URL(request.url);
    const search = searchParams.get("search") || "";
    const majorCode = searchParams.get("major_code") || "";

    let query = `
      SELECT 
        s.StudentID,
        s.Student_Name,
        s.Student_Surname,
        s.Student_Website,
        s.major_code,
        COALESCE(m.major_name, 'ไม่ระบุ') AS major_name
      FROM tb_student s
      LEFT JOIN tb_major m ON s.major_code = m.major_code
      WHERE 1=1
    `;

    const params = [];

    // กรองตามสาขาวิชา
    if (majorCode.trim() !== "") {
      query += " AND s.major_code = ?";
      params.push(majorCode.trim());
    }

    // ค้นหาตามรหัสนักศึกษา, ชื่อ หรือนามสกุล
    if (search.trim() !== "") {
      const searchTerm = `%${search.trim()}%`;
      query += ` AND (
        s.StudentID LIKE ? 
        OR s.Student_Name LIKE ? 
        OR s.Student_Surname LIKE ?
      )`;
      params.push(searchTerm, searchTerm, searchTerm);
    }

    query += " ORDER BY s.StudentID ASC";

    const [students] = await pool.query(query, params);

    return NextResponse.json({
      success: true,
      count: students.length,
      data: students,
    });
  } catch (error) {
    console.error("GET Students Error:", error);
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการดึงข้อมูลนักศึกษา" },
      { status: 500 }
    );
  }
}

/**
 * POST /api/students
 * เพิ่มข้อมูลนักศึกษาใหม่
 */
export async function POST(request) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  try {
    const body = await request.json().catch(() => ({}));
    const studentId = (body.StudentID || body.student_code || "").trim();
    const studentName = (body.Student_Name || body.first_name || "").trim();
    const studentSurname = (body.Student_Surname || body.last_name || "").trim();
    const studentWebsite = (body.Student_Website || body.website || "").trim();
    const majorCode = (body.major_code || "").trim();

    if (!studentId || !studentName || !studentSurname) {
      return NextResponse.json(
        { success: false, message: "กรุณาระบุรหัสนักศึกษา ชื่อ และนามสกุล" },
        { status: 400 }
      );
    }

    // ตรวจสอบว่ารหัสนักศึกษาซ้ำหรือไม่
    const [existing] = await pool.execute(
      "SELECT StudentID FROM tb_student WHERE StudentID = ? LIMIT 1",
      [studentId]
    );

    if (existing.length > 0) {
      return NextResponse.json(
        { success: false, message: `รหัสนักศึกษา ${studentId} นี้มีอยู่ในระบบแล้ว` },
        { status: 409 }
      );
    }

    // ตรวจสอบว่า major_code ถูกต้องถ้ามีการระบุ
    if (majorCode) {
      const [majorExists] = await pool.execute(
        "SELECT major_code FROM tb_major WHERE major_code = ? LIMIT 1",
        [majorCode]
      );
      if (majorExists.length === 0) {
        return NextResponse.json(
          { success: false, message: "ไม่พบรหัสสาขาวิชาที่เลือกในระบบ" },
          { status: 400 }
        );
      }
    }

    await pool.execute(
      `INSERT INTO tb_student (StudentID, Student_Name, Student_Surname, Student_Website, major_code)
       VALUES (?, ?, ?, ?, ?)`,
      [studentId, studentName, studentSurname, studentWebsite || null, majorCode || null]
    );

    return NextResponse.json(
      {
        success: true,
        message: "เพิ่มข้อมูลนักศึกษาเรียบร้อยแล้ว",
        data: {
          StudentID: studentId,
          Student_Name: studentName,
          Student_Surname: studentSurname,
          Student_Website: studentWebsite,
          major_code: majorCode,
        },
      },
      { status: 201 }
    );
  } catch (error) {
    console.error("Create Student Error:", error);
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการเพิ่มข้อมูลนักศึกษา" },
      { status: 500 }
    );
  }
}
