"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { FRONTEND_ROUTES, navigateTo } from "@/lib/router";
import { studentApi, majorApi, authStorage } from "@/lib/api";
import Modal from "@/components/Modal";

export default function StudentsPage() {
  const router = useRouter();

  // State ข้อมูล
  const [students, setStudents] = useState([]);
  const [majors, setMajors] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // State ค้นหาและตัวกรอง
  const [searchTerm, setSearchTerm] = useState("");
  const [selectedMajor, setSelectedMajor] = useState("");

  // State Modal (Add / Edit)
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    StudentID: "",
    Student_Name: "",
    Student_Surname: "",
    Student_Website: "",
    major_code: "",
  });
  const [formError, setFormError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  // State Modal (Delete Confirm)
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [studentToDelete, setStudentToDelete] = useState(null);

  // Notification Toast
  const [toast, setToast] = useState(null);

  const showToast = (message, type = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  // โหลดข้อมูลนักศึกษาและสาขา
  useEffect(() => {
    if (!authStorage.isAuthenticated()) {
      navigateTo(router, FRONTEND_ROUTES.LOGIN);
      return;
    }

    let isMounted = true;
    const fetchData = async () => {
      try {
        const [majorsRes, studentsRes] = await Promise.all([
          majorApi.getAll(),
          studentApi.getAll({ search: searchTerm, major_code: selectedMajor }),
        ]);

        if (isMounted) {
          if (majorsRes.success) setMajors(majorsRes.data || []);
          if (studentsRes.success) setStudents(studentsRes.data || []);
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

    fetchData();

    return () => {
      isMounted = false;
    };
  }, [router, searchTerm, selectedMajor]);

  const reloadStudents = async () => {
    setIsLoading(true);
    try {
      const res = await studentApi.getAll({
        search: searchTerm,
        major_code: selectedMajor,
      });
      if (res.success) setStudents(res.data || []);
    } catch (err) {
      showToast(err.message, "error");
    } finally {
      setIsLoading(false);
    }
  };

  // เปิด Modal เพิ่มข้อมูล
  const handleOpenAddModal = () => {
    setIsEditing(false);
    setFormData({
      StudentID: "",
      Student_Name: "",
      Student_Surname: "",
      Student_Website: "",
      major_code: majors.length > 0 ? majors[0].major_code : "",
    });
    setFormError("");
    setIsFormModalOpen(true);
  };

  // เปิด Modal แก้ไขข้อมูล
  const handleOpenEditModal = (student) => {
    setIsEditing(true);
    setFormData({
      StudentID: student.StudentID,
      Student_Name: student.Student_Name,
      Student_Surname: student.Student_Surname,
      Student_Website: student.Student_Website || "",
      major_code: student.major_code || "",
    });
    setFormError("");
    setIsFormModalOpen(true);
  };

  // บันทึกข้อมูล
  const handleFormSubmit = async (e) => {
    e.preventDefault();
    setFormError("");

    if (!formData.StudentID.trim() || !formData.Student_Name.trim() || !formData.Student_Surname.trim()) {
      setFormError("กรุณากรอกรหัส, ชื่อ และนามสกุล");
      return;
    }

    setIsSubmitting(true);
    try {
      if (isEditing) {
        await studentApi.update(formData.StudentID, formData);
        showToast("บันทึกการแก้ไขเรียบร้อยแล้ว");
      } else {
        await studentApi.create(formData);
        showToast("เพิ่มข้อมูลนักศึกษาเรียบร้อยแล้ว");
      }
      setIsFormModalOpen(false);
      reloadStudents();
    } catch (err) {
      setFormError(err.message || "เกิดข้อผิดพลาดในการบันทึก");
    } finally {
      setIsSubmitting(false);
    }
  };

  // ลบข้อมูล
  const handleConfirmDelete = async () => {
    if (!studentToDelete) return;
    setIsSubmitting(true);
    try {
      await studentApi.delete(studentToDelete.StudentID);
      showToast("ลบข้อมูลเรียบร้อยแล้ว");
      setIsDeleteModalOpen(false);
      setStudentToDelete(null);
      reloadStudents();
    } catch (err) {
      showToast(err.message, "error");
    } finally {
      setIsSubmitting(false);
    }
  };

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
            ข้อมูลนักศึกษา
          </h1>
          <p className="text-xs text-zinc-500 mt-0.5">
            พบข้อมูลทั้งหมด {students.length} รายการ
          </p>
        </div>

        <button
          onClick={handleOpenAddModal}
          className="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors cursor-pointer"
        >
          + เพิ่มนักศึกษา
        </button>
      </div>

      {/* Filter / Search Bar */}
      <div className="flex flex-col sm:flex-row gap-2 mb-4">
        <input
          type="text"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          placeholder="ค้นหารหัส, ชื่อ หรือนามสกุล..."
          className="flex-1 px-3 py-1.5 rounded-md border border-zinc-300 text-xs focus:border-zinc-900 focus:outline-none bg-white"
        />

        <select
          value={selectedMajor}
          onChange={(e) => setSelectedMajor(e.target.value)}
          className="px-3 py-1.5 rounded-md border border-zinc-300 text-xs focus:border-zinc-900 focus:outline-none bg-white cursor-pointer"
        >
          <option value="">ทุกสาขาวิชา</option>
          {majors.map((m) => (
            <option key={m.major_code} value={m.major_code}>
              {m.major_code} - {m.major_name}
            </option>
          ))}
        </select>

        {(searchTerm || selectedMajor) && (
          <button
            type="button"
            onClick={() => {
              setSearchTerm("");
              setSelectedMajor("");
            }}
            className="px-2.5 py-1.5 text-xs text-zinc-600 hover:text-zinc-900 border border-zinc-200 rounded-md bg-white cursor-pointer"
          >
            ล้างตัวกรอง
          </button>
        )}
      </div>

      {/* Table */}
      <div className="bg-white rounded-lg border border-zinc-200 overflow-hidden shadow-2xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-medium">
              <tr>
                <th className="py-2.5 px-3 w-10 text-center">#</th>
                <th className="py-2.5 px-3">รหัสนักศึกษา</th>
                <th className="py-2.5 px-3">ชื่อ - นามสกุล</th>
                <th className="py-2.5 px-3">สาขาวิชา</th>
                <th className="py-2.5 px-3">เว็บไซต์</th>
                <th className="py-2.5 px-3 text-right">จัดการ</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-100">
              {isLoading ? (
                <tr>
                  <td colSpan={6} className="py-8 text-center text-zinc-400">
                    กำลังโหลดข้อมูล...
                  </td>
                </tr>
              ) : students.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-8 text-center text-zinc-400">
                    ไม่พบข้อมูลที่ค้นหา
                  </td>
                </tr>
              ) : (
                students.map((student, index) => (
                  <tr key={student.StudentID} className="hover:bg-zinc-50/50">
                    <td className="py-2 px-3 text-center text-zinc-400 font-mono text-[11px]">
                      {index + 1}
                    </td>
                    <td className="py-2 px-3 font-mono font-medium text-zinc-900">
                      {student.StudentID}
                    </td>
                    <td className="py-2 px-3 text-zinc-800">
                      {student.Student_Name} {student.Student_Surname}
                    </td>
                    <td className="py-2 px-3 text-zinc-600">
                      {student.major_name || student.major_code || "-"}
                    </td>
                    <td className="py-2 px-3 text-zinc-500">
                      {student.Student_Website ? (
                        <a
                          href={
                            student.Student_Website.startsWith("http")
                              ? student.Student_Website
                              : `https://${student.Student_Website}`
                          }
                          target="_blank"
                          rel="noreferrer"
                          className="text-zinc-600 hover:text-zinc-900 hover:underline"
                        >
                          {student.Student_Website}
                        </a>
                      ) : (
                        "-"
                      )}
                    </td>
                    <td className="py-2 px-3 text-right">
                      <div className="inline-flex items-center gap-1.5">
                        <button
                          onClick={() => handleOpenEditModal(student)}
                          className="px-2 py-0.5 rounded-md border border-zinc-300 hover:border-zinc-400 hover:bg-zinc-50 text-zinc-700 text-xs font-medium cursor-pointer transition-colors"
                        >
                          แก้ไข
                        </button>
                        <button
                          onClick={() => {
                            setStudentToDelete(student);
                            setIsDeleteModalOpen(true);
                          }}
                          className="px-2 py-0.5 rounded-md border border-rose-200 hover:border-rose-300 hover:bg-rose-50 text-rose-600 text-xs font-medium cursor-pointer transition-colors"
                        >
                          ลบ
                        </button>
                      </div>
                    </td>
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
        title={isEditing ? "แก้ไขข้อมูลนักศึกษา" : "เพิ่มนักศึกษา"}
      >
        <form onSubmit={handleFormSubmit} className="space-y-3 text-xs">
          {formError && (
            <div className="p-2 rounded bg-rose-50 border border-rose-200 text-rose-700">
              {formError}
            </div>
          )}

          <div>
            <label className="block text-zinc-700 mb-1 font-medium">รหัสนักศึกษา</label>
            <input
              type="text"
              value={formData.StudentID}
              onChange={(e) => setFormData({ ...formData, StudentID: e.target.value })}
              placeholder="เช่น 6601001"
              disabled={isEditing || isSubmitting}
              className="w-full px-2.5 py-1.5 rounded-md border border-zinc-300 focus:border-zinc-900 focus:outline-none disabled:bg-zinc-100"
            />
          </div>

          <div className="grid grid-cols-2 gap-2">
            <div>
              <label className="block text-zinc-700 mb-1 font-medium">ชื่อ</label>
              <input
                type="text"
                value={formData.Student_Name}
                onChange={(e) => setFormData({ ...formData, Student_Name: e.target.value })}
                placeholder="ชื่อ"
                disabled={isSubmitting}
                className="w-full px-2.5 py-1.5 rounded-md border border-zinc-300 focus:border-zinc-900 focus:outline-none"
              />
            </div>
            <div>
              <label className="block text-zinc-700 mb-1 font-medium">นามสกุล</label>
              <input
                type="text"
                value={formData.Student_Surname}
                onChange={(e) => setFormData({ ...formData, Student_Surname: e.target.value })}
                placeholder="นามสกุล"
                disabled={isSubmitting}
                className="w-full px-2.5 py-1.5 rounded-md border border-zinc-300 focus:border-zinc-900 focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label className="block text-zinc-700 mb-1 font-medium">สาขาวิชา</label>
            <select
              value={formData.major_code}
              onChange={(e) => setFormData({ ...formData, major_code: e.target.value })}
              disabled={isSubmitting}
              className="w-full px-2.5 py-1.5 rounded-md border border-zinc-300 focus:border-zinc-900 focus:outline-none bg-white"
            >
              <option value="">เลือกสาขาวิชา</option>
              {majors.map((m) => (
                <option key={m.major_code} value={m.major_code}>
                  {m.major_code} - {m.major_name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-zinc-700 mb-1 font-medium">เว็บไซต์ / ลิงก์</label>
            <input
              type="text"
              value={formData.Student_Website}
              onChange={(e) => setFormData({ ...formData, Student_Website: e.target.value })}
              placeholder="https://..."
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
        title="ยืนยันการลบ"
      >
        <div className="space-y-3 text-xs">
          <p className="text-zinc-600">
            ต้องการลบข้อมูลนักศึกษา <b>{studentToDelete?.StudentID} - {studentToDelete?.Student_Name} {studentToDelete?.Student_Surname}</b> หรือไม่?
          </p>
          <div className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
            <button
              type="button"
              onClick={() => setIsDeleteModalOpen(false)}
              disabled={isSubmitting}
              className="px-3 py-1.5 rounded-md border border-zinc-200 text-zinc-600 hover:bg-zinc-50 cursor-pointer"
            >
              ยกเลิก
            </button>
            <button
              type="button"
              onClick={handleConfirmDelete}
              disabled={isSubmitting}
              className="px-3 py-1.5 rounded-md bg-rose-600 hover:bg-rose-700 text-white font-medium cursor-pointer disabled:opacity-50"
            >
              {isSubmitting ? "กำลังลบ..." : "ลบข้อมูล"}
            </button>
          </div>
        </div>
      </Modal>
    </div>
  );
}
