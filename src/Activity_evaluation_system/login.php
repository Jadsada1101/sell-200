<?php
session_start();
require_once __DIR__ . '/config/db.php';

// หากล็อกอินอยู่แล้ว ให้ redirect ไปยังหน้าที่ถูกต้อง
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
        exit;
    } else {
        header('Location: student/dashboard.php');
        exit;
    }
}

$error_message = '';
$username_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_val = trim($_POST['username'] ?? '');
    $password_val = trim($_POST['password'] ?? '');

    if (empty($username_val) || empty($password_val)) {
        $error_message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username_val]);
        $user = $stmt->fetch();

        if ($user && password_verify($password_val, $user['password'])) {
            // ล็อกอินสำเร็จ บันทึก Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
                exit;
            } else {
                header('Location: student/dashboard.php');
                exit;
            }
        } else {
            $error_message = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบประเมินการจัดกิจกรรม </title>
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
                            top: '#8a5823',
                            dark: '#482f16',
                            brown: '#8a5823',
                            brownHover: '#724719',
                            cream: '#fdfaf5',
                            border: '#ede5d8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f1f3f7;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <header class="bg-[#8a5823] text-white text-xs sm:text-sm py-2.5 px-4 sm:px-8 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2 font-medium">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z"/>
                </svg>
                <span>มหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา</span>
            </div>
            <div class="text-white/80 font-light hidden md:block">
                Rajamangala University of Technology Lanna
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-6">
        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl overflow-hidden grid grid-cols-1 md:grid-cols-2 border border-gray-100">
            
            <!-- Left Column: University & Activity Info -->
            <div class="bg-[#482f16] text-white p-8 sm:p-10 flex flex-col justify-center items-center text-center relative overflow-hidden">
                <!-- University Logo -->
                <div class="mb-4">
                    <img src="assets/images/logo.png" alt="ตรามหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา" class="h-28 sm:h-32 object-contain mx-auto drop-shadow-md">
                </div>

                <h2 class="text-base sm:text-lg font-medium text-white tracking-wide">
                    มหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา
                </h2>
                <p class="text-xs text-white/70 font-light mt-0.5">
                    Rajamangala University of Technology Lanna
                </p>

                <!-- Divider Line -->
                <div class="w-12 h-1 bg-[#c89344] rounded-full my-5"></div>

                <!-- Activity Title -->
                <h3 class="text-xl sm:text-2xl font-semibold text-white leading-snug">
                    ระบบประเมินการจัดกิจกรรม<br>
                </h3>

                <!-- Activity Description -->
                <p class="text-xs sm:text-sm text-amber-100/70 font-light mt-4 max-w-xs leading-relaxed">
                    แบบสอบถามความพึงพอใจต่อการจัดกิจกรรม สำหรับนักศึกษาสาขาเทคโนโลยีสารสนเทศ
                </p>
            </div>

            <!-- Right Column: Login Form -->
            <div class="p-8 sm:p-10 flex flex-col justify-center bg-white">
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">เข้าสู่ระบบ</h1>
                    <p class="text-gray-500 text-xs sm:text-sm mt-1">กรอกบัญชีของท่านเพื่อเริ่มทำแบบประเมิน</p>
                </div>

                <!-- Alert Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-4">
                    <!-- Username / Student ID -->
                    <div>
                        <label for="username" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                            ชื่อผู้ใช้ / รหัสนักศึกษา
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="<?= htmlspecialchars($username_val) ?>" 
                                required 
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                                placeholder="กรอกชื่อผู้ใช้ หรือ รหัสนักศึกษา"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                            รหัสผ่าน
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="w-full pl-10 pr-10 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                                placeholder="กรอกรหัสผ่าน"
                            >
                            <!-- Show/Hide Password Toggle -->
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                            >
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eye-slash-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full bg-[#8a5823] hover:bg-[#724719] text-white font-medium py-3 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2 text-sm sm:text-base cursor-pointer"
                        >
                            <span>เข้าสู่ระบบ</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Demo Account Information Box -->
                <div class="mt-6 p-4 rounded-xl bg-[#fdfaf5] border border-[#ede5d8] text-xs leading-relaxed text-gray-700">
                    <div class="flex items-center gap-1.5 font-semibold text-amber-900 mb-2">
                        <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>บัญชีทดสอบ</span>
                    </div>
                    <div class="grid grid-cols-[60px_1fr] gap-y-1.5 text-gray-600">
                        <span class="text-gray-500">ผู้ดูแล</span>
                        <span class="font-medium text-gray-800">admin / admin123</span>
                        <span class="text-gray-500">นักศึกษา</span>
                        <span class="font-medium text-gray-800">รหัสนักศึกษา <span class="text-gray-500 font-normal">(รหัสผ่านตั้งต้น = รหัสนักศึกษา)</span></span>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Bottom Footer -->
    <footer class="py-4 text-center text-xs text-gray-500 flex items-center justify-center gap-2">
        <img src="assets/images/logo.png" alt="RMUTL Logo" class="h-5 w-auto object-contain">
        <span>มหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา</span>
    </footer>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
