<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../app/controllers/StudentController.php"; 
require_once "../app/controllers/MajorController.php"; 
require_once "../app/controllers/AuthController.php"; 

$action = $_GET["action"] ?? "index"; 

switch ($action) { 
    // ==========================================
    // 1. เส้นทางระบบยืนยันตัวตน (Auth Routes)
    // ==========================================
    case "login":
        $authController = new AuthController();
        $authController->login_page();
        break;

    case "authenticate":
        $authController = new AuthController();
        $authController->authenticate();
        break;

    case "logout":
        $authController = new AuthController();
        $authController->logout();
        break;

    // ==========================================
    // 2. เส้นทางจัดการนักศึกษา (Student Routes)
    // ==========================================
    case "index": 
        $studentController = new StudentController();
        $studentController->index(); 
        break; 

    case "add": 
        $studentController = new StudentController();
        $studentController->add(); 
        break; 

    case "store": 
        $studentController = new StudentController();
        $studentController->store(); 
        break; 

    case "edit": 
        $studentController = new StudentController();
        $studentController->edit(); 
        break; 

    case "update": 
        $studentController = new StudentController();
        $studentController->update(); 
        break; 

    case "delete": 
        $studentController = new StudentController();
        $studentController->delete(); 
        break; 
    
    // ==========================================
    // 3. เส้นทางจัดการสาขาวิชา (Major Routes)
    // ==========================================
    case "add_major": 
        $majorController = new MajorController();
        $majorController->add(); 
        break; 

    case "store_major": 
        $majorController = new MajorController();
        $majorController->store(); 
        break; 

    case "edit_major": 
        $majorController = new MajorController();
        $majorController->edit(); 
        break; 

    case "update_major": 
        $majorController = new MajorController();
        $majorController->update(); 
        break; 

    case "delete_major": 
        $majorController = new MajorController();
        $majorController->delete(); 
        break; 

    default: 
        $studentController = new StudentController();
        $studentController->index(); 
        break;
} 
