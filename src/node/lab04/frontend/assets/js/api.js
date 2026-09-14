/**
 * API Service (Axios Client)
 * เชื่อมต่อไปยัง Express Backend API (พอร์ต 5001)
 */

// กำหนด Base URL อัตโนมัติ: หากรันผ่านพอร์ต 5001 ให้ใช้ /api ได้ทันที
// หากรันผ่าน Live Server (เช่น พอร์ต 5500, 3000) ให้ชี้ไปที่พอร์ต 5001
const isPort5001 = window.location.port === "5001";
const currentHost = window.location.hostname || "localhost";
const API_BASE_URL = isPort5001 ? "/api" : `http://${currentHost}:5001/api`;

// สร้าง Axios Instance
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
  },
  timeout: 10000,
});

// Request Interceptor: แนบ JWT Token เข้าไปใน Authorization Header อัตโนมัติ
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("lab04_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response Interceptor: จัดการกรณี Token หมดอายุหรือไม่ได้รับอนุญาต
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      // หาก Token หมดอายุหรือไม่ถูกต้อง ให้เคลียร์และพาไปหน้า Login
      if (localStorage.getItem("lab04_token")) {
        localStorage.removeItem("lab04_token");
        localStorage.removeItem("lab04_user");
        if (typeof showLoginView === "function") {
          showLoginView();
          showToast("เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่", "error");
        }
      }
    }
    return Promise.reject(error);
  }
);

// หมวดหมู่งาน Authentication
const authApi = {
  login: async (username, password) => {
    const response = await apiClient.post("/auth/login", { username, password });
    return response.data;
  },
  getProfile: async () => {
    const response = await apiClient.get("/auth/me");
    return response.data;
  },
};

// หมวดหมู่งาน สาขาวิชา (tb_major)
const majorApi = {
  getAll: async () => {
    const response = await apiClient.get("/majors");
    return response.data;
  },
  getByCode: async (code) => {
    const response = await apiClient.get(`/majors/${code}`);
    return response.data;
  },
  create: async (majorData) => {
    const response = await apiClient.post("/majors", majorData);
    return response.data;
  },
  update: async (code, majorData) => {
    const response = await apiClient.put(`/majors/${code}`, majorData);
    return response.data;
  },
  delete: async (code) => {
    const response = await apiClient.delete(`/majors/${code}`);
    return response.data;
  },
};

// หมวดหมู่งาน นักศึกษา (tb_student)
const studentApi = {
  getAll: async (params = {}) => {
    const response = await apiClient.get("/students", { params });
    return response.data;
  },
  getById: async (id) => {
    const response = await apiClient.get(`/students/${id}`);
    return response.data;
  },
  create: async (studentData) => {
    const response = await apiClient.post("/students", studentData);
    return response.data;
  },
  update: async (id, studentData) => {
    const response = await apiClient.put(`/students/${id}`, studentData);
    return response.data;
  },
  delete: async (id) => {
    const response = await apiClient.delete(`/students/${id}`);
    return response.data;
  },
};

// หมวดหมู่งาน รายงาน PDF
const reportApi = {
  getPdfUrl: (params = {}) => {
    const token = localStorage.getItem("lab04_token") || "";
    const query = new URLSearchParams({ ...params, token }).toString();
    return `${API_BASE_URL}/reports/students-pdf?${query}`;
  },
};
