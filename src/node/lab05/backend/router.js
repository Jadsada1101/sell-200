const express = require("express");
const router = express.Router();

const authRoutes = require("./routes/authRoutes");
const majorRoutes = require("./routes/majorRoutes");
const studentRoutes = require("./routes/studentRoutes");
const reportRoutes = require("./routes/reportRoutes");

// รวมทุก API Routes ให้อยู่ใน router.js กลางเพื่อความอ่านง่ายและเป็นระเบียบ
router.use("/auth", authRoutes);
router.use("/majors", majorRoutes);
router.use("/students", studentRoutes);
router.use("/reports", reportRoutes);

module.exports = router;
