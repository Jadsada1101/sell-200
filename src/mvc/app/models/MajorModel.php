<?php 
class MajorModel 
{ 
    private $apiUrl; 

    public function __construct()
    {
        // กำหนด URL ของ REST API สำหรับจัดการสาขาวิชา
        if (isset($_SERVER['HTTP_HOST'])) {
            $scriptDirectory = dirname($_SERVER['SCRIPT_NAME']);
            $basePath = rtrim(str_replace('/public', '', $scriptDirectory), '/\\');
            $this->apiUrl = "http://127.0.0.1" . $basePath . "/api/major.php";
        } else {
            $this->apiUrl = "http://127.0.0.1/MVC/api/major.php"; 
        }
    }

    // ดึงข้อมูลสาขาวิชาทั้งหมด
    public function getAll() 
    { 
        $jsonResponse = @file_get_contents($this->apiUrl); 
        if ($jsonResponse === false) {
            return [];
        }
        $result = json_decode($jsonResponse, true); 
        return $result["data"] ?? []; 
    } 

    // ดึงข้อมูลสาขาวิชา 1 สาขาตาม major_code
    public function getOne($majorCode) 
    { 
        $url = $this->apiUrl . "?id=" . urlencode($majorCode);
        $jsonResponse = @file_get_contents($url); 
        if ($jsonResponse === false) {
            return null;
        }
        $result = json_decode($jsonResponse, true); 
        return $result["data"] ?? null; 
    } 

    // เพิ่มสาขาวิชาใหม่
    public function create($majorData) 
    { 
        return $this->sendApiRequest($this->apiUrl, "POST", $majorData); 
    } 

    // แก้ไขข้อมูลสาขาวิชา
    public function update($majorData) 
    { 
        return $this->sendApiRequest($this->apiUrl, "PUT", $majorData); 
    } 

    // ลบสาขาวิชา
    public function delete($majorCode) 
    { 
        return $this->sendApiRequest($this->apiUrl, "DELETE", [ 
            "major_code" => $majorCode 
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
