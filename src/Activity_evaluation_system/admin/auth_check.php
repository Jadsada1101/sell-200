<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าเข้าสู่ระบบแล้วหรือไม่ และต้องมีบทบาทเป็น admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
