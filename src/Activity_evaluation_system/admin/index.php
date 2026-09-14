<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/period_helper.php';

// 1. ดึงจำนวนนักศึกษาทั้งหมด
$stmt_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$total_students = (int)$stmt_students->fetchColumn();

// 2. ดึงจำนวนกิจกรรมทั้งหมด
$stmt_activities = $pdo->query("SELECT COUNT(*) FROM activities WHERE status = 'active'");
$total_activities = (int)$stmt_activities->fetchColumn();

// 3. ดึงจำนวนผู้ที่ประเมินแล้ว (นับจำนวนนักศึกษาที่ไม่ซ้ำ)
$stmt_eval = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM evaluations");
$evaluated_count = (int)$stmt_eval->fetchColumn();

// คำนวณเปอร์เซ็นต์
$evaluated_percent = $total_students > 0 ? round(($evaluated_count / $total_students) * 100) : 0;

// 4. คำนวณคะแนนเฉลี่ยรวม
$stmt_avg = $pdo->query("SELECT AVG(score) FROM evaluation_answers");
$avg_score = $stmt_avg->fetchColumn();
$avg_score_formatted = $avg_score !== false && $avg_score !== null ? number_format((float)$avg_score, 2) : '0.00';

// กำหนดตัวแปรสำหรับ layout
$current_page = 'overview';
$page_title = 'ภาพรวม - แผงควบคุมผู้ดูแลระบบ';
$breadcrumb_sub = 'ภาพรวม';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Header Title -->
<div class="mb-8">
    <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">ADMIN CONSOLE</span>
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">แผงควบคุมผู้ดูแลระบบ</h1>
    <p class="text-sm text-gray-500 mt-1">ภาพรวมการประเมินกิจกรรมและเมนูการจัดการ</p>
</div>

<!-- 4 Stat Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Card 1: นักศึกษาทั้งหมด -->
    <div class="bg-white rounded-2xl p-5 border-l-4 border-[#8a5823] shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-[#fbf4eb] text-[#8a5823] flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-900 leading-tight"><?= $total_students ?></div>
            <div class="text-xs text-gray-500 mt-0.5">นักศึกษาทั้งหมด</div>
        </div>
    </div>

    <!-- Card 2: กิจกรรมทั้งหมด -->
    <div class="bg-white rounded-2xl p-5 border-l-4 border-sky-500 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-900 leading-tight"><?= $total_activities ?></div>
            <div class="text-xs text-gray-500 mt-0.5">กิจกรรมเปิดใช้งาน</div>
        </div>
    </div>

    <!-- Card 3: ประเมินแล้ว -->
    <div class="bg-white rounded-2xl p-5 border-l-4 border-emerald-500 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-900 leading-tight"><?= $evaluated_count ?></div>
            <div class="text-xs text-gray-500 mt-0.5">ประเมินแล้ว (<?= $evaluated_percent ?>%)</div>
        </div>
    </div>

    <!-- Card 4: คะแนนเฉลี่ยรวม -->
    <div class="bg-white rounded-2xl p-5 border-l-4 border-amber-500 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-gray-900 leading-tight"><?= $avg_score_formatted ?></div>
            <div class="text-xs text-gray-500 mt-0.5">คะแนนเฉลี่ยรวม</div>
        </div>
    </div>
</div>

<!-- Quick Navigation Action Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Card 1: จัดการคำถามและเวลา -->
    <a href="questions.php" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl border border-[#8a5823]/30 text-[#8a5823] flex items-center justify-center mb-4 group-hover:bg-[#8a5823] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-gray-800 group-hover:text-[#8a5823] transition-colors">จัดการคำถามและเวลา</h2>
            <p class="text-xs text-gray-500 mt-1">เพิ่ม-แก้ไขคำถาม และตั้งเวลาเปิด-ปิด</p>
        </div>
    </a>

    <!-- Card 2: จัดการนักศึกษา -->
    <a href="students.php" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl border border-[#8a5823]/30 text-[#8a5823] flex items-center justify-center mb-4 group-hover:bg-[#8a5823] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-gray-800 group-hover:text-[#8a5823] transition-colors">จัดการนักศึกษา</h2>
            <p class="text-xs text-gray-500 mt-1">เพิ่มบัญชี รีเซ็ตรหัสผ่าน</p>
        </div>
    </a>

    <!-- Card 3: ผลประเมิน (ตาราง) -->
    <a href="results.php" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl border border-[#8a5823]/30 text-[#8a5823] flex items-center justify-center mb-4 group-hover:bg-[#8a5823] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-gray-800 group-hover:text-[#8a5823] transition-colors">ผลประเมิน (ตาราง)</h2>
            <p class="text-xs text-gray-500 mt-1">สรุปคะแนนรายข้อ + ข้อเสนอแนะ</p>
        </div>
    </a>

    <!-- Card 4: แดชบอร์ด (กราฟ) -->
    <a href="dashboard.php" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between">
        <div>
            <div class="w-10 h-10 rounded-xl border border-[#8a5823]/30 text-[#8a5823] flex items-center justify-center mb-4 group-hover:bg-[#8a5823] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-gray-800 group-hover:text-[#8a5823] transition-colors">แดชบอร์ด (กราฟ)</h2>
            <p class="text-xs text-gray-500 mt-1">มองเห็นผลเป็นภาพ</p>
        </div>
    </a>
</div>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
