<!DOCTYPE html> 
<html lang="th"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>เพิ่มสาขาวิชา — ระบบจัดการข้อมูลนักศึกษา</title> 
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
        <a href="index.php?table=major" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>กลับไปหน้ารายการสาขาวิชา</span>
        </a>

        <!-- Form Card Container -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-200">
                <h1 class="text-lg font-bold text-slate-900">เพิ่มข้อมูลสาขาวิชา</h1>
                <p class="text-xs text-slate-500 mt-0.5">กรอกรหัสและชื่อสาขาวิชาใหม่เพื่อบันทึกลงระบบ</p>
            </div>

            <!-- Form Body -->
            <form method="POST" action="index.php?action=store_major" class="p-6 space-y-4">
                
                <!-- รหัสสาขา -->
                <div class="space-y-1.5">
                    <label for="major_code" class="block text-xs font-medium text-slate-700">
                        รหัสสาขา (Major Code) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="major_code" 
                           name="major_code" 
                           placeholder="เช่น CS, IT, SE" 
                           required 
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm font-mono uppercase focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none placeholder:text-slate-400 placeholder:font-sans">
                </div>

                <!-- ชื่อสาขาวิชา -->
                <div class="space-y-1.5">
                    <label for="major_name" class="block text-xs font-medium text-slate-700">
                        ชื่อสาขาวิชา (Major Name) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="major_name" 
                           name="major_name" 
                           placeholder="เช่น วิทยาการคอมพิวเตอร์" 
                           required 
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none placeholder:text-slate-400">
                </div>

                <!-- หมายเหตุ -->
                <div class="space-y-1.5">
                    <label for="remark" class="block text-xs font-medium text-slate-700">
                        หมายเหตุ (Remark)
                    </label>
                    <textarea id="remark" 
                              name="remark" 
                              rows="3" 
                              placeholder="ระบุรายละเอียดเพิ่มเติม (ถ้ามี)"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all outline-none placeholder:text-slate-400 resize-none"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-2">
                    <a href="index.php?table=major" 
                       class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium text-xs transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-medium text-xs transition-colors shadow-xs">
                        บันทึกข้อมูล
                    </button>
                </div>

            </form>
        </div>
    </div>

</body> 
</html> 
