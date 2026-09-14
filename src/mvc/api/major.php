<?php 
header("Content-Type: application/json; charset=UTF-8"); 
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
 
require_once "config/database.php"; 
require_once "models/Major.php"; 
 
$database = new Database(); 
$dbConnection = $database->connect(); 
$majorModel = new Major($dbConnection); 
$requestMethod = $_SERVER["REQUEST_METHOD"]; 

switch ($requestMethod) {
    // ==========================================
    // 1. GET: ดึงข้อมูลสาขาวิชา (ทั้งหมด หรือ รายสาขา)
    // ==========================================
    case "GET":
        if (isset($_GET["id"]) && !empty($_GET["id"])) {
            // ดึงข้อมูลสาขาวิชารายสาขาตาม major_code
            $majorModel->major_code = $_GET["id"];
            $statement = $majorModel->getOne();
            $majorData = $statement->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => true,
                "data" => $majorData ?: null
            ]);
        } else {
            // ดึงข้อมูลสาขาวิชาทั้งหมด
            $statement = $majorModel->getAll();
            $majorList = $statement->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => true,
                "data" => $majorList
            ]);
        }
        break;

    // ==========================================
    // 2. POST: เพิ่มข้อมูลสาขาวิชาใหม่
    // ==========================================
    case "POST":
        $requestData = json_decode(file_get_contents("php://input"), true);
        $majorModel->major_code = $requestData["major_code"] ?? "";
        $majorModel->major_name = $requestData["major_name"] ?? "";
        $majorModel->remark = $requestData["remark"] ?? "";

        $majorModel->create();
        echo json_encode([
            "status" => true,
            "message" => "เพิ่มข้อมูลสาขาวิชาสำเร็จ"
        ]);
        break;

    // ==========================================
    // 3. PUT: แก้ไขข้อมูลสาขาวิชา
    // ==========================================
    case "PUT":
        $requestData = json_decode(file_get_contents("php://input"), true);
        $majorModel->major_code = $requestData["major_code"] ?? "";
        $majorModel->major_name = $requestData["major_name"] ?? "";
        $majorModel->remark = $requestData["remark"] ?? "";

        $majorModel->update();
        echo json_encode([
            "status" => true,
            "message" => "แก้ไขข้อมูลสาขาวิชาสำเร็จ"
        ]);
        break;

    // ==========================================
    // 4. DELETE: ลบข้อมูลสาขาวิชา
    // ==========================================
    case "DELETE":
        $requestData = json_decode(file_get_contents("php://input"), true);
        $majorModel->major_code = $requestData["major_code"] ?? "";

        $majorModel->delete();
        echo json_encode([
            "status" => true,
            "message" => "ลบข้อมูลสาขาวิชาสำเร็จ"
        ]);
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Method Not Allowed"
        ]);
        break;
}
