<?php
// กำหนดตัวแปรสำหรับ layout ฝั่งนักศึกษา
$current_page = $current_page ?? 'dashboard';
$page_title = $page_title ?? 'ระบบประเมินกิจกรรม - นักศึกษา';
$breadcrumb_sub = $breadcrumb_sub ?? 'หน้าแรก';
$student_name = $_SESSION['fullname'] ?? 'นักศึกษา';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - ระบบประเมินกิจกรรม IT</title>
    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Prompt', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            gold: '#8a5823',
                            dark: '#482f16',
                            hover: '#724719',
                            cream: '#fdfaf5',
                            activeMenu: '#fbf6ee'
                        }
                    }
                }
            }
        }
    </script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f3f4f8;
        }
        .swal2-popup {
            font-family: 'Prompt', sans-serif !important;
            border-radius: 1.25rem !important;
            padding: 1.5rem !important;
        }
        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
        }
        .swal2-html-container {
            font-size: 0.875rem !important;
            color: #4b5563 !important;
            line-height: 1.5 !important;
        }
        .swal2-actions {
            gap: 0.75rem !important;
        }
        .swal2-confirm {
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            padding: 0.6rem 1.5rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .swal2-cancel {
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            padding: 0.6rem 1.5rem !important;
            background-color: #f3f4f6 !important;
            color: #374151 !important;
        }
        .swal2-cancel:hover {
            background-color: #e5e7eb !important;
        }
    </style>
    <script>
        function confirmAction(event, options = {}) {
            event.preventDefault();
            const form = event.target.closest('form');
            const href = event.target.closest('a')?.getAttribute('href');

            Swal.fire({
                title: options.title || 'ยืนยันการทำรายการ?',
                text: options.text || '',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmButtonColor || '#dc2626',
                cancelButtonColor: options.cancelButtonColor || '#6b7280',
                confirmButtonText: options.confirmButtonText || 'ยืนยัน',
                cancelButtonText: options.cancelButtonText || 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) {
                        form.submit();
                    } else if (href) {
                        window.location.href = href;
                    }
                }
            });
            return false;
        }
    </script>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- 1. Topmost Brown Header Bar -->
    <header class="bg-[#8a5823] text-white text-xs sm:text-sm py-2 px-4 sm:px-8 shadow-sm">
        <div class="w-full flex items-center justify-between">
            <div class="flex items-center gap-2 font-normal text-white">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z"/>
                </svg>
                <span class="truncate">มหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา</span>
            </div>
            <div class="text-white/80 font-light text-xs hidden md:block shrink-0">
                Rajamangala University of Technology Lanna
            </div>
        </div>
    </header>

    <!-- Backdrop Overlay สำหรับเมนูบนหน้าจอมือถือ -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden md:hidden transition-opacity"></div>

    <div class="flex flex-1 relative">
        <!-- 2. Left Sidebar (ซ่อนแบบ Drawer บนมือถือ และแสดงถาวรบนหน้าจอคอม) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col justify-between shrink-0 min-h-screen md:min-h-[calc(100vh-37px)] overflow-y-auto transform -translate-x-full md:translate-x-0 md:static md:inset-auto md:z-auto transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
            <div>
                <!-- System Brand Title -->
                <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img src="../assets/images/logo.png" alt="Logo" class="h-10 w-auto object-contain shrink-0">
                        <div>
                            <h1 class="font-bold text-gray-800 text-sm leading-tight">ระบบประเมินกิจกรรม</h1>
                            <p class="text-xs text-gray-500 leading-tight"></p>
                        </div>
                    </div>
                    <!-- ปุ่มปิดเมนูบนหน้าจอมือถือ -->
                    <button id="sidebar-close-btn" type="button" class="md:hidden p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" aria-label="ปิดเมนู">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <div class="px-4 py-4">
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-2 px-3">เมนูนักศึกษา</p>
                    <nav class="space-y-1">
                        <!-- เมนู 1: หน้าแรก -->
                        <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors <?= $current_page === 'dashboard' ? 'bg-[#fbf6ee] text-[#8a5823] font-semibold border-l-4 border-[#8a5823]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <svg class="w-5 h-5 <?= $current_page === 'dashboard' ? 'text-[#8a5823]' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>หน้าแรก</span>
                        </a>

                        <!-- เมนู 2: ทำแบบประเมิน -->
                        <a href="evaluate.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors <?= $current_page === 'evaluate' ? 'bg-[#fbf6ee] text-[#8a5823] font-semibold border-l-4 border-[#8a5823]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <svg class="w-5 h-5 <?= $current_page === 'evaluate' ? 'text-[#8a5823]' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span>ทำแบบประเมิน</span>
                        </a>

                        <!-- เมนู 3: ข้อมูลของฉัน -->
                        <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors <?= $current_page === 'profile' ? 'bg-[#fbf6ee] text-[#8a5823] font-semibold border-l-4 border-[#8a5823]' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <svg class="w-5 h-5 <?= $current_page === 'profile' ? 'text-[#8a5823]' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>ข้อมูลของฉัน</span>
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- 3. Right Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- White Breadcrumb Sub-bar -->
            <div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex items-center justify-between text-xs text-gray-600">
                <div class="flex items-center gap-2 min-w-0">
                    <!-- ปุ่มเปิดเมนูบนหน้าจอมือถือ (Hamburger Button) -->
                    <button id="sidebar-toggle-btn" type="button" class="md:hidden p-1.5 -ml-1 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#8a5823] transition-colors shrink-0 cursor-pointer" aria-label="เปิดเมนู">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="flex items-center gap-1.5 truncate">
                        <svg class="w-4 h-4 text-gray-400 shrink-0 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="hidden sm:inline">นักศึกษา</span>
                        <span class="text-gray-300 hidden sm:inline">&gt;</span>
                        <span class="text-gray-900 font-medium truncate"><?= htmlspecialchars($breadcrumb_sub) ?></span>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <div class="flex items-center gap-1.5 text-gray-700 font-medium">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden sm:inline"><?= htmlspecialchars($student_name) ?></span>
                    </div>
                    <a href="../logout.php" onclick="return confirmAction(event, { title: 'ต้องการออกจากระบบหรือไม่?', text: 'คุณจะต้องเข้าสู่ระบบใหม่อีกครั้งเพื่อเข้าใช้งานระบบ', icon: 'question', confirmButtonText: 'ออกจากระบบ', confirmButtonColor: '#dc2626' });" class="group border border-red-200 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white font-semibold py-1.5 px-2.5 sm:px-3 rounded-lg text-xs flex items-center gap-1.5 transition-all shadow-sm">
                        <svg class="w-3.5 h-3.5 text-red-500 group-hover:text-white transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">ออกจากระบบ</span>
                    </a>
                </div>
            </div>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 sm:p-8">
