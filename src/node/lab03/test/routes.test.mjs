import { test, describe } from "node:test";
import assert from "node:assert/strict";
import { POST as scoreHandler } from "../app/api/score/route.js";
import { POST as discountHandler } from "../app/api/discount/route.js";
import { POST as multiplicationHandler } from "../app/api/multiplication/route.js";
import { POST as bmiHandler } from "../app/api/bmi/route.js";

// Helper function ส่ง POST request จำลอง
function makePostRequest(url, body) {
  return new Request(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

describe("Scenario 1: POST /api/score (โปรแกรมคำนวณเกรด)", () => {
  test("TC1.1: คะแนนรวม 85 ได้เกรด A", async () => {
    const req = makePostRequest("http://localhost:3000/api/score", {
      studentName: "สมชาย",
      midtermScore: 50,
      finalScore: 35,
    });
    const res = await scoreHandler(req);
    const data = await res.json();
    assert.equal(data.success, true);
    assert.equal(data.total, 85);
    assert.equal(data.grade, "A");
    assert.equal(data.label, "ดีเยี่ยม (Excellent)");
  });

  test("TC1.2: ขอบเขตเกรด A พอดี (80 คะแนน)", async () => {
    const req = makePostRequest("http://localhost:3000/api/score", {
      studentName: "สมหญิง",
      midtermScore: 45,
      finalScore: 35,
    });
    const res = await scoreHandler(req);
    const data = await res.json();
    assert.equal(data.total, 80);
    assert.equal(data.grade, "A");
  });

  test("TC1.3: ขอบเขตเกรด B พอดี (70 คะแนน)", async () => {
    const req = makePostRequest("http://localhost:3000/api/score", {
      midtermScore: 40,
      finalScore: 30,
    });
    const res = await scoreHandler(req);
    const data = await res.json();
    assert.equal(data.total, 70);
    assert.equal(data.grade, "B");
  });

  test("TC1.4: ขอบเขตเกรด C พอดี (60 คะแนน)", async () => {
    const req = makePostRequest("http://localhost:3000/api/score", {
      midtermScore: 35,
      finalScore: 25,
    });
    const res = await scoreHandler(req);
    const data = await res.json();
    assert.equal(data.total, 60);
    assert.equal(data.grade, "C");
  });

  test("TC1.5: ขอบเขตเกรด D พอดี (50 คะแนน)", async () => {
    const req = makePostRequest("http://localhost:3000/api/score", {
      midtermScore: 30,
      finalScore: 20,
    });
    const res = await scoreHandler(req);
    const data = await res.json();
    assert.equal(data.total, 50);
    assert.equal(data.grade, "D");
  });

  test("TC1.6: คะแนนต่ำกว่า 50 ได้เกรด F (49 คะแนน)", async () => {
    const req = makePostRequest("http://localhost:3000/api/score", {
      midtermScore: 29,
      finalScore: 20,
    });
    const res = await scoreHandler(req);
    const data = await res.json();
    assert.equal(data.total, 49);
    assert.equal(data.grade, "F");
    assert.equal(data.label, "ไม่ผ่านเกณฑ์ (Fail)");
  });
});

describe("Scenario 2: POST /api/discount (โปรแกรมคำนวณส่วนลดสินค้า)", () => {
  test("TC2.1: ซื้อเกิน 10,000 บาท ได้ส่วนลด 5%", async () => {
    const req = makePostRequest("http://localhost:3000/api/discount", {
      customerName: "สมหมาย",
      price: 15000,
    });
    const res = await discountHandler(req);
    const data = await res.json();
    assert.equal(data.discountRate, 5);
    assert.equal(data.discountAmount, 750);
    assert.equal(data.netPrice, 14250);
  });

  test("TC2.2: ขอบเขต 10,000 บาทพอดี (ยังไม่เกิน 10,000) ได้ส่วนลด 3%", async () => {
    const req = makePostRequest("http://localhost:3000/api/discount", {
      price: 10000,
    });
    const res = await discountHandler(req);
    const data = await res.json();
    assert.equal(data.discountRate, 3);
    assert.equal(data.discountAmount, 300);
    assert.equal(data.netPrice, 9700);
  });

  test("TC2.3: ยอดซื้อเกิน 5,000 บาท ได้ส่วนลด 3%", async () => {
    const req = makePostRequest("http://localhost:3000/api/discount", {
      price: 8000,
    });
    const res = await discountHandler(req);
    const data = await res.json();
    assert.equal(data.discountRate, 3);
    assert.equal(data.discountAmount, 240);
    assert.equal(data.netPrice, 7760);
  });

  test("TC2.4: ขอบเขต 5,000 บาทพอดี ได้ส่วนลด 0%", async () => {
    const req = makePostRequest("http://localhost:3000/api/discount", {
      price: 5000,
    });
    const res = await discountHandler(req);
    const data = await res.json();
    assert.equal(data.discountRate, 0);
    assert.equal(data.discountAmount, 0);
    assert.equal(data.netPrice, 5000);
  });

  test("TC2.5: ซื้อน้อยกว่า 5,000 บาท ได้ส่วนลด 0%", async () => {
    const req = makePostRequest("http://localhost:3000/api/discount", {
      price: 2500,
    });
    const res = await discountHandler(req);
    const data = await res.json();
    assert.equal(data.discountRate, 0);
    assert.equal(data.discountAmount, 0);
    assert.equal(data.netPrice, 2500);
  });
});

describe("Scenario 3: POST /api/multiplication (โปรแกรมแม่สูตรคูณ)", () => {
  test("TC3.1: แม่สูตรคูณ 2 มี 12 แถวและผลคูณถูกต้อง", async () => {
    const req = makePostRequest("http://localhost:3000/api/multiplication", {
      num: 2,
    });
    const res = await multiplicationHandler(req);
    const data = await res.json();
    assert.equal(data.baseNumber, 2);
    assert.equal(data.rows.length, 12);
    assert.equal(data.rows[0].result, 2);
    assert.equal(data.rows[11].result, 24);
  });

  test("TC3.2: แม่สูตรคูณ 12 ผลคูณตัวสุดท้ายต้องเป็น 144", async () => {
    const req = makePostRequest("http://localhost:3000/api/multiplication", {
      num: 12,
    });
    const res = await multiplicationHandler(req);
    const data = await res.json();
    assert.equal(data.rows[11].result, 144);
  });

  test("TC3.3: แม่สูตรคูณ 25 ผลคูณตัวสุดท้ายต้องเป็น 300", async () => {
    const req = makePostRequest("http://localhost:3000/api/multiplication", {
      num: 25,
    });
    const res = await multiplicationHandler(req);
    const data = await res.json();
    assert.equal(data.rows[11].result, 300);
  });
});

describe("Scenario 4: POST /api/bmi (โปรแกรมคำนวณดัชนีมวลกาย)", () => {
  test("TC4.1: สมส่วน / ปกติ (BMI 18.5 - 22.9)", async () => {
    const req = makePostRequest("http://localhost:3000/api/bmi", {
      userName: "วิชัย",
      weight: 65,
      height: 170,
    });
    const res = await bmiHandler(req);
    const data = await res.json();
    assert.equal(data.formattedBmi, "22.49");
    assert.equal(data.category, "สมส่วน / ปกติ");
  });

  test("TC4.2: น้ำหนักน้อย / ผอม (BMI < 18.5)", async () => {
    const req = makePostRequest("http://localhost:3000/api/bmi", {
      weight: 45,
      height: 170,
    });
    const res = await bmiHandler(req);
    const data = await res.json();
    assert.equal(data.formattedBmi, "15.57");
    assert.equal(data.category, "น้ำหนักน้อย / ผอม");
  });

  test("TC4.3: น้ำหนักเกิน / ท้วม (BMI 23.0 - 24.9)", async () => {
    const req = makePostRequest("http://localhost:3000/api/bmi", {
      weight: 70,
      height: 170,
    });
    const res = await bmiHandler(req);
    const data = await res.json();
    assert.equal(data.formattedBmi, "24.22");
    assert.equal(data.category, "น้ำหนักเกิน / ท้วม");
  });

  test("TC4.4: โรคอ้วนระดับ 1 (BMI 25.0 - 29.9)", async () => {
    const req = makePostRequest("http://localhost:3000/api/bmi", {
      weight: 80,
      height: 170,
    });
    const res = await bmiHandler(req);
    const data = await res.json();
    assert.equal(data.formattedBmi, "27.68");
    assert.equal(data.category, "โรคอ้วนระดับ 1");
  });

  test("TC4.5: โรคอ้วนระดับ 2 อันตราย (BMI >= 30.0)", async () => {
    const req = makePostRequest("http://localhost:3000/api/bmi", {
      weight: 95,
      height: 170,
    });
    const res = await bmiHandler(req);
    const data = await res.json();
    assert.equal(data.formattedBmi, "32.87");
    assert.equal(data.category, "โรคอ้วนระดับ 2 (อันตราย)");
  });
});
