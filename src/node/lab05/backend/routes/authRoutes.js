const express = require("express");
const router = express.Router();
const authController = require("../controllers/authController");
const { authenticateToken } = require("../middlewares/authMiddleware");

// Route เข้าสู่ระบบ
router.post("/login", authController.login);

// Route ดึงข้อมูลผู้ใช้ปัจจุบัน (ต้องมี Token)
router.get("/me", authenticateToken, authController.getProfile);

module.exports = router;
