const db = require("../config/database");

// ดึงรายการสาขาวิชาทั้งหมด พร้อมจำนวนนักศึกษาในสาขา
async function getAllMajors(req, res) {
  try {
    const query = `
      SELECT 
        m.major_code,
        m.major_name,
        m.remark,
        m.created_at,
        COUNT(s.StudentID) AS student_count
      FROM tb_major m
      LEFT JOIN tb_student s ON m.major_code = s.major_code
      GROUP BY m.major_code, m.major_name, m.remark, m.created_at
      ORDER BY m.major_code ASC
    `;
    const [majors] = await db.execute(query);
    return res.json({
      success: true,
      data: majors,
    });
  } catch (error) {
    console.error("Error in getAllMajors:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถดึงข้อมูลสาขาวิชาได้",
    });
  }
}

// ดึงสาขาวิชาตามรหัส (major_code) พร้อมข้อมูลนักศึกษาในสังกัด
async function getMajorByCode(req, res) {
  try {
    const { code } = req.params;
    const [rows] = await db.execute(
      "SELECT * FROM tb_major WHERE major_code = ? LIMIT 1",
      [code]
    );

    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "ไม่พบสาขาวิชานี้",
      });
    }

    const major = rows[0];

    // ดึงรายชื่อนักศึกษาในสังกัดสาขาวิชานี้
    const [students] = await db.execute(
      "SELECT StudentID, Student_Name, Student_Surname, Student_Website FROM tb_student WHERE major_code = ? ORDER BY StudentID ASC",
      [code]
    );

    major.student_count = students.length;
    major.students = students;

    return res.json({
      success: true,
      data: major,
    });
  } catch (error) {
    console.error("Error in getMajorByCode:", error);
    return res.status(500).json({
      success: false,
      message: "เกิดข้อผิดพลาดในการค้นหาสาขาวิชา",
    });
  }
}

// เพิ่มสาขาวิชาใหม่ (Admin เท่านั้น)
async function createMajor(req, res) {
  try {
    if (req.user && req.user.role !== "admin") {
      return res.status(403).json({
        success: false,
        message: "เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถเพิ่มสาขาวิชาได้",
      });
    }

    const { major_code, major_name, remark } = req.body;

    if (!major_code || !major_name) {
      return res.status(400).json({
        success: false,
        message: "กรุณาระบุรหัสสาขาวิชาและชื่อสาขาวิชา",
      });
    }

    const code = major_code.trim().toUpperCase();
    const name = major_name.trim();
    const remarkText = remark ? remark.trim() : null;

    // ตรวจสอบรหัสสาขาซ้ำ
    const [existing] = await db.execute(
      "SELECT major_code FROM tb_major WHERE major_code = ? LIMIT 1",
      [code]
    );

    if (existing.length > 0) {
      return res.status(409).json({
        success: false,
        message: `รหัสสาขาวิชา "${code}" มีอยู่ในระบบแล้ว`,
      });
    }

    await db.execute(
      "INSERT INTO tb_major (major_code, major_name, remark) VALUES (?, ?, ?)",
      [code, name, remarkText]
    );

    return res.status(201).json({
      success: true,
      message: "เพิ่มสาขาวิชาเรียบร้อยแล้ว",
      data: { major_code: code, major_name: name, remark: remarkText },
    });
  } catch (error) {
    console.error("Error in createMajor:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถเพิ่มสาขาวิชาได้",
    });
  }
}

// แก้ไขข้อมูลสาขาวิชา (Admin เท่านั้น)
async function updateMajor(req, res) {
  try {
    if (req.user && req.user.role !== "admin") {
      return res.status(403).json({
        success: false,
        message: "เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถแก้ไขสาขาวิชาได้",
      });
    }

    const { code } = req.params;
    const { major_name, remark } = req.body;

    if (!major_name) {
      return res.status(400).json({
        success: false,
        message: "กรุณาระบุชื่อสาขาวิชา",
      });
    }

    const [result] = await db.execute(
      "UPDATE tb_major SET major_name = ?, remark = ? WHERE major_code = ?",
      [major_name.trim(), remark ? remark.trim() : null, code]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "ไม่พบสาขาวิชาที่ต้องการแก้ไข",
      });
    }

    return res.json({
      success: true,
      message: "แก้ไขสาขาวิชาเรียบร้อยแล้ว",
    });
  } catch (error) {
    console.error("Error in updateMajor:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถแก้ไขสาขาวิชาได้",
    });
  }
}

// ลบสาขาวิชา (Admin เท่านั้น)
async function deleteMajor(req, res) {
  try {
    if (req.user && req.user.role !== "admin") {
      return res.status(403).json({
        success: false,
        message: "เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถลบสาขาวิชาได้",
      });
    }

    const { code } = req.params;

    // ตรวจสอบว่ายังมีนักศึกษาอยู่ในสาขานี้หรือไม่
    const [students] = await db.execute(
      "SELECT COUNT(*) AS count FROM tb_student WHERE major_code = ?",
      [code]
    );

    if (students[0].count > 0) {
      return res.status(400).json({
        success: false,
        message: `ไม่สามารถลบสาขานี้ได้ เนื่องจากยังมีนักศึกษาจำนวน ${students[0].count} คนสังกัดอยู่`,
      });
    }

    const [result] = await db.execute(
      "DELETE FROM tb_major WHERE major_code = ?",
      [code]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "ไม่พบสาขาวิชาที่ต้องการลบ",
      });
    }

    return res.json({
      success: true,
      message: "ลบสาขาวิชาเรียบร้อยแล้ว",
    });
  } catch (error) {
    console.error("Error in deleteMajor:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถลบสาขาวิชาได้",
    });
  }
}

module.exports = {
  getAllMajors,
  getMajorByCode,
  createMajor,
  updateMajor,
  deleteMajor,
};
