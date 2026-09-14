<?php 
require_once "../app/models/UserModel.php"; 

class AuthController 
{ 
    private $userModel; 

    public function __construct() 
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel(); 
    } 
 
    // แสดงหน้าฟอร์มเข้าสู่ระบบ (Login)
    public function login_page($errorMessage = null) 
    { 
        // หากเข้าสู่ระบบแล้ว ให้ส่งกลับไปยังหน้าแรก
        if (isset($_SESSION['user'])) {
            header("Location: index.php");
            exit();
        }

        $error = $errorMessage;
        require_once "../app/views/auth/login.php"; 
    } 
 
    // รับค่าจากฟอร์มเพื่อตรวจสอบข้อมูลการเข้าสู่ระบบ
    public function authenticate() 
    { 
        $username = trim($_POST["username"] ?? "");
        $password = trim($_POST["password"] ?? "");

        if (empty($username) || empty($password)) {
            $this->login_page("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
            return;
        }

        $loginResult = $this->userModel->authenticate($username, $password);

        if (isset($loginResult["status"]) && $loginResult["status"] === true) {
            // บันทึกข้อมูลผู้ใช้ลงใน Session
            $_SESSION['user'] = $loginResult["data"];
            header("Location: index.php");
            exit();
        } else {
            $errorMessage = $loginResult["message"] ?? "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            $this->login_page($errorMessage);
        }
    } 

    // ออกจากระบบ (Logout)
    public function logout() 
    { 
        unset($_SESSION['user']);
        session_destroy();

        header("Location: index.php?action=login");
        exit();
    } 
} 
