<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// ดึงรายการกิจกรรมทั้งหมด
$stmt_acts = $pdo->query("SELECT * FROM activities ORDER BY id ASC");
$activities = $stmt_acts->fetchAll();

$selected_activity_id = (int)($_GET['activity_id'] ?? 0);
$current_activity = null;

if ($selected_activity_id > 0) {
    foreach ($activities as $act) {
        if ($act['id'] == $selected_activity_id) {
            $current_activity = $act;
            break;
        }
    }
}

if (!$current_activity && !empty($activities)) {
    $current_activity = $activities[0];
    $selected_activity_id = $current_activity['id'];
}

// ดึงจำนวนนักศึกษาทั้งหมด
$stmt_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$total_students = (int)$stmt_students->fetchColumn();

// ดึงจำนวนผู้ตอบแบบประเมินสำหรับกิจกรรมนี้
$stmt_resp = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE activity_id = ?");
$stmt_resp->execute([$selected_activity_id]);
$answered_count = (int)$stmt_resp->fetchColumn();
$unanswered_count = max(0, $total_students - $answered_count);

// ดึงข้อคำถามประเภท rating ของกิจกรรมนี้
$stmt_q = $pdo->prepare("
    SELECT id, question_text, sort_order 
    FROM questions 
    WHERE activity_id = ? AND question_type = 'rating' AND status = 'active'
    ORDER BY sort_order ASC, id ASC
");
$stmt_q->execute([$selected_activity_id]);
$rating_questions = $stmt_q->fetchAll();

$chart_labels = [];
$avg_scores = [];
$distribution = [
    'c5' => [],
    'c4' => [],
    'c3' => [],
    'c2' => [],
    'c1' => []
];

foreach ($rating_questions as $idx => $q) {
    $qid = $q['id'];
    $chart_labels[] = 'ข้อ ' . ($idx + 1);

    // ดึงคะแนน
    $stmt_s = $pdo->prepare("
        SELECT ea.score, COUNT(*) as count 
        FROM evaluation_answers ea
        JOIN evaluations e ON ea.evaluation_id = e.id
        WHERE ea.question_id = ? AND e.activity_id = ?
        GROUP BY ea.score
    ");
    $stmt_s->execute([$qid, $selected_activity_id]);
    $scores = $stmt_s->fetchAll(PDO::FETCH_KEY_PAIR);

    $s5 = $scores[5] ?? 0;
    $s4 = $scores[4] ?? 0;
    $s3 = $scores[3] ?? 0;
    $s2 = $scores[2] ?? 0;
    $s1 = $scores[1] ?? 0;
    $total_q_resp = $s5 + $s4 + $s3 + $s2 + $s1;

    $avg = $total_q_resp > 0 ? (($s5 * 5 + $s4 * 4 + $s3 * 3 + $s2 * 2 + $s1 * 1) / $total_q_resp) : 0;
    $avg_scores[] = round($avg, 2);

    $distribution['c5'][] = $s5;
    $distribution['c4'][] = $s4;
    $distribution['c3'][] = $s3;
    $distribution['c2'][] = $s2;
    $distribution['c1'][] = $s1;
}

// กำหนดตัวแปร layout
$current_page = 'chart_dashboard';
$page_title = 'แดชบอร์ดสรุปผลการประเมิน';
$breadcrumb_sub = 'แดชบอร์ด';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Header Title -->
<div class="mb-6">
    <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">DASHBOARD</span>
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">แดชบอร์ดสรุปผลการประเมิน</h1>
    <p class="text-sm text-gray-500 mt-1">
        กิจกรรม: <strong class="text-gray-800"><?= htmlspecialchars($current_activity['name'] ?? '') ?></strong> · ผู้ตอบแล้ว <?= $answered_count ?>/<?= $total_students ?> คน
    </p>
</div>

<!-- Activity Selection Tabs -->
<div class="mb-6">
    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">เลือกกิจกรรมที่ต้องการดูแดชบอร์ด:</label>
    <div class="flex flex-wrap items-center gap-2">
        <?php foreach ($activities as $act): ?>
            <?php $is_active_tab = ($act['id'] == $selected_activity_id); ?>
            <a 
                href="dashboard.php?activity_id=<?= $act['id'] ?>"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all <?= $is_active_tab ? 'bg-[#8a5823] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-amber-50/50 border border-gray-200 hover:border-amber-300' ?>"
            >
                <svg class="w-4 h-4 <?= $is_active_tab ? 'text-amber-200' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span><?= htmlspecialchars($act['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($rating_questions)): ?>
    <div class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-100 shadow-sm mb-6">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p class="text-base font-semibold text-gray-700">ยังไม่มีข้อคำถามในกิจกรรมนี้</p>
        <p class="text-xs text-gray-500 mt-1">กรุณาเพิ่มข้อคำถามในเมนู "จัดการข้อคำถาม" ก่อนแสดงกราฟ</p>
        <a href="questions.php?activity_id=<?= $selected_activity_id ?>" class="mt-4 inline-block px-4 py-2 bg-[#8a5823] text-white rounded-xl text-xs font-medium hover:bg-[#724719] transition-colors">
            ไปที่จัดการข้อคำถาม
        </a>
    </div>
<?php else: ?>
    <!-- Row 1: Bar Chart & Doughnut Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left: คะแนนเฉลี่ยรายข้อ (เต็ม 5) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <h2 class="font-bold text-gray-800 text-sm sm:text-base mb-4">คะแนนเฉลี่ยรายข้อ (เต็ม 5)</h2>
            <div class="h-64 sm:h-72 w-full relative">
                <canvas id="barAvgChart"></canvas>
            </div>
        </div>

        <!-- Right: สัดส่วนผู้ตอบแบบประเมิน -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <h2 class="font-bold text-gray-800 text-sm sm:text-base mb-4">สัดส่วนผู้ตอบแบบประเมิน</h2>
            <div class="h-56 sm:h-64 w-full relative flex items-center justify-center">
                <canvas id="doughnutRespChart"></canvas>
            </div>
            <div class="flex items-center justify-center gap-6 text-xs text-gray-600 mt-4 pt-3 border-t border-gray-50">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#8a5823]"></span>
                    <span>ตอบแล้ว (<?= $answered_count ?>)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                    <span>ยังไม่ตอบ (<?= $unanswered_count ?>)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Stacked Bar Chart การกระจายคะแนน -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
        <h2 class="font-bold text-gray-800 text-sm sm:text-base mb-4">การกระจายคะแนนแต่ละระดับ (1-5)</h2>
        <div class="h-72 sm:h-80 w-full relative">
            <canvas id="stackedDistChart"></canvas>
        </div>
    </div>

    <!-- Row 3: Question Legend / Reference List -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 text-sm sm:text-base mb-4">รายการคำอธิบายหมายเลขข้อคำถาม</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs sm:text-sm">
            <?php foreach ($rating_questions as $idx => $q): ?>
                <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-gray-50/70 border border-gray-100">
                    <span class="w-6 h-6 rounded-md bg-[#8a5823] text-white font-bold text-xs flex items-center justify-center shrink-0">
                        <?= $idx + 1 ?>
                    </span>
                    <span class="text-gray-700 pt-0.5 leading-relaxed"><?= htmlspecialchars($q['question_text']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chart.js Scripts Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Chart.js Default Font Setting
            Chart.defaults.font.family = 'Prompt, sans-serif';

            const chartLabels = <?= json_encode($chart_labels, JSON_UNESCAPED_UNICODE) ?>;
            const avgScores = <?= json_encode($avg_scores) ?>;

            // 1. Bar Chart: คะแนนเฉลี่ยรายข้อ
            const ctxBar = document.getElementById('barAvgChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'คะแนนเฉลี่ย',
                        data: avgScores,
                        backgroundColor: '#8a5823',
                        borderRadius: 8,
                        barThickness: 24,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 0,
                            max: 5,
                            ticks: { stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // 2. Doughnut Chart: สัดส่วนผู้ตอบ
            const ctxDoughnut = document.getElementById('doughnutRespChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['ตอบแล้ว', 'ยังไม่ตอบ'],
                    datasets: [{
                        data: [<?= $answered_count ?>, <?= $unanswered_count ?>],
                        backgroundColor: ['#8a5823', '#e5e7eb'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // 3. Stacked Bar Chart: การกระจายคะแนนแต่ละระดับ (1-5)
            const ctxStacked = document.getElementById('stackedDistChart').getContext('2d');
            new Chart(ctxStacked, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { label: 'ระดับ 5 (มากที่สุด)', data: <?= json_encode($distribution['c5']) ?>, backgroundColor: '#059669', borderRadius: 4 },
                        { label: 'ระดับ 4 (มาก)', data: <?= json_encode($distribution['c4']) ?>, backgroundColor: '#10b981', borderRadius: 4 },
                        { label: 'ระดับ 3 (ปานกลาง)', data: <?= json_encode($distribution['c3']) ?>, backgroundColor: '#f59e0b', borderRadius: 4 },
                        { label: 'ระดับ 2 (น้อย)', data: <?= json_encode($distribution['c2']) ?>, backgroundColor: '#f97316', borderRadius: 4 },
                        { label: 'ระดับ 1 (น้อยที่สุด)', data: <?= json_encode($distribution['c1']) ?>, backgroundColor: '#ef4444', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
