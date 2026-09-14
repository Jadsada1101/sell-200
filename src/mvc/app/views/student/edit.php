<!DOCTYPE html> 
<html lang="th"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>แก้ไขข้อมูลนักศึกษา — ระบบจัดการข้อมูลนักศึกษา</title> 
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
 
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased selection:bg-slate-800 selection:text-white py-10 px-4"> 
    
    <div class="max-w-xl mx-auto space-y-4">
        
        <!-- Back Link -->
        <a href="index.php?table=student" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>กลับไปหน้ารายการนักศึกษา</span>
        </a>

        <!-- Form Card Container -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-200">
                <h1 class="text-lg font-bold text-slate-900">แก้ไขข้อมูลนักศึกษา</h1>
                <p class="text-xs text-slate-500 mt-0.5">ปรับปรุงข้อมูลรายละเอียดของนักศึกษาในระบบ</p>
            </div>

            <!-- Form Body -->
            <form method="POST" action="index.php?action=update" class="p-6 space-y-4">
                
                <!-- รหัสนักศึกษา (Readonly) -->
                <div class="space-y-1.5">
                    <label for="StudentID" class="block text-xs font-medium text-slate-700 flex items-center justify-between">
                        <span>รหัสนักศึกษา</span>
                        <span class="text-[11px] text-slate-400 font-normal">ไม่อนุญาตให้แก้ไขรหัส</span>
                    </label>
                    <input type="text" 
                           id="StudentID" 
                           name="StudentID" 
                           value="<?= htmlspecialchars($student["StudentID"] ?? "") ?>" 
                           readonly 
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-100 text-slate-600 font-mono text-sm outline-none cursor-not-allowed">
                </div>

                <!-- Grid: ชื่อ & นามสกุล -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="Student_Name" class="block text-xs font-medium text-slate-700">
                            ชื่อ <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="Student_Name" 
                               name="Student_Name" 
                               value="<?= htmlspecialchars($student["Student_Name"] ?? "") ?>" 
                               required 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label for="Student_Surname" class="block text-xs font-medium text-slate-700">
                            นามสกุล <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="Student_Surname" 
                               name="Student_Surname" 
                               value="<?= htmlspecialchars($student["Student_Surname"] ?? "") ?>" 
                               required 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none">
                    </div>
                </div>

                <!-- สาขาวิชา -->
                <div class="space-y-1.5">
                    <label for="major_code" class="block text-xs font-medium text-slate-700">
                        สาขาวิชา
                    </label>
                    <select id="major_code" 
                            name="major_code" 
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none">
                        <option value="">-- เลือกสาขาวิชา --</option>
                        <?php if (!empty($majors) && is_array($majors)): ?>
                            <?php foreach ($majors as $m): ?>
                                <option value="<?= htmlspecialchars($m["major_code"]) ?>" 
                                    <?= (isset($student["major_code"]) && $student["major_code"] === $m["major_code"]) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($m["major_name"]) ?> (<?= htmlspecialchars($m["major_code"]) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- เว็บไซต์ -->
                <div class="space-y-1.5">
                    <label for="Student_Website" class="block text-xs font-medium text-slate-700">
                        เว็บไซต์ / พอร์ตโฟลิโอ
                    </label>
                    <input type="url" 
                           id="Student_Website" 
                           name="Student_Website" 
                           value="<?= htmlspecialchars($student["Student_Website"] ?? "") ?>" 
                           placeholder="https://example.com" 
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none placeholder:text-slate-400">
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-2">
                    <a href="index.php?table=student" 
                       class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium text-xs transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-medium text-xs transition-colors shadow-xs">
                        บันทึกการแก้ไข
                    </button>
                </div>

            </form>
        </div>
    </div>

</body> 
</html> 
