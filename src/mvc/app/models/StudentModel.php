<?php 
class StudentModel 
{ 
    private $apiUrl; 

    public function __construct()
    {
        // กำหนด URL ของ REST API สำหรับจัดการนักศึกษา
        if (isset($_SERVER['HTTP_HOST'])) {
            $scriptDirectory = dirname($_SERVER['SCRIPT_NAME']);
            $basePath = rtrim(str_replace('/public', '', $scriptDirectory), '/\\');
            $this->apiUrl = "http://127.0.0.1" . $basePath . "/api/student.php";
        } else {
            $this->apiUrl = "http://127.0.0.1/MVC/api/student.php"; 
        }
    }
 
    // ดึงข้อมูลนักศึกษาทั้งหมด
    public function getAll() 
    { 
        $jsonResponse = @file_get_contents($this->apiUrl); 
        if ($jsonResponse === false) {
            return [];
        }
        $result = json_decode($jsonResponse, true); 
        return $result["data"] ?? []; 
    } 
 
    // ดึงข้อมูลนักศึกษา 1 คนตาม StudentID
    public function getOne($studentId) 
    { 
        $url = $this->apiUrl . "?id=" . urlencode($studentId);
        $jsonResponse = @file_get_contents($url); 
        if ($jsonResponse === false) {
            return null;
        }
        $result = json_decode($jsonResponse, true); 
        return $result["data"] ?? null; 
    } 

    // เพิ่มข้อมูลนักศึกษาใหม่
    public function create($studentData) 
    { 
        return $this->sendApiRequest($this->apiUrl, "POST", $studentData); 
    } 
 
    // แก้ไขข้อมูลนักศึกษา
    public function update($studentData) 
    { 
        return $this->sendApiRequest($this->apiUrl, "PUT", $studentData); 
    } 
 
    // ลบข้อมูลนักศึกษา
    public function delete($studentId) 
    { 
        return $this->sendApiRequest($this->apiUrl, "DELETE", [ 
            "StudentID" => $studentId 
        ]); 
    } 

    // ฟังก์ชันผู้ช่วยสำหรับส่งคำขอ HTTP ไปยัง REST API
    private function sendApiRequest($url, $httpMethod, $payloadData) 
    { 
        $httpOptions = [ 
            "http" => [ 
                "header"  => "Content-Type: application/json", 
                "method"  => $httpMethod, 
                "content" => json_encode($payloadData) 
            ] 
        ]; 
        $streamContext = stream_context_create($httpOptions); 
        $responseBody = @file_get_contents($url, false, $streamContext); 
        
        if ($responseBody === false) {
            return [
                "status" => false, 
                "message" => "ไม่สามารถเชื่อมต่อ REST API ได้"
            ];
        }
        return json_decode($responseBody, true); 
    } 
} 
