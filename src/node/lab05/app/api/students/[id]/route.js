import { NextResponse } from "next/server";
import pool from "@/lib/db";
import { authenticateRequest } from "@/lib/auth";

/**
 * GET /api/students/:id
 * ดึงข้อมูลนักศึกษารายคน โดย id คือ StudentID
 */
export async function GET(request, { params }) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  const { id } = await params;

  try {
    const [students] = await pool.execute(
      `SELECT s.StudentID, s.Student_Name, s.Student_Surname, s.Student_Website, s.major_code, m.major_name
       FROM tb_student s
       LEFT JOIN tb_major m ON s.major_code = m.major_code
       WHERE s.StudentID = ? LIMIT 1`,
      [id]
    );

    if (students.length === 0) {
      return NextResponse.json(
        { success: false, message: "ไม่พบข้อมูลนักศึกษา" },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: students[0],
    });
  } catch (error) {
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการดึงข้อมูลนักศึกษา" },
      { status: 500 }
    );
  }
}

/**
 * PUT /api/students/:id
 * แก้ไขข้อมูลนักศึกษา โดย id คือ StudentID
 */
export async function PUT(request, { params }) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  const { id } = await params;
  const body = await request.json().catch(() => ({}));
  const studentName = (body.Student_Name || body.first_name || "").trim();
  const studentSurname = (body.Student_Surname || body.last_name || "").trim();
  const studentWebsite = (body.Student_Website || body.website || "").trim();
  const majorCode = (body.major_code || "").trim();

  if (!studentName || !studentSurname) {
    return NextResponse.json(
      { success: false, message: "กรุณากรอกชื่อและนามสกุลให้ครบถ้วน" },
      { status: 400 }
    );
  }

  try {
    const [result] = await pool.execute(
      `UPDATE tb_student 
       SET Student_Name = ?, Student_Surname = ?, Student_Website = ?, major_code = ?
       WHERE StudentID = ?`,
      [studentName, studentSurname, studentWebsite || null, majorCode || null, id]
    );

    if (result.affectedRows === 0) {
      return NextResponse.json(
        { success: false, message: "ไม่พบนักศึกษาที่ต้องการแก้ไข" },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: "แก้ไขข้อมูลนักศึกษาเรียบร้อยแล้ว",
    });
  } catch (error) {
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการแก้ไขข้อมูลนักศึกษา" },
      { status: 500 }
    );
  }
}

/**
 * DELETE /api/students/:id
 * ลบข้อมูลนักศึกษา โดย id คือ StudentID
 */
export async function DELETE(request, { params }) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  const { id } = await params;

  try {
    const [result] = await pool.execute("DELETE FROM tb_student WHERE StudentID = ?", [id]);

    if (result.affectedRows === 0) {
      return NextResponse.json(
        { success: false, message: "ไม่พบข้อมูลนักศึกษาที่ต้องการลบ" },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: "ลบข้อมูลนักศึกษาเรียบร้อยแล้ว",
    });
  } catch (error) {
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการลบข้อมูลนักศึกษา" },
      { status: 500 }
    );
  }
}
