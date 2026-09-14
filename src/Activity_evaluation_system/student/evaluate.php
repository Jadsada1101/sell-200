<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/period_helper.php';

$student_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// ตรวจสอบกิจกรรมที่เลือกประเมิน
$activity_id = (int)($_GET['activity_id'] ?? ($_POST['activity_id'] ?? 1));

// ดึงข้อมูลกิจกรรม
$stmt_act = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
$stmt_act->execute([$activity_id]);
$activity = $stmt_act->fetch();

if (!$activity) {
    // หากไม่พบกิจกรรม ให้ดึงกิจกรรมแรก
    $stmt_act_first = $pdo->query("SELECT * FROM activities ORDER BY id ASC LIMIT 1");
    $activity = $stmt_act_first->fetch();
    $activity_id = $activity ? (int)$activity['id'] : 1;
}

// ตรวจสอบสถานะการเปิดรับการประเมินเฉพาะกิจกรรมนี้
$status_info = check_activity_evaluation_status($pdo, $activity_id);
$is_open = $status_info['is_open'];

// ตรวจสอบว่าเคยส่งแบบประเมินสำหรับกิจกรรมนี้แล้วหรือไม่
$stmt_eval = $pdo->prepare("SELECT * FROM evaluations WHERE student_id = ? AND activity_id = ? LIMIT 1");
$stmt_eval->execute([$student_id, $activity_id]);
$existing_eval = $stmt_eval->fetch();
$has_evaluated = (bool)$existing_eval;

// ดึงคำตอบเดิมของนักศึกษา (ถ้ามี)
$existing_answers = [];
if ($has_evaluated) {
    $stmt_ans = $pdo->prepare("SELECT question_id, score FROM evaluation_answers WHERE evaluation_id = ?");
    $stmt_ans->execute([$existing_eval['id']]);
    $existing_answers = $stmt_ans->fetchAll(PDO::FETCH_KEY_PAIR);
}

// จัดการ Request: ส่งหรืออัปเดตแบบประเมิน (Submit / Update Evaluation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_open) {
        $error_msg = 'ขณะนี้ยังไม่เปิดหรือปิดรับการประเมินแล้ว (' . $status_info['reason'] . ') ไม่สามารถบันทึกได้';
    } else {
        $scores = $_POST['scores'] ?? [];
        $suggestion = trim($_POST['suggestion'] ?? '');

        // ดึงข้อคำถามประเภท rating ทั้งหมดของกิจกรรมนี้
        $stmt_check_q = $pdo->prepare("SELECT id FROM questions WHERE activity_id = ? AND question_type = 'rating' AND status = 'active'");
        $stmt_check_q->execute([$activity_id]);
        $required_q_ids = $stmt_check_q->fetchAll(PDO::FETCH_COLUMN);

        // ตรวจสอบว่าตอบครบทุกข้อหรือไม่
        $missing_any = false;
        foreach ($required_q_ids as $qid) {
            if (!isset($scores[$qid]) || empty($scores[$qid]) || !in_array((int)$scores[$qid], [1, 2, 3, 4, 5])) {
                $missing_any = true;
                break;
            }
        }

        if ($missing_any) {
            $error_msg = 'กรุณาให้คะแนนความพึงพอใจให้ครบทุกข้อ (ข้อ 1 ถึง 5)';
        } else {
            try {
                $pdo->beginTransaction();

                if ($has_evaluated) {
                    // กรณีเคยส่งแล้ว แต่แบบประเมินยังเปิดอยู่ -> ทำการ UPDATE คำตอบ
                    $eval_id = (int)$existing_eval['id'];

                    $stmt_update_eval = $pdo->prepare("
                        UPDATE evaluations 
                        SET suggestion = ?, submitted_at = NOW() 
                        WHERE id = ? AND student_id = ?
                    ");
                    $stmt_update_eval->execute([!empty($suggestion) ? $suggestion : null, $eval_id, $student_id]);

                    // ลบคำตอบเดิมของแบบประเมินนี้
                    $stmt_del_ans = $pdo->prepare("DELETE FROM evaluation_answers WHERE evaluation_id = ?");
                    $stmt_del_ans->execute([$eval_id]);

                    // บันทึกคำตอบใหม่
                    $stmt_insert_ans = $pdo->prepare("
                        INSERT INTO evaluation_answers (evaluation_id, question_id, score) 
                        VALUES (?, ?, ?)
                    ");
                    foreach ($required_q_ids as $qid) {
                        $score_val = (int)$scores[$qid];
                        $stmt_insert_ans->execute([$eval_id, $qid, $score_val]);
                    }

                    $pdo->commit();
                    $success_msg = 'บันทึกการแก้ไขแบบประเมินเรียบร้อยแล้ว';
                    $existing_eval['suggestion'] = $suggestion;
                    $existing_eval['submitted_at'] = date('Y-m-d H:i:s');
                    $existing_answers = $scores;

                } else {
                    // กรณียังไม่เคยส่ง -> บันทึกแบบประเมินใหม่
                    $stmt_insert_eval = $pdo->prepare("
                        INSERT INTO evaluations (student_id, activity_id, suggestion, submitted_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt_insert_eval->execute([$student_id, $activity_id, !empty($suggestion) ? $suggestion : null]);
                    $eval_id = $pdo->lastInsertId();

                    // บันทึกคะแนนรายข้อ
                    $stmt_insert_ans = $pdo->prepare("
                        INSERT INTO evaluation_answers (evaluation_id, question_id, score) 
                        VALUES (?, ?, ?)
                    ");
                    foreach ($required_q_ids as $qid) {
                        $score_val = (int)$scores[$qid];
                        $stmt_insert_ans->execute([$eval_id, $qid, $score_val]);
                    }

                    $pdo->commit();
                    $success_msg = 'บันทึกแบบประเมินเรียบร้อยแล้ว ขอบคุณที่ให้ความร่วมมือครับ';
                    $has_evaluated = true;
                    $existing_eval = [
                        'id' => $eval_id,
                        'activity_id' => $activity_id,
                        'suggestion' => $suggestion,
                        'submitted_at' => date('Y-m-d H:i:s')
                    ];
                    $existing_answers = $scores;
                }

            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage();
            }
        }
    }
}

// ดึงข้อคำถามทั้งหมดที่ active ของกิจกรรมนี้
$stmt_questions = $pdo->prepare("
    SELECT * FROM questions 
    WHERE activity_id = ? AND status = 'active' 
    ORDER BY sort_order ASC, id ASC
");
$stmt_questions->execute([$activity_id]);
$questions = $stmt_questions->fetchAll();

// กำหนดตัวแปร layout
$current_page = 'evaluate';
$page_title = 'ทำแบบประเมิน - ' . ($activity['name'] ?? '');
$breadcrumb_sub = 'ทำแบบประเมิน';

require_once __DIR__ . '/layout_top.php';
?>

<style>
    /* Custom CSS สำหรับ Radio Button กลมสไตล์ประเมิน */
    .scale-circle {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .scale-label {
        transition: color 0.2s ease, font-weight 0.2s ease;
    }
    .scale-radio:checked + label .scale-circle {
        background-color: #8a5823 !important;
        border-color: #8a5823 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 4px 10px -1px rgba(138, 88, 35, 0.35);
        transform: scale(1.1);
    }
    .scale-radio:checked + label .scale-label {
        color: #8a5823 !important;
        font-weight: 700 !important;
    }
    .scale-radio:not(:disabled) + label:hover .scale-circle {
        border-color: #8a5823;
        transform: scale(1.06);
    }
    .scale-radio:not(:disabled) + label:active .scale-circle {
        transform: scale(0.95);
    }
</style>

<!-- Header Title & Activity Name -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="dashboard.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#8a5823] hover:underline mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>กลับหน้าหลัก</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">EVALUATION FORM</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-900">
                <?= htmlspecialchars($activity['name']) ?>
            </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">แบบประเมิน: <?= htmlspecialchars($activity['name']) ?></h1>
        <p class="text-sm text-gray-500 mt-1">เลือกระดับความพึงพอใจในแต่ละข้อ · 1 = น้อยที่สุด , 5 = มากที่สุด</p>
    </div>
</div>

<!-- Alert Banner: สถานะปิด หรือ ส่งแล้ว -->
<?php if (!$is_open && !$has_evaluated): ?>
    <div class="mb-6 p-4 rounded-2xl bg-[#fdfaf5] border border-[#ede5d8] text-amber-900 text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
        <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>ขณะนี้ยังไม่เปิด / ปิดรับการประเมินแล้ว (<?= htmlspecialchars($status_info['reason']) ?>) — ดูได้แต่บันทึกไม่ได้</span>
    </div>
<?php endif; ?>

<?php if ($has_evaluated): ?>
    <?php if ($is_open): ?>
        <!-- กรณีเคยประเมินแล้วและระบบยังเปิดอยู่: ให้แก้ไขได้ -->
        <div class="mb-6 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-900 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <div>
                    <span class="font-bold">ท่านเคยส่งแบบประเมินสำหรับกิจกรรมนี้แล้ว</span>
                    <span class="text-xs text-amber-700 block sm:inline sm:ml-1">
                        (ส่งล่าสุดเมื่อ <?= date('d/m/Y H:i น.', strtotime($existing_eval['submitted_at'])) ?>)
                    </span>
                    <p class="text-xs text-amber-800/80 mt-0.5">
                        เนื่องจากแบบประเมินยังเปิดอยู่ ท่านสามารถแก้ไขคำตอบและกดปุ่ม <strong>"บันทึกการแก้ไข"</strong> ด้านล่างเพื่ออัปเดตได้
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 bg-amber-100 text-amber-900 border border-amber-200 rounded-lg shrink-0 self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                <span>เปิดให้แก้ไขได้</span>
            </span>
        </div>
    <?php else: ?>
        <!-- กรณีเคยประเมินแล้วแต่ระบบปิดแล้ว: ดูย้อนหลังได้อย่างเดียว -->
        <div class="mb-6 p-4 rounded-2xl bg-gray-50 border border-gray-200 text-gray-700 text-xs sm:text-sm flex items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <div>
                    <span class="font-bold">ท่านได้ส่งแบบประเมินสำหรับกิจกรรมนี้แล้ว</span>
                    <span class="text-xs text-gray-500 block sm:inline sm:ml-2">
                        (ส่งเมื่อ <?= date('d/m/Y H:i น.', strtotime($existing_eval['submitted_at'])) ?>)
                    </span>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-gray-200 text-gray-700 rounded-lg shrink-0">
                โหมดดูคำตอบย้อนหลัง (ปิดรับแล้ว)
            </span>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($success_msg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-center gap-2.5 shadow-xs">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
<?php endif; ?>

<?php
    $can_edit = $is_open;
    $is_disabled = !$can_edit;
    $confirm_title = $has_evaluated ? 'ยืนยันการบันทึกการแก้ไข?' : 'ยืนยันการส่งแบบประเมิน?';
    $confirm_text = $has_evaluated ? 'คุณต้องการอัปเดตคำตอบของแบบประเมินนี้ใช่หรือไม่?' : 'ยืนยันที่จะส่งแบบประเมินนี้หรือไม่? (สามารถกลับมาแก้ไขได้ตลอดช่วงเวลาที่เปิดรับ)';
    $confirm_btn = $has_evaluated ? 'ใช่, บันทึกการแก้ไข' : 'ยืนยันส่งแบบประเมิน';
?>

<!-- Evaluation Form -->
<form method="POST" action="evaluate.php" onsubmit="return confirmAction(event, { title: <?= json_encode($confirm_title, JSON_UNESCAPED_UNICODE) ?>, text: <?= json_encode($confirm_text, JSON_UNESCAPED_UNICODE) ?>, icon: 'question', confirmButtonText: <?= json_encode($confirm_btn, JSON_UNESCAPED_UNICODE) ?>, confirmButtonColor: '#8a5823' });" class="space-y-6 max-w-4xl">
    <input type="hidden" name="activity_id" value="<?= $activity_id ?>">

    <?php if (empty($questions)): ?>
        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-100 shadow-sm">
            <p>ยังไม่มีข้อคำถามในกิจกรรมนี้</p>
            <a href="dashboard.php" class="mt-2 inline-block text-xs font-semibold text-[#8a5823] hover:underline">
                กลับหน้าแรก
            </a>
        </div>
    <?php endif; ?>

    <?php foreach ($questions as $index => $q): ?>
        <?php 
            $qid = $q['id'];
            $selected_score = $existing_answers[$qid] ?? 0;
        ?>
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-gray-100">
            <!-- Question Title -->
            <div class="flex items-start gap-3 mb-6">
                <span class="w-7 h-7 rounded-full bg-amber-50 text-[#8a5823] font-bold text-xs flex items-center justify-center shrink-0 border border-amber-200">
                    <?= $index + 1 ?>
                </span>
                <h3 class="text-sm sm:text-base font-semibold text-gray-800 pt-0.5">
                    <?= htmlspecialchars($q['question_text']) ?>
                </h3>
            </div>

            <?php if ($q['question_type'] === 'rating'): ?>
                <!-- Connected Radio Circles (1 to 5) -->
                <div class="relative py-4 px-2 sm:px-8">
                    <!-- Background Connector Line -->
                    <div class="absolute top-[34px] sm:top-[36px] left-10 right-10 h-0.5 bg-gray-200 -z-0"></div>

                    <div class="flex items-center justify-between relative z-10">
                        <?php 
                        $labels = [
                            1 => 'น้อยที่สุด',
                            2 => 'น้อย',
                            3 => 'ปานกลาง',
                            4 => 'มาก',
                            5 => 'มากที่สุด'
                        ];
                        for ($score = 1; $score <= 5; $score++): 
                            $is_checked = ($selected_score == $score);
                        ?>
                            <div class="flex flex-col items-center">
                                <input 
                                    type="radio" 
                                    id="q_<?= $qid ?>_<?= $score ?>" 
                                    name="scores[<?= $qid ?>]" 
                                    value="<?= $score ?>"
                                    class="sr-only scale-radio"
                                    <?= $is_checked ? 'checked' : '' ?>
                                    <?= $is_disabled ? 'disabled' : '' ?>
                                    required
                                >
                                <label 
                                    for="q_<?= $qid ?>_<?= $score ?>" 
                                    class="flex flex-col items-center cursor-pointer <?= $is_disabled ? 'cursor-default' : '' ?>"
                                >
                                    <!-- Circle -->
                                    <div class="scale-circle w-9 h-9 sm:w-11 sm:h-11 rounded-full border-2 bg-white border-gray-300 text-gray-700 hover:border-[#8a5823] flex items-center justify-center text-xs sm:text-sm font-semibold">
                                        <?= $score ?>
                                    </div>
                                    <!-- Label Text below -->
                                    <span class="scale-label text-[11px] sm:text-xs text-gray-500 mt-2 font-light">
                                        <?= $labels[$score] ?>
                                    </span>
                                </label>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($questions)): ?>
        <!-- Card Suggestion Box -->
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-sm border border-gray-100">
            <label for="suggestion" class="block text-sm font-semibold text-gray-800 mb-2">
                ข้อเสนอแนะเพิ่มเติม (ถ้ามี)
            </label>
            <textarea 
                name="suggestion" 
                id="suggestion" 
                rows="4" 
                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all placeholder-gray-400 <?= $is_disabled ? 'bg-gray-50 text-gray-600' : '' ?>"
                placeholder="กรอกความคิดเห็น ข้อเสนอแนะ หรือสิ่งที่อยากให้ปรับปรุงในกิจกรรมนี้..."
                <?= $is_disabled ? 'readonly' : '' ?>
            ><?= htmlspecialchars($existing_eval['suggestion'] ?? '') ?></textarea>
        </div>

        <!-- Submit Button -->
        <?php if ($can_edit): ?>
            <div class="flex justify-end pt-2">
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-8 py-3 bg-[#8a5823] hover:bg-[#724719] text-white text-sm font-semibold rounded-xl shadow-md transition-colors cursor-pointer flex items-center justify-center gap-2"
                >
                    <?php if ($has_evaluated): ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>บันทึกการแก้ไขแบบประเมิน</span>
                    <?php else: ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>ส่งแบบประเมิน</span>
                    <?php endif; ?>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</form>

<script>
    <?php if (!empty($success_msg)): ?>
        Swal.fire({
            icon: 'success',
            title: 'บันทึกสำเร็จ!',
            text: <?= json_encode($success_msg, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonColor: '#8a5823',
            confirmButtonText: 'ตกลง'
        });
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถบันทึกได้',
            text: <?= json_encode($error_msg, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonColor: '#8a5823',
            confirmButtonText: 'ตกลง'
        });
    <?php endif; ?>
</script>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
