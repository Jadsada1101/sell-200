<?php
// ฟังก์ชันตรวจสอบสถานะว่ากิจกรรมเปิดรับการประเมินอยู่ในขณะนี้หรือไม่
function check_activity_evaluation_status($pdo, $activity_id) {
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$activity_id]);
    $act = $stmt->fetch();

    if (!$act) {
        return [
            'is_open' => false,
            'reason' => 'ไม่พบข้อมูลกิจกรรม',
            'activity' => null,
            'period_text' => 'ไม่พบกิจกรรม'
        ];
    }

    // 1. ตรวจสอบสถานะการใช้งานของกิจกรรม (status)
    if ($act['status'] !== 'active') {
        return [
            'is_open' => false,
            'reason' => 'กิจกรรมนี้ปิดใช้งานแล้ว',
            'activity' => $act,
            'period_text' => 'ปิดใช้งานกิจกรรม'
        ];
    }

    // 2. ตรวจสอบสวิตช์เปิด-ปิดการประเมิน (is_open)
    if ($act['is_open'] != 1) {
        return [
            'is_open' => false,
            'reason' => 'ปิดรับการประเมิน (สวิตช์ปิดอยู่)',
            'activity' => $act,
            'period_text' => 'ปิดรับการประเมิน'
        ];
    }

    $current_time = time();

    // 3. ตรวจสอบเวลาเริ่มต้น (ถ้ามีการกำหนด)
    if (!empty($act['start_time'])) {
        $start_timestamp = strtotime($act['start_time']);
        if ($current_time < $start_timestamp) {
            return [
                'is_open' => false,
                'reason' => 'ยังไม่ถึงเวลาเริ่มการประเมิน',
                'activity' => $act,
                'period_text' => 'เริ่ม ' . date('d/m/Y H:i', $start_timestamp)
            ];
        }
    }

    // 4. ตรวจสอบเวลาสิ้นสุด (ถ้ามีการกำหนด)
    if (!empty($act['end_time'])) {
        $end_timestamp = strtotime($act['end_time']);
        if ($current_time > $end_timestamp) {
            return [
                'is_open' => false,
                'reason' => 'สิ้นสุดระยะเวลาการประเมินแล้ว',
                'activity' => $act,
                'period_text' => 'สิ้นสุดแล้ว'
            ];
        }
    }

    // จัดรูปแบบข้อความช่วงเวลา
    $period_text = 'ไม่จำกัดช่วงเวลา';
    if (!empty($act['start_time']) && !empty($act['end_time'])) {
        $period_text = date('d/m/Y H:i', strtotime($act['start_time'])) . ' → ' . date('d/m/Y H:i', strtotime($act['end_time']));
    } elseif (!empty($act['start_time'])) {
        $period_text = 'เริ่มตั้งแต่ ' . date('d/m/Y H:i', strtotime($act['start_time']));
    } elseif (!empty($act['end_time'])) {
        $period_text = 'จนถึง ' . date('d/m/Y H:i', strtotime($act['end_time']));
    }

    return [
        'is_open' => true,
        'reason' => 'เปิดรับการประเมิน',
        'activity' => $act,
        'period_text' => $period_text
    ];
}

// Wrapper เพื่อ backward compatibility
function check_evaluation_status($pdo, $activity_id = 1) {
    return check_activity_evaluation_status($pdo, $activity_id);
}
