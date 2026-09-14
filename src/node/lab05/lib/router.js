/**
 * lib/router.js - รวมเส้นทาง (Routing System) สำหรับ Lab05
 * ใช้สำหรับจัดการ Route ทั้งฝั่ง Frontend และ Backend อย่างเป็นระบบตามหลัก Junior Clean Code
 */

// เส้นทางหน้าเว็บฝั่ง Frontend (UI Pages)
export const FRONTEND_ROUTES = {
  HOME: "/",
  LOGIN: "/",
  STUDENTS: "/students",
  MAJORS: "/majors",
  REPORTS: "/reports",
};

// เส้นทาง API ฝั่ง Backend (API Endpoints)
export const API_ROUTES = {
  AUTH_LOGIN: "/api/auth/login",
  AUTH_ME: "/api/auth/me",
  STUDENTS: "/api/students",
  STUDENT_DETAIL: (id) => `/api/students/${id}`,
  MAJORS: "/api/majors",
  MAJOR_DETAIL: (code) => `/api/majors/${code}`,
  REPORTS_PDF: "/api/reports/students-pdf",
};

/**
 * ฟังก์ชันช่วยเปลี่ยนหน้า (Client Navigation Helper)
 * ใช้งานร่วมกับ useRouter() จาก 'next/navigation'
 */
export function navigateTo(router, routePath) {
  if (router && typeof router.push === "function") {
    router.push(routePath);
  }
}

/**
 * รายการเมนูหลักสำหรับแสดงผลใน Navbar
 */
export const NAVIGATION_ITEMS = [
  { label: "จัดการนักศึกษา", href: FRONTEND_ROUTES.STUDENTS, icon: "GraduationCap" },
  { label: "จัดการสาขาวิชา", href: FRONTEND_ROUTES.MAJORS, icon: "BookOpen" },
  { label: "ออกรายงาน PDF", href: FRONTEND_ROUTES.REPORTS, icon: "FileText" },
];
