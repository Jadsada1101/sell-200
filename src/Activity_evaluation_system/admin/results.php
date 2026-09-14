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

// ดึงข้อคำถามประเภท rating ของกิจกรรมที่เลือก
$stmt_q = $pdo->prepare("
    SELECT id, question_text, sort_order 
    FROM questions 
    WHERE activity_id = ? AND question_type = 'rating' AND status = 'active'
    ORDER BY sort_order ASC, id ASC
");
$stmt_q->execute([$selected_activity_id]);
$rating_questions = $stmt_q->fetchAll();
$total_rating_q_count = count($rating_questions);

// ดึงจำนวนผู้ตอบแบบประเมินของกิจกรรมที่เลือก
$stmt_respondents = $pdo->prepare("SELECT COUNT(*) FROM evaluations WHERE activity_id = ?");
$stmt_respondents->execute([$selected_activity_id]);
$total_respondents = (int)$stmt_respondents->fetchColumn();

// คำนวณสถิติรายข้อ
$question_stats = [];
$total_all_score = 0;
$total_all_answers = 0;

foreach ($rating_questions as $q) {
    $qid = $q['id'];

    // นับจำนวนคนที่ให้คะแนน 5, 4, 3, 2, 1
    $stmt_score = $pdo->prepare("
        SELECT ea.score, COUNT(*) as count 
        FROM evaluation_answers ea
        JOIN evaluations e ON ea.evaluation_id = e.id
        WHERE ea.question_id = ? AND e.activity_id = ?
        GROUP BY ea.score
    ");
    $stmt_score->execute([$qid, $selected_activity_id]);
    $scores = $stmt_score->fetchAll(PDO::FETCH_KEY_PAIR);

    $c5 = $scores[5] ?? 0;
    $c4 = $scores[4] ?? 0;
    $c3 = $scores[3] ?? 0;
    $c2 = $scores[2] ?? 0;
    $c1 = $scores[1] ?? 0;
    $respondent_count = $c5 + $c4 + $c3 + $c2 + $c1;

    $sum_score = ($c5 * 5) + ($c4 * 4) + ($c3 * 3) + ($c2 * 2) + ($c1 * 1);
    $avg = $respondent_count > 0 ? ($sum_score / $respondent_count) : 0;

    $total_all_score += $sum_score;
    $total_all_answers += $respondent_count;

    $question_stats[] = [
        'id' => $qid,
        'text' => $q['question_text'],
        'c5' => $c5,
        'c4' => $c4,
        'c3' => $c3,
        'c2' => $c2,
        'c1' => $c1,
        'total' => $respondent_count,
        'avg' => $avg
    ];
}

$overall_avg = $total_all_answers > 0 ? ($total_all_score / $total_all_answers) : 0;
$overall_avg_formatted = $total_all_answers > 0 ? number_format($overall_avg, 2) : '0.00';

// ดึงข้อเสนอแนะอื่นๆ ของกิจกรรมที่เลือก
$stmt_suggestions = $pdo->prepare("
    SELECT e.suggestion, e.submitted_at, u.fullname 
    FROM evaluations e
    JOIN users u ON e.student_id = u.id
    WHERE e.activity_id = ? AND e.suggestion IS NOT NULL AND TRIM(e.suggestion) != ''
    ORDER BY e.submitted_at DESC
");
$stmt_suggestions->execute([$selected_activity_id]);
$suggestions = $stmt_suggestions->fetchAll();
$suggestion_count = count($suggestions);

// ระบบส่งออก CSV (Export CSV)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // รองรับทั้งพยัญชนะ (\p{L}) และสระ/วรรณยุกต์ภาษาไทย (\p{M}) เพื่อไม่ให้สระและวรรณยุกต์ถูกตัดเป็น _
    $act_name_clean = trim(preg_replace('/[^\p{L}\p{M}\p{N}\-_]+/u', '_', $current_activity['name'] ?? 'กิจกรรม'), '_');
    $filename = 'สรุปผล_' . $act_name_clean . '_' . date('Y-m-d') . '.csv';
    $encoded_filename = rawurlencode($filename);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encoded_filename);
    
    // ใส่ BOM เพื่อให้ Excel เปิดภาษาไทยได้โดยไม่เป็นภาษาต่างดาว
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ข้อ', 'รายการประเมิน', 'ระดับ 5', 'ระดับ 4', 'ระดับ 3', 'ระดับ 2', 'ระดับ 1', 'จำนวนผู้ตอบ', 'ค่าเฉลี่ย']);

    foreach ($question_stats as $idx => $row) {
        fputcsv($output, [
            $idx + 1,
            $row['text'],
            $row['c5'],
            $row['c4'],
            $row['c3'],
            $row['c2'],
            $row['c1'],
            $row['total'],
            number_format($row['avg'], 2)
        ]);
    }

    fputcsv($output, []);
    fputcsv($output, ['', 'ค่าเฉลี่ยรวมทั้งหมด', '', '', '', '', '', $total_respondents . ' คน', $overall_avg_formatted]);
    fclose($output);
    exit;
}

// กำหนดตัวแปรสำหรับ layout
$current_page = 'results';
$page_title = 'สรุปผลการประเมิน';
$breadcrumb_sub = 'ผลการประเมิน';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Header Title & Export Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">RESULTS</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">สรุปผลการประเมิน</h1>
        <p class="text-sm text-gray-500 mt-1">
            กิจกรรม: <strong class="text-gray-800"><?= htmlspecialchars($current_activity['name'] ?? '') ?></strong>
        </p>
    </div>
    <div>
        <a 
            href="results.php?activity_id=<?= $selected_activity_id ?>&export=csv" 
            class="inline-flex items-center gap-2 bg-[#8a5823] hover:bg-[#724719] text-white text-sm font-medium py-2.5 px-5 rounded-xl shadow transition-colors cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>ส่งออก CSV</span>
        </a>
    </div>
</div>

<!-- Activity Selection Tabs -->
<div class="mb-6">
    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">เลือกกิจกรรมที่ต้องการดูผลสรุป:</label>
    <div class="flex flex-wrap items-center gap-2">
        <?php foreach ($activities as $act): ?>
            <?php $is_active_tab = ($act['id'] == $selected_activity_id); ?>
            <a 
                href="results.php?activity_id=<?= $act['id'] ?>"
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

<!-- 3 Stat Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    <!-- Card 1: ผู้ตอบ -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <span class="text-xs font-semibold text-gray-400 uppercase">ผู้ตอบแบบประเมิน</span>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-0.5">
                <?= $total_respondents ?> <span class="text-sm font-normal text-gray-500">คน</span>
            </div>
        </div>
    </div>

    <!-- Card 2: คะแนนเฉลี่ย -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#8a5823] flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-amber-500 fill-amber-400" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        <div>
            <span class="text-xs font-semibold text-gray-400 uppercase">คะแนนเฉลี่ยรวม</span>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-0.5">
                <?= $overall_avg_formatted ?> <span class="text-sm font-normal text-gray-500">/ 5.00</span>
            </div>
        </div>
    </div>

    <!-- Card 3: จำนวนข้อ -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div>
            <span class="text-xs font-semibold text-gray-400 uppercase">จำนวนข้อคำถาม</span>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-0.5">
                <?= $total_rating_q_count ?> <span class="text-sm font-normal text-gray-500">ข้อ</span>
            </div>
        </div>
    </div>
</div>

<!-- Results Table Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-800 text-sm sm:text-base">ตารางสรุปคะแนนประเมินรายข้อ</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 bg-gray-50/50">
                    <th class="py-4 px-4 w-12 text-center">ข้อ</th>
                    <th class="py-4 px-6 min-w-[240px]">รายการประเมิน</th>
                    <th class="py-4 px-3 w-16 text-center">มากที่สุด (5)</th>
                    <th class="py-4 px-3 w-16 text-center">มาก (4)</th>
                    <th class="py-4 px-3 w-16 text-center">ปานกลาง (3)</th>
                    <th class="py-4 px-3 w-16 text-center">น้อย (2)</th>
                    <th class="py-4 px-3 w-16 text-center">น้อยที่สุด (1)</th>
                    <th class="py-4 px-4 w-20 text-center">ผู้ตอบ</th>
                    <th class="py-4 px-4 w-24 text-center">ค่าเฉลี่ย</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs sm:text-sm text-gray-700">
                <?php if (empty($question_stats)): ?>
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-400">ยังไม่มีข้อมูลคำถามในกิจกรรมนี้</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($question_stats as $idx => $row): ?>
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-4 px-4 text-center font-medium text-gray-900"><?= $idx + 1 ?></td>
                            <td class="py-4 px-6 font-normal text-gray-800"><?= htmlspecialchars($row['text']) ?></td>
                            <td class="py-4 px-3 text-center text-gray-600 font-mono"><?= $row['c5'] ?></td>
                            <td class="py-4 px-3 text-center text-gray-600 font-mono"><?= $row['c4'] ?></td>
                            <td class="py-4 px-3 text-center text-gray-600 font-mono"><?= $row['c3'] ?></td>
                            <td class="py-4 px-3 text-center text-gray-600 font-mono"><?= $row['c2'] ?></td>
                            <td class="py-4 px-3 text-center text-gray-600 font-mono"><?= $row['c1'] ?></td>
                            <td class="py-4 px-4 text-center text-gray-700 font-mono font-medium"><?= $row['total'] ?></td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-block px-2.5 py-1 bg-amber-50 text-[#8a5823] font-bold rounded-lg text-xs font-mono">
                                    <?= number_format($row['avg'], 2) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($question_stats)): ?>
                <tfoot>
                    <tr class="bg-gray-50/80 font-bold text-xs sm:text-sm text-gray-900 border-t-2 border-gray-200">
                        <td colspan="2" class="py-4 px-6 text-right">ค่าเฉลี่ยรวมทั้งหมด:</td>
                        <td colspan="5"></td>
                        <td class="py-4 px-4 text-center font-mono"><?= $total_respondents ?></td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-block px-3 py-1 bg-[#8a5823] text-white font-bold rounded-lg text-xs font-mono">
                                <?= $overall_avg_formatted ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Suggestions Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-bold text-gray-800 text-sm sm:text-base">ข้อเสนอแนะเพิ่มเติมจากนักศึกษา</h3>
        <span class="text-xs font-semibold text-gray-400"><?= $suggestion_count ?> ข้อความ</span>
    </div>
    <div class="p-6">
        <?php if (empty($suggestions)): ?>
            <p class="text-center text-gray-400 py-6 text-sm">ยังไม่มีข้อเสนอแนะเพิ่มเติมในกิจกรรมนี้</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($suggestions as $s): ?>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-sm text-gray-800"><?= nl2br(htmlspecialchars($s['suggestion'])) ?></p>
                        <div class="mt-2 flex items-center justify-between text-[11px] text-gray-400">
                            <span>ผู้ตอบ: <?= htmlspecialchars($s['fullname']) ?></span>
                            <span><?= date('d/m/Y H:i น.', strtotime($s['submitted_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
