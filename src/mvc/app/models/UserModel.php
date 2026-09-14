<?php 
class UserModel 
{ 
    private $apiUrl; 

    public function __construct()
    {
        // กำหนด URL ของ REST API สำหรับตรวจสอบการเข้าสู่ระบบ
        if (isset($_SERVER['HTTP_HOST'])) {
            $scriptDirectory = dirname($_SERVER['SCRIPT_NAME']);
            $basePath = rtrim(str_replace('/public', '', $scriptDirectory), '/\\');
            $this->apiUrl = "http://127.0.0.1" . $basePath . "/api/login.php";
        } else {
            $this->apiUrl = "http://127.0.0.1/MVC/api/login.php"; 
        }
    }

    // ส่งคำขอตรวจสอบชื่อผู้ใช้และรหัสผ่าน
    public function authenticate($username, $password)
    {
        $httpOptions = [ 
            "http" => [ 
                "header"  => "Content-Type: application/json", 
                "method"  => "POST", 
                "content" => json_encode([
                    "username" => $username,
                    "password" => $password
                ]) 
            ] 
        ]; 
        $streamContext = stream_context_create($httpOptions); 
        $responseBody = @file_get_contents($this->apiUrl, false, $streamContext); 
        
        if ($responseBody === false) {
            return [
                "status" => false, 
                "message" => "ไม่สามารถเชื่อมต่อระบบยืนยันตัวตนได้"
            ];
        }
        return json_decode($responseBody, true); 
    }
}
