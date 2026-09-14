/**
 * POST /api/discount
 * รับราคาสินค้า คำนวณส่วนลด และส่งผลลัพธ์กลับ
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

    const customerName = (body.customerName || "ลูกค้าทั่วไป").trim();
    const price = Number(body.price) || 0;

    // คำนวณส่วนลดตามขั้นราคา
    let discountRate = 0;
    if (price > 10000) {
      discountRate = 5;
    } else if (price > 5000) {
      discountRate = 3;
    }

    const discountAmount = price * (discountRate / 100);
    const netPrice = price - discountAmount;

    return Response.json({
      success: true,
      customerName,
      price,
      discountRate,
      discountAmount,
      netPrice,
      formattedPrice: price.toLocaleString("th-TH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }),
      formattedDiscount: discountAmount.toLocaleString("th-TH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }),
      formattedNetPrice: netPrice.toLocaleString("th-TH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }),
    });
  } catch (error) {
    return Response.json(
      { success: false, message: "ข้อมูลไม่ถูกต้อง", error: error.message },
      { status: 400 }
    );
  }
}
