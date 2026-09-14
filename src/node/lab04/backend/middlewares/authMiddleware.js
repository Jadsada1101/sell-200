const jwt = require("jsonwebtoken");

const JWT_SECRET = process.env.JWT_SECRET || "supersecret_lab04_jwt_key_2026";

// Middleware ตรวจสอบ JWT Token จาก Authorization Header
function authenticateToken(req, res, next) {
  const authHeader = req.headers["authorization"];
  const token = authHeader && authHeader.split(" ")[1]; // ดึงค่า Bearer <token>

  if (!token) {
    return res.status(401).json({
      success: false,
      message: "ไม่พบ Token กรุณาเข้าสู่ระบบก่อนใช้งาน",
    });
  }

  jwt.verify(token, JWT_SECRET, (err, decodedUser) => {
    if (err) {
      return res.status(403).json({
        success: false,
        message: "Token หมดอายุหรือไม่ถูกต้อง กรุณาเข้าสู่ระบบใหม่",
      });
    }

    req.user = decodedUser;
    next();
  });
}

// Middleware ตรวจสอบสิทธิ์เฉพาะ Admin (ถ้าต้องการ)
function requireAdmin(req, res, next) {
  if (req.user && req.user.role === "admin") {
    next();
  } else {
    return res.status(403).json({
      success: false,
      message: "การกระทำนี้อนุญาตเฉพาะผู้ดูแลระบบ (Admin) เท่านั้น",
    });
  }
}

module.exports = {
  authenticateToken,
  requireAdmin,
};
