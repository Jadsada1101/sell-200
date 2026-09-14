<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/period_helper.php';

$success_msg = '';
$error_msg = '';

// 1. จัดการ Request เกี่ยวกับ กิจกรรม (Activity)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // เพิ่มกิจกรรมใหม่
    if ($action === 'add_activity') {
        $activity_name = trim($_POST['activity_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $is_open = isset($_POST['is_open']) ? (int)$_POST['is_open'] : 1;
        $start_time = !empty($_POST['start_time']) ? date('Y-m-d H:i:s', strtotime($_POST['start_time'])) : null;
        $end_time = !empty($_POST['end_time']) ? date('Y-m-d H:i:s', strtotime($_POST['end_time'])) : null;

        if (!empty($activity_name)) {
            $stmt = $pdo->prepare("INSERT INTO activities (name, description, status, is_open, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$activity_name, $description, $status, $is_open, $start_time, $end_time]);
            $new_act_id = $pdo->lastInsertId();
            header("Location: questions.php?activity_id=" . $new_act_id . "&msg=add_act_success");
            exit;
        } else {
            $error_msg = 'กรุณากรอกชื่อกิจกรรม';
        }
    }

    // แก้ไขกิจกรรมและกำหนดช่วงเวลาประเมิน
    if ($action === 'edit_activity') {
        $activity_id = (int)($_POST['activity_id'] ?? 0);
        $activity_name = trim($_POST['activity_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $is_open = isset($_POST['is_open']) ? (int)$_POST['is_open'] : 1;
        $start_time = !empty($_POST['start_time']) ? date('Y-m-d H:i:s', strtotime($_POST['start_time'])) : null;
        $end_time = !empty($_POST['end_time']) ? date('Y-m-d H:i:s', strtotime($_POST['end_time'])) : null;

        if ($activity_id > 0 && !empty($activity_name)) {
            $stmt = $pdo->prepare("UPDATE activities SET name = ?, description = ?, status = ?, is_open = ?, start_time = ?, end_time = ? WHERE id = ?");
            $stmt->execute([$activity_name, $description, $status, $is_open, $start_time, $end_time, $activity_id]);
            header("Location: questions.php?activity_id=" . $activity_id . "&msg=edit_act_success");
            exit;
        } else {
            $error_msg = 'กรุณากรอกชื่อกิจกรรมให้ถูกต้อง';
        }
    }

    // สลับเปิด-ปิดรับการประเมินกิจกรรม
    if ($action === 'toggle_eval_status') {
        $activity_id = (int)($_POST['activity_id'] ?? 0);
        if ($activity_id > 0) {
            $stmt = $pdo->prepare("UPDATE activities SET is_open = 1 - is_open WHERE id = ?");
            $stmt->execute([$activity_id]);
            header("Location: questions.php?activity_id=" . $activity_id . "&msg=toggle_eval_success");
            exit;
        }
    }

    // ลบกิจกรรม
    if ($action === 'delete_activity') {
        $activity_id = (int)($_POST['activity_id'] ?? 0);
        // ตรวจสอบว่าไม่ใช่กิจกรรมสุดท้าย
        $count_act = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
        if ($count_act <= 1) {
            $error_msg = 'ไม่สามารถลบกิจกรรมนี้ได้ เนื่องจากต้องมีอย่างน้อย 1 กิจกรรมในระบบ';
        } else {
            $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
            $stmt->execute([$activity_id]);
            // ดึงกิจกรรมแรกที่เหลืออยู่
            $first_id = $pdo->query("SELECT id FROM activities ORDER BY id ASC LIMIT 1")->fetchColumn();
            header("Location: questions.php?activity_id=" . $first_id . "&msg=del_act_success");
            exit;
        }
    }

    // เพิ่มข้อคำถาม
    if ($action === 'add') {
        $activity_id = (int)($_POST['activity_id'] ?? 1);
        $question_text = trim($_POST['question_text'] ?? '');
        $question_type = $_POST['question_type'] ?? 'rating';
        $status = $_POST['status'] ?? 'active';

        if (!empty($question_text)) {
            // หา sort_order ล่าสุดของกิจกรรมนั้น
            $stmt_max = $pdo->prepare("SELECT MAX(sort_order) FROM questions WHERE activity_id = ?");
            $stmt_max->execute([$activity_id]);
            $next_sort = ((int)$stmt_max->fetchColumn()) + 1;

            $stmt = $pdo->prepare("
                INSERT INTO questions (activity_id, question_text, question_type, status, sort_order) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$activity_id, $question_text, $question_type, $status, $next_sort]);
            header("Location: questions.php?activity_id=" . $activity_id . "&msg=add_success");
            exit;
        } else {
            $error_msg = 'กรุณากรอกข้อความคำถาม';
        }
    }

    // แก้ไขข้อคำถาม
    if ($action === 'edit') {
        $question_id = (int)($_POST['question_id'] ?? 0);
        $activity_id = (int)($_POST['activity_id'] ?? 1);
        $question_text = trim($_POST['question_text'] ?? '');
        $question_type = $_POST['question_type'] ?? 'rating';
        $status = $_POST['status'] ?? 'active';

        if ($question_id > 0 && !empty($question_text)) {
            $stmt = $pdo->prepare("
                UPDATE questions 
                SET activity_id = ?, question_text = ?, question_type = ?, status = ? 
                WHERE id = ?
            ");
            $stmt->execute([$activity_id, $question_text, $question_type, $status, $question_id]);
            header("Location: questions.php?activity_id=" . $activity_id . "&msg=edit_success");
            exit;
        } else {
            $error_msg = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        }
    }

    // ลบข้อคำถาม
    if ($action === 'delete') {
        $question_id = (int)($_POST['question_id'] ?? 0);
        $act_id = (int)($_POST['activity_id'] ?? 1);
        if ($question_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            $stmt->execute([$question_id]);
            header("Location: questions.php?activity_id=" . $act_id . "&msg=del_success");
            exit;
        }
    }
}

// ข้อความแจ้งเตือนจาก redirect
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'add_success': $success_msg = 'เพิ่มข้อคำถามเรียบร้อยแล้ว'; break;
        case 'edit_success': $success_msg = 'แก้ไขข้อคำถามเรียบร้อยแล้ว'; break;
        case 'del_success': $success_msg = 'ลบข้อคำถามเรียบร้อยแล้ว'; break;
        case 'add_act_success': $success_msg = 'เพิ่มกิจกรรมใหม่เรียบร้อยแล้ว'; break;
        case 'edit_act_success': $success_msg = 'แก้ไขข้อมูลกิจกรรมและช่วงเวลาเรียบร้อยแล้ว'; break;
        case 'del_act_success': $success_msg = 'ลบกิจกรรมเรียบร้อยแล้ว'; break;
        case 'toggle_eval_success': $success_msg = 'เปลี่ยนสถานะเปิด-ปิดรับการประเมินเรียบร้อยแล้ว'; break;
    }
}

// 2. ดึงรายการกิจกรรมทั้งหมด พร้อมนับจำนวนข้อคำถามในแต่ละกิจกรรม
$stmt_acts = $pdo->query("
    SELECT a.*, COUNT(q.id) as question_count 
    FROM activities a 
    LEFT JOIN questions q ON a.id = q.activity_id 
    GROUP BY a.id 
    ORDER BY a.id ASC
");
$activities = $stmt_acts->fetchAll();

// เลือกกิจกรรมที่กำลังแสดงผล
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

// หากยังไม่ได้เลือก หรือไม่พบ ให้เลือกกิจกรรมแรกเป็นค่าเริ่มต้น
if (!$current_activity && !empty($activities)) {
    $current_activity = $activities[0];
    $selected_activity_id = $current_activity['id'];
}

// 3. ดึงรายการคำถามเฉพาะกิจกรรมที่เลือก
$stmt_q = $pdo->prepare("
    SELECT * FROM questions 
    WHERE activity_id = ? 
    ORDER BY sort_order ASC, id ASC
");
$stmt_q->execute([$selected_activity_id]);
$questions = $stmt_q->fetchAll();

// กำหนดตัวแปรสำหรับ layout
$current_page = 'questions';
$page_title = 'จัดการข้อคำถาม';
$breadcrumb_sub = 'จัดการคำถาม';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Header Title & Action Buttons -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">QUESTIONS MANAGEMENT</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">จัดการข้อคำถาม</h1>
        <p class="text-sm text-gray-500 mt-1">เพิ่ม แก้ไข หรือลบหัวข้อการประเมินแยกตามแต่ละกิจกรรม (เช่น กีฬาสี, ไหว้ครู)</p>
    </div>
    <div>
        <!-- ปุ่มเพิ่มกิจกรรมใหม่ -->
        <button 
            type="button" 
            onclick="openAddActivityModal()" 
            class="inline-flex items-center gap-2 bg-amber-50 hover:bg-amber-100 text-[#8a5823] border border-amber-200 text-sm font-semibold py-2.5 px-4 rounded-xl shadow-xs transition-colors cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>+ เพิ่มกิจกรรมใหม่</span>
        </button>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($success_msg)): ?>
    <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center gap-2 shadow-xs">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm flex items-center gap-2 shadow-xs">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
<?php endif; ?>

<!-- Activity Selection Tabs -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-2">
        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">เลือกกิจกรรมที่ต้องการจัดการข้อคำถาม:</label>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <?php foreach ($activities as $act): ?>
            <?php $is_active_tab = ($act['id'] == $selected_activity_id); ?>
            <a 
                href="questions.php?activity_id=<?= $act['id'] ?>"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all <?= $is_active_tab ? 'bg-[#8a5823] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-amber-50/50 border border-gray-200 hover:border-amber-300' ?>"
            >
                <svg class="w-4 h-4 <?= $is_active_tab ? 'text-amber-200' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span><?= htmlspecialchars($act['name']) ?></span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold <?= $is_active_tab ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' ?>">
                    <?= $act['question_count'] ?> ข้อ
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Current Activity Banner & Management -->
<!-- Current Activity Banner & Management -->
<?php if ($current_activity): ?>
<?php 
    $current_eval_status = check_activity_evaluation_status($pdo, $current_activity['id']); 
    $is_eval_open = $current_eval_status['is_open'];
?>
<div class="bg-gradient-to-r from-amber-50/70 via-white to-amber-50/30 rounded-2xl p-4 sm:p-5 border border-amber-200/70 mb-6 shadow-xs">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base sm:text-lg font-bold text-[#54381e]">
                    <?= htmlspecialchars($current_activity['name']) ?>
                </h2>
                <?php if ($current_activity['status'] === 'active'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-100 text-emerald-800">
                        เปิดใช้งานกิจกรรม
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-200 text-gray-700">
                        ปิดใช้งานกิจกรรม
                    </span>
                <?php endif; ?>

                <!-- ป้ายสถานะการเปิดรับการประเมิน -->
                <?php if ($is_eval_open): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-600 text-white shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        เปิดรับการประเมิน
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-600 text-white shadow-2xs" title="<?= htmlspecialchars($current_eval_status['reason']) ?>">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                        <?= htmlspecialchars($current_eval_status['reason']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                <?= !empty($current_activity['description']) ? htmlspecialchars($current_activity['description']) : 'ไม่มีคำอธิบายเพิ่มเติม' ?>
            </p>

            <!-- แสดงช่วงเวลากำหนดการ -->
            <div class="flex items-center gap-1.5 mt-2.5 text-xs text-gray-500 font-mono">
                <svg class="w-4 h-4 text-[#8a5823]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>ช่วงเวลากำหนดการ:</span>
                <span class="font-semibold text-gray-700"><?= htmlspecialchars($current_eval_status['period_text']) ?></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- ปุ่มสลับเปิด/ปิด รับประเมินทันที -->
            <form method="POST" action="questions.php" class="inline" onsubmit="return confirmAction(event, { 
                title: '<?= $current_activity['is_open'] ? 'ต้องการปิดรับการประเมิน?' : 'ต้องการเปิดรับการประเมิน?' ?>', 
                text: '<?= $current_activity['is_open'] ? 'นักศึกษาจะไม่สามารถส่งแบบประเมินสำหรับกิจกรรมนี้ได้ชั่วคราว' : 'เปิดให้นักศึกษาสามารถเข้ามาทำแบบประเมินกิจกรรมนี้ได้' ?>', 
                icon: 'question', 
                confirmButtonText: '<?= $current_activity['is_open'] ? 'ใช่, ปิดรับ' : 'ใช่, เปิดรับ' ?>', 
                confirmButtonColor: '<?= $current_activity['is_open'] ? '#d97706' : '#10b981' ?>' 
            });">
                <input type="hidden" name="action" value="toggle_eval_status">
                <input type="hidden" name="activity_id" value="<?= $current_activity['id'] ?>">
                <?php if ($current_activity['is_open']): ?>
                    <button 
                        type="submit" 
                        class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 rounded-lg text-xs font-semibold shadow-2xs transition-colors flex items-center gap-1.5 cursor-pointer"
                        title="คลิกเพื่อปิดรับการประเมินทันที"
                    >
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>คลิกเพื่อปิดรับประเมิน</span>
                    </button>
                <?php else: ?>
                    <button 
                        type="submit" 
                        class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-xs font-semibold shadow-2xs transition-colors flex items-center gap-1.5 cursor-pointer"
                        title="คลิกเพื่อเปิดรับการประเมินทันที"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>คลิกเพื่อเปิดรับประเมิน</span>
                    </button>
                <?php endif; ?>
            </form>

            <!-- ปุ่มแก้ไขกิจกรรมและช่วงเวลา -->
            <button 
                type="button" 
                onclick='openEditActivityModal(<?= json_encode($current_activity, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)' 
                class="px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 shadow-2xs transition-colors flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span>แก้ไขกิจกรรม & กำหนดเวลา</span>
            </button>

            <?php if (count($activities) > 1): ?>
                <!-- ปุ่มลบกิจกรรมนี้ -->
                <form method="POST" action="questions.php" onsubmit="return confirmAction(event, { title: 'ยืนยันการลบกิจกรรม?', text: 'คำเตือน: การลบกิจกรรมนี้จะลบข้อคำถามและผลการประเมินทั้งหมดที่เกี่ยวข้องด้วย ยืนยันที่จะลบหรือไม่?', icon: 'warning', confirmButtonText: 'ใช่, ลบกิจกรรม', confirmButtonColor: '#dc2626' });" class="inline">
                    <input type="hidden" name="action" value="delete_activity">
                    <input type="hidden" name="activity_id" value="<?= $current_activity['id'] ?>">
                    <button 
                        type="submit" 
                        class="px-3 py-1.5 bg-white hover:bg-red-50 border border-red-200 text-red-600 hover:text-red-700 rounded-lg text-xs font-medium shadow-2xs transition-colors flex items-center gap-1 cursor-pointer"
                        title="ลบกิจกรรมนี้"
                    >
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>ลบกิจกรรม</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Questions Table Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
            <span>รายการข้อคำถาม</span>
            <span class="text-xs font-normal text-gray-500">(<?= count($questions) ?> รายการ)</span>
        </h3>
        <div>
            <button 
                type="button" 
                onclick="openAddModal()" 
                class="inline-flex items-center gap-2 bg-[#8a5823] hover:bg-[#724719] text-white text-xs sm:text-sm font-medium py-2 px-4 rounded-xl shadow transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ เพิ่มคำถามในกิจกรรมนี้</span>
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 bg-gray-50/50">
                    <th class="py-4 px-6 w-16 text-center">ลำดับ</th>
                    <th class="py-4 px-6">คำถาม</th>
                    <th class="py-4 px-6 w-32 text-center">ประเภท</th>
                    <th class="py-4 px-6 w-28 text-center">สถานะ</th>
                    <th class="py-4 px-6 w-32 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs sm:text-sm text-gray-700">
                <?php if (empty($questions)): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>ยังไม่มีข้อคำถามในกิจกรรมนี้</span>
                                <button type="button" onclick="openAddModal()" class="mt-1 text-xs text-[#8a5823] hover:underline font-semibold cursor-pointer">
                                    + คลิกที่นี่เพื่อเพิ่มคำถามแรก
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($questions as $index => $q): ?>
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-4 px-6 text-center font-medium text-gray-900"><?= $index + 1 ?></td>
                            <td class="py-4 px-6 font-normal text-gray-800"><?= htmlspecialchars($q['question_text']) ?></td>
                            <td class="py-4 px-6 text-center">
                                <?php if ($q['question_type'] === 'rating'): ?>
                                    <span class="inline-block px-3 py-1 bg-[#06b6d4] text-white text-xs font-medium rounded-full">
                                        ให้คะแนน
                                    </span>
                                <?php else: ?>
                                    <span class="inline-block px-3 py-1 bg-[#f59e0b] text-white text-xs font-medium rounded-full">
                                        ข้อความ
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?php if ($q['status'] === 'active'): ?>
                                    <span class="inline-block px-3 py-1 bg-[#10b981] text-white text-xs font-medium rounded-full">
                                        ใช้งาน
                                    </span>
                                <?php else: ?>
                                    <span class="inline-block px-3 py-1 bg-gray-400 text-white text-xs font-medium rounded-full">
                                        ปิดใช้งาน
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        onclick='openEditModal(<?= json_encode($q, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)' 
                                        class="p-2 text-gray-500 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                                        title="แก้ไข"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>

                                    <!-- Delete Button Form -->
                                    <form method="POST" action="questions.php" onsubmit="return confirmAction(event, { title: 'ยืนยันที่จะลบข้อคำถามนี้หรือไม่?', text: 'หากลบแล้วข้อมูลข้อคำถามนี้จะไม่สามารถกู้คืนได้', icon: 'warning', confirmButtonText: 'ใช่, ลบข้อคำถาม', confirmButtonColor: '#dc2626' });" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                        <input type="hidden" name="activity_id" value="<?= $selected_activity_id ?>">
                                        <button 
                                            type="submit" 
                                            class="p-2 text-red-500 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50 transition-colors cursor-pointer"
                                            title="ลบ"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 1. Modal สำหรับ เพิ่ม / แก้ไข คำถาม -->
<div id="questionModal" class="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative border border-gray-100">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800">เพิ่มคำถาม</h3>
            <button type="button" onclick="closeQuestionModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="questions.php" class="mt-4 space-y-4">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="question_id" id="questionId" value="">

            <div>
                <label for="questionActivityId" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">สังกัดกิจกรรม</label>
                <select 
                    name="activity_id" 
                    id="questionActivityId" 
                    required
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                >
                    <?php foreach ($activities as $act): ?>
                        <option value="<?= $act['id'] ?>" <?= $act['id'] == $selected_activity_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($act['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="questionText" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">ข้อความคำถาม</label>
                <input 
                    type="text" 
                    name="question_text" 
                    id="questionText" 
                    required 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    placeholder="กรอกข้อความคำถาม เช่น ความเหมาะสมของเวลาและสถานที่..."
                >
            </div>

            <div>
                <label for="questionType" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">ประเภท</label>
                <select 
                    name="question_type" 
                    id="questionType" 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                >
                    <option value="rating">ให้คะแนน 1-5</option>
                    <option value="text">ข้อความ (แสดงความคิดเห็น)</option>
                </select>
            </div>

            <div>
                <label for="questionStatus" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">สถานะ</label>
                <select 
                    name="status" 
                    id="questionStatus" 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                >
                    <option value="active">ใช้งาน</option>
                    <option value="inactive">ปิดใช้งาน</option>
                </select>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    onclick="closeQuestionModal()" 
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs sm:text-sm font-medium transition-colors cursor-pointer"
                >
                    ยกเลิก
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-[#8a5823] hover:bg-[#724719] text-white rounded-xl text-xs sm:text-sm font-medium shadow transition-colors cursor-pointer"
                >
                    บันทึกคำถาม
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal สำหรับ เพิ่ม / แก้ไข กิจกรรม -->
<div id="activityModal" class="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative border border-gray-100">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <h3 id="activityModalTitle" class="text-lg font-bold text-gray-800">เพิ่มกิจกรรมใหม่</h3>
            <button type="button" onclick="closeActivityModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="questions.php" class="mt-4 space-y-4">
            <input type="hidden" name="action" id="actFormAction" value="add_activity">
            <input type="hidden" name="activity_id" id="actActivityId" value="">

            <div>
                <label for="activityNameInput" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">ชื่อกิจกรรม</label>
                <input 
                    type="text" 
                    name="activity_name" 
                    id="activityNameInput" 
                    required 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    placeholder="เช่น กิจกรรม วันกีฬาสี, กิจกรรม ไหว้ครู"
                >
            </div>

            <div>
                <label for="activityDescInput" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">คำอธิบายกิจกรรม (ถ้ามี)</label>
                <textarea 
                    name="description" 
                    id="activityDescInput" 
                    rows="3"
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    placeholder="รายละเอียดหรือวัตถุประสงค์ของกิจกรรม"
                ></textarea>
            </div>

            <div id="actStatusGroup">
                <label for="activityStatusInput" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">สถานะกิจกรรม</label>
                <select 
                    name="status" 
                    id="activityStatusInput" 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                >
                    <option value="active">เปิดใช้งาน</option>
                    <option value="inactive">ปิดใช้งาน</option>
                </select>
            </div>

            <!-- การเปิดรับประเมิน และ ช่วงเวลากำหนดการ -->
            <div class="pt-3 border-t border-gray-100 space-y-3">
                <h4 class="text-xs font-bold text-[#8a5823] uppercase tracking-wider">กำหนดการเปิด-ปิดรับการประเมิน</h4>
                
                <div>
                    <label for="activityIsOpenInput" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">สถานะเปิดรับการประเมิน</label>
                    <select 
                        name="is_open" 
                        id="activityIsOpenInput" 
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    >
                        <option value="1">🟢 เปิดรับการประเมิน</option>
                        <option value="0">🔴 ปิดรับการประเมิน</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="activityStartTimeInput" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">วัน-เวลา เริ่มต้น</label>
                        <input 
                            type="datetime-local" 
                            name="start_time" 
                            id="activityStartTimeInput" 
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">เว้นว่างหากไม่จำกัดเวลาเริ่ม</p>
                    </div>
                    <div>
                        <label for="activityEndTimeInput" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">วัน-เวลา สิ้นสุด</label>
                        <input 
                            type="datetime-local" 
                            name="end_time" 
                            id="activityEndTimeInput" 
                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">เว้นว่างหากไม่จำกัดเวลาปิด</p>
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    onclick="closeActivityModal()" 
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs sm:text-sm font-medium transition-colors cursor-pointer"
                >
                    ยกเลิก
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-[#8a5823] hover:bg-[#724719] text-white rounded-xl text-xs sm:text-sm font-medium shadow transition-colors cursor-pointer"
                >
                    บันทึกกิจกรรม
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Question Modal Scripts ---
    const questionModal = document.getElementById('questionModal');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const questionId = document.getElementById('questionId');
    const questionActivityId = document.getElementById('questionActivityId');
    const questionText = document.getElementById('questionText');
    const questionType = document.getElementById('questionType');
    const questionStatus = document.getElementById('questionStatus');

    function openAddModal() {
        modalTitle.textContent = 'เพิ่มคำถาม';
        formAction.value = 'add';
        questionId.value = '';
        questionActivityId.value = '<?= $selected_activity_id ?>';
        questionText.value = '';
        questionType.value = 'rating';
        questionStatus.value = 'active';
        questionModal.classList.remove('hidden');
    }

    function openEditModal(q) {
        modalTitle.textContent = 'แก้ไขคำถาม';
        formAction.value = 'edit';
        questionId.value = q.id;
        questionActivityId.value = q.activity_id;
        questionText.value = q.question_text;
        questionType.value = q.question_type;
        questionStatus.value = q.status;
        questionModal.classList.remove('hidden');
    }

    function closeQuestionModal() {
        questionModal.classList.add('hidden');
    }

    // --- Activity Modal Scripts ---
    const activityModal = document.getElementById('activityModal');
    const activityModalTitle = document.getElementById('activityModalTitle');
    const actFormAction = document.getElementById('actFormAction');
    const actActivityId = document.getElementById('actActivityId');
    const activityNameInput = document.getElementById('activityNameInput');
    const activityDescInput = document.getElementById('activityDescInput');
    const activityStatusInput = document.getElementById('activityStatusInput');
    const activityIsOpenInput = document.getElementById('activityIsOpenInput');
    const activityStartTimeInput = document.getElementById('activityStartTimeInput');
    const activityEndTimeInput = document.getElementById('activityEndTimeInput');

    function openAddActivityModal() {
        activityModalTitle.textContent = 'เพิ่มกิจกรรมใหม่';
        actFormAction.value = 'add_activity';
        actActivityId.value = '';
        activityNameInput.value = '';
        activityDescInput.value = '';
        activityStatusInput.value = 'active';
        activityIsOpenInput.value = '1';
        activityStartTimeInput.value = '';
        activityEndTimeInput.value = '';
        activityModal.classList.remove('hidden');
    }

    function openEditActivityModal(act) {
        activityModalTitle.textContent = 'แก้ไขกิจกรรมและช่วงเวลาประเมิน';
        actFormAction.value = 'edit_activity';
        actActivityId.value = act.id;
        activityNameInput.value = act.name;
        activityDescInput.value = act.description || '';
        activityStatusInput.value = act.status || 'active';
        activityIsOpenInput.value = (act.is_open != null) ? act.is_open : '1';
        activityStartTimeInput.value = act.start_time ? act.start_time.replace(' ', 'T').substring(0, 16) : '';
        activityEndTimeInput.value = act.end_time ? act.end_time.replace(' ', 'T').substring(0, 16) : '';
        activityModal.classList.remove('hidden');
    }

    function closeActivityModal() {
        activityModal.classList.add('hidden');
    }

    <?php if (!empty($success_msg)): ?>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: <?= json_encode($success_msg, JSON_UNESCAPED_UNICODE) ?>,
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true
        });
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: <?= json_encode($error_msg, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonColor: '#8a5823',
            confirmButtonText: 'ตกลง'
        });
    <?php endif; ?>
</script>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
