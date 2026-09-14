<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$student_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// ดึงข้อมูลปัจจุบันของนักศึกษา
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$student_id]);
$user = $stmt->fetch();

if (!$user) {
    die("ไม่พบข้อมูลผู้ใช้");
}

// จัดการ Request: อัปเดตข้อมูลส่วนตัว (Update Profile)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $subdistrict = trim($_POST['subdistrict'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($fullname)) {
        $error_msg = 'กรุณากรอกชื่อ-นามสกุล';
    } else {
        $update_password = false;
        $hashed_new_pwd = '';

        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error_msg = 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน';
            } elseif (strlen($new_password) < 4) {
                $error_msg = 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 4 ตัวอักษร';
            } else {
                $update_password = true;
                $hashed_new_pwd = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        if (empty($error_msg)) {
            $email_val = !empty($email) ? $email : null;
            $phone_val = !empty($phone) ? $phone : null;
            $province_val = !empty($province) ? $province : null;
            $district_val = !empty($district) ? $district : null;
            $subdistrict_val = !empty($subdistrict) ? $subdistrict : null;
            $zipcode_val = !empty($zipcode) ? $zipcode : null;

            if ($update_password) {
                $stmt_upd = $pdo->prepare("
                    UPDATE users 
                    SET fullname = ?, email = ?, phone = ?, province = ?, district = ?, subdistrict = ?, zipcode = ?, password = ? 
                    WHERE id = ?
                ");
                $stmt_upd->execute([
                    $fullname, 
                    $email_val, 
                    $phone_val, 
                    $province_val, 
                    $district_val, 
                    $subdistrict_val, 
                    $zipcode_val, 
                    $hashed_new_pwd, 
                    $student_id
                ]);
            } else {
                $stmt_upd = $pdo->prepare("
                    UPDATE users 
                    SET fullname = ?, email = ?, phone = ?, province = ?, district = ?, subdistrict = ?, zipcode = ? 
                    WHERE id = ?
                ");
                $stmt_upd->execute([
                    $fullname, 
                    $email_val, 
                    $phone_val, 
                    $province_val, 
                    $district_val, 
                    $subdistrict_val, 
                    $zipcode_val, 
                    $student_id
                ]);
            }

            // อัปเดต Session
            $_SESSION['fullname'] = $fullname;
            $success_msg = 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว';

            // รีเฟรชข้อมูลผู้ใช้
            $stmt->execute([$student_id]);
            $user = $stmt->fetch();
        }
    }
}

// กำหนดตัวแปร layout
$current_page = 'profile';
$page_title = 'ข้อมูลของฉัน';
$breadcrumb_sub = 'ข้อมูลของฉัน';

require_once __DIR__ . '/layout_top.php';
?>

<!-- Header Title -->
<div class="mb-6">
    <span class="text-xs font-semibold text-[#8a5823] tracking-widest uppercase">MY PROFILE</span>
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">ข้อมูลของฉัน</h1>
    <p class="text-sm text-gray-500 mt-1">ปรับปรุงข้อมูลส่วนตัวและเปลี่ยนรหัสผ่าน</p>
</div>


<!-- Form Card -->
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
    <form method="POST" action="profile.php" class="space-y-5">
        
        <!-- รหัสนักศึกษา (Read-only) -->
        <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                รหัสนักศึกษา
            </label>
            <input 
                type="text" 
                value="<?= htmlspecialchars($user['username']) ?>" 
                disabled 
                class="w-full px-4 py-2.5 sm:py-3 bg-gray-100 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-600 cursor-not-allowed font-mono"
            >
        </div>

        <!-- ชื่อ-นามสกุล -->
        <div>
            <label for="fullname" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                ชื่อ-นามสกุล
            </label>
            <input 
                type="text" 
                id="fullname" 
                name="fullname" 
                value="<?= htmlspecialchars($user['fullname']) ?>" 
                required 
                class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
            >
        </div>

        <!-- อีเมล -->
        <div>
            <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                อีเมล
            </label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                placeholder="เช่น student@rmutl.ac.th"
            >
        </div>

        <!-- เบอร์โทร -->
        <div>
            <label for="phone" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                เบอร์โทร
            </label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                placeholder="เช่น 0812345678"
            >
        </div>

        <!-- Section: ข้อมูลที่อยู่ -->
        <div class="pt-4 border-t border-gray-100">
            <h2 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#8a5823]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>ข้อมูลที่อยู่</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- จังหวัด -->
                <div>
                    <label for="province" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                        จังหวัด
                    </label>
                    <select 
                        id="province" 
                        name="province" 
                        class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all cursor-pointer"
                    >
                        <option value="">-- กำลังโหลดจังหวัด... --</option>
                    </select>
                </div>

                <!-- อำเภอ / เขต -->
                <div>
                    <label for="district" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                        อำเภอ / เขต
                    </label>
                    <select 
                        id="district" 
                        name="district" 
                        disabled
                        class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all cursor-pointer disabled:bg-gray-100 disabled:cursor-not-allowed"
                    >
                        <option value="">-- กรุณาเลือกจังหวัดก่อน --</option>
                    </select>
                </div>

                <!-- ตำบล / แขวง -->
                <div>
                    <label for="subdistrict" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                        ตำบล / แขวง
                    </label>
                    <select 
                        id="subdistrict" 
                        name="subdistrict" 
                        disabled
                        class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all cursor-pointer disabled:bg-gray-100 disabled:cursor-not-allowed"
                    >
                        <option value="">-- กรุณาเลือกอำเภอก่อน --</option>
                    </select>
                </div>

                <!-- รหัสไปรษณีย์ -->
                <div>
                    <label for="zipcode" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                        รหัสไปรษณีย์
                    </label>
                    <input 
                        type="text" 
                        id="zipcode" 
                        name="zipcode" 
                        value="<?= htmlspecialchars($user['zipcode'] ?? '') ?>" 
                        placeholder="รหัสไปรษณีย์จะขึ้นอัตโนมัติ"
                        maxlength="10"
                        class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    >
                </div>
            </div>
        </div>

        <!-- Section: เปลี่ยนรหัสผ่าน -->
        <div class="pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 font-medium mb-3">เปลี่ยนรหัสผ่าน (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</p>
            
            <div class="space-y-4">
                <div>
                    <label for="new_password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                        รหัสผ่านใหม่
                    </label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    >
                </div>

                <div>
                    <label for="confirm_password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                        ยืนยันรหัสผ่านใหม่
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="w-full px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#8a5823] focus:border-transparent transition-all"
                    >
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-gray-100">
            <button 
                type="submit" 
                class="inline-flex items-center gap-2 bg-[#8a5823] hover:bg-[#724719] text-white text-xs sm:text-sm font-medium py-2.5 px-6 rounded-xl shadow transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span>บันทึก</span>
            </button>
        </div>

    </form>
</div>

<!-- Cascading Address Selector Script -->
<script>
// ข้อมูลที่อยู่ที่บันทึกไว้ในฐานข้อมูล (สำหรับเลือกค่าเดิมเริ่มต้น)
const savedAddress = {
    province: <?= json_encode($user['province'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    district: <?= json_encode($user['district'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    subdistrict: <?= json_encode($user['subdistrict'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    zipcode: <?= json_encode($user['zipcode'] ?? '', JSON_UNESCAPED_UNICODE) ?>
};

let thaiAddressData = [];

const provinceSelect = document.getElementById('province');
const districtSelect = document.getElementById('district');
const subdistrictSelect = document.getElementById('subdistrict');
const zipcodeEl = document.getElementById('zipcode');

// 1. โหลดข้อมูลที่อยู่จาก JSON
fetch('../assets/data/thai_address.json')
    .then(response => {
        if (!response.ok) throw new Error('ไม่สามารถโหลดข้อมูลที่อยู่ได้');
        return response.json();
    })
    .then(data => {
        thaiAddressData = data;
        renderProvinces();
    })
    .catch(err => {
        console.error('Address load error:', err);
        provinceSelect.innerHTML = '<option value="">โหลดข้อมูลล้มเหลว</option>';
    });

// 2. แสดงรายชื่อจังหวัด
function renderProvinces() {
    let optionsHtml = '<option value="">-- เลือกจังหวัด --</option>';
    thaiAddressData.forEach(p => {
        const isSelected = (p.name === savedAddress.province) ? 'selected' : '';
        optionsHtml += `<option value="${p.name}" ${isSelected}>${p.name}</option>`;
    });
    provinceSelect.innerHTML = optionsHtml;

    // ถ้ามีค่าจังหวัดเดิมที่บันทึกไว้ ให้โหลดอำเภอต่อ
    if (savedAddress.province) {
        renderDistricts(savedAddress.province, true);
    }
}

// 3. แสดงรายชื่ออำเภอตามจังหวัดที่เลือก
function renderDistricts(provinceName, isInitial = false) {
    const provinceObj = thaiAddressData.find(p => p.name === provinceName);

    if (!provinceObj) {
        districtSelect.innerHTML = '<option value="">-- กรุณาเลือกจังหวัดก่อน --</option>';
        districtSelect.disabled = true;
        resetSubdistricts();
        return;
    }

    let optionsHtml = '<option value="">-- เลือกอำเภอ / เขต --</option>';
    provinceObj.amphures.forEach(a => {
        const isSelected = (isInitial && a.name === savedAddress.district) ? 'selected' : '';
        optionsHtml += `<option value="${a.name}" ${isSelected}>${a.name}</option>`;
    });

    districtSelect.innerHTML = optionsHtml;
    districtSelect.disabled = false;

    if (isInitial && savedAddress.district) {
        renderSubdistricts(provinceName, savedAddress.district, true);
    } else {
        resetSubdistricts();
    }
}

// 4. แสดงรายชื่อตำบลตามอำเภอที่เลือก
function renderSubdistricts(provinceName, districtName, isInitial = false) {
    const provinceObj = thaiAddressData.find(p => p.name === provinceName);
    const districtObj = provinceObj ? provinceObj.amphures.find(a => a.name === districtName) : null;

    if (!districtObj) {
        resetSubdistricts();
        return;
    }

    let optionsHtml = '<option value="">-- เลือกตำบล / แขวง --</option>';
    districtObj.tambons.forEach(t => {
        const isSelected = (isInitial && t.name === savedAddress.subdistrict) ? 'selected' : '';
        optionsHtml += `<option value="${t.name}" data-zip="${t.zip}" ${isSelected}>${t.name}</option>`;
    });

    subdistrictSelect.innerHTML = optionsHtml;
    subdistrictSelect.disabled = false;

    if (isInitial && savedAddress.zipcode) {
        zipcodeEl.value = savedAddress.zipcode;
    }
}

// ล้างตัวเลือกตำบลและรหัสไปรษณีย์
function resetSubdistricts() {
    subdistrictSelect.innerHTML = '<option value="">-- กรุณาเลือกอำเภอก่อน --</option>';
    subdistrictSelect.disabled = true;
    zipcodeEl.value = '';
}

// Event Listeners เมื่อผู้ใช้เลือกเปลี่ยน Dropdown
provinceSelect.addEventListener('change', function() {
    savedAddress.district = '';
    savedAddress.subdistrict = '';
    savedAddress.zipcode = '';
    renderDistricts(this.value, false);
});

districtSelect.addEventListener('change', function() {
    savedAddress.subdistrict = '';
    savedAddress.zipcode = '';
    renderSubdistricts(provinceSelect.value, this.value, false);
});

subdistrictSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const zip = selectedOption ? selectedOption.getAttribute('data-zip') : '';
    if (zip) {
        zipcodeEl.value = zip;
    }
});
</script>

<!-- SweetAlert2 Popups -->
<?php if (!empty($success_msg)): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'สำเร็จ',
    text: <?= json_encode($success_msg) ?>,
    confirmButtonColor: '#8a5823',
    confirmButtonText: 'ตกลง',
    timer: 2000
});
</script>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'เกิดข้อผิดพลาด',
    text: <?= json_encode($error_msg) ?>,
    confirmButtonColor: '#8a5823',
    confirmButtonText: 'ตกลง'
});
</script>
<?php endif; ?>

<?php
require_once __DIR__ . '/layout_bottom.php';
?>
