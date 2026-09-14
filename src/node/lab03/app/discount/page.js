"use client";

import { useState } from "react";
import Link from "next/link";

export default function DiscountPage() {
  const [customerName, setCustomerName] = useState("");
  const [price, setPrice] = useState("");
  const [result, setResult] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // ส่งข้อมูลแบบ POST ไปที่ Route Handler: /api/discount
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const res = await fetch("/api/discount", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          customerName,
          price: Number(price),
        }),
      });

      const data = await res.json();
      if (data.success) {
        setResult(data);
      }
    } catch (error) {
      console.error("Error calculating discount:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleReset = () => {
    setResult(null);
  };

  const handleClearAll = () => {
    setCustomerName("");
    setPrice("");
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
              โปรแกรมที่ 02 &bull; POST /api/discount
            </span>
            <h1 className="text-xl font-bold text-zinc-900">คำนวณส่วนลดสินค้า</h1>
            <p className="text-xs text-zinc-500 mt-1">
              คำนวณมูลค่าส่วนลดและราคาสุทธิที่ต้องชำระตามขั้นราคาสินค้า
            </p>
          </div>

          {/* Discount Criteria Table */}
          <div className="mb-6">
            <h2 className="text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">
              เกณฑ์การให้ส่วนลด
            </h2>
            <div className="overflow-hidden rounded-lg border border-zinc-200">
              <table className="w-full text-xs text-left">
                <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-medium">
                  <tr>
                    <th className="py-2.5 px-3.5">ราคาสินค้า (บาท)</th>
                    <th className="py-2.5 px-3.5 text-right">อัตราส่วนลด</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-100 text-zinc-700">
                  <tr>
                    <td className="py-2 px-3.5">1 - 5,000 บาท</td>
                    <td className="py-2 px-3.5 text-right font-medium text-zinc-400">
                      0% (ไม่มีส่วนลด)
                    </td>
                  </tr>
                  <tr>
                    <td className="py-2 px-3.5">5,001 - 10,000 บาท</td>
                    <td className="py-2 px-3.5 text-right font-semibold text-zinc-900">
                      3%
                    </td>
                  </tr>
                  <tr>
                    <td className="py-2 px-3.5">มากกว่า 10,000 บาท ขึ้นไป</td>
                    <td className="py-2 px-3.5 text-right font-semibold text-emerald-700">
                      5%
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
                htmlFor="customerName"
                className="block text-xs font-medium text-zinc-700 mb-1.5"
              >
                ชื่อลูกค้า / ผู้ซื้อ
              </label>
              <input
                type="text"
                id="customerName"
                value={customerName}
                onChange={(e) => setCustomerName(e.target.value)}
                placeholder="เช่น นายวิทยา หรือ ลูกค้าทั่วไป"
                required
                className="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
              />
            </div>

            <div>
              <label
                htmlFor="price"
                className="block text-xs font-medium text-zinc-700 mb-1.5"
              >
                ราคาสินค้า (บาท)
              </label>
              <div className="relative">
                <input
                  type="number"
                  step="any"
                  min="0"
                  id="price"
                  value={price}
                  onChange={(e) => setPrice(e.target.value)}
                  placeholder="เช่น 7500"
                  required
                  className="w-full px-3.5 py-2.5 pr-12 rounded-lg border border-zinc-300 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-transparent transition-all"
                />
                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-zinc-400">
                  บาท
                </div>
              </div>
            </div>

            <div className="pt-2">
              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 disabled:bg-zinc-400 text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer"
              >
                {isLoading ? "กำลังประมวลผล..." : "คำนวณส่วนลดและยอดสุทธิ (POST)"}
              </button>
            </div>
          </form>
        </div>
      ) : (
        /* Result Card */
        <div className="bg-white rounded-xl border border-zinc-200 shadow-sm p-6 sm:p-8">
          <div className="text-center pb-6 border-b border-zinc-100">
            <span className="inline-block px-2 py-0.5 rounded bg-zinc-100 text-zinc-600 text-xs font-medium mb-2">
              ใบสรุปยอดคำนวณ
            </span>
            <h1 className="text-xl font-bold text-zinc-900">สรุปการคำนวณส่วนลด</h1>
            <p className="text-xs text-zinc-500 mt-1">
              ชื่อลูกค้า:{" "}
              <span className="font-medium text-zinc-800">
                {result.customerName}
              </span>
            </p>
          </div>

          {/* Receipt Table */}
          <div className="rounded-lg border border-zinc-200 overflow-hidden my-6 text-xs">
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
              <span className="text-zinc-600">ราคาสินค้าปกติ</span>
              <span className="font-medium text-zinc-900">
                {result.formattedPrice} บาท
              </span>
            </div>
            <div className="flex justify-between items-center py-2.5 px-3.5 bg-zinc-50/50 border-b border-zinc-200">
              <span className="text-zinc-600">
                อัตราส่วนลด ({result.discountRate}%)
              </span>
              <span className="font-medium text-emerald-700">
                -{result.formattedDiscount} บาท
              </span>
            </div>
            <div className="flex justify-between items-center py-3.5 px-3.5 bg-zinc-50 font-bold text-sm text-zinc-900">
              <span>ยอดเงินสุทธิที่ต้องชำระ</span>
              <span>{result.formattedNetPrice} บาท</span>
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <button
              type="button"
              onClick={handleClearAll}
              className="w-full text-center py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer"
            >
              คำนวณรายการอื่นต่อ
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
