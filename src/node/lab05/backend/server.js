const express = require("express");
const cors = require("cors");
require("dotenv").config();

const apiRouter = require("./router");

const app = express();
const PORT = process.env.PORT || 5001;

// เปิดใช้งาน CORS ให้ Frontend สามารถเรียก API ได้
app.use(cors({
  origin: "*",
  methods: ["GET", "POST", "PUT", "DELETE"],
  allowedHeaders: ["Content-Type", "Authorization"]
}));

// อ่านข้อมูล Request Body แบบ JSON และ URL-encoded
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// เส้นทางทดสอบเซิร์ฟเวอร์
app.get("/api/health", (req, res) => {
  res.json({
    status: "ok",
    message: "Lab05 Express Server is running smoothly",
    timestamp: new Date().toISOString()
  });
});

// ลงทะเบียน API Routes ผ่าน router.js
app.use("/api", apiRouter);

// ให้บริการไฟล์ Static ฝั่ง Frontend (สามารถเปิดใช้งานผ่าน http://localhost:5001 ได้ทันที)
const path = require("path");
app.use(express.static(path.join(__dirname, "../frontend")));

// จัดการกรณีเรียก Route API ที่ไม่มีอยู่
app.use("/api/*", (req, res) => {
  res.status(404).json({
    success: false,
    message: "Endpoint not found (ไม่พบเส้นทาง API นี้)"
  });
});

// Fallback ไปที่หน้า Frontend สำหรับ Route อื่น ๆ
app.get("*", (req, res) => {
  res.sendFile(path.join(__dirname, "../frontend/index.html"));
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error("Unhandled Server Error:", err);
  res.status(500).json({
    success: false,
    message: "เกิดข้อผิดพลาดภายในระบบเซิร์ฟเวอร์"
  });
});

// เริ่มต้นเซิร์ฟเวอร์
app.listen(PORT, () => {
  console.log(`=========================================`);
  console.log(`🚀 Lab05 Backend API is running on:`);
  console.log(`   http://localhost:${PORT}`);
  console.log(`=========================================`);
});
