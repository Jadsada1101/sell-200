"use client";

import { useState } from "react";
import Link from "next/link";

export default function GradePage() {
  const [studentName, setStudentName] = useState("");
  const [midtermScore, setMidtermScore] = useState("");
  const [finalScore, setFinalScore] = useState("");
  const [result, setResult] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // ส่งข้อมูลแบบ POST ไปที่ Route Handler: /api/score
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const res = await fetch("/api/score", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          studentName,
          midtermScore: Number(midtermScore),
          finalScore: Number(finalScore),
        }),
      });

      const data = await res.json();
      if (data.success) {
        setResult(data);
      }
    } catch (error) {
      console.error("Error evaluating grade:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleReset = () => {
    setResult(null);
  };

  const handleClearAll = () => {
    setStudentName("");
    setMidtermScore("");
    setFinalScore("");
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
              โปรแกรมที่ 01 &bull; POST /api/score
            </span>
            <h1 className="text-xl font-bold text-zinc-900">คำนวณเกรดการเรียน</h1>
            <p className="text-xs text-zinc-500 mt-1">
              กรอกชื่อและคะแนนสอบเพื่อประเมินผลการเรียน (ตัดเกรด A - F)
            </p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label
                htmlFor="studentName"
                className="block text-xs font-medium text-zinc-700 mb-1.5"
              >
                ชื่อ - นามสกุลนักเรียน
              </label>
              <input
                type="text"
                id="studentName"
                value={studentName}
                onChange={(e) => setStudentName(e.target.value)}
                placeholder="เช่น สมชาย ใจดี"
                required
                className="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
              />
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label
                  htmlFor="midtermScore"
                  className="text-xs font-medium text-zinc-700"
                >
                  คะแนนระหว่างภาค
                </label>
                <span className="text-xs text-zinc-400">คะแนนเต็ม 60</span>
              </div>
              <input
                type="number"
                id="midtermScore"
                min="0"
                max="60"
                step="any"
                value={midtermScore}
                onChange={(e) => setMidtermScore(e.target.value)}
                placeholder="0 - 60"
                required
                className="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
              />
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label
                  htmlFor="finalScore"
                  className="text-xs font-medium text-zinc-700"
                >
                  คะแนนปลายภาค
                </label>
                <span className="text-xs text-zinc-400">คะแนนเต็ม 40</span>
              </div>
              <input
                type="number"
                id="finalScore"
                min="0"
                max="40"
                step="any"
                value={finalScore}
                onChange={(e) => setFinalScore(e.target.value)}
                placeholder="0 - 40"
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
                {isLoading ? "กำลังประมวลผล..." : "คำนวณและตัดเกรด (POST)"}
              </button>
            </div>
          </form>
        </div>
      ) : (
        /* Result Card */
        <div className="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
          <div className="text-center pb-6 border-b border-zinc-100">
            <span className="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
              ผลการประเมิน
            </span>
            <h1 className="text-xl font-bold text-zinc-900">ผลการตัดเกรดการเรียน</h1>
            <p className="text-xs text-zinc-500 mt-1">{result.studentName}</p>
          </div>

          <div className="py-6 text-center">
            <div
              className={`inline-flex items-center justify-center w-20 h-20 rounded-xl border ${result.badgeClass} mb-2`}
            >
              <span className="text-4xl font-extrabold tracking-tight">
                {result.grade}
              </span>
            </div>
            <p className="text-xs font-medium text-zinc-600">{result.label}</p>
          </div>

          <div className="rounded-lg border border-zinc-200 overflow-hidden mb-6 text-xs">
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50 border-b border-zinc-200">
              <span className="text-zinc-600">คะแนนระหว่างภาค (เต็ม 60)</span>
              <span className="font-medium text-zinc-900">{result.mid} คะแนน</span>
            </div>
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
              <span className="text-zinc-600">คะแนนปลายภาค (เต็ม 40)</span>
              <span className="font-medium text-zinc-900">{result.final} คะแนน</span>
            </div>
            <div className="flex justify-between items-center py-3 px-3.5 bg-white font-semibold">
              <span className="text-zinc-900 text-sm">คะแนนรวมทั้งหมด</span>
              <span className="text-zinc-900 text-sm">{result.total} / 100</span>
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <button
              type="button"
              onClick={handleClearAll}
              className="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer"
            >
              คำนวณคะแนนอื่นต่อ
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
