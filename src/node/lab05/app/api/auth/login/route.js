import { NextResponse } from "next/server";
import bcrypt from "bcryptjs";
import jwt from "jsonwebtoken";
import pool from "@/lib/db";
import { JWT_SECRET } from "@/lib/auth";

/**
 * POST /api/auth/login
 * รับ username และ password เพื่อเข้าสู่ระบบ และสร้าง JWT Token
 */
export async function POST(request) {
  try {
    const body = await request.json().catch(() => ({}));
    const { username, password } = body;

    if (!username || !password) {
      return NextResponse.json(
        {
          success: false,
          message: "กรุณากรอกชื่อผู้ใช้และรหัสผ่านให้ครบถ้วน",
        },
        { status: 400 }
      );
    }

    // ค้นหาผู้ใช้จากตาราง tb_users
    const [users] = await pool.execute(
      "SELECT user_id, username, password, fullname, role FROM tb_users WHERE username = ? LIMIT 1",
      [username.trim()]
    );

    if (users.length === 0) {
      return NextResponse.json(
        {
          success: false,
          message: "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง",
        },
        { status: 401 }
      );
    }

    const user = users[0];

    // แปลง prefix $2y$ (PHP format) เป็น $2a$ สำหรับ bcryptjs
    const storedHash = user.password.replace(/^\$2y\$/, "$2a$");
    const isPasswordValid = bcrypt.compareSync(password, storedHash);

    if (!isPasswordValid) {
      return NextResponse.json(
        {
          success: false,
          message: "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง",
        },
        { status: 401 }
      );
    }

    // สร้าง JWT Token อายุ 1 วัน
    const token = jwt.sign(
      {
        user_id: user.user_id,
        username: user.username,
        fullname: user.fullname,
        role: user.role,
      },
      JWT_SECRET,
      { expiresIn: "1d" }
    );

    return NextResponse.json({
      success: true,
      message: "เข้าสู่ระบบสำเร็จ",
      token,
      user: {
        user_id: user.user_id,
        username: user.username,
        fullname: user.fullname,
        role: user.role,
      },
    });
  } catch (error) {
    console.error("Login API Error:", error);
    return NextResponse.json(
      {
        success: false,
        message: "เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์",
        error: error.message,
      },
      { status: 500 }
    );
  }
}
