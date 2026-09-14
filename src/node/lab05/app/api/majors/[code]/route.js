import { NextResponse } from "next/server";
import pool from "@/lib/db";
import { authenticateRequest } from "@/lib/auth";

/**
 * GET /api/majors/:code
 * ดึงข้อมูลสาขาวิชารายสาขา
 */
export async function GET(request, { params }) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  const { code } = await params;

  try {
    const [majors] = await pool.execute(
      "SELECT major_code, major_name FROM tb_major WHERE major_code = ? LIMIT 1",
      [code]
    );

    if (majors.length === 0) {
      return NextResponse.json(
        { success: false, message: "ไม่พบข้อมูลสาขาวิชานี้" },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: majors[0],
    });
  } catch (error) {
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการดึงข้อมูลสาขา" },
      { status: 500 }
    );
  }
}

/**
 * PUT /api/majors/:code
 * แก้ไขข้อมูลสาขาวิชา
 */
export async function PUT(request, { params }) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  // เฉพาะ Admin เท่านั้นที่สามารถแก้ไขสาขาวิชาได้
  if (user.role !== "admin") {
    return NextResponse.json(
      { success: false, message: "เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถแก้ไขสาขาวิชาได้" },
      { status: 403 }
    );
  }

  const { code } = await params;
  const body = await request.json().catch(() => ({}));
  const { major_name } = body;

  if (!major_name) {
    return NextResponse.json(
      { success: false, message: "กรุณากรอกชื่อสาขาวิชา" },
      { status: 400 }
    );
  }

  try {
    const [result] = await pool.execute(
      "UPDATE tb_major SET major_name = ? WHERE major_code = ?",
      [major_name.trim(), code]
    );

    if (result.affectedRows === 0) {
      return NextResponse.json(
        { success: false, message: "ไม่พบสาขาวิชาที่ต้องการแก้ไข" },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: "แก้ไขข้อมูลสาขาวิชาเรียบร้อยแล้ว",
    });
  } catch (error) {
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการแก้ไขสาขาวิชา" },
      { status: 500 }
    );
  }
}

/**
 * DELETE /api/majors/:code
 * ลบสาขาวิชา (ตรวจสอบว่ามีนักศึกษาใช้อยู่หรือไม่)
 */
export async function DELETE(request, { params }) {
  const user = authenticateRequest(request);
  if (!user) {
    return NextResponse.json(
      { success: false, message: "กรุณาเข้าสู่ระบบก่อนใช้งาน" },
      { status: 401 }
    );
  }

  // อนุญาตเฉพาะ Admin ในการลบสาขาวิชา
  if (user.role !== "admin") {
    return NextResponse.json(
      { success: false, message: "เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถลบสาขาวิชาได้" },
      { status: 403 }
    );
  }

  const { code } = await params;

  try {
    // ตรวจสอบว่ามีนักศึกษาผูกกับสาขานี้หรือไม่
    const [students] = await pool.execute(
      "SELECT COUNT(*) AS count FROM tb_student WHERE major_code = ?",
      [code]
    );

    if (students[0].count > 0) {
      return NextResponse.json(
        {
          success: false,
          message: `ไม่สามารถลบสาขานี้ได้ เนื่องจากยังมีนักศึกษาจำนวน ${students[0].count} คน สังกัดอยู่`,
        },
        { status: 400 }
      );
    }

    const [result] = await pool.execute(
      "DELETE FROM tb_major WHERE major_code = ?",
      [code]
    );

    if (result.affectedRows === 0) {
      return NextResponse.json(
        { success: false, message: "ไม่พบสาขาวิชาที่ต้องการลบ" },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: "ลบข้อมูลสาขาวิชาเรียบร้อยแล้ว",
    });
  } catch (error) {
    return NextResponse.json(
      { success: false, message: "เกิดข้อผิดพลาดในการลบสาขาวิชา" },
      { status: 500 }
    );
  }
}
