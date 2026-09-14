<?php 
// ดึง URL Path ที่เรียกเข้ามาจากฝั่งผู้ใช้ (เช่น /mvc หรือ /mvc/)
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/mvc', PHP_URL_PATH);
$cleanPath = rtrim($requestPath, '/');
$cleanPath = preg_replace('#/index\.php$#i', '', $cleanPath);

// ส่งต่อ Query String เดิม (ถ้ามี เช่น ?action=login หรือ ?table=major)
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';

// ส่งต่อ (Redirect) ไปยัง /public/ ของโปรเจกต์
header("Location: " . $cleanPath . "/public/" . $queryString, true, 302);
exit();
