<!DOCTYPE html> 
<html lang="th"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>เข้าสู่ระบบ — ระบบจัดการข้อมูลนักศึกษา</title> 
    <!-- Tailwind CSS v4 Browser Build -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- Google Fonts: Prompt & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Prompt', 'Inter', sans-serif; }
    </style>
</head> 
 
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased selection:bg-slate-800 selection:text-white flex items-center justify-center p-4"> 
    
    <!-- Login Card Container -->
    <div class="w-full max-w-sm bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-1">
            <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center mx-auto mb-3 shadow-xs">
                <i class="fa-solid fa-graduation-cap text-base"></i>
            </div>
            <h1 class="text-lg font-bold text-slate-900">
                เข้าสู่ระบบ
            </h1>
            <p class="text-xs text-slate-500">
                ระบบจัดการข้อมูลนักศึกษาและสาขาวิชา
            </p>
        </div>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="index.php?action=authenticate" class="space-y-4">
            
            <!-- Username Field -->
            <div class="space-y-1.5">
                <label for="username" class="block text-xs font-medium text-slate-700">
                    ชื่อผู้ใช้ (Username)
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="admin" 
                       required 
                       autofocus
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none placeholder:text-slate-400">
            </div>

            <!-- Password Field -->
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-medium text-slate-700">
                    รหัสผ่าน (Password)
                </label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="••••••••" 
                           required 
                           class="w-full pl-3 pr-10 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none placeholder:text-slate-400">
                    <button type="button" 
                            onclick="togglePasswordVisibility()" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                            title="แสดง/ซ่อนรหัสผ่าน">
                        <i id="togglePasswordIcon" class="fa-solid fa-eye text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-medium text-sm transition-colors shadow-xs cursor-pointer mt-1">
                เข้าสู่ระบบ
            </button>
        </form>

        <!-- Demo Account Box -->
        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs space-y-2">
            <div class="flex items-center justify-between text-slate-600">
                <span class="font-medium">บัญชีทดสอบระบบ:</span>
                <button type="button" 
                        onclick="autoFillAdmin()" 
                        class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium cursor-pointer">
                    กรอกข้อมูลอัตโนมัติ
                </button>
            </div>
            <div onclick="autoFillAdmin()" 
                 class="flex items-center justify-between font-mono bg-white px-2.5 py-1.5 rounded border border-slate-200 text-slate-600 text-[11px] cursor-pointer hover:bg-slate-50 transition-colors">
                <span>user: <strong class="text-slate-900">admin</strong></span>
                <span>pass: <strong class="text-slate-900">admin123</strong></span>
            </div>
        </div>

        <div class="text-center text-[11px] text-slate-400 pt-1">
            Modular MVC Architecture & REST API
        </div>

    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.getElementById("togglePasswordIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }

        function autoFillAdmin() {
            document.getElementById("username").value = "admin";
            document.getElementById("password").value = "admin123";
        }
    </script>
</body> 
</html> 
