/**
 * Application Main Logic (Clean & Human-Crafted)
 * Vanilla JS + Tailwind CSS + Axios
 */

// Global State
let currentUser = null;
let majorsData = [];
let studentsData = [];
let currentTab = "students"; // 'students' | 'majors'
let deleteTarget = null; // { type: 'student' | 'major', id: string, name: string }

// -----------------------------------------------------------------
// 1. Toast Notification Helper (Minimalist Dark Toasts)
// -----------------------------------------------------------------
function showToast(message, type = "success") {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");

  const icons = {
    success: `<svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`,
    error: `<svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`,
    info: `<svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
  };

  toast.className = `flex items-center gap-2 px-3.5 py-2.5 rounded-lg bg-zinc-900 text-zinc-100 text-xs font-medium shadow-lg border border-zinc-800 transition-all duration-200 opacity-0 translate-y-1`;
  toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;

  container.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.remove("opacity-0", "translate-y-1");
  });

  setTimeout(() => {
    toast.classList.add("opacity-0", "translate-y-1");
    setTimeout(() => toast.remove(), 200);
  }, 3000);
}

// -----------------------------------------------------------------
// 2. View Switching (Login vs Main App)
// -----------------------------------------------------------------
function showLoginView() {
  document.getElementById("login-view").classList.remove("hidden");
  document.getElementById("app-view").classList.add("hidden");
  document.getElementById("login-username").focus();
}

function showAppView() {
  document.getElementById("login-view").classList.add("hidden");
  document.getElementById("app-view").classList.remove("hidden");

  // แสดงข้อมูล User ใน Navbar
  if (currentUser) {
    document.getElementById("user-fullname").textContent = currentUser.fullname || currentUser.username;
    
    // ตั้งค่าตัวอักษรย่อใน Avatar
    const initial = (currentUser.fullname || currentUser.username || "U").charAt(0).toUpperCase();
    document.getElementById("user-avatar").textContent = initial;

    const roleBadge = document.getElementById("user-role-badge");
    roleBadge.textContent = currentUser.role === "admin" ? "ผู้ดูแลระบบ (Admin)" : "เจ้าหน้าที่ (Staff)";
  }

  // โหลดข้อมูลเริ่มต้น
  loadMajorsList();
  loadStudentsList();
}

// -----------------------------------------------------------------
// 3. Tab Switching (Segmented Control)
// -----------------------------------------------------------------
function switchTab(tabName) {
  currentTab = tabName;
  const btnStudents = document.getElementById("tab-btn-students");
  const btnMajors = document.getElementById("tab-btn-majors");
  const tabStudents = document.getElementById("tab-content-students");
  const tabMajors = document.getElementById("tab-content-majors");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");

  const activeClass = "px-3 py-1.5 rounded-md bg-white text-zinc-900 shadow-xs transition-all font-medium";
  const inactiveClass = "px-3 py-1.5 rounded-md text-zinc-600 hover:text-zinc-900 transition-all";

  if (tabName === "students") {
    btnStudents.className = activeClass;
    btnMajors.className = inactiveClass;
    tabStudents.classList.remove("hidden");
    tabMajors.classList.add("hidden");
    pageTitle.textContent = "รายชื่อนักศึกษา";
    pageSubtitle.textContent = "จัดการข้อมูลนักศึกษา และความสัมพันธ์กับสาขาวิชา";
    loadStudentsList();
  } else {
    btnMajors.className = activeClass;
    btnStudents.className = inactiveClass;
    tabMajors.classList.remove("hidden");
    tabStudents.classList.add("hidden");
    pageTitle.textContent = "ข้อมูลสาขาวิชา";
    pageSubtitle.textContent = "จัดการรายชื่อสาขาวิชาหลักในระบบ (tb_major 1:M tb_student)";
    loadMajorsList();
  }
}

// -----------------------------------------------------------------
// 4. Authentication Logic
// -----------------------------------------------------------------
async function handleLogin(e) {
  e.preventDefault();
  const username = document.getElementById("login-username").value.trim();
  const password = document.getElementById("login-password").value;

  if (!username || !password) {
    showToast("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน", "error");
    return;
  }

  const btnSubmit = document.getElementById("login-submit-btn");
  btnSubmit.disabled = true;
  btnSubmit.textContent = "กำลังตรวจสอบข้อมูล...";

  try {
    const result = await authApi.login(username, password);
    if (result.success) {
      localStorage.setItem("lab04_token", result.token);
      localStorage.setItem("lab04_user", JSON.stringify(result.user));
      currentUser = result.user;
      showToast(`เข้าสู่ระบบสำเร็จ: ${result.user.fullname || result.user.username}`, "success");
      showAppView();
    }
  } catch (error) {
    console.error("Login Error:", error);
    let msg = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    if (error.response?.data?.message) {
      msg = error.response.data.message;
    } else if (error.code === "ERR_NETWORK" || !error.response) {
      msg = "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ Backend (พอร์ต 5001) ได้ กรุณารันเซิร์ฟเวอร์ก่อนใช้งาน";
    }
    showToast(msg, "error");
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.textContent = "เข้าสู่ระบบ";
  }
}

function quickLogin(username, password) {
  document.getElementById("login-username").value = username;
  document.getElementById("login-password").value = password;
  document.getElementById("login-form").dispatchEvent(new Event("submit"));
}

function handleLogout() {
  localStorage.removeItem("lab04_token");
  localStorage.removeItem("lab04_user");
  currentUser = null;
  showToast("ออกจากระบบเรียบร้อย", "info");
  showLoginView();
}

// -----------------------------------------------------------------
// 5. Major Management (tb_major)
// -----------------------------------------------------------------
async function loadMajorsList() {
  try {
    const response = await majorApi.getAll();
    majorsData = response.data || [];
    renderMajorsTable(majorsData);
    populateMajorFilterDropdown(majorsData);
    populateMajorFormSelect(majorsData);
  } catch (error) {
    showToast("ไม่สามารถโหลดข้อมูลสาขาวิชาได้", "error");
  }
}

function renderMajorsTable(majors) {
  const tbody = document.getElementById("majors-table-body");
  const countBadge = document.getElementById("majors-count-badge");
  countBadge.textContent = `${majors.length} สาขาวิชา`;

  if (majors.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="py-10 text-center text-zinc-400 text-xs">ยังไม่มีข้อมูลสาขาวิชา</td></tr>`;
    return;
  }

  tbody.innerHTML = majors.map((m, index) => `
    <tr class="hover:bg-zinc-50/60 transition-colors">
      <td class="py-2.5 px-4 text-center text-zinc-400 text-xs font-mono">${index + 1}</td>
      <td class="py-2.5 px-4">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-zinc-100 text-zinc-800 border border-zinc-200/60">
          ${m.major_code}
        </span>
      </td>
      <td class="py-2.5 px-4 font-medium text-zinc-900">${m.major_name}</td>
      <td class="py-2.5 px-4 text-zinc-500 text-xs">${m.remark || "-"}</td>
      <td class="py-2.5 px-4 text-center">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono text-zinc-600 bg-zinc-100">
          ${m.student_count || 0}
        </span>
      </td>
      <td class="py-2.5 px-4 text-right space-x-1">
        <button onclick="openEditMajorModal('${m.major_code}')" class="p-1 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded transition-colors" title="แก้ไข">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </button>
        <button onclick="confirmDelete('major', '${m.major_code}', '${m.major_name}')" class="p-1 text-zinc-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="ลบ">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </td>
    </tr>
  `).join("");
}

function openAddMajorModal() {
  document.getElementById("major-modal-title").textContent = "เพิ่มสาขาวิชาใหม่";
  document.getElementById("major-form").reset();
  document.getElementById("major-code-input").disabled = false;
  document.getElementById("major-form-mode").value = "create";
  openModal("major-modal");
}

function openEditMajorModal(code) {
  const major = majorsData.find((m) => m.major_code === code);
  if (!major) return;

  document.getElementById("major-modal-title").textContent = `แก้ไขสาขาวิชา (${code})`;
  document.getElementById("major-code-input").value = major.major_code;
  document.getElementById("major-code-input").disabled = true;
  document.getElementById("major-name-input").value = major.major_name;
  document.getElementById("major-remark-input").value = major.remark || "";
  document.getElementById("major-form-mode").value = "edit";
  openModal("major-modal");
}

async function handleSaveMajor(e) {
  e.preventDefault();
  const mode = document.getElementById("major-form-mode").value;
  const major_code = document.getElementById("major-code-input").value.trim().toUpperCase();
  const major_name = document.getElementById("major-name-input").value.trim();
  const remark = document.getElementById("major-remark-input").value.trim();

  if (!major_code || !major_name) {
    showToast("กรุณากรอกรหัสและชื่อสาขาวิชา", "error");
    return;
  }

  try {
    if (mode === "create") {
      await majorApi.create({ major_code, major_name, remark });
      showToast("เพิ่มสาขาวิชาสำเร็จ", "success");
    } else {
      await majorApi.update(major_code, { major_name, remark });
      showToast("แก้ไขข้อมูลสาขาวิชาสำเร็จ", "success");
    }
    closeModal("major-modal");
    loadMajorsList();
  } catch (error) {
    const msg = error.response?.data?.message || "บันทึกข้อมูลไม่สำเร็จ";
    showToast(msg, "error");
  }
}

// -----------------------------------------------------------------
// 6. Student Management (tb_student)
// -----------------------------------------------------------------
async function loadStudentsList() {
  const search = document.getElementById("student-search-input")?.value || "";
  const major_code = document.getElementById("student-major-filter")?.value || "";

  try {
    const response = await studentApi.getAll({ search, major_code });
    studentsData = response.data || [];
    renderStudentsTable(studentsData);
  } catch (error) {
    showToast("ไม่สามารถโหลดข้อมูลนักศึกษาได้", "error");
  }
}

function renderStudentsTable(students) {
  const tbody = document.getElementById("students-table-body");
  const countBadge = document.getElementById("students-count-badge");
  countBadge.textContent = `${students.length} คน`;

  if (students.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="py-12 text-center text-zinc-400 text-xs">ไม่พบข้อมูลนักศึกษา</td></tr>`;
    return;
  }

  tbody.innerHTML = students.map((s, index) => `
    <tr class="hover:bg-zinc-50/60 transition-colors">
      <td class="py-2.5 px-4 text-center text-zinc-400 text-xs font-mono">${index + 1}</td>
      <td class="py-2.5 px-4 font-mono text-zinc-800 text-xs tabular-nums whitespace-nowrap">
        ${s.StudentID}
      </td>
      <td class="py-2.5 px-4 font-medium text-zinc-900">
        ${s.Student_Name} ${s.Student_Surname}
      </td>
      <td class="py-2.5 px-4">
        ${s.major_code ? `
          <button onclick="viewMajorDetails('${s.major_code}')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-zinc-50 hover:bg-zinc-100 text-zinc-700 hover:text-zinc-950 border border-zinc-200/80 transition-all cursor-pointer shadow-2xs group"
            title="คลิกเพื่อดูข้อมูลสาขาวิชา ${s.major_code}">
            <svg class="w-3 h-3 text-zinc-400 group-hover:text-zinc-600 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="font-mono font-semibold">${s.major_code}</span>
            <span class="text-zinc-500 font-normal truncate max-w-[130px]">· ${s.major_name || ''}</span>
          </button>
        ` : `<span class="text-zinc-400 text-xs">-</span>`}
      </td>
      <td class="py-2.5 px-4 text-xs">
        ${s.Student_Website ? `
          <a href="${s.Student_Website.startsWith('http') ? s.Student_Website : 'https://' + s.Student_Website}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-zinc-600 hover:text-zinc-900 underline underline-offset-2 max-w-[200px] truncate font-mono text-[11px]">
            <span class="truncate">${s.Student_Website}</span>
            <svg class="w-3 h-3 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          </a>
        ` : `<span class="text-zinc-300">-</span>`}
      </td>
      <td class="py-2.5 px-4 text-right space-x-1 whitespace-nowrap">
        <button onclick="openEditStudentModal('${s.StudentID}')" class="p-1 text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 rounded transition-colors" title="แก้ไข">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </button>
        <button onclick="confirmDelete('student', '${s.StudentID}', '${s.Student_Name} ${s.Student_Surname}')" class="p-1 text-zinc-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="ลบ">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </td>
    </tr>
  `).join("");
}

function openAddStudentModal() {
  document.getElementById("student-modal-title").textContent = "เพิ่มนักศึกษาใหม่";
  document.getElementById("student-form").reset();
  document.getElementById("student-id-input").disabled = false;
  document.getElementById("student-form-mode").value = "create";
  openModal("student-modal");
}

function openEditStudentModal(studentId) {
  const student = studentsData.find((s) => s.StudentID === studentId);
  if (!student) return;

  document.getElementById("student-modal-title").textContent = `แก้ไขข้อมูลนักศึกษา (${studentId})`;
  document.getElementById("student-id-input").value = student.StudentID;
  document.getElementById("student-id-input").disabled = true;
  document.getElementById("student-name-input").value = student.Student_Name;
  document.getElementById("student-surname-input").value = student.Student_Surname;
  document.getElementById("student-website-input").value = student.Student_Website || "";
  document.getElementById("student-major-select").value = student.major_code || "";
  document.getElementById("student-form-mode").value = "edit";
  openModal("student-modal");
}

async function handleSaveStudent(e) {
  e.preventDefault();
  const mode = document.getElementById("student-form-mode").value;
  const StudentID = document.getElementById("student-id-input").value.trim();
  const Student_Name = document.getElementById("student-name-input").value.trim();
  const Student_Surname = document.getElementById("student-surname-input").value.trim();
  const Student_Website = document.getElementById("student-website-input").value.trim();
  const major_code = document.getElementById("student-major-select").value || null;

  if (!StudentID || !Student_Name || !Student_Surname) {
    showToast("กรุณาระบุรหัสนักศึกษา ชื่อ และนามสกุล", "error");
    return;
  }

  try {
    if (mode === "create") {
      await studentApi.create({ StudentID, Student_Name, Student_Surname, Student_Website, major_code });
      showToast("เพิ่มนักศึกษาสำเร็จ", "success");
    } else {
      await studentApi.update(StudentID, { Student_Name, Student_Surname, Student_Website, major_code });
      showToast("แก้ไขข้อมูลนักศึกษาสำเร็จ", "success");
    }
    closeModal("student-modal");
    loadStudentsList();
    loadMajorsList();
  } catch (error) {
    const msg = error.response?.data?.message || "บันทึกข้อมูลไม่สำเร็จ";
    showToast(msg, "error");
  }
}

// -----------------------------------------------------------------
// 7. Search & Filter & PDF Report
// -----------------------------------------------------------------
let searchDebounceTimeout = null;
function handleStudentSearch() {
  clearTimeout(searchDebounceTimeout);
  searchDebounceTimeout = setTimeout(() => {
    loadStudentsList();
  }, 200);
}

function handleDownloadPdf() {
  const search = document.getElementById("student-search-input")?.value || "";
  const major_code = document.getElementById("student-major-filter")?.value || "";
  const pdfUrl = reportApi.getPdfUrl({ search, major_code });

  window.open(pdfUrl, "_blank");
}

// -----------------------------------------------------------------
// 8. Delete Confirmation & Execution
// -----------------------------------------------------------------
function confirmDelete(type, id, name) {
  deleteTarget = { type, id, name };
  const title = type === "student" ? "ลบข้อมูลนักศึกษา" : "ลบข้อมูลสาขาวิชา";
  const desc = `คุณต้องการลบ <b>${name}</b> (${id}) ใช่หรือไม่?`;

  document.getElementById("delete-modal-title").textContent = title;
  document.getElementById("delete-modal-desc").innerHTML = desc;
  openModal("delete-modal");
}

async function executeDelete() {
  if (!deleteTarget) return;

  const btn = document.getElementById("confirm-delete-btn");
  btn.disabled = true;

  try {
    if (deleteTarget.type === "student") {
      await studentApi.delete(deleteTarget.id);
      showToast(`ลบนักศึกษา "${deleteTarget.name}" สำเร็จ`, "success");
      loadStudentsList();
      loadMajorsList();
    } else {
      await majorApi.delete(deleteTarget.id);
      showToast(`ลบสาขาวิชา "${deleteTarget.name}" สำเร็จ`, "success");
      loadMajorsList();
      loadStudentsList();
    }
    closeModal("delete-modal");
  } catch (error) {
    const msg = error.response?.data?.message || "ไม่สามารถลบข้อมูลได้";
    showToast(msg, "error");
  } finally {
    btn.disabled = false;
    deleteTarget = null;
  }
}

// -----------------------------------------------------------------
// 9. Dropdown & Modal Helpers
// -----------------------------------------------------------------
function populateMajorFilterDropdown(majors) {
  const select = document.getElementById("student-major-filter");
  if (!select) return;
  const currentVal = select.value;
  select.innerHTML = `<option value="">สาขาวิชาทั้งหมด</option>` +
    majors.map((m) => `<option value="${m.major_code}">${m.major_code} - ${m.major_name}</option>`).join("");
  select.value = currentVal;
}

function populateMajorFormSelect(majors) {
  const select = document.getElementById("student-major-select");
  if (!select) return;
  const currentVal = select.value;
  select.innerHTML = `<option value="">-- ไม่ระบุสาขาวิชา --</option>` +
    majors.map((m) => `<option value="${m.major_code}">${m.major_code} - ${m.major_name}</option>`).join("");
  select.value = currentVal;
}

// -----------------------------------------------------------------
// 9.0 Floating Major Pop-up (แสดง Popup ข้อมูลสาขาวิชาทันทีเมื่อกด)
// -----------------------------------------------------------------
let popupCurrentMajorCode = null;

function showMajorPopup(event, majorCode) {
  event.stopPropagation();
  popupCurrentMajorCode = majorCode;

  const popup = document.getElementById("major-popup");
  if (!popup) return;

  const major = majorsData.find((m) => m.major_code === majorCode);

  document.getElementById("popup-major-code").textContent = majorCode;
  document.getElementById("popup-major-name").textContent = major ? major.major_name : majorCode;
  document.getElementById("popup-major-remark").textContent = (major && major.remark) ? major.remark : "ไม่มีหมายเหตุ";
  document.getElementById("popup-major-count").textContent = `${major ? (major.student_count || 0) : 0} คน`;

  // เปิดแสดง Popup
  popup.classList.remove("hidden");

  // คำนวณพิกัดให้ Popup ลอยชี้ไปที่ปุ่ม
  const buttonRect = event.currentTarget.getBoundingClientRect();
  const popupWidth = popup.offsetWidth || 288;
  const popupHeight = popup.offsetHeight || 155;

  let left = buttonRect.left;
  // ป้องกันตกขอบขวา
  if (left + popupWidth > window.innerWidth - 16) {
    left = window.innerWidth - popupWidth - 16;
  }
  if (left < 16) left = 16;

  // แนวตั้ง: ถ้าด้านล่างมีที่พอ ให้แสดงข้างล่าง ถ้าไม่พอให้แสดงข้างบน
  let top = buttonRect.bottom + 6;
  if (top + popupHeight > window.innerHeight - 16) {
    top = buttonRect.top - popupHeight - 6;
  }

  popup.style.left = `${Math.round(left)}px`;
  popup.style.top = `${Math.round(top)}px`;
}

function closeMajorPopup() {
  const popup = document.getElementById("major-popup");
  if (popup) {
    popup.classList.add("hidden");
  }
}

function filterByPopupMajor() {
  if (!popupCurrentMajorCode) return;
  const select = document.getElementById("student-major-filter");
  if (select) {
    select.value = popupCurrentMajorCode;
    loadStudentsList();
    showToast(`กรองแสดงเฉพาะสาขาวิชา ${popupCurrentMajorCode}`, "info");
  }
  closeMajorPopup();
}

function openFullMajorDetailsFromPopup() {
  const code = popupCurrentMajorCode;
  closeMajorPopup();
  if (code) {
    viewMajorDetails(code);
  }
}

// คลิกนอก Popup ให้ปิดอัตโนมัติ
document.addEventListener("click", (e) => {
  const popup = document.getElementById("major-popup");
  if (popup && !popup.classList.contains("hidden")) {
    if (!popup.contains(e.target)) {
      closeMajorPopup();
    }
  }
});

// เลื่อนหน้าจอให้ปิด Popup เพื่อความเรียบร้อย
window.addEventListener("scroll", closeMajorPopup, true);

// -----------------------------------------------------------------
// 9.1 View Major Details Modal (เมื่อคลิกที่สาขาวิชาในตารางนักศึกษา)
// -----------------------------------------------------------------
let currentViewedMajorCode = null;

async function viewMajorDetails(majorCode) {
  currentViewedMajorCode = majorCode;

  document.getElementById("view-major-code-badge").textContent = majorCode;
  document.getElementById("view-major-code").textContent = majorCode;

  const cachedMajor = majorsData.find((m) => m.major_code === majorCode);
  if (cachedMajor) {
    document.getElementById("view-major-name").textContent = cachedMajor.major_name;
    document.getElementById("view-major-remark").textContent = cachedMajor.remark || "ไม่มีหมายเหตุ";
    document.getElementById("view-major-count").textContent = `${cachedMajor.student_count || 0} คน`;
    document.getElementById("view-major-students-badge").textContent = `${cachedMajor.student_count || 0} คน`;
  } else {
    document.getElementById("view-major-name").textContent = "กำลังโหลด...";
    document.getElementById("view-major-remark").textContent = "กำลังโหลด...";
    document.getElementById("view-major-count").textContent = "-";
    document.getElementById("view-major-students-badge").textContent = "-";
  }

  document.getElementById("view-major-students-list").innerHTML = `<div class="py-6 text-center text-zinc-400 text-xs">กำลังโหลดรายชื่อนักศึกษา...</div>`;

  openModal("view-major-modal");

  try {
    const res = await majorApi.getByCode(majorCode);
    const major = res.data;
    if (major) {
      document.getElementById("view-major-code").textContent = major.major_code;
      document.getElementById("view-major-name").textContent = major.major_name;
      document.getElementById("view-major-remark").textContent = major.remark || "ไม่มีหมายเหตุ";
      document.getElementById("view-major-count").textContent = `${major.student_count || 0} คน`;
      document.getElementById("view-major-students-badge").textContent = `${major.student_count || 0} คน`;

      const listContainer = document.getElementById("view-major-students-list");
      if (major.students && major.students.length > 0) {
        listContainer.innerHTML = major.students.map((st, idx) => `
          <div class="px-3.5 py-2 flex items-center justify-between hover:bg-zinc-50/70 transition-colors">
            <div class="flex items-center gap-2.5">
              <span class="text-zinc-400 font-mono text-[10px] w-4 text-right">${idx + 1}</span>
              <span class="font-mono text-zinc-700 text-xs tabular-nums">${st.StudentID}</span>
              <span class="font-medium text-zinc-900 text-xs">${st.Student_Name} ${st.Student_Surname}</span>
            </div>
            ${st.Student_Website ? `
              <a href="${st.Student_Website.startsWith('http') ? st.Student_Website : 'https://' + st.Student_Website}" target="_blank" rel="noopener noreferrer" class="text-zinc-400 hover:text-zinc-800 text-[11px] underline font-mono truncate max-w-[120px]">
                เว็บ
              </a>
            ` : '<span class="text-zinc-300 text-[10px]">-</span>'}
          </div>
        `).join("");
      } else {
        listContainer.innerHTML = `<div class="py-6 text-center text-zinc-400 text-xs">ยังไม่มีนักศึกษาในสังกัดสาขาวิชานี้</div>`;
      }
    }
  } catch (error) {
    showToast("ไม่สามารถดึงข้อมูลสาขาวิชาได้", "error");
    closeModal("view-major-modal");
  }
}

function filterStudentsByCurrentMajor() {
  if (!currentViewedMajorCode) return;
  const select = document.getElementById("student-major-filter");
  if (select) {
    select.value = currentViewedMajorCode;
    loadStudentsList();
    showToast(`กรองแสดงเฉพาะสาขาวิชา ${currentViewedMajorCode}`, "info");
  }
  closeModal("view-major-modal");
}

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  }
}

// -----------------------------------------------------------------
// 10. App Initialization
// -----------------------------------------------------------------
window.addEventListener("DOMContentLoaded", async () => {
  const token = localStorage.getItem("lab04_token");
  const storedUser = localStorage.getItem("lab04_user");

  if (token && storedUser) {
    try {
      currentUser = JSON.parse(storedUser);
      const res = await authApi.getProfile();
      currentUser = res.user;
      localStorage.setItem("lab04_user", JSON.stringify(res.user));
      showAppView();
    } catch (e) {
      showLoginView();
    }
  } else {
    showLoginView();
  }
});
