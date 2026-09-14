"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { FRONTEND_ROUTES, navigateTo } from "@/lib/router";
import { majorApi, authStorage } from "@/lib/api";
import Modal from "@/components/Modal";

export default function MajorsPage() {
  const router = useRouter();

  const [majors, setMajors] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [currentUser, setCurrentUser] = useState(null);

  // State Modal (Add / Edit)
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    major_code: "",
    major_name: "",
  });
  const [formError, setFormError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  // State Modal (Delete Confirm)
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [majorToDelete, setMajorToDelete] = useState(null);

  // Notification Toast
  const [toast, setToast] = useState(null);

  const showToast = (message, type = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  useEffect(() => {
    if (!authStorage.isAuthenticated()) {
      navigateTo(router, FRONTEND_ROUTES.LOGIN);
      return;
    }

    let isMounted = true;
    const fetchMajors = async () => {
      try {
        const res = await majorApi.getAll();
        if (isMounted) {
          if (res.success) setMajors(res.data || []);
          setCurrentUser(authStorage.getUser());
        }
      } catch (err) {
        if (isMounted) {
          if (err.message.includes("401")) {
            navigateTo(router, FRONTEND_ROUTES.LOGIN);
          } else {
            showToast(err.message, "error");
          }
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchMajors();

    return () => {
      isMounted = false;
    };
  }, [router]);

  const reloadMajors = async () => {
    setIsLoading(true);
    try {
      const res = await majorApi.getAll();
      if (res.success) setMajors(res.data || []);
    } catch (err) {
      showToast(err.message, "error");
    } finally {
      setIsLoading(false);
    }
  };

  const handleOpenAddModal = () => {
    setIsEditing(false);
    setFormData({ major_code: "", major_name: "" });
    setFormError("");
    setIsFormModalOpen(true);
  };

  const handleOpenEditModal = (major) => {
    setIsEditing(true);
    setFormData({
      major_code: major.major_code,
      major_name: major.major_name,
    });
    setFormError("");
    setIsFormModalOpen(true);
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    setFormError("");

    if (!formData.major_code.trim() || !formData.major_name.trim()) {
      setFormError("กรุณากรอกรหัสสาขาและชื่อสาขาวิชา");
      return;
    }

    setIsSubmitting(true);
    try {
      if (isEditing) {
        await majorApi.update(formData.major_code, formData);
        showToast("บันทึกการแก้ไขเรียบร้อยแล้ว");
      } else {
        await majorApi.create(formData);
        showToast("เพิ่มสาขาวิชาเรียบร้อยแล้ว");
      }
      setIsFormModalOpen(false);
      reloadMajors();
    } catch (err) {
      setFormError(err.message || "เกิดข้อผิดพลาดในการบันทึก");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleConfirmDelete = async () => {
    if (!majorToDelete) return;
    setIsSubmitting(true);
    try {
      await majorApi.delete(majorToDelete.major_code);
      showToast("ลบสาขาวิชาเรียบร้อยแล้ว");
      setIsDeleteModalOpen(false);
      setMajorToDelete(null);
      reloadMajors();
    } catch (err) {
      showToast(err.message || "เกิดข้อผิดพลาดในการลบ", "error");
    } finally {
      setIsSubmitting(false);
    }
  };

  const isAdmin = currentUser?.role === "admin";

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 w-full">
      {/* Toast */}
      {toast && (
        <div className="fixed bottom-5 right-5 z-50 px-3.5 py-2 rounded-md shadow-md border text-xs font-medium bg-white border-zinc-200">
          <span className={toast.type === "error" ? "text-rose-600" : "text-emerald-700"}>
            {toast.message}
          </span>
        </div>
      )}

      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
          <h1 className="text-lg font-semibold text-zinc-900">
            สาขาวิชา
          </h1>
          <p className="text-xs text-zinc-500 mt-0.5">
            ทั้งหมด {majors.length} สาขา
          </p>
        </div>

        {isAdmin ? (
          <button
            onClick={handleOpenAddModal}
            className="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors cursor-pointer"
          >
            + เพิ่มสาขาวิชา
          </button>
        ) : (
          <span className="text-xs text-zinc-500 bg-zinc-100 border border-zinc-200 px-2.5 py-1 rounded-md">
            สิทธิ์ Staff (ดูข้อมูลได้อย่างเดียว)
          </span>
        )}
      </div>

      {/* Table */}
      <div className="bg-white rounded-lg border border-zinc-200 overflow-hidden shadow-2xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-medium">
              <tr>
                <th className="py-2.5 px-3 w-10 text-center">#</th>
                <th className="py-2.5 px-3">รหัสสาขา</th>
                <th className="py-2.5 px-3">ชื่อสาขาวิชา</th>
                <th className="py-2.5 px-3 text-center">นักศึกษาในสังกัด</th>
                {isAdmin && <th className="py-2.5 px-3 text-right">จัดการ</th>}
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-100">
              {isLoading ? (
                <tr>
                  <td colSpan={isAdmin ? 5 : 4} className="py-8 text-center text-zinc-400">
                    กำลังโหลดข้อมูล...
                  </td>
                </tr>
              ) : majors.length === 0 ? (
                <tr>
                  <td colSpan={isAdmin ? 5 : 4} className="py-8 text-center text-zinc-400">
                    ยังไม่มีข้อมูลสาขาวิชา
                  </td>
                </tr>
              ) : (
                majors.map((major, index) => (
                  <tr key={major.major_code} className="hover:bg-zinc-50/50">
                    <td className="py-2 px-3 text-center text-zinc-400 font-mono text-[11px]">
                      {index + 1}
                    </td>
                    <td className="py-2 px-3 font-mono font-medium text-zinc-900">
                      {major.major_code}
                    </td>
                    <td className="py-2 px-3 text-zinc-800">
                      {major.major_name}
                    </td>
                    <td className="py-2 px-3 text-center font-mono text-zinc-600">
                      {major.student_count || 0} คน
                    </td>
                    {isAdmin && (
                      <td className="py-2 px-3 text-right">
                        <div className="inline-flex items-center gap-1.5">
                          <button
                            onClick={() => handleOpenEditModal(major)}
                            className="px-2 py-0.5 rounded-md border border-zinc-300 hover:border-zinc-400 hover:bg-zinc-50 text-zinc-700 text-xs font-medium cursor-pointer transition-colors"
                          >
                            แก้ไข
                          </button>
                          <button
                            onClick={() => {
                              setMajorToDelete(major);
                              setIsDeleteModalOpen(true);
                            }}
                            className="px-2 py-0.5 rounded-md border border-rose-200 hover:border-rose-300 hover:bg-rose-50 text-rose-600 text-xs font-medium cursor-pointer transition-colors"
                          >
                            ลบ
                          </button>
                        </div>
                      </td>
                    )}
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Add / Edit */}
      <Modal
        isOpen={isFormModalOpen}
        onClose={() => setIsFormModalOpen(false)}
        title={isEditing ? "แก้ไขสาขาวิชา" : "เพิ่มสาขาวิชา"}
      >
        <form onSubmit={handleFormSubmit} className="space-y-3 text-xs">
          {formError && (
            <div className="p-2 rounded bg-rose-50 border border-rose-200 text-rose-700">
              {formError}
            </div>
          )}

          <div>
            <label className="block text-zinc-700 mb-1 font-medium">รหัสสาขาวิชา</label>
            <input
              type="text"
              value={formData.major_code}
              onChange={(e) => setFormData({ ...formData, major_code: e.target.value })}
              placeholder="เช่น CS, IT"
              disabled={isEditing || isSubmitting}
              className="w-full px-2.5 py-1.5 rounded-md border border-zinc-300 focus:border-zinc-900 focus:outline-none disabled:bg-zinc-100"
            />
          </div>

          <div>
            <label className="block text-zinc-700 mb-1 font-medium">ชื่อสาขาวิชา</label>
            <input
              type="text"
              value={formData.major_name}
              onChange={(e) => setFormData({ ...formData, major_name: e.target.value })}
              placeholder="เช่น วิทยาการคอมพิวเตอร์"
              disabled={isSubmitting}
              className="w-full px-2.5 py-1.5 rounded-md border border-zinc-300 focus:border-zinc-900 focus:outline-none"
            />
          </div>

          <div className="flex justify-end gap-2 pt-3 border-t border-zinc-100">
            <button
              type="button"
              onClick={() => setIsFormModalOpen(false)}
              disabled={isSubmitting}
              className="px-3 py-1.5 rounded-md border border-zinc-200 text-zinc-600 hover:bg-zinc-50 cursor-pointer"
            >
              ยกเลิก
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-3 py-1.5 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white font-medium cursor-pointer disabled:opacity-50"
            >
              {isSubmitting ? "กำลังบันทึก..." : "บันทึก"}
            </button>
          </div>
        </form>
      </Modal>

      {/* Modal Delete */}
      <Modal
        isOpen={isDeleteModalOpen}
        onClose={() => setIsDeleteModalOpen(false)}
        title="ยืนยันการลบสาขาวิชา"
      >
        <div className="space-y-3 text-xs">
          {majorToDelete?.student_count > 0 ? (
            <p className="text-amber-800 bg-amber-50 p-3 rounded-md border border-amber-200">
              ไม่สามารถลบสาขา <b>{majorToDelete.major_name}</b> ได้ เนื่องจากยังมีนักศึกษาจำนวน <b>{majorToDelete.student_count} คน</b> สังกัดอยู่
            </p>
          ) : (
            <p className="text-zinc-600">
              ต้องการลบสาขาวิชา <b>{majorToDelete?.major_code} - {majorToDelete?.major_name}</b> หรือไม่?
            </p>
          )}

          <div className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
            <button
              type="button"
              onClick={() => setIsDeleteModalOpen(false)}
              disabled={isSubmitting}
              className="px-3 py-1.5 rounded-md border border-zinc-200 text-zinc-600 hover:bg-zinc-50 cursor-pointer"
            >
              {majorToDelete?.student_count > 0 ? "ปิด" : "ยกเลิก"}
            </button>
            {(!majorToDelete || majorToDelete.student_count === 0) && (
              <button
                type="button"
                onClick={handleConfirmDelete}
                disabled={isSubmitting}
                className="px-3 py-1.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white font-medium cursor-pointer disabled:opacity-50"
              >
                {isSubmitting ? "กำลังลบ..." : "ลบสาขา"}
              </button>
            )}
          </div>
        </div>
      </Modal>
    </div>
  );
}
