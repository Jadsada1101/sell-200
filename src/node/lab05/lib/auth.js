import jwt from "jsonwebtoken";

const JWT_SECRET = process.env.JWT_SECRET || "supersecret_lab04_jwt_key_2026";

/**
 * ฟังก์ชันตรวจสอบ JWT Token จาก Request Headers หรือ Query Params
 * @param {Request} request - Next.js Request object
 * @returns {object|null} ข้อมูล User ที่ถอดรหัสแล้ว หรือ null หากไม่ถูกต้อง
 */
export function authenticateRequest(request) {
  try {
    let token = null;

    // 1. อ่านจาก Authorization header: "Bearer <token>"
    const authHeader = request.headers.get("authorization");
    if (authHeader && authHeader.startsWith("Bearer ")) {
      token = authHeader.substring(7).trim();
    }

    // 2. หากไม่มีใน header ลองอ่านจาก query parameter: "?token=<token>"
    if (!token) {
      const url = new URL(request.url);
      token = url.searchParams.get("token");
    }

    if (!token) {
      return null;
    }

    // ตรวจสอบและถอดรหัส Token
    const decoded = jwt.verify(token, JWT_SECRET);
    return decoded;
  } catch (error) {
    console.error("JWT Verification error:", error.message);
    return null;
  }
}

export { JWT_SECRET };
