/**
 * POST /api/score
 * รับข้อมูลคะแนน คำนวณตัดเกรด A - F และส่งผลลัพธ์กลับ
 */
export async function POST(request) {
  try {
    let body = {};
    const contentType = request.headers.get("content-type") || "";

    if (contentType.includes("application/json")) {
      body = await request.json();
    } else if (contentType.includes("form") || contentType.includes("multipart")) {
      const formData = await request.formData();
      body = Object.fromEntries(formData.entries());
    }

    const studentName = (body.studentName || body.name || "ไม่ระบุชื่อ").trim();
    const mid = Number(body.midtermScore || body.mid) || 0;
    const final = Number(body.finalScore || body.final) || 0;
    const total = mid + final;

    // คำนวณตัดเกรดตามเกณฑ์
    let grade = "F";
    let label = "ไม่ผ่านเกณฑ์ (Fail)";
    let badgeClass = "bg-rose-50 text-rose-700 border-rose-200";

    if (total >= 80) {
      grade = "A";
      label = "ดีเยี่ยม (Excellent)";
      badgeClass = "bg-emerald-50 text-emerald-700 border-emerald-200";
    } else if (total >= 70) {
      grade = "B";
      label = "ดีมาก (Very Good)";
      badgeClass = "bg-blue-50 text-blue-700 border-blue-200";
    } else if (total >= 60) {
      grade = "C";
      label = "ปานกลาง (Good)";
      badgeClass = "bg-amber-50 text-amber-700 border-amber-200";
    } else if (total >= 50) {
      grade = "D";
      label = "ผ่านเกณฑ์ขั้นต่ำ (Pass)";
      badgeClass = "bg-orange-50 text-orange-700 border-orange-200";
    }

    return Response.json({
      success: true,
      studentName,
      mid,
      final,
      total,
      grade,
      label,
      badgeClass,
    });
  } catch (error) {
    return Response.json(
      { success: false, message: "ข้อมูลไม่ถูกต้อง", error: error.message },
      { status: 400 }
    );
  }
}
