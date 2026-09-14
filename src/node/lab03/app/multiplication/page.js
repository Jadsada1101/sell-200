"use client";

import { useState } from "react";
import Link from "next/link";

export default function MultiplicationPage() {
  const [num, setNum] = useState("");
  const [result, setResult] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // ส่งข้อมูลแบบ POST ไปที่ Route Handler: /api/multiplication
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const res = await fetch("/api/multiplication", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          num: Number(num),
        }),
      });

      const data = await res.json();
      if (data.success) {
        setResult(data);
      }
    } catch (error) {
      console.error("Error generating multiplication table:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleReset = () => {
    setResult(null);
  };

  const handleClearAll = () => {
    setNum("");
    setResult(null);
  };

  return (
    <div className="w-full max-w-md mx-auto my-auto py-6">
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
            <span>เลือกแม่สูตรคูณใหม่</span>
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
              โปรแกรมที่ 03 &bull; POST /api/multiplication
            </span>
            <h1 className="text-xl font-bold text-zinc-900">โปรแกรมแม่สูตรคูณ</h1>
            <p className="text-xs text-zinc-500 mt-1">
              แสดงตารางสูตรคูณตั้งแต่ลำดับที่ 1 ถึง 12 ตามแม่ที่ต้องการ
            </p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label
                htmlFor="num"
                className="block text-xs font-medium text-zinc-700 mb-1.5"
              >
                ป้อนแม่สูตรคูณ (ตัวเลข)
              </label>
              <input
                type="number"
                id="num"
                min="1"
                max="1000"
                value={num}
                onChange={(e) => setNum(e.target.value)}
                placeholder="เช่น 2, 9, 12, 25"
                required
                className="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
              />
            </div>

            <div className="pt-2">
              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 disabled:bg-zinc-400 text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer"
              >
                {isLoading ? "กำลังสร้างตาราง..." : "แสดงตารางสูตรคูณ (POST)"}
              </button>
            </div>
          </form>
        </div>
      ) : (
        /* Result Card */
        <div className="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
          <div className="text-center pb-6 border-b border-zinc-100">
            <span className="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
              ตารางสูตรคูณ
            </span>
            <h1 className="text-xl font-bold text-zinc-900">
              แม่สูตรคูณ {result.baseNumber}
            </h1>
            <p className="text-xs text-zinc-500 mt-1">
              ผลคูณตั้งแต่ลำดับที่ 1 ถึง 12
            </p>
          </div>

          {/* Table List */}
          <div className="rounded-lg border border-zinc-200 overflow-hidden my-6">
            {result.rows.map((row) => (
              <div
                key={row.step}
                className={`flex justify-between items-center py-2 px-3 text-xs ${
                  row.step % 2 === 0 ? "bg-zinc-50/70" : "bg-white"
                } border-b border-zinc-100 last:border-0`}
              >
                <span className="text-zinc-600 font-mono">
                  {result.baseNumber} &times; {row.step}
                </span>
                <span className="font-bold text-zinc-900 font-mono">
                  = {row.result}
                </span>
              </div>
            ))}
          </div>

          <div className="flex flex-col gap-2">
            <button
              type="button"
              onClick={handleClearAll}
              className="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer"
            >
              เลือกแม่สูตรคูณอื่น
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
