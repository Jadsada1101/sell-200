"use client";

import { useSyncExternalStore } from "react";
import { usePathname, useRouter } from "next/navigation";
import Link from "next/link";
import { FRONTEND_ROUTES, NAVIGATION_ITEMS } from "@/lib/router";
import { authStorage } from "@/lib/api";

const emptySubscribe = () => () => {};

// Hook ตรวจสอบการ Mount ของ Client เพื่อป้องกัน Hydration Mismatch
function useIsClient() {
  return useSyncExternalStore(
    emptySubscribe,
    () => true,
    () => false
  );
}

// Hook อ่านข้อมูล User จาก LocalStorage แบบ Reactive
function useCurrentUser() {
  const userJson = useSyncExternalStore(
    (callback) => {
      if (typeof window === "undefined") return () => {};
      window.addEventListener("storage", callback);
      return () => window.removeEventListener("storage", callback);
    },
    () => (typeof window !== "undefined" ? localStorage.getItem("lab05_user") : null),
    () => null
  );

  if (!userJson) return null;
  try {
    return JSON.parse(userJson);
  } catch {
    return null;
  }
}

export default function Navbar() {
  const router = useRouter();
  const pathname = usePathname();
  const isClient = useIsClient();
  const currentUser = useCurrentUser();

  const handleLogout = () => {
    authStorage.clear();
    router.push(FRONTEND_ROUTES.LOGIN);
  };

  // หน้า Login ไม่ต้องแสดง Navbar ทั้งบน Server และ Client
  if (pathname === FRONTEND_ROUTES.LOGIN) {
    return null;
  }

  return (
    <header className="bg-white border-b border-zinc-200">
      <div className="max-w-6xl mx-auto px-4 sm:px-6">
        <div className="flex items-center justify-between h-14">
          {/* Logo / System Name */}
          <div className="flex items-center gap-8">
            <Link
              href={FRONTEND_ROUTES.STUDENTS}
              className="flex items-center gap-2 font-semibold text-zinc-900 text-sm tracking-tight hover:opacity-80"
            >
              <span className="w-2 h-2 rounded-full bg-zinc-900"></span>
              <span>ระบบทะเบียนนักศึกษา</span>
            </Link>

            {/* Navigation Links */}
            <nav className="hidden md:flex items-center gap-1">
              {NAVIGATION_ITEMS.map((item) => {
                const isActive = pathname === item.href;
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    className={`px-3 py-1.5 rounded-md text-xs font-medium transition-colors ${
                      isActive
                        ? "bg-zinc-100 text-zinc-900 font-semibold"
                        : "text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50"
                    }`}
                  >
                    {item.label}
                  </Link>
                );
              })}
            </nav>
          </div>

          {/* User Info & Actions */}
          <div className="flex items-center gap-3">
            {isClient && currentUser && (
              <div className="flex items-center gap-3 text-xs">
                <span className="text-zinc-600">
                  {currentUser.fullname || currentUser.username}
                </span>
                <span className="text-[11px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600 border border-zinc-200 font-mono">
                  {currentUser.role}
                </span>
                <button
                  onClick={handleLogout}
                  className="px-2.5 py-1 rounded-md border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 text-zinc-600 hover:text-zinc-900 transition-colors cursor-pointer"
                >
                  ออกจากระบบ
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Mobile Navigation */}
      <div className="md:hidden flex border-t border-zinc-100 px-4 py-2 gap-1 overflow-x-auto bg-zinc-50">
        {NAVIGATION_ITEMS.map((item) => {
          const isActive = pathname === item.href;
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`px-2.5 py-1 rounded text-xs whitespace-nowrap ${
                isActive
                  ? "bg-white text-zinc-900 font-semibold shadow-2xs border border-zinc-200"
                  : "text-zinc-600 hover:text-zinc-900"
              }`}
            >
              {item.label}
            </Link>
          );
        })}
      </div>
    </header>
  );
}
