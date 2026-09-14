/**
 * POST /api/bmi
 * รับน้ำหนักและส่วนสูง คำนวณและประเมินผล BMI ตามเกณฑ์เอเชีย และส่งผลลัพธ์กลับ
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

    const userName = (body.userName || body.name || "บุคคลทั่วไป").trim();
    const weight = Number(body.weight) || 0;
    const height = Number(body.height) || 0;

    const heightInMeters = height / 100;
    let bmi = 0;

    if (heightInMeters > 0) {
      bmi = weight / (heightInMeters * heightInMeters);
    }

    // แปลผลตามเกณฑ์มาตรฐานเอเชีย
    let category = "น้ำหนักน้อย / ผอม";
    let badgeClass = "bg-blue-50 text-blue-700 border-blue-200";
    let advice =
      "น้ำหนักต่ำกว่าเกณฑ์มาตรฐาน ควรรับประทานอาหารที่มีคุณค่าทางโภชนาการครบ 5 หมู่ และเพิ่มมวลกล้ามเนื้อ";

    if (bmi >= 30.0) {
      category = "โรคอ้วนระดับ 2 (อันตราย)";
      badgeClass = "bg-rose-50 text-rose-700 border-rose-200";
      advice =
        "อยู่ในเกณฑ์อ้วนมาก เสี่ยงต่อปัญหาสุขภาพและโรคแทรกซ้อนร้ายแรง ควรปรึกษาแพทย์หรือผู้เชี่ยวชาญ";
    } else if (bmi >= 25.0) {
      category = "โรคอ้วนระดับ 1";
      badgeClass = "bg-orange-50 text-orange-700 border-orange-200";
      advice =
        "อยู่ในเกณฑ์อ้วน มีความเสี่ยงต่อโรคความดันโลหิตและเบาหวาน ควรควบคุมอาหารและออกกำลังกายอย่างสม่ำเสมอ";
    } else if (bmi >= 23.0) {
      category = "น้ำหนักเกิน / ท้วม";
      badgeClass = "bg-amber-50 text-amber-700 border-amber-200";
      advice =
        "เริ่มมีภาวะน้ำหนักเกิน ควรเริ่มปรับเปลี่ยนพฤติกรรมการรับประทานอาหารและเพิ่มกิจกรรมการเคลื่อนไหวร่างกาย";
    } else if (bmi >= 18.5) {
      category = "สมส่วน / ปกติ";
      badgeClass = "bg-emerald-50 text-emerald-700 border-emerald-200";
      advice =
        "น้ำหนักอยู่ในเกณฑ์มาตรฐาน สุขภาพดี ควรรักษาพฤติกรรมการใช้ชีวิตและการออกกำลังกายนี้ไว้";
    }

    return Response.json({
      success: true,
      userName,
      weight,
      height,
      heightInMeters,
      bmi,
      formattedBmi: bmi.toFixed(2),
      formattedWeight: weight.toLocaleString("th-TH", { maximumFractionDigits: 2 }),
      formattedHeight: height.toLocaleString("th-TH", { maximumFractionDigits: 2 }),
      category,
      badgeClass,
      advice,
    });
  } catch (error) {
    return Response.json(
      { success: false, message: "ข้อมูลไม่ถูกต้อง", error: error.message },
      { status: 400 }
    );
  }
}
