/**
 * lib/api.js - ตัวช่วยเรียก API ฝั่ง Frontend (Client API Service)
 * จัดการเรื่อง Authorization Token และแปลงข้อมูล JSON ให้พร้อมใช้งาน
 */

import { API_ROUTES } from "./router";

// คีย์สำหรับเก็บ Token ใน LocalStorage
const TOKEN_KEY = "lab05_token";
const USER_KEY = "lab05_user";

// จัดการ Token & User ใน LocalStorage
export const authStorage = {
  getToken: () => {
    if (typeof window === "undefined") return null;
    return localStorage.getItem(TOKEN_KEY);
  },
  setToken: (token) => {
    if (typeof window === "undefined") return;
    localStorage.setItem(TOKEN_KEY, token);
  },
  getUser: () => {
    if (typeof window === "undefined") return null;
    const userStr = localStorage.getItem(USER_KEY);
    try {
      return userStr ? JSON.parse(userStr) : null;
    } catch {
      return null;
    }
  },
  setUser: (user) => {
    if (typeof window === "undefined") return;
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  },
  clear: () => {
    if (typeof window === "undefined") return;
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  },
  isAuthenticated: () => {
    return !!authStorage.getToken();
  },
};

// ฟังก์ชัน fetch พื้นฐานที่แนบ Header และ Token ให้อัตโนมัติ
async function apiRequest(endpoint, options = {}) {
  const token = authStorage.getToken();
  const headers = {
    "Content-Type": "application/json",
    ...(options.headers || {}),
  };

  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  const response = await fetch(endpoint, {
    ...options,
    headers,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    if (response.status === 401 && typeof window !== "undefined") {
      authStorage.clear();
    }
    const errorMessage = data.message || `เกิดข้อผิดพลาด (${response.status})`;
    throw new Error(errorMessage);
  }

  return data;
}

// 1. Authentication API
export const authApi = {
  login: async (username, password) => {
    const res = await apiRequest(API_ROUTES.AUTH_LOGIN, {
      method: "POST",
      body: JSON.stringify({ username, password }),
    });
    if (res.token && res.user) {
      authStorage.setToken(res.token);
      authStorage.setUser(res.user);
    }
    return res;
  },
  getMe: async () => {
    return await apiRequest(API_ROUTES.AUTH_ME);
  },
  logout: () => {
    authStorage.clear();
  },
};

// 2. Student Management API
export const studentApi = {
  getAll: async ({ search = "", major_code = "" } = {}) => {
    const params = new URLSearchParams();
    if (search) params.append("search", search);
    if (major_code) params.append("major_code", major_code);

    const queryString = params.toString() ? `?${params.toString()}` : "";
    return await apiRequest(`${API_ROUTES.STUDENTS}${queryString}`);
  },
  getById: async (id) => {
    return await apiRequest(API_ROUTES.STUDENT_DETAIL(id));
  },
  create: async (studentData) => {
    return await apiRequest(API_ROUTES.STUDENTS, {
      method: "POST",
      body: JSON.stringify(studentData),
    });
  },
  update: async (id, studentData) => {
    return await apiRequest(API_ROUTES.STUDENT_DETAIL(id), {
      method: "PUT",
      body: JSON.stringify(studentData),
    });
  },
  delete: async (id) => {
    return await apiRequest(API_ROUTES.STUDENT_DETAIL(id), {
      method: "DELETE",
    });
  },
};

// 3. Major Management API
export const majorApi = {
  getAll: async () => {
    return await apiRequest(API_ROUTES.MAJORS);
  },
  create: async (majorData) => {
    return await apiRequest(API_ROUTES.MAJORS, {
      method: "POST",
      body: JSON.stringify(majorData),
    });
  },
  update: async (code, majorData) => {
    return await apiRequest(API_ROUTES.MAJOR_DETAIL(code), {
      method: "PUT",
      body: JSON.stringify(majorData),
    });
  },
  delete: async (code) => {
    return await apiRequest(API_ROUTES.MAJOR_DETAIL(code), {
      method: "DELETE",
    });
  },
};

// 4. Report PDF API
export const reportApi = {
  getPdfDownloadUrl: (major_code = "") => {
    const token = authStorage.getToken();
    const params = new URLSearchParams();
    if (token) params.append("token", token);
    if (major_code) params.append("major_code", major_code);
    return `${API_ROUTES.REPORTS_PDF}?${params.toString()}`;
  },
};
