import path from "path";
import PDFDocument from "pdfkit";
import pool from "@/lib/db";
import { authenticateRequest } from "@/lib/auth";

/**
 * GET /api/reports/students-pdf
 * สร้างรายงานรายชื่อนักศึกษาในรูปแบบ PDF พร้อมรองรับตัวกรองสาขาวิชาและฟอนต์ภาษาไทย
 */
export async function GET(request) {
  const user = authenticateRequest(request);
  if (!user) {
    return new Response(
      JSON.stringify({ success: false, message: "กรุณาเข้าสู่ระบบก่อนดาวน์โหลดรายงาน" }),
      { status: 401, headers: { "Content-Type": "application/json" } }
    );
  }

  try {
    const { searchParams } = new URL(request.url);
    const majorCode = searchParams.get("major_code") || "";
    const search = searchParams.get("search") || "";

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

    if (majorCode.trim() !== "") {
      query += " AND s.major_code = ?";
      params.push(majorCode.trim());
    }

    if (search.trim() !== "") {
      const searchTerm = `%${search.trim()}%`;
      query += ` AND (s.StudentID LIKE ? OR s.Student_Name LIKE ? OR s.Student_Surname LIKE ?)`;
      params.push(searchTerm, searchTerm, searchTerm);
    }

    query += " ORDER BY s.StudentID ASC";

    const [students] = await pool.query(query, params);

    // สร้าง PDF Document
    const doc = new PDFDocument({
      size: "A4",
      margin: 36,
      info: {
        Title: "รายงานข้อมูลนักศึกษา (Lab05 Next.js)",
        Author: "Lab05 Next.js System",
      },
    });

    const fontRegular = path.join(process.cwd(), "assets/fonts/Prompt-Regular.ttf");
    const fontBold = path.join(process.cwd(), "assets/fonts/Prompt-Bold.ttf");
    doc.registerFont("Prompt", fontRegular);
    doc.registerFont("Prompt-Bold", fontBold);

    // Buffer สำหรับรวบรวมข้อมูล PDF
    const chunks = [];
    doc.on("data", (chunk) => chunks.push(chunk));

    const pdfPromise = new Promise((resolve, reject) => {
      doc.on("end", () => resolve(Buffer.concat(chunks)));
      doc.on("error", reject);
    });

    // หัวรายงาน
    doc.font("Prompt-Bold").fontSize(18).fillColor("#1e293b").text("รายงานรายชื่อนักศึกษา", { align: "center" });
    doc.font("Prompt").fontSize(10).fillColor("#64748b").text("ระบบบริหารจัดการข้อมูลนักศึกษาและสาขาวิชา (Lab05 - Next.js)", { align: "center" });
    doc.moveDown(0.5);

    const printDate = new Date().toLocaleDateString("th-TH", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });

    let filterText = "สาขาวิชา: ทั้งหมด";
    if (majorCode.trim() !== "") {
      filterText = `สาขาวิชา: ${majorCode.trim()}`;
    }

    doc.font("Prompt").fontSize(9).fillColor("#334155");
    doc.text(`${filterText}  |  วันที่พิมพ์รายงาน: ${printDate}  |  จำนวนทั้งหมด: ${students.length} รายการ`, { align: "left" });
    doc.moveDown(0.8);

    // วาดตารางข้อมูล
    const startX = 36;
    let currentY = doc.y;
    const colWidths = {
      no: 30,
      id: 110,
      name: 180,
      major: 90,
      website: 105,
    };

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

      doc.strokeColor("#cbd5e1").lineWidth(0.5).moveTo(startX, y + 24).lineTo(startX + 523, y + 24).stroke();
    }

    drawTableHeader(currentY);
    currentY += 24;

    students.forEach((student, index) => {
      if (currentY > 760) {
        doc.addPage();
        currentY = 36;
        drawTableHeader(currentY);
        currentY += 24;
      }

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

      doc.strokeColor("#e2e8f0").lineWidth(0.5).moveTo(startX, currentY + 20).lineTo(startX + 523, currentY + 20).stroke();
      currentY += 20;
    });

    doc.moveDown(1.5);
    doc.font("Prompt-Bold").fontSize(9).fillColor("#475569").text(`รวมนักศึกษาทั้งสิ้น ${students.length} รายการ`, { align: "right" });

    doc.end();

    const pdfBuffer = await pdfPromise;

    return new Response(pdfBuffer, {
      status: 200,
      headers: {
        "Content-Type": "application/pdf",
        "Content-Disposition": 'inline; filename="student_report.pdf"',
      },
    });
  } catch (error) {
    console.error("PDF Report Error:", error);
    return new Response(
      JSON.stringify({ success: false, message: "เกิดข้อผิดพลาดในการสร้าง PDF" }),
      { status: 500, headers: { "Content-Type": "application/json" } }
    );
  }
}
