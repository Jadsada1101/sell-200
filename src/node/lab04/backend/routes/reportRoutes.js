const express = require("express");
const router = express.Router();
const reportController = require("../controllers/reportController");
const { authenticateToken } = require("../middlewares/authMiddleware");

// ออกรายงาน PDF นักศึกษา (รองรับ token ผ่าน query หรือ header)
// สำหรับเปิดผ่าน link browser โดยตรง เรายอมรับ token ผ่าน ?token= ด้วย
router.get("/students-pdf", (req, res, next) => {
  if (req.query.token && !req.headers["authorization"]) {
    req.headers["authorization"] = `Bearer ${req.query.token}`;
  }
  next();
}, authenticateToken, reportController.generateStudentPdfReport);

module.exports = router;
