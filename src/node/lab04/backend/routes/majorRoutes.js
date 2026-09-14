const express = require("express");
const router = express.Router();
const majorController = require("../controllers/majorController");
const { authenticateToken } = require("../middlewares/authMiddleware");

// ทุก route ของ major ต้องผ่านการตรวจสอบ Token
router.use(authenticateToken);

// GET /api/majors - รายการสาขาวิชาทั้งหมด
router.get("/", majorController.getAllMajors);

// GET /api/majors/:code - รายละเอียดสาขาวิชา
router.get("/:code", majorController.getMajorByCode);

// POST /api/majors - เพิ่มสาขาวิชา
router.post("/", majorController.createMajor);

// PUT /api/majors/:code - แก้ไขสาขาวิชา
router.put("/:code", majorController.updateMajor);

// DELETE /api/majors/:code - ลบสาขาวิชา
router.delete("/:code", majorController.deleteMajor);

module.exports = router;
