<!DOCTYPE html> 
<html lang="th"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>ระบบจัดการข้อมูลนักศึกษาและสาขาวิชา</title> 
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
 
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased selection:bg-slate-800 selection:text-white flex flex-col"> 
    <?php 
    $isMajorTable = (isset($viewTable) && $viewTable === 'major'); 
    $currentUser = $_SESSION['user'] ?? null;
    $studentCount = !empty($students) && is_array($students) ? count($students) : 0;
    $majorCount = !empty($majors) && is_array($majors) ? count($majors) : 0;
    ?>

    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand Title -->
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center text-sm font-semibold shadow-xs">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 text-base tracking-tight">ระบบทะเบียนนักศึกษา</span>
                        <span class="hidden sm:inline-block text-slate-400 text-xs font-normal ml-2 pl-2 border-l border-slate-200">Student & Major Management</span>
                    </div>
                </div>

                <!-- User Profile & Logout -->
                <?php if ($currentUser): ?>
                    <div class="flex items-center space-x-3">
                        <div class="hidden sm:flex items-center space-x-2 text-xs text-slate-600 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-medium text-slate-800"><?= htmlspecialchars($currentUser['fullname'] ?? 'ผู้ใช้งาน') ?></span>
                            <span class="text-slate-400 font-mono">(<?= htmlspecialchars($currentUser['username'] ?? '') ?>)</span>
                        </div>
                        <button type="button" 
                                onclick="openLogoutModal()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition-colors cursor-pointer">
                            <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>
                            <span>ออกจากระบบ</span>
                        </button>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        
        <!-- Page Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-xl font-bold text-slate-900">
                    <?= $isMajorTable ? 'ข้อมูลสาขาวิชา' : 'ข้อมูลนักศึกษา' ?>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    <?= $isMajorTable 
                        ? 'จัดการข้อมูลรหัสสาขา ชื่อสาขาวิชา และหมายเหตุในระบบ' 
                        : 'จัดการรายชื่อนักศึกษา ข้อมูลการติดต่อ และสาขาวิชาที่สังกัด' ?>
                </p>
            </div>

            <!-- Segmented Switcher & Primary Action Button -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Navigation Tabs -->
                <div class="inline-flex p-1 rounded-xl bg-slate-200/70 border border-slate-200 text-xs font-medium">
                    <a href="index.php?table=student" 
                       class="px-3 py-1.5 rounded-lg transition-all <?= !$isMajorTable ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' ?>">
                        นักศึกษา (<?= $studentCount ?>)
                    </a>
                    <a href="index.php?table=major" 
                       class="px-3 py-1.5 rounded-lg transition-all <?= $isMajorTable ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' ?>">
                        สาขาวิชา (<?= $majorCount ?>)
                    </a>
                </div>

                <!-- Add Button -->
                <?php if ($isMajorTable): ?>
                    <a href="index.php?action=add_major" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs font-medium transition-colors shadow-xs">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>เพิ่มสาขาวิชา</span>
                    </a>
                <?php else: ?>
                    <a href="index.php?action=add" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs font-medium transition-colors shadow-xs">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>เพิ่มนักศึกษา</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Data Table Container -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <?php if ($isMajorTable): ?>
                    <!-- ======================================================== -->
                    <!-- MAJOR TABLE VIEW -->
                    <!-- ======================================================== -->
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-4 w-16 text-center whitespace-nowrap">ลำดับ</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">รหัสสาขา</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">ชื่อสาขาวิชา</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">หมายเหตุ</th>
                                <th class="py-3.5 px-4 w-36 text-center whitespace-nowrap">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            <?php if (!empty($majors) && is_array($majors)): ?> 
                                <?php $i = 1; foreach ($majors as $row): ?> 
                                    <tr class="hover:bg-slate-50/80 transition-colors"> 
                                        <td class="py-3.5 px-4 text-center text-slate-400 font-medium text-xs">
                                            <?= $i++ ?>
                                        </td> 
                                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900 text-xs whitespace-nowrap">
                                            <?= htmlspecialchars($row["major_code"] ?? "") ?>
                                        </td> 
                                        <td class="py-3.5 px-4 font-medium text-slate-800">
                                            <?= htmlspecialchars($row["major_name"] ?? "") ?>
                                        </td> 
                                        <td class="py-3.5 px-4 text-slate-500 text-xs">
                                            <?= htmlspecialchars($row["remark"] ?? "-") ?>
                                        </td> 
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap"> 
                                            <div class="inline-flex items-center space-x-1.5">
                                                <a href="index.php?action=edit_major&id=<?= urlencode($row["major_code"] ?? "") ?>" 
                                                   class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs font-medium transition-colors">
                                                    แก้ไข
                                                </a> 
                                                <button type="button" 
                                                        data-type="major"
                                                        data-id="<?= urlencode($row["major_code"] ?? "") ?>" 
                                                        data-code="<?= htmlspecialchars($row["major_code"] ?? "") ?>" 
                                                        data-name="<?= htmlspecialchars($row["major_name"] ?? "") ?>" 
                                                        onclick="openDeleteModal(this)"
                                                        class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:text-rose-700 hover:bg-rose-50 text-xs font-medium transition-colors cursor-pointer">
                                                    ลบ
                                                </button>
                                            </div>
                                        </td> 
                                    </tr> 
                                <?php endforeach; ?> 
                            <?php else: ?> 
                                <tr> 
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <p class="text-sm">ไม่พบข้อมูลสาขาวิชาในระบบ</p>
                                    </td> 
                                </tr> 
                            <?php endif; ?> 
                        </tbody> 
                    </table>

                <?php else: ?>
                    <!-- ======================================================== -->
                    <!-- STUDENT TABLE VIEW -->
                    <!-- ======================================================== -->
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3.5 px-4 w-16 text-center whitespace-nowrap">ลำดับ</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">รหัสนักศึกษา</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">ชื่อ - นามสกุล</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">สาขาวิชา</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">เว็บไซต์</th>
                                <th class="py-3.5 px-4 w-36 text-center whitespace-nowrap">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            <?php if (!empty($students) && is_array($students)): ?> 
                                <?php $i = 1; foreach ($students as $row): ?> 
                                    <tr class="hover:bg-slate-50/80 transition-colors"> 
                                        <td class="py-3.5 px-4 text-center text-slate-400 font-medium text-xs">
                                            <?= $i++ ?>
                                        </td> 
                                        <td class="py-3.5 px-4 font-mono font-medium text-slate-900 text-xs whitespace-nowrap">
                                            <?= htmlspecialchars($row["StudentID"] ?? "") ?>
                                        </td> 
                                        <td class="py-3.5 px-4 font-medium text-slate-800">
                                            <?= htmlspecialchars($row["Student_Name"] ?? "") ?> <?= htmlspecialchars($row["Student_Surname"] ?? "") ?>
                                        </td> 
                                        <td class="py-3.5 px-4 text-slate-700">
                                            <?php if (!empty($row["major_name"])): ?>
                                                <button type="button" 
                                                        data-code="<?= htmlspecialchars($row["major_code"] ?? "") ?>" 
                                                        data-name="<?= htmlspecialchars($row["major_name"] ?? "") ?>" 
                                                        data-remark="<?= htmlspecialchars($row["remark"] ?? "ไม่มีหมายเหตุ") ?>" 
                                                        onclick="openMajorModal(this)" 
                                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:underline cursor-pointer">
                                                    <span><?= htmlspecialchars($row["major_name"] ?? "") ?></span>
                                                    <i class="fa-solid fa-circle-info text-[11px] text-indigo-400"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            <?php if (!empty($row["Student_Website"])): ?>
                                                <a href="<?= htmlspecialchars($row["Student_Website"]) ?>" target="_blank" rel="noopener noreferrer" 
                                                   class="inline-flex items-center gap-1 text-xs text-slate-600 hover:text-slate-900 hover:underline">
                                                    <span><?= htmlspecialchars(parse_url($row["Student_Website"], PHP_URL_HOST) ?? $row["Student_Website"]) ?></span>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap"> 
                                            <div class="inline-flex items-center space-x-1.5">
                                                <a href="index.php?action=edit&id=<?= urlencode($row["StudentID"] ?? "") ?>" 
                                                   class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs font-medium transition-colors">
                                                    แก้ไข
                                                </a> 
                                                <button type="button" 
                                                        data-type="student"
                                                        data-id="<?= urlencode($row["StudentID"] ?? "") ?>" 
                                                        data-studentid="<?= htmlspecialchars($row["StudentID"] ?? "") ?>" 
                                                        data-name="<?= htmlspecialchars($row["Student_Name"] ?? "") ?> <?= htmlspecialchars($row["Student_Surname"] ?? "") ?>" 
                                                        onclick="openDeleteModal(this)"
                                                        class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:text-rose-700 hover:bg-rose-50 text-xs font-medium transition-colors cursor-pointer">
                                                    ลบ
                                                </button>
                                            </div>
                                        </td> 
                                    </tr> 
                                <?php endforeach; ?> 
                            <?php else: ?> 
                                <tr> 
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <p class="text-sm">ไม่พบข้อมูลนักศึกษาในระบบ</p>
                                    </td> 
                                </tr> 
                            <?php endif; ?> 
                        </tbody> 
                    </table>
                <?php endif; ?>
            </div>

            <!-- Table Footer -->
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <span>ระบบจัดการข้อมูลนักศึกษาและสาขาวิชา (Modular MVC + REST API)</span>
                <span>ทั้งหมด: <?= $isMajorTable ? $majorCount . ' สาขา' : $studentCount . ' ราย' ?></span>
            </div>
        </div>
    </main> 

    <!-- Major Details Modal Popup -->
    <div id="majorModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/40 transition-all duration-200">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl border border-slate-200 overflow-hidden transform scale-95 transition-transform duration-200" id="majorModalCard">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">รายละเอียดสาขาวิชา</h3>
                    <p class="text-xs text-slate-500">Major Details</p>
                </div>
                <button type="button" onclick="closeMajorModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-5 space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-500 block">รหัสสาขา</span>
                    <span id="modalMajorCode" class="font-mono font-semibold text-slate-900 text-sm">
                        -
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">ชื่อสาขาวิชา</span>
                    <p id="modalMajorName" class="font-medium text-slate-900">
                        -
                    </p>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 block">หมายเหตุ</span>
                    <p id="modalMajorRemark" class="text-slate-600 text-xs leading-relaxed">
                        -
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" onclick="closeMajorModal()" class="px-4 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium text-xs transition-colors cursor-pointer">
                    ปิด
                </button>
            </div>

        </div>
    </div>

    <!-- Confirm Delete Modal Popup (Student & Major) -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/40 transition-all duration-200">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl border border-slate-200 overflow-hidden transform scale-95 transition-transform duration-200" id="deleteModalCard">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-rose-700" id="deleteModalTitle">ยืนยันการลบข้อมูล</h3>
                    <p class="text-xs text-slate-500">Confirm Deletion</p>
                </div>
                <button type="button" onclick="closeDeleteModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-5 space-y-3 text-sm">
                <p class="text-xs text-slate-600" id="deleteModalDescription">
                    คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้ออกจากระบบ?
                </p>

                <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500" id="deleteIDLabel">รหัส:</span>
                        <span id="deleteStudentID" class="font-mono font-semibold text-slate-800">-</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200/60 pt-1.5">
                        <span class="text-slate-500" id="deleteNameLabel">ชื่อ:</span>
                        <span id="deleteStudentName" class="font-medium text-slate-800">-</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeDeleteModal()" class="px-3.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium text-xs transition-colors cursor-pointer">
                    ยกเลิก
                </button>
                <a id="confirmDeleteBtn" href="#" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs transition-colors">
                    <span>ลบข้อมูล</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Confirm Logout Modal Popup -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/40 transition-all duration-200">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl border border-slate-200 overflow-hidden transform scale-95 transition-transform duration-200" id="logoutModalCard">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">ยืนยันการออกจากระบบ</h3>
                    <p class="text-xs text-slate-500">Confirm Sign Out</p>
                </div>
                <button type="button" onclick="closeLogoutModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-5 space-y-3 text-sm">
                <p class="text-xs text-slate-600">
                    คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?
                </p>

                <?php if ($currentUser): ?>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <span class="text-slate-500">บัญชีผู้ใช้:</span>
                        <span class="font-medium text-slate-800">
                            <?= htmlspecialchars($currentUser['fullname'] ?? 'ผู้ใช้') ?> 
                            <span class="text-slate-400 font-mono">(<?= htmlspecialchars($currentUser['username'] ?? '') ?>)</span>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeLogoutModal()" class="px-3.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 font-medium text-xs transition-colors cursor-pointer">
                    ยกเลิก
                </button>
                <a href="index.php?action=logout" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition-colors">
                    <span>ออกจากระบบ</span>
                </a>
            </div>

        </div>
    </div>

    <script>
        // Major Details Modal Functions
        function openMajorModal(el) {
            const code = el.getAttribute('data-code') || '-';
            const name = el.getAttribute('data-name') || '-';
            const remark = el.getAttribute('data-remark') || 'ไม่มีหมายเหตุ';

            document.getElementById('modalMajorCode').innerText = code;
            document.getElementById('modalMajorName').innerText = name;
            document.getElementById('modalMajorRemark').innerText = remark;

            const modal = document.getElementById('majorModal');
            const card = document.getElementById('majorModalCard');
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }, 10);
        }

        function closeMajorModal() {
            const modal = document.getElementById('majorModal');
            const card = document.getElementById('majorModalCard');
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 150);
        }

        // Delete Modal Functions (Student & Major)
        function openDeleteModal(el) {
            const type = el.getAttribute('data-type') || 'student';
            const id = el.getAttribute('data-id') || '';

            if (type === 'major') {
                const code = el.getAttribute('data-code') || '-';
                const name = el.getAttribute('data-name') || '-';
                document.getElementById('deleteModalTitle').innerText = 'ยืนยันการลบสาขาวิชา';
                document.getElementById('deleteModalDescription').innerHTML = 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลสาขาวิชานี้? <span class="block text-xs text-rose-500 font-medium mt-1">* หากมีนักศึกษาสังกัดสาขานี้ รหัสสาขาของนักศึกษาจะถูกเปลี่ยนเป็นค่าว่างอัตโนมัติ</span>';
                document.getElementById('deleteIDLabel').innerText = 'รหัสสาขา:';
                document.getElementById('deleteNameLabel').innerText = 'ชื่อสาขาวิชา:';
                document.getElementById('deleteStudentID').innerText = code;
                document.getElementById('deleteStudentName').innerText = name;
                document.getElementById('confirmDeleteBtn').setAttribute('href', 'index.php?action=delete_major&id=' + id);
            } else {
                const studentId = el.getAttribute('data-studentid') || '-';
                const name = el.getAttribute('data-name') || '-';
                document.getElementById('deleteModalTitle').innerText = 'ยืนยันการลบนักศึกษา';
                document.getElementById('deleteModalDescription').innerHTML = 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนักศึกษารายนี้ออกจากระบบ? <span class="block text-xs text-rose-500 font-medium mt-1">* การลบข้อมูลนี้ไม่สามารถย้อนคืนได้</span>';
                document.getElementById('deleteIDLabel').innerText = 'รหัสนักศึกษา:';
                document.getElementById('deleteNameLabel').innerText = 'ชื่อ - นามสกุล:';
                document.getElementById('deleteStudentID').innerText = studentId;
                document.getElementById('deleteStudentName').innerText = name;
                document.getElementById('confirmDeleteBtn').setAttribute('href', 'index.php?action=delete&id=' + id);
            }

            const modal = document.getElementById('deleteModal');
            const card = document.getElementById('deleteModalCard');
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const card = document.getElementById('deleteModalCard');
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 150);
        }

        // Logout Modal Functions
        function openLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const card = document.getElementById('logoutModalCard');
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }, 10);
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const card = document.getElementById('logoutModalCard');
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 150);
        }

        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMajorModal();
                closeDeleteModal();
                closeLogoutModal();
            }
        });
    </script>
</body> 
</html> 
