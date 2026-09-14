<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/period_helper.php';

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['fullname'] ?? 'นักศึกษา';

// 1. ดึงรายการกิจกรรมทั้งหมดที่เปิดใช้งาน
$stmt_acts = $pdo->query("SELECT * FROM activities WHERE status = 'active' ORDER BY id ASC");
$all_activities = $stmt_acts->fetchAll();

// 2. ดึงแบบประเมินที่นักศึกษาคนนี้เคยส่งแล้ว (เก็บเป็น map ด้วย activity_id)
$stmt_my_evals = $pdo->prepare("SELECT activity_id, id, submitted_at FROM evaluations WHERE student_id = ?");
$stmt_my_evals->execute([$student_id]);
$my_evals = [];
while ($row = $stmt_my_evals->fetch()) {
    $my_evals[$row['activity_id']] = $row;
}

// 3. ดึงจำนวนคำถามที่เปิดใช้งานแยกตามกิจกรรม
$stmt_q_counts = $pdo->query("SELECT activity_id, COUNT(*) as c FROM questions WHERE status = 'active' GROUP BY activity_id");
$q_counts = $stmt_q_counts->fetchAll(PDO::FETCH_KEY_PAIR);

$activities = [];
$open_activities_count = 0;
foreach ($all_activities as $act) {
    $aid = $act['id'];
    $act_status = check_activity_evaluation_status($pdo, $aid);
    $act['is_open'] = $act_status['is_open'];
    $act['period_text'] = $act_status['period_text'];
    $act['status_reason'] = $act_status['reason'];
    if ($act['is_open']) {
        $open_activities_count++;
    }

    $act['question_count'] = $q_counts[$aid] ?? 0;
    $act['evaluation_id'] = $my_evals[$aid]['id'] ?? null;
    $act['submitted_at'] = $my_evals[$aid]['submitted_at'] ?? null;
    $activities[] = $act;
}

// คำนวณสถิติของนักศึกษา
$total_activities = count($activities);
$completed_activities = 0;
foreach ($activities as $act) {
    if (!empty($act['evaluation_id'])) {
        $completed_activities++;
    }
}

// กำหนดตัวแปร layout
$current_page = 'dashboard';
$page_title = 'หน้าแรก';
$breadcrumb_sub = 'หน้าแรก';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Header Title -->
<div class="mb-8">
    <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">STUDENT PORTAL</span>
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">สวัสดี, <?= htmlspecialchars($student_name) ?></h1>
    <p class="text-sm text-gray-500 mt-1">ยินดีต้อนรับสู่ระบบประเมินการจัดกิจกรรม มหาวิทยาลัยเทคโนโลยีราชมงคลล้านนา</p>
</div>

<!-- 2 Status & Progress Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mb-8">
    
    <!-- Card 1: กิจกรรมที่เปิดรับการประเมิน -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2.5 text-sky-600 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="text-base font-bold text-gray-800">กิจกรรมที่เปิดรับการประเมิน</h2>
            </div>

            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-3xl font-extrabold text-sky-700"><?= $open_activities_count ?></span>
                <span class="text-sm text-gray-500 font-medium">/ <?= $total_activities ?> กิจกรรม</span>
            </div>
            <p class="text-xs text-gray-400">
                <?= $open_activities_count > 0 ? 'มีกิจกรรมที่เปิดให้เข้าประเมินได้ในขณะนี้' : 'ขณะนี้ยังไม่มีกิจกรรมที่เปิดรับการประเมิน' ?>
            </p>
        </div>

        <div class="text-xs text-gray-500 pt-3 border-t border-gray-100 flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full <?= $open_activities_count > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' ?>"></span>
            <span><?= $open_activities_count > 0 ? 'ระบบพร้อมรับการประเมิน' : 'รอเปิดรับการประเมิน' ?></span>
        </div>
    </div>

    <!-- Card 2: ภาพรวมการประเมินของฉัน -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2.5 text-[#8a5823] mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h2 class="text-base font-bold text-gray-800">ความคืบหน้าของฉัน</h2>
            </div>

            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-3xl font-extrabold text-[#54381e]"><?= $completed_activities ?></span>
                <span class="text-sm text-gray-500 font-medium">/ <?= $total_activities ?> กิจกรรม</span>
            </div>
            <p class="text-xs text-gray-400">
                <?= ($completed_activities === $total_activities && $total_activities > 0) ? 'ท่านประเมินครบทุกกิจกรรมแล้ว' : 'ยังมีกิจกรรมที่รอการประเมิน' ?>
            </p>
        </div>

        <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden mt-4">
            <?php $percent = $total_activities > 0 ? round(($completed_activities / $total_activities) * 100) : 0; ?>
            <div class="bg-[#8a5823] h-full rounded-full transition-all duration-500" style="width: <?= $percent ?>%;"></div>
        </div>
    </div>
</div>

<!-- Available Activities List for Evaluation -->
<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#8a5823]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>รายการกิจกรรมที่เปิดให้ประเมิน</span>
        </h2>
        <span class="text-xs text-gray-500"><?= count($activities) ?> กิจกรรม</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($activities as $act): ?>
            <?php $is_done = !empty($act['evaluation_id']); ?>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <!-- Card Top Header -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold <?= $is_done ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' ?>">
                            <?= $is_done ? '✓ ประเมินแล้ว' : '• ยังไม่ประเมิน' ?>
                        </span>
                        <span class="text-xs text-gray-400 font-mono">
                            <?= $act['question_count'] ?> ข้อคำถาม
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-bold text-gray-900 mb-1.5 leading-snug">
                        <?= htmlspecialchars($act['name']) ?>
                    </h3>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-2">
                        <?= !empty($act['description']) ? htmlspecialchars($act['description']) : 'แบบประเมินความพึงพอใจต่อการดำเนินกิจกรรม' ?>
                    </p>

                    <!-- Schedule Info -->
                    <div class="flex items-center gap-1 text-[11px] text-gray-400 font-mono mb-2">
                        <svg class="w-3.5 h-3.5 text-[#8a5823]/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate"><?= htmlspecialchars($act['period_text']) ?></span>
                    </div>
                </div>

                <!-- Card Bottom Action -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <?php if ($is_done): ?>
                        <span class="text-[11px] text-gray-400">
                            <?= date('d/m/Y', strtotime($act['submitted_at'])) ?>
                        </span>
                        <?php if ($act['is_open']): ?>
                            <a 
                                href="evaluate.php?activity_id=<?= $act['id'] ?>"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 px-3.5 py-2 rounded-xl transition-colors shadow-2xs"
                                title="แบบประเมินยังเปิดอยู่ สามารถคลิกเพื่อดูหรือแก้ไขคำตอบได้"
                            >
                                <span>ดู / แก้ไขผลที่ส่ง</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <a 
                                href="evaluate.php?activity_id=<?= $act['id'] ?>"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 border border-gray-200 px-3.5 py-2 rounded-xl transition-colors"
                            >
                                <span>ดูผลที่ส่ง</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($act['is_open']): ?>
                            <span class="text-[11px] text-amber-600 font-medium">รอการประเมิน</span>
                            <a 
                                href="evaluate.php?activity_id=<?= $act['id'] ?>"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-[#8a5823] hover:bg-[#724719] px-4 py-2 rounded-xl shadow-xs transition-colors"
                            >
                                <span>ทำแบบประเมิน</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="text-[11px] text-gray-400" title="<?= htmlspecialchars($act['status_reason']) ?>">ปิดรับประเมิน</span>
                            <a 
                                href="evaluate.php?activity_id=<?= $act['id'] ?>"
                                class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-xl transition-colors"
                            >
                                <span>ดูแบบประเมิน</span>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
