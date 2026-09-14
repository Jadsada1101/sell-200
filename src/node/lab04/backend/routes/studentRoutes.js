const express = require("express");
const router = express.Router();
const studentController = require("../controllers/studentController");
const { authenticateToken } = require("../middlewares/authMiddleware");

// ทุก route ของ student ต้องผ่านการตรวจสอบ Token
router.use(authenticateToken);

// GET /api/students - ดึงรายการนักศึกษา พร้อมรองรับ ?search= และ ?major_code=
router.get("/", studentController.getAllStudents);

// GET /api/students/:id - ดึงข้อมูลนักศึกษารายคน
router.get("/:id", studentController.getStudentById);

// POST /api/students - เพิ่มข้อมูลนักศึกษา
router.post("/", studentController.createStudent);

// PUT /api/students/:id - แก้ไขข้อมูลนักศึกษา
router.put("/:id", studentController.updateStudent);

// DELETE /api/students/:id - ลบข้อมูลนักศึกษา
router.delete("/:id", studentController.deleteStudent);

module.exports = router;
