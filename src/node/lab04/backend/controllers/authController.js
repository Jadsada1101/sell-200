const db = require("../config/database");
const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");

const JWT_SECRET = process.env.JWT_SECRET || "supersecret_lab04_jwt_key_2026";

// ฟังก์ชันเข้าสู่ระบบ
async function login(req, res) {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: "กรุณากรอกชื่อผู้ใช้และรหัสผ่านให้ครบถ้วน",
      });
    }

    // ค้นหาผู้ใช้ในตาราง tb_users
    const [users] = await db.execute(
      "SELECT user_id, username, password, fullname, role FROM tb_users WHERE username = ? LIMIT 1",
      [username.trim()]
    );

    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง",
      });
    }

    const user = users[0];

    // แปลง prefix $2y$ (จาก PHP) ให้เป็น $2a$ เพื่อให้ bcryptjs ตรวจสอบได้
    const storedHash = user.password.replace(/^\$2y\$/, "$2a$");
    const isPasswordValid = bcrypt.compareSync(password, storedHash);

    if (!isPasswordValid) {
      return res.status(401).json({
        success: false,
        message: "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง",
      });
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

    return res.json({
      success: true,
      message: "เข้าสู่ระบบสำเร็จ",
      token: token,
      user: {
        user_id: user.user_id,
        username: user.username,
        fullname: user.fullname,
        role: user.role,
      },
    });
  } catch (error) {
    console.error("Login Error:", error);
    return res.status(500).json({
      success: false,
      message: "เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์",
    });
  }
}

// ฟังก์ชันดึงข้อมูลผู้ใช้ปัจจุบัน (จาก Token)
async function getProfile(req, res) {
  return res.json({
    success: true,
    user: req.user,
  });
}

module.exports = {
  login,
  getProfile,
};
