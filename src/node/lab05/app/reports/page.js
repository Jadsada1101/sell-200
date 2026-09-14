"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { FRONTEND_ROUTES, navigateTo } from "@/lib/router";
import { majorApi, reportApi, authStorage } from "@/lib/api";

export default function ReportsPage() {
  const router = useRouter();
  const [majors, setMajors] = useState([]);
  const [selectedMajor, setSelectedMajor] = useState("");
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!authStorage.isAuthenticated()) {
      navigateTo(router, FRONTEND_ROUTES.LOGIN);
      return;
    }

    let isMounted = true;
    const loadMajors = async () => {
      try {
        const res = await majorApi.getAll();
        if (isMounted && res.success) {
          setMajors(res.data || []);
        }
      } catch (err) {
        console.error("Error loading majors:", err);
      } finally {
        if (isMounted) setIsLoading(false);
      }
    };

    loadMajors();

    return () => {
      isMounted = false;
    };
  }, [router]);

  const handleOpenPdf = () => {
    const url = reportApi.getPdfDownloadUrl(selectedMajor);
    window.open(url, "_blank");
  };

  return (
    <div className="max-w-xl mx-auto px-4 py-10 w-full">
      <div className="mb-6">
        <h1 className="text-lg font-semibold text-zinc-900">
          ออกรายงานข้อมูลนักศึกษา (PDF)
        </h1>
        <p className="text-xs text-zinc-500 mt-0.5">
          พิมพ์และดาวน์โหลดรายงานสรุปรายชื่อนักศึกษา
        </p>
      </div>

      <div className="bg-white rounded-lg border border-zinc-200 p-5 shadow-2xs space-y-4">
        <div>
          <label className="block text-xs font-medium text-zinc-700 mb-1.5">
            เลือกสาขาวิชาที่ต้องการออกรายงาน
          </label>
          <select
            value={selectedMajor}
            onChange={(e) => setSelectedMajor(e.target.value)}
            disabled={isLoading}
            className="w-full px-3 py-2 rounded-md border border-zinc-300 text-xs focus:border-zinc-900 focus:outline-none bg-white cursor-pointer"
          >
            <option value="">ทั้งหมด (ทุกสาขาวิชา)</option>
            {majors.map((m) => (
              <option key={m.major_code} value={m.major_code}>
                {m.major_code} - {m.major_name} ({m.student_count || 0} คน)
              </option>
            ))}
          </select>
        </div>

        <div className="flex gap-2 pt-2 border-t border-zinc-100">
          <button
            type="button"
            onClick={handleOpenPdf}
            disabled={isLoading}
            className="flex-1 py-2 px-3 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors cursor-pointer disabled:opacity-50"
          >
            เปิดดู PDF
          </button>
          <a
            href={reportApi.getPdfDownloadUrl(selectedMajor)}
            download="student_report.pdf"
            className="flex-1 py-2 px-3 text-center rounded-md border border-zinc-300 hover:bg-zinc-50 text-zinc-700 text-xs font-medium transition-colors"
          >
            ดาวน์โหลดไฟล์
          </a>
        </div>
      </div>
    </div>
  );
}
