<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าเข้าสู่ระบบแล้วหรือไม่ และต้องเป็นนักศึกษา (student) เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}
