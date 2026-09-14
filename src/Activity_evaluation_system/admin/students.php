<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$success_msg = '';
$error_msg = '';

// จัดการ Request: เพิ่มนักศึกษา (Add Student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $student_code = trim($_POST['student_code'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');

    if (!empty($student_code) && !empty($fullname)) {
        // ตรวจสอบรหัสนักศึกษาซ้ำ
        $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt_chk->execute([$student_code]);
        if ($stmt_chk->fetch()) {
            $error_msg = 'รหัสนักศึกษานี้มีอยู่ในระบบแล้ว';
        } else {
            // รหัสผ่านตั้งต้น = รหัสนักศึกษา
            $hashed_password = password_hash($student_code, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, fullname, role) 
                VALUES (?, ?, ?, 'student')
            ");
            $stmt->execute([$student_code, $hashed_password, $fullname]);
            $success_msg = "เพิ่มนักศึกษา {$fullname} ({$student_code}) เรียบร้อยแล้ว";
        }
    } else {
        $error_msg = 'กรุณากรอกรหัสนักศึกษาและชื่อ-นามสกุล';
    }
}

// จัดการ Request: รีเซ็ตรหัสผ่าน (Reset Password to Student ID)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if ($user_id > 0) {
        $stmt_get = $pdo->prepare("SELECT username, fullname FROM users WHERE id = ? AND role = 'student' LIMIT 1");
        $stmt_get->execute([$user_id]);
        $student = $stmt_get->fetch();

        if ($student) {
            $reset_pwd = password_hash($student['username'], PASSWORD_DEFAULT);
            $stmt_reset = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_reset->execute([$reset_pwd, $user_id]);
            $success_msg = "รีเซ็ตรหัสผ่านของ {$student['fullname']} เป็นรหัสผ่านเริ่มต้นเรียบร้อยแล้ว";
        }
    }
}

// จัดการ Request: ลบนักศึกษา (Delete Student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if ($user_id > 0) {
        $stmt_del = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt_del->execute([$user_id]);
        $success_msg = 'ลบข้อมูลนักศึกษาเรียบร้อยแล้ว';
    }
}

// ดึงรายการกิจกรรมทั้งหมด
$stmt_acts = $pdo->query("SELECT * FROM activities ORDER BY id ASC");
$activities = $stmt_acts->fetchAll();
$total_activities_count = count($activities);

$selected_activity_id = (int)($_GET['activity_id'] ?? 0);

// ดึงรายชื่อนักศึกษาทั้งหมด พร้อมสถานะประเมิน
if ($selected_activity_id > 0) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.fullname, 
               IF(e.id IS NOT NULL, 1, 0) AS has_evaluated,
               e.submitted_at
        FROM users u
        LEFT JOIN evaluations e ON u.id = e.student_id AND e.activity_id = ?
        WHERE u.role = 'student'
        ORDER BY u.id ASC
    ");
    $stmt->execute([$selected_activity_id]);
    $students = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.fullname, 
               COUNT(e.id) AS evaluated_count,
               IF(COUNT(e.id) > 0, 1, 0) AS has_evaluated,
               MAX(e.submitted_at) AS submitted_at
        FROM users u
        LEFT JOIN evaluations e ON u.id = e.student_id
        WHERE u.role = 'student'
        GROUP BY u.id, u.username, u.fullname
        ORDER BY u.id ASC
    ");
    $students = $stmt->fetchAll();
}
$total_students_count = count($students);

// กำหนดตัวแปร layout
$current_page = 'students';
$page_title = 'จัดการนักศึกษา';
$breadcrumb_sub = 'นักศึกษา';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Header Title & Add Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">STUDENTS</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">จัดการนักศึกษา</h1>
        <p class="text-sm text-gray-500 mt-1">รายชื่อบัญชีนักศึกษา · <?= $total_students_count ?> คน</p>
    </div>
    <div>
        <button 
            type="button" 
            onclick="openAddStudentModal()" 
            class="inline-flex items-center gap-2 bg-[#8a5823] hover:bg-[#724719] text-white text-sm font-medium py-2.5 px-5 rounded-xl shadow transition-colors cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>เพิ่มนักศึกษา</span>
        </button>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($success_msg)): ?>
    <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
<?php endif; ?>

<!-- Activity Selection Tabs -->
<div class="mb-6">
    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">กรองสถานะประเมินตามกิจกรรม:</label>
    <div class="flex flex-wrap items-center gap-2">
        <a 
            href="students.php"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all <?= ($selected_activity_id == 0) ? 'bg-[#8a5823] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-amber-50/50 border border-gray-200 hover:border-amber-300' ?>"
        >
            <span>ทั้งหมด (ภาพรวม)</span>
        </a>
        <?php foreach ($activities as $act): ?>
            <?php $is_active_tab = ($act['id'] == $selected_activity_id); ?>
            <a 
                href="students.php?activity_id=<?= $act['id'] ?>"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all <?= $is_active_tab ? 'bg-[#8a5823] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-amber-50/50 border border-gray-200 hover:border-amber-300' ?>"
            >
                <span><?= htmlspecialchars($act['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Students Table Card -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 bg-gray-50/50">
                    <th class="py-4 px-6 w-16 text-center">#</th>
                    <th class="py-4 px-6 w-48">รหัสนักศึกษา</th>
                    <th class="py-4 px-6">ชื่อ-นามสกุล</th>
                    <th class="py-4 px-6 w-44 text-center">สถานะประเมิน</th>
                    <th class="py-4 px-6 w-44 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs sm:text-sm text-gray-700">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">ยังไม่มีรายชื่อนักศึกษาในระบบ</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $index => $std): ?>
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-4 px-6 text-center font-medium text-gray-500"><?= $index + 1 ?></td>
                            <td class="py-4 px-6 font-medium text-gray-800 tracking-wide font-mono text-xs"><?= htmlspecialchars($std['username']) ?></td>
                            <td class="py-4 px-6 text-gray-800 font-normal"><?= htmlspecialchars($std['fullname']) ?></td>
                            <td class="py-4 px-6 text-center">
                                <?php if ($selected_activity_id > 0): ?>
                                    <?php if ($std['has_evaluated']): ?>
                                        <span class="inline-block px-3 py-1 bg-emerald-500 text-white text-xs font-medium rounded-full">
                                            ประเมินแล้ว
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-block px-3 py-1 bg-[#64748b] text-white text-xs font-medium rounded-full">
                                            ยังไม่ประเมิน
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($std['evaluated_count'] > 0): ?>
                                        <span class="inline-block px-3 py-1 bg-emerald-500 text-white text-xs font-medium rounded-full">
                                            ประเมินแล้ว (<?= $std['evaluated_count'] ?>/<?= $total_activities_count ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-block px-3 py-1 bg-[#64748b] text-white text-xs font-medium rounded-full">
                                            ยังไม่ประเมิน
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Reset Password Button Form -->
                                    <form method="POST" action="students.php" onsubmit="return confirmAction(event, { title: 'รีเซ็ตรหัสผ่าน?', text: 'ต้องการรีเซ็ตรหัสผ่านของนักศึกษารายนี้กลับเป็นรหัสเริ่มต้น (รหัสนักศึกษา) หรือไม่?', icon: 'question', confirmButtonText: 'ยืนยันรีเซ็ต', confirmButtonColor: '#8a5823' });" class="inline">
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?= $std['id'] ?>">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-medium transition-colors cursor-pointer"
                                            title="รีเซ็ตรหัสผ่าน"
                                        >
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            <span>รีเซ็ตรหัส</span>
                                        </button>
                                    </form>

                                    <!-- Delete Button Form -->
                                    <form method="POST" action="students.php" onsubmit="return confirmAction(event, { title: 'ยืนยันการลบนักศึกษา?', text: 'ยืนยันที่จะลบบัญชีนักศึกษารายนี้หรือไม่? ข้อมูลการประเมินจะถูกลบไปด้วย', icon: 'warning', confirmButtonText: 'ใช่, ลบบัญชี', confirmButtonColor: '#dc2626' });" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $std['id'] ?>">
                                        <button 
                                            type="submit" 
                                            class="p-1.5 text-red-500 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50 transition-colors cursor-pointer"
                                            title="ลบนักศึกษา"
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

<!-- Modal สำหรับ เพิ่มนักศึกษา -->
<div id="addStudentModal" class="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative border border-gray-100">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">เพิ่มนักศึกษา</h3>
            <button type="button" onclick="closeAddStudentModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="students.php" class="mt-4 space-y-4">
            <input type="hidden" name="action" value="add">

            <div>
                <label for="student_code" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">รหัสนักศึกษา</label>
                <input 
                    type="text" 
                    name="student_code" 
                    id="student_code" 
                    required 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    placeholder="เช่น 68642206033-1"
                >
            </div>

            <div>
                <label for="fullname" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">ชื่อ-นามสกุล</label>
                <input 
                    type="text" 
                    name="fullname" 
                    id="fullname" 
                    required 
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    placeholder="กรอกชื่อและนามสกุล"
                >
            </div>

            <!-- Helper Note -->
            <p class="text-xs text-gray-500 flex items-center gap-1 pt-1">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>รหัสผ่านตั้งต้นจะเท่ากับรหัสนักศึกษา</span>
            </p>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    onclick="closeAddStudentModal()" 
                    class="px-5 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-xs sm:text-sm font-medium transition-colors cursor-pointer"
                >
                    ยกเลิก
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-[#8a5823] hover:bg-[#724719] text-white rounded-xl text-xs sm:text-sm font-medium shadow transition-colors cursor-pointer"
                >
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const addStudentModal = document.getElementById('addStudentModal');

    function openAddStudentModal() {
        document.getElementById('student_code').value = '';
        document.getElementById('fullname').value = '';
        addStudentModal.classList.remove('hidden');
    }

    function closeAddStudentModal() {
        addStudentModal.classList.add('hidden');
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
