const PDFDocument = require("pdfkit");
const path = require("path");
const db = require("../config/database");

// สร้างรายงาน PDF รายชื่อนักศึกษา
async function generateStudentPdfReport(req, res) {
  try {
    const { major_code, search } = req.query;

    let query = `
      SELECT 
        s.StudentID,
        s.Student_Name,
        s.Student_Surname,
        s.Student_Website,
        s.major_code,
        COALESCE(m.major_name, 'ไม่ระบุ') AS major_name
      FROM tb_student s
      LEFT JOIN tb_major m ON s.major_code = m.major_code
      WHERE 1=1
    `;
    const params = [];

    if (major_code && major_code.trim() !== "") {
      query += " AND s.major_code = ?";
      params.push(major_code.trim());
    }

    if (search && search.trim() !== "") {
      const searchTerm = `%${search.trim()}%`;
      query += ` AND (s.StudentID LIKE ? OR s.Student_Name LIKE ? OR s.Student_Surname LIKE ?)`;
      params.push(searchTerm, searchTerm, searchTerm);
    }

    query += " ORDER BY s.StudentID ASC";

    const [students] = await db.execute(query, params);

    // สร้างเอกสาร PDF A4 แนวนอนหรือแนวตั้ง (A4 แนวนอนเหมาะกับตารางกว้าง)
    const doc = new PDFDocument({
      size: "A4",
      margin: 36,
      info: {
        Title: "รายงานข้อมูลนักศึกษา",
        Author: "Lab04 Express System",
      },
    });

    // ลงทะเบียนฟอนต์ภาษาไทย (Prompt)
    const fontRegular = path.join(__dirname, "../assets/fonts/Prompt-Regular.ttf");
    const fontBold = path.join(__dirname, "../assets/fonts/Prompt-Bold.ttf");
    doc.registerFont("Prompt", fontRegular);
    doc.registerFont("Prompt-Bold", fontBold);

    // ตั้งค่า Header สำหรับดาวน์โหลดหรือเปิดดู PDF ในเบราว์เซอร์
    res.setHeader("Content-Type", "application/pdf");
    res.setHeader("Content-Disposition", 'inline; filename="student_report.pdf"');

    doc.pipe(res);

    // หัวรายงาน
    doc.font("Prompt-Bold").fontSize(18).fillColor("#1e293b").text("รายงานรายชื่อนักศึกษา", { align: "center" });
    doc.font("Prompt").fontSize(10).fillColor("#64748b").text("ระบบบริหารจัดการข้อมูลนักศึกษาและสาขาวิชา (Lab04)", { align: "center" });
    doc.moveDown(0.5);

    // ข้อมูลตัวกรองและวันที่ออกรายงาน
    const printDate = new Date().toLocaleDateString("th-TH", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });

    let filterText = "สาขาวิชา: ทั้งหมด";
    if (major_code && major_code.trim() !== "") {
      filterText = `สาขาวิชา: ${major_code.trim()}`;
    }

    doc.font("Prompt").fontSize(9).fillColor("#334155");
    doc.text(`${filterText}  |  วันที่พิมพ์รายงาน: ${printDate}  |  จำนวนทั้งหมด: ${students.length} รายการ`, { align: "left" });
    doc.moveDown(0.8);

    // โครงร่างตาราง
    const startX = 36;
    let currentY = doc.y;
    const colWidths = {
      no: 30,
      id: 110,
      name: 180,
      major: 90,
      website: 105,
    };

    // ฟังก์ชันวาด Header ของตาราง
    function drawTableHeader(y) {
      doc.rect(startX, y, 523, 24).fill("#f1f5f9");
      doc.font("Prompt-Bold").fontSize(9).fillColor("#0f172a");

      let x = startX + 5;
      doc.text("ลำดับ", x, y + 6, { width: colWidths.no, align: "center" });
      x += colWidths.no;
      doc.text("รหัสนักศึกษา", x, y + 6, { width: colWidths.id });
      x += colWidths.id;
      doc.text("ชื่อ - นามสกุล", x, y + 6, { width: colWidths.name });
      x += colWidths.name;
      doc.text("สาขาวิชา", x, y + 6, { width: colWidths.major });
      x += colWidths.major;
      doc.text("เว็บไซต์", x, y + 6, { width: colWidths.website });

      // เส้นใต้ Header
      doc.strokeColor("#cbd5e1").lineWidth(0.5).moveTo(startX, y + 24).lineTo(startX + 523, y + 24).stroke();
    }

    drawTableHeader(currentY);
    currentY += 24;

    // วาดแถวข้อมูลนักศึกษา
    students.forEach((student, index) => {
      // ตรวจสอบขึ้นหน้าใหม่
      if (currentY > 760) {
        doc.addPage();
        currentY = 36;
        drawTableHeader(currentY);
        currentY += 24;
      }

      // สลับสีแถวเบา ๆ ให้อ่านง่าย
      if (index % 2 === 1) {
        doc.rect(startX, currentY, 523, 20).fill("#f8fafc");
      }

      doc.font("Prompt").fontSize(8.5).fillColor("#334155");

      let x = startX + 5;
      doc.text((index + 1).toString(), x, currentY + 5, { width: colWidths.no, align: "center" });
      x += colWidths.no;
      doc.text(student.StudentID, x, currentY + 5, { width: colWidths.id });
      x += colWidths.name ? x : x;
      doc.text(`${student.Student_Name} ${student.Student_Surname}`, x, currentY + 5, { width: colWidths.name });
      x += colWidths.name;
      const majorLabel = student.major_code ? `${student.major_code}` : "-";
      doc.text(majorLabel, x, currentY + 5, { width: colWidths.major });
      x += colWidths.major;
      const webLabel = student.Student_Website || "-";
      doc.text(webLabel, x, currentY + 5, { width: colWidths.website, lineBreak: false });

      // เส้นคั่นระหว่างแถว
      doc.strokeColor("#e2e8f0").lineWidth(0.5).moveTo(startX, currentY + 20).lineTo(startX + 523, currentY + 20).stroke();
      currentY += 20;
    });

    // สรุปท้ายรายงาน
    doc.moveDown(1.5);
    doc.font("Prompt-Bold").fontSize(9).fillColor("#475569").text(`รวมนักศึกษาทั้งสิ้น ${students.length} รายการ`, { align: "right" });

    doc.end();
  } catch (error) {
    console.error("Error in generateStudentPdfReport:", error);
    if (!res.headersSent) {
      return res.status(500).json({
        success: false,
        message: "ไม่สามารถสร้างรายงาน PDF ได้",
      });
    }
  }
}

module.exports = {
  generateStudentPdfReport,
};
