/**
 * POST /api/multiplication
 * รับแม่สูตรคูณ สร้างตารางแถว 1 ถึง 12 และส่งผลลัพธ์กลับ
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

    const num = Number(body.num) || 1;
    const rows = [];

    for (let i = 1; i <= 12; i++) {
      rows.push({
        step: i,
        result: num * i,
      });
    }

    return Response.json({
      success: true,
      baseNumber: num,
      rows,
    });
  } catch (error) {
    return Response.json(
      { success: false, message: "ข้อมูลไม่ถูกต้อง", error: error.message },
      { status: 400 }
    );
  }
}
