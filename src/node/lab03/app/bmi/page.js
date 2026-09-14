"use client";

import { useState } from "react";
import Link from "next/link";

export default function BmiPage() {
  const [userName, setUserName] = useState("");
  const [weight, setWeight] = useState("");
  const [height, setHeight] = useState("");
  const [result, setResult] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // ส่งข้อมูลแบบ POST ไปที่ Route Handler: /api/bmi
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const res = await fetch("/api/bmi", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          userName,
          weight: Number(weight),
          height: Number(height),
        }),
      });

      const data = await res.json();
      if (data.success) {
        setResult(data);
      }
    } catch (error) {
      console.error("Error calculating BMI:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleReset = () => {
    setResult(null);
  };

  const handleClearAll = () => {
    setUserName("");
    setWeight("");
    setHeight("");
    setResult(null);
  };

  return (
    <div className="w-full max-w-lg mx-auto my-auto py-6">
      {/* Back Link */}
      <div className="mb-4">
        {result ? (
          <button
            type="button"
            onClick={handleReset}
            className="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="w-3.5 h-3.5"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <line x1="19" y1="12" x2="5" y2="12" />
              <polyline points="12 19 5 12 12 5" />
            </svg>
            <span>คำนวณใหม่อีกครั้ง</span>
          </button>
        ) : (
          <Link
            href="/"
            className="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="w-3.5 h-3.5"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <line x1="19" y1="12" x2="5" y2="12" />
              <polyline points="12 19 5 12 12 5" />
            </svg>
            <span>กลับสู่หน้าหลัก</span>
          </Link>
        )}
      </div>

      {!result ? (
        /* Form Card */
        <div className="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
          <div className="mb-6">
            <span className="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
              โปรแกรมที่ 04 &bull; POST /api/bmi
            </span>
            <h1 className="text-xl font-bold text-zinc-900">
              โปรแกรมคำนวณดัชนีมวลกาย (BMI)
            </h1>
            <p className="text-xs text-zinc-500 mt-1">
              ประเมินภาวะสุขภาพและสัดส่วนร่างกายจากน้ำหนักและส่วนสูง
            </p>
          </div>

          {/* BMI Reference Table */}
          <div className="mb-6">
            <h2 className="text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
              เกณฑ์มาตรฐาน BMI (เอเชีย)
            </h2>
            <div className="overflow-hidden rounded-lg border border-zinc-200">
              <table className="w-full text-xs text-left">
                <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-medium">
                  <tr>
                    <th className="py-2 px-3">ค่า BMI</th>
                    <th className="py-2 px-3">ภาวะสุขภาพ</th>
                    <th className="py-2 px-3 text-right">ความเสี่ยงโรค</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-100 text-zinc-700">
                  <tr>
                    <td className="py-1.5 px-3 font-mono">&lt; 18.5</td>
                    <td className="py-1.5 px-3">น้ำหนักน้อย / ผอม</td>
                    <td className="py-1.5 px-3 text-right text-zinc-500">
                      เสี่ยงขาดสารอาหาร
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1.5 px-3 font-mono">18.5 - 22.9</td>
                    <td className="py-1.5 px-3 font-semibold text-emerald-700">
                      สมส่วน / ปกติ
                    </td>
                    <td className="py-1.5 px-3 text-right text-emerald-700 font-medium">
                      สุขภาพดี
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1.5 px-3 font-mono">23.0 - 24.9</td>
                    <td className="py-1.5 px-3 font-medium text-amber-700">
                      น้ำหนักเกิน / ท้วม
                    </td>
                    <td className="py-1.5 px-3 text-right text-amber-700">
                      เริ่มมีความเสี่ยง
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1.5 px-3 font-mono">25.0 - 29.9</td>
                    <td className="py-1.5 px-3 font-medium text-orange-700">
                      โรคอ้วนระดับ 1
                    </td>
                    <td className="py-1.5 px-3 text-right text-orange-700">
                      เสี่ยงปานกลาง
                    </td>
                  </tr>
                  <tr>
                    <td className="py-1.5 px-3 font-mono">&ge; 30.0</td>
                    <td className="py-1.5 px-3 font-bold text-rose-700">
                      โรคอ้วนระดับ 2
                    </td>
                    <td className="py-1.5 px-3 text-right text-rose-700 font-medium">
                      เสี่ยงสูงมาก
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label
                htmlFor="userName"
                className="block text-xs font-medium text-zinc-700 mb-1.5"
              >
                ชื่อผู้รับการประเมิน (ไม่บังคับ)
              </label>
              <input
                type="text"
                id="userName"
                value={userName}
                onChange={(e) => setUserName(e.target.value)}
                placeholder="เช่น นายสมหมาย หรือ บุคคลทั่วไป"
                className="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label
                  htmlFor="weight"
                  className="block text-xs font-medium text-zinc-700 mb-1.5"
                >
                  น้ำหนักตัว (กก.)
                </label>
                <div className="relative">
                  <input
                    type="number"
                    step="any"
                    min="1"
                    max="300"
                    id="weight"
                    value={weight}
                    onChange={(e) => setWeight(e.target.value)}
                    placeholder="เช่น 65"
                    required
                    className="w-full px-3.5 py-2.5 pr-12 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
                  />
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-zinc-400">
                    kg
                  </div>
                </div>
              </div>

              <div>
                <label
                  htmlFor="height"
                  className="block text-xs font-medium text-zinc-700 mb-1.5"
                >
                  ส่วนสูง (ซม.)
                </label>
                <div className="relative">
                  <input
                    type="number"
                    step="any"
                    min="1"
                    max="250"
                    id="height"
                    value={height}
                    onChange={(e) => setHeight(e.target.value)}
                    placeholder="เช่น 170"
                    required
                    className="w-full px-3.5 py-2.5 pr-12 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
                  />
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-zinc-400">
                    cm
                  </div>
                </div>
              </div>
            </div>

            <div className="pt-2">
              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 disabled:bg-zinc-400 text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer"
              >
                {isLoading ? "กำลังคำนวณ..." : "คำนวณค่า BMI และประเมินผล (POST)"}
              </button>
            </div>
          </form>
        </div>
      ) : (
        /* Result Card */
        <div className="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
          <div className="text-center pb-6 border-b border-zinc-100">
            <span className="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
              ผลการประเมินสุขภาพ
            </span>
            <h1 className="text-xl font-bold text-zinc-900">
              ผลการคำนวณดัชนีมวลกาย (BMI)
            </h1>
            <p className="text-xs text-zinc-500 mt-1">{result.userName}</p>
          </div>

          <div className="py-6 text-center">
            <div className="text-4xl font-extrabold text-zinc-900 tracking-tight">
              {result.formattedBmi}{" "}
              <span className="text-sm font-medium text-zinc-400">kg/m²</span>
            </div>
            <div
              className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border ${result.badgeClass} mt-2`}
            >
              {result.category}
            </div>
          </div>

          {/* Health Advice Box */}
          <div className="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 mb-6 text-xs text-zinc-600 leading-relaxed">
            <span className="font-semibold text-zinc-800">คำแนะนำ: </span>
            {result.advice}
          </div>

          {/* Details Table */}
          <div className="rounded-lg border border-zinc-200 overflow-hidden mb-6 text-xs">
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
              <span className="text-zinc-600">น้ำหนักตัว</span>
              <span className="font-medium text-zinc-900">
                {result.formattedWeight} กิโลกรัม
              </span>
            </div>
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
              <span className="text-zinc-600">ส่วนสูง</span>
              <span className="font-medium text-zinc-900">
                {result.formattedHeight} ซม. ({result.heightInMeters?.toFixed(2)} ม.)
              </span>
            </div>
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50">
              <span className="text-zinc-600">สูตรการคำนวณ</span>
              <span className="font-mono text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded text-[11px]">
                {result.formattedWeight} / ({result.heightInMeters?.toFixed(2)})²
              </span>
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <button
              type="button"
              onClick={handleClearAll}
              className="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer"
            >
              คำนวณค่า BMI อื่นต่อ
            </button>
            <Link
              href="/"
              className="w-full text-center py-2 px-4 border border-zinc-200 hover:bg-zinc-50 text-zinc-700 text-xs font-medium rounded-lg transition-colors"
            >
              กลับสู่หน้าหลัก
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
