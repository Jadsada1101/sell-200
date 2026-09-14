"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { FRONTEND_ROUTES, navigateTo } from "@/lib/router";
import { authApi, authStorage } from "@/lib/api";

export default function LoginPage() {
  const router = useRouter();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (authStorage.isAuthenticated()) {
      navigateTo(router, FRONTEND_ROUTES.STUDENTS);
    }
  }, [router]);

  const handleLogin = async (e) => {
    if (e) e.preventDefault();
    setErrorMessage("");

    if (!username.trim() || !password) {
      setErrorMessage("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
      return;
    }

    setIsLoading(true);
    try {
      await authApi.login(username, password);
      navigateTo(router, FRONTEND_ROUTES.STUDENTS);
    } catch (error) {
      setErrorMessage(error.message || "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
    } finally {
      setIsLoading(false);
    }
  };

  const handleQuickLogin = (user, pass) => {
    setUsername(user);
    setPassword(pass);
    setIsLoading(true);
    setErrorMessage("");
    authApi
      .login(user, pass)
      .then(() => {
        navigateTo(router, FRONTEND_ROUTES.STUDENTS);
      })
      .catch((err) => {
        setErrorMessage(err.message);
      })
      .finally(() => {
        setIsLoading(false);
      });
  };

  return (
    <div className="flex-1 flex items-center justify-center p-4">
      <div className="w-full max-w-sm bg-white rounded-xl border border-zinc-200 p-6 sm:p-8 shadow-xs">
        {/* Header */}
        <div className="mb-6">
          <h1 className="text-lg font-semibold text-zinc-900 tracking-tight">
            เข้าสู่ระบบ
          </h1>
          <p className="text-xs text-zinc-500 mt-1">
            ระบบบริหารจัดการข้อมูลนักศึกษา
          </p>
        </div>

        {/* Error Alert */}
        {errorMessage && (
          <div className="mb-4 p-3 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-xs">
            {errorMessage}
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleLogin} className="space-y-4">
          <div>
            <label className="block text-xs font-medium text-zinc-700 mb-1">
              ชื่อผู้ใช้
            </label>
            <input
              type="text"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              placeholder="admin หรือ staff"
              disabled={isLoading}
              className="w-full px-3 py-2 rounded-md border border-zinc-300 text-sm focus:border-zinc-900 focus:outline-none transition-colors"
            />
          </div>

          <div>
            <label className="block text-xs font-medium text-zinc-700 mb-1">
              รหัสผ่าน
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="••••••••"
              disabled={isLoading}
              className="w-full px-3 py-2 rounded-md border border-zinc-300 text-sm focus:border-zinc-900 focus:outline-none transition-colors"
            />
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-2 px-4 rounded-md bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors disabled:opacity-50 cursor-pointer"
          >
            {isLoading ? "กำลังตรวจสอบ..." : "เข้าสู่ระบบ"}
          </button>
        </form>

        {/* Quick Test Accounts */}
        <div className="mt-6 pt-5 border-t border-zinc-100 text-center">
          <p className="text-[11px] text-zinc-400 mb-2">บัญชีสำหรับทดสอบ:</p>
          <div className="flex justify-center gap-2">
            <button
              type="button"
              onClick={() => handleQuickLogin("admin", "admin123")}
              disabled={isLoading}
              className="px-2.5 py-1 text-xs text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded transition-colors"
            >
              Admin
            </button>
            <button
              type="button"
              onClick={() => handleQuickLogin("staff", "staff123")}
              disabled={isLoading}
              className="px-2.5 py-1 text-xs text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded transition-colors"
            >
              Staff
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
