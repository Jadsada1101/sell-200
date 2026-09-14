<?php 
header("Content-Type: application/json; charset=UTF-8"); 
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
 
require_once "config/database.php"; 
require_once "models/User.php"; 
 
$database = new Database(); 
$dbConnection = $database->connect(); 
$userModel = new User($dbConnection); 
 
// รับข้อมูล JSON จาก Request Body
$requestData = json_decode(file_get_contents("php://input"), true); 
$userModel->username = $requestData["username"] ?? ""; 
$userModel->password = $requestData["password"] ?? ""; 

$userData = $userModel->login();

if ($userData !== false) {
    echo json_encode([
        "status" => true,
        "message" => "เข้าสู่ระบบสำเร็จ",
        "data" => $userData
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"
    ]);
}
