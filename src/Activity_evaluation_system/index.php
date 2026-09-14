<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
        exit;
    } else {
        header('Location: student/dashboard.php');
        exit;
    }
}

header('Location: login.php');
exit;
