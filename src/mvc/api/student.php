<?php 
header("Content-Type: application/json; charset=UTF-8"); 
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
 
require_once "config/database.php"; 
require_once "models/Student.php"; 
 
$database = new Database(); 
$dbConnection = $database->connect(); 
$studentModel = new Student($dbConnection); 
$requestMethod = $_SERVER["REQUEST_METHOD"]; 
 
switch ($requestMethod) { 
    // ==========================================
    // 1. GET: ดึงข้อมูลนักศึกษา (ทั้งหมด หรือ รายคน)
    // ==========================================
    case "GET": 
        if (isset($_GET["id"]) && !empty($_GET["id"])) { 
            // ดึงข้อมูลนักศึกษารายบุคคลตาม StudentID
            $studentModel->StudentID = $_GET["id"]; 
            $statement = $studentModel->getOne(); 
            $studentData = $statement->fetch(PDO::FETCH_ASSOC); 

            echo json_encode([ 
                "status" => true, 
                "data" => $studentData ?: null 
            ]); 
        } else { 
            // ดึงข้อมูลนักศึกษาทั้งหมด
            $statement = $studentModel->getAll(); 
            $studentList = $statement->fetchAll(PDO::FETCH_ASSOC); 

            echo json_encode([ 
                "status" => true, 
                "data" => $studentList 
            ]); 
        } 
        break; 
 
    // ==========================================
    // 2. POST: เพิ่มข้อมูลนักศึกษาใหม่
    // ==========================================
    case "POST": 
        $requestData = json_decode(file_get_contents("php://input"), true); 
        $studentModel->StudentID = $requestData["StudentID"] ?? ""; 
        $studentModel->Student_Name = $requestData["Student_Name"] ?? ""; 
        $studentModel->Student_Surname = $requestData["Student_Surname"] ?? ""; 
        $studentModel->Student_Website = $requestData["Student_Website"] ?? ""; 
        $studentModel->major_code = !empty($requestData["major_code"]) ? $requestData["major_code"] : null; 

        $studentModel->create(); 
        echo json_encode([ 
            "status" => true, 
            "message" => "เพิ่มข้อมูลนักศึกษาสำเร็จ" 
        ]); 
        break; 
 
    // ==========================================
    // 3. PUT: แก้ไขข้อมูลนักศึกษา
    // ==========================================
    case "PUT": 
        $requestData = json_decode(file_get_contents("php://input"), true); 
        $studentModel->StudentID = $requestData["StudentID"] ?? ""; 
        $studentModel->Student_Name = $requestData["Student_Name"] ?? ""; 
        $studentModel->Student_Surname = $requestData["Student_Surname"] ?? ""; 
        $studentModel->Student_Website = $requestData["Student_Website"] ?? ""; 
        $studentModel->major_code = !empty($requestData["major_code"]) ? $requestData["major_code"] : null; 

        $studentModel->update(); 
        echo json_encode([ 
            "status" => true, 
            "message" => "แก้ไขข้อมูลนักศึกษาสำเร็จ" 
        ]); 
        break; 
 
    // ==========================================
    // 4. DELETE: ลบข้อมูลนักศึกษา
    // ==========================================
    case "DELETE": 
        $requestData = json_decode(file_get_contents("php://input"), true); 
        $studentModel->StudentID = $requestData["StudentID"] ?? ""; 

        $studentModel->delete(); 
        echo json_encode([ 
            "status" => true, 
            "message" => "ลบข้อมูลนักศึกษาสำเร็จ" 
        ]); 
        break; 

    default:
        echo json_encode([
            "status" => false,
            "message" => "Method Not Allowed"
        ]);
        break;
} 
