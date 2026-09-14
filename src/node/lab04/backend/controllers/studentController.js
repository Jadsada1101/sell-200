const db = require("../config/database");

// ดึงรายการนักศึกษาทั้งหมด รองรับการค้นหา (search) และกรองตามสาขา (major_code)
async function getAllStudents(req, res) {
  try {
    const { search, major_code } = req.query;

    let query = `
      SELECT 
        s.StudentID,
        s.Student_Name,
        s.Student_Surname,
        s.Student_Website,
        s.major_code,
        m.major_name
      FROM tb_student s
      LEFT JOIN tb_major m ON s.major_code = m.major_code
      WHERE 1=1
    `;
    const params = [];

    // กรองตามสาขาวิชา
    if (major_code && major_code.trim() !== "") {
      query += " AND s.major_code = ?";
      params.push(major_code.trim());
    }

    // ค้นหาตามรหัสนักศึกษา, ชื่อ หรือนามสกุล
    if (search && search.trim() !== "") {
      const searchTerm = `%${search.trim()}%`;
      query += ` AND (
        s.StudentID LIKE ? 
        OR s.Student_Name LIKE ? 
        OR s.Student_Surname LIKE ?
      )`;
      params.push(searchTerm, searchTerm, searchTerm);
    }

    query += " ORDER BY s.StudentID ASC";

    const [students] = await db.execute(query, params);

    return res.json({
      success: true,
      count: students.length,
      data: students,
    });
  } catch (error) {
    console.error("Error in getAllStudents:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถดึงข้อมูลนักศึกษาได้",
    });
  }
}

// ดึงข้อมูลนักศึกษารายคน
async function getStudentById(req, res) {
  try {
    const { id } = req.params;
    const query = `
      SELECT 
        s.StudentID,
        s.Student_Name,
        s.Student_Surname,
        s.Student_Website,
        s.major_code,
        m.major_name
      FROM tb_student s
      LEFT JOIN tb_major m ON s.major_code = m.major_code
      WHERE s.StudentID = ?
      LIMIT 1
    `;
    const [rows] = await db.execute(query, [id]);

    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "ไม่พบข้อมูลนักศึกษา",
      });
    }

    return res.json({
      success: true,
      data: rows[0],
    });
  } catch (error) {
    console.error("Error in getStudentById:", error);
    return res.status(500).json({
      success: false,
      message: "เกิดข้อผิดพลาดในการค้นหาข้อมูลนักศึกษา",
    });
  }
}

// เพิ่มข้อมูลนักศึกษาใหม่
async function createStudent(req, res) {
  try {
    const { StudentID, Student_Name, Student_Surname, Student_Website, major_code } = req.body;

    if (!StudentID || !Student_Name || !Student_Surname) {
      return res.status(400).json({
        success: false,
        message: "กรุณาระบุรหัสนักศึกษา ชื่อ และนามสกุล",
      });
    }

    const sId = StudentID.trim();
    const sName = Student_Name.trim();
    const sSurname = Student_Surname.trim();
    const sWebsite = Student_Website ? Student_Website.trim() : "";
    const mCode = major_code ? major_code.trim() : null;

    // ตรวจสอบว่ารหัสนักศึกษาซ้ำหรือไม่
    const [existing] = await db.execute(
      "SELECT StudentID FROM tb_student WHERE StudentID = ? LIMIT 1",
      [sId]
    );

    if (existing.length > 0) {
      return res.status(409).json({
        success: false,
        message: `รหัสนักศึกษา "${sId}" มีอยู่ในระบบแล้ว`,
      });
    }

    await db.execute(
      "INSERT INTO tb_student (StudentID, Student_Name, Student_Surname, Student_Website, major_code) VALUES (?, ?, ?, ?, ?)",
      [sId, sName, sSurname, sWebsite, mCode]
    );

    return res.status(201).json({
      success: true,
      message: "เพิ่มข้อมูลนักศึกษาเรียบร้อยแล้ว",
      data: {
        StudentID: sId,
        Student_Name: sName,
        Student_Surname: sSurname,
        Student_Website: sWebsite,
        major_code: mCode,
      },
    });
  } catch (error) {
    console.error("Error in createStudent:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถบันทึกข้อมูลนักศึกษาได้",
    });
  }
}

// แก้ไขข้อมูลนักศึกษา
async function updateStudent(req, res) {
  try {
    const { id } = req.params;
    const { Student_Name, Student_Surname, Student_Website, major_code } = req.body;

    if (!Student_Name || !Student_Surname) {
      return res.status(400).json({
        success: false,
        message: "กรุณากรอกชื่อและนามสกุล",
      });
    }

    const [result] = await db.execute(
      `UPDATE tb_student 
       SET Student_Name = ?, Student_Surname = ?, Student_Website = ?, major_code = ? 
       WHERE StudentID = ?`,
      [
        Student_Name.trim(),
        Student_Surname.trim(),
        Student_Website ? Student_Website.trim() : "",
        major_code ? major_code.trim() : null,
        id,
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "ไม่พบข้อมูลนักศึกษาที่ต้องการแก้ไข",
      });
    }

    return res.json({
      success: true,
      message: "แก้ไขข้อมูลนักศึกษาเรียบร้อยแล้ว",
    });
  } catch (error) {
    console.error("Error in updateStudent:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถแก้ไขข้อมูลนักศึกษาได้",
    });
  }
}

// ลบข้อมูลนักศึกษา
async function deleteStudent(req, res) {
  try {
    const { id } = req.params;

    const [result] = await db.execute(
      "DELETE FROM tb_student WHERE StudentID = ?",
      [id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "ไม่พบข้อมูลนักศึกษาที่ต้องการลบ",
      });
    }

    return res.json({
      success: true,
      message: "ลบข้อมูลนักศึกษาเรียบร้อยแล้ว",
    });
  } catch (error) {
    console.error("Error in deleteStudent:", error);
    return res.status(500).json({
      success: false,
      message: "ไม่สามารถลบข้อมูลนักศึกษาได้",
    });
  }
}

module.exports = {
  getAllStudents,
  getStudentById,
  createStudent,
  updateStudent,
  deleteStudent,
};
