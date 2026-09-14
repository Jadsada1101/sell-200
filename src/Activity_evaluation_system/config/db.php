<?php
// การตั้งค่าการเชื่อมต่อฐานข้อมูล MySQL
$database_host = 'db';
$database_user = 'root';
$database_pass = 'root';
$database_name = 'sell200_db';

try {
    $pdo = new PDO(
        "mysql:host={$database_host};dbname={$database_name};charset=utf8mb4",
        $database_user,
        $database_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage());
}
