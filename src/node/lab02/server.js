const express = require("express");
const app = express();
const PORT = 3000;

// เปิดให้ Express อ่านข้อมูล Form แบบ urlencoded และเสิร์ฟไฟล์ Static จากโฟลเดอร์ public
app.use(express.urlencoded({ extended: true }));
app.use(express.static("public"));

// ==========================================
// 1. Layout Template Helper (Junior Clean Code)
// ==========================================
function renderBaseLayout(title, bodyContent) {
  return `<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-800 min-h-screen antialiased flex flex-col justify-center p-4 sm:p-8">
    <div class="w-full max-w-md mx-auto my-auto py-6">
        ${bodyContent}
    </div>
</body>
</html>`;
}

// ==========================================
// 2. Route Handlers & Business Logic
// ==========================================

// Route: คำนวณเกรด
app.post("/score", (req, res) => {
  const name = req.body.name ? req.body.name.trim() : "ไม่ระบุชื่อ";
  const mid = Number(req.body.mid) || 0;
  const final = Number(req.body.final) || 0;
  const total = mid + final;

  // คำนวณ Grade ตามเกณฑ์
  let grade = "F";
  let gradeStyle = {
    badge: "bg-rose-50 text-rose-700 border-rose-200",
    label: "ไม่ผ่านเกณฑ์ (Fail)",
  };

  if (total >= 80) {
    grade = "A";
    gradeStyle = {
      badge: "bg-emerald-50 text-emerald-700 border-emerald-200",
      label: "ดีเยี่ยม (Excellent)",
    };
  } else if (total >= 70) {
    grade = "B";
    gradeStyle = {
      badge: "bg-blue-50 text-blue-700 border-blue-200",
      label: "ดีมาก (Very Good)",
    };
  } else if (total >= 60) {
    grade = "C";
    gradeStyle = {
      badge: "bg-amber-50 text-amber-700 border-amber-200",
      label: "ปานกลาง (Good)",
    };
  } else if (total >= 50) {
    grade = "D";
    gradeStyle = {
      badge: "bg-orange-50 text-orange-700 border-orange-200",
      label: "ผ่านเกณฑ์ขั้นต่ำ (Pass)",
    };
  }

  const content = `
    <!-- Back to Form Link -->
    <div class="mb-4">
        <a href="/grade.html" class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>คำนวณใหม่อีกครั้ง</span>
        </a>
    </div>

    <!-- Result Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
        <!-- Header -->
        <div class="text-center pb-6 border-b border-zinc-100">
            <span class="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
                ผลการประเมิน
            </span>
            <h1 class="text-xl font-bold text-zinc-900">ผลการตัดเกรดการเรียน</h1>
            <p class="text-xs text-zinc-500 mt-1">${name}</p>
        </div>

        <!-- Grade Badge Box -->
        <div class="py-6 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-xl border ${gradeStyle.badge} mb-2">
                <span class="text-4xl font-extrabold tracking-tight">${grade}</span>
            </div>
            <p class="text-xs font-medium text-zinc-600">${gradeStyle.label}</p>
        </div>

        <!-- Score Breakdown Table -->
        <div class="rounded-lg border border-zinc-200 overflow-hidden mb-6 text-xs">
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50 border-b border-zinc-200">
                <span class="text-zinc-600">คะแนนระหว่างภาค (เต็ม 60)</span>
                <span class="font-medium text-zinc-900">${mid} คะแนน</span>
            </div>
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
                <span class="text-zinc-600">คะแนนปลายภาค (เต็ม 40)</span>
                <span class="font-medium text-zinc-900">${final} คะแนน</span>
            </div>
            <div class="flex justify-between items-center py-3 px-3.5 bg-white font-semibold">
                <span class="text-zinc-900 text-sm">คะแนนรวมทั้งหมด</span>
                <span class="text-zinc-900 text-sm">${total} / 100</span>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-col gap-2">
            <a href="/grade.html"
                class="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors">
                คำนวณคะแนนอื่นต่อ
            </a>
            <a href="/index.html"
                class="w-full text-center py-2 px-4 border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-lg transition-colors">
                กลับสู่หน้าหลัก
            </a>
        </div>
    </div>
  `;

  res.send(renderBaseLayout(`ผลการเรียน - ${name}`, content));
});

// Route: คำนวณส่วนลดสินค้า
app.post("/discount_product", (req, res) => {
  const customerName = req.body.customerName ? req.body.customerName.trim() : "ลูกค้าทั่วไป";
  const price = Number(req.body.price) || 0;

  // คำนวณอัตราส่วนลด
  let discountRate = 0;
  if (price > 10000) {
    discountRate = 5;
  } else if (price > 5000) {
    discountRate = 3;
  }

  const discountAmount = price * (discountRate / 100);
  const netPrice = price - discountAmount;

  // จัดรูปแบบตัวเลข
  const formattedPrice = price.toLocaleString("th-TH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const formattedDiscount = discountAmount.toLocaleString("th-TH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const formattedNetPrice = netPrice.toLocaleString("th-TH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const content = `
    <!-- Back to Form Link -->
    <div class="mb-4">
        <a href="/discount_product.html" class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>คำนวณใหม่อีกครั้ง</span>
        </a>
    </div>

    <!-- Result Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
        <!-- Header -->
        <div class="text-center pb-6 border-b border-zinc-100">
            <span class="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
                ใบสรุปยอดคำนวณ
            </span>
            <h1 class="text-xl font-bold text-zinc-900">สรุปการคำนวณส่วนลด</h1>
            <p class="text-xs text-zinc-500 mt-1">ชื่อลูกค้า: <span class="font-medium text-zinc-800">${customerName}</span></p>
        </div>

        <!-- Receipt Table -->
        <div class="rounded-lg border border-zinc-200 overflow-hidden my-6 text-xs">
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
                <span class="text-zinc-600">ราคาสินค้าปกติ</span>
                <span class="font-medium text-zinc-900">${formattedPrice} บาท</span>
            </div>
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
                <span class="text-zinc-600">อัตราส่วนลด (${discountRate}%)</span>
                <span class="font-medium text-emerald-700">-${formattedDiscount} บาท</span>
            </div>
            <div class="flex justify-between items-center py-3.5 px-3.5 bg-zinc-50 font-bold text-sm text-zinc-900">
                <span>ยอดเงินสุทธิที่ต้องชำระ</span>
                <span>${formattedNetPrice} บาท</span>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-col gap-2">
            <a href="/discount_product.html"
                class="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors">
                คำนวณรายการอื่นต่อ
            </a>
            <a href="/index.html"
                class="w-full text-center py-2 px-4 border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-lg transition-colors">
                กลับสู่หน้าหลัก
            </a>
        </div>
    </div>
  `;

  res.send(renderBaseLayout(`ผลการคำนวณส่วนลด - ${customerName}`, content));
});

// Route: แม่สูตรคูณ
app.post("/multiplication", (req, res) => {
  const num = Number(req.body.num) || 1;

  let tableRows = "";
  for (let i = 1; i <= 12; i++) {
    const result = num * i;
    tableRows += `
      <div class="flex justify-between items-center py-2 px-3 text-xs ${i % 2 === 0 ? 'bg-zinc-50/70' : 'bg-white'} border-b border-zinc-100 last:border-0">
          <span class="text-zinc-600 font-mono">${num} &times; ${i}</span>
          <span class="font-bold text-zinc-900 font-mono">= ${result}</span>
      </div>
    `;
  }

  const content = `
    <!-- Back to Form Link -->
    <div class="mb-4">
        <a href="/multiplication.html" class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>เลือกแม่สูตรคูณใหม่</span>
        </a>
    </div>

    <!-- Result Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
        <!-- Header -->
        <div class="text-center pb-6 border-b border-zinc-100">
            <span class="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
                ตารางสูตรคูณ
            </span>
            <h1 class="text-xl font-bold text-zinc-900">แม่สูตรคูณ ${num}</h1>
            <p class="text-xs text-zinc-500 mt-1">ผลคูณตั้งแต่ลำดับที่ 1 ถึง 12</p>
        </div>

        <!-- Table List -->
        <div class="rounded-lg border border-zinc-200 overflow-hidden my-6">
            ${tableRows}
        </div>

        <!-- Action Links -->
        <div class="flex flex-col gap-2">
            <a href="/multiplication.html"
                class="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors">
                เลือกแม่สูตรคูณอื่น
            </a>
            <a href="/index.html"
                class="w-full text-center py-2 px-4 border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-lg transition-colors">
                กลับสู่หน้าหลัก
            </a>
        </div>
    </div>
  `;

  res.send(renderBaseLayout(`แม่สูตรคูณ ${num}`, content));
});

// Route: คำนวณดัชนีมวลกาย (BMI)
app.post("/bmi", (req, res) => {
  const name = req.body.name ? req.body.name.trim() : "บุคคลทั่วไป";
  const weight = Number(req.body.weight) || 0;
  const height = Number(req.body.height) || 0; // หน่วยเป็นเซนติเมตร

  // คำนวณค่า BMI: น้ำหนัก (กก.) / (ส่วนสูง (เมตร))^2
  const heightInMeters = height / 100;
  let bmi = 0;
  if (heightInMeters > 0) {
    bmi = weight / (heightInMeters * heightInMeters);
  }

  // แปลผลตามเกณฑ์มาตรฐานเอเชีย
  let category = "น้ำหนักน้อย / ผอม";
  let badgeStyle = "bg-blue-50 text-blue-700 border-blue-200";
  let advice = "น้ำหนักต่ำกว่าเกณฑ์มาตรฐาน ควรรับประทานอาหารที่มีคุณค่าทางโภชนาการครบ 5 หมู่ และเพิ่มมวลกล้ามเนื้อ";

  if (bmi >= 30.0) {
    category = "โรคอ้วนระดับ 2 (อันตราย)";
    badgeStyle = "bg-rose-50 text-rose-700 border-rose-200";
    advice = "อยู่ในเกณฑ์อ้วนมาก เสี่ยงต่อปัญหาสุขภาพและโรคแทรกซ้อนร้ายแรง ควรปรึกษาแพทย์หรือผู้เชี่ยวชาญ";
  } else if (bmi >= 25.0) {
    category = "โรคอ้วนระดับ 1";
    badgeStyle = "bg-orange-50 text-orange-700 border-orange-200";
    advice = "อยู่ในเกณฑ์อ้วน มีความเสี่ยงต่อโรคความดันโลหิตและเบาหวาน ควรควบคุมอาหารและออกกำลังกายอย่างสม่ำเสมอ";
  } else if (bmi >= 23.0) {
    category = "น้ำหนักเกิน / ท้วม";
    badgeStyle = "bg-amber-50 text-amber-700 border-amber-200";
    advice = "เริ่มมีภาวะน้ำหนักเกิน ควรเริ่มปรับเปลี่ยนพฤติกรรมการรับประทานอาหารและเพิ่มกิจกรรมการเคลื่อนไหวร่างกาย";
  } else if (bmi >= 18.5) {
    category = "สมส่วน / ปกติ";
    badgeStyle = "bg-emerald-50 text-emerald-700 border-emerald-200";
    advice = "น้ำหนักอยู่ในเกณฑ์มาตรฐาน สุขภาพดี ควรรักษาพฤติกรรมการใช้ชีวิตและการออกกำลังกายนี้ไว้";
  }

  const formattedBmi = bmi.toFixed(2);
  const formattedWeight = weight.toLocaleString("th-TH", { maximumFractionDigits: 2 });
  const formattedHeight = height.toLocaleString("th-TH", { maximumFractionDigits: 2 });

  const content = `
    <!-- Back to Form Link -->
    <div class="mb-4">
        <a href="/bmi.html" class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>คำนวณใหม่อีกครั้ง</span>
        </a>
    </div>

    <!-- Result Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
        <!-- Header -->
        <div class="text-center pb-6 border-b border-zinc-100">
            <span class="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
                ผลการประเมินสุขภาพ
            </span>
            <h1 class="text-xl font-bold text-zinc-900">ผลการคำนวณดัชนีมวลกาย (BMI)</h1>
            <p class="text-xs text-zinc-500 mt-1">${name}</p>
        </div>

        <!-- Big Display Result -->
        <div class="py-6 text-center">
            <div class="text-4xl font-extrabold text-zinc-900 tracking-tight">
                ${formattedBmi} <span class="text-sm font-medium text-zinc-400">kg/m²</span>
            </div>
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border ${badgeStyle} mt-2">
                ${category}
            </div>
        </div>

        <!-- Health Advice Box -->
        <div class="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 mb-6 text-xs text-zinc-600 leading-relaxed">
            <span class="font-semibold text-zinc-800">คำแนะนำ: </span>${advice}
        </div>

        <!-- Details Table -->
        <div class="rounded-lg border border-zinc-200 overflow-hidden mb-6 text-xs">
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
                <span class="text-zinc-600">น้ำหนักตัว</span>
                <span class="font-medium text-zinc-900">${formattedWeight} กิโลกรัม</span>
            </div>
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
                <span class="text-zinc-600">ส่วนสูง</span>
                <span class="font-medium text-zinc-900">${formattedHeight} ซม. (${heightInMeters.toFixed(2)} ม.)</span>
            </div>
            <div class="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50">
                <span class="text-zinc-600">สูตรการคำนวณ</span>
                <span class="font-mono text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded text-[11px]">${formattedWeight} / (${heightInMeters.toFixed(2)})²</span>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-col gap-2">
            <a href="/bmi.html"
                class="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors">
                คำนวณค่า BMI อื่นต่อ
            </a>
            <a href="/index.html"
                class="w-full text-center py-2 px-4 border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-lg transition-colors">
                กลับสู่หน้าหลัก
            </a>
        </div>
    </div>
  `;

  res.send(renderBaseLayout(`ผลการคำนวณ BMI - ${name}`, content));
});

// เริ่มต้น Server
app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});