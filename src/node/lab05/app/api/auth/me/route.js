import { NextResponse } from "next/server";
import { authenticateRequest } from "@/lib/auth";

/**
 * GET /api/auth/me
 * ดึงข้อมูลผู้ใช้ปัจจุบันจาก JWT Token
 */
export async function GET(request) {
  const user = authenticateRequest(request);

  if (!user) {
    return NextResponse.json(
      {
        success: false,
        message: "กรุณาเข้าสู่ระบบก่อนใช้งาน (Token ไม่ถูกต้องหรือหมดอายุ)",
      },
      { status: 401 }
    );
  }

  return NextResponse.json({
    success: true,
    user,
  });
}
