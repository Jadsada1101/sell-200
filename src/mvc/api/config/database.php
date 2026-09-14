<?php 
class Database 
{ 
    private $host = "sell200_mysql"; 
    private $dbname = "db_student"; 
    private $username = "root"; 
    private $password = "root"; 
    public $conn; 

    public function connect() 
    { 
        // รายการ host ที่จะทดลองเชื่อมต่อ (รองรับทั้ง Docker, Local IP และ Localhost)
        $fallbackHosts = [$this->host, "127.0.0.1", "localhost"];

        foreach ($fallbackHosts as $currentHost) {
            try { 
                $this->conn = new PDO( 
                    "mysql:host=" . $currentHost . ";dbname=" . $this->dbname . ";charset=utf8mb4", 
                    $this->username, 
                    $this->password 
                ); 
     
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                return $this->conn; 
     
            } catch (PDOException $exception) { 
                // หากเชื่อมต่อ host ปัจจุบันไม่สำเร็จ ให้ลอง host ถัดไป
                continue;
            } 
        }

        echo json_encode([ 
            "status" => false, 
            "message" => "Database Connection Failed: ไม่สามารถเชื่อมต่อฐานข้อมูลได้" 
        ]); 
        exit(); 
    } 
}
