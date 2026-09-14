<?php 
require_once "../app/models/MajorModel.php"; 

class MajorController 
{ 
    private $majorModel; 
    
    public function __construct() 
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Auth Guard: ป้องกันผู้ใช้ที่ยังไม่ได้ล็อกอิน
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $this->majorModel = new MajorModel(); 
    } 

    // แสดงหน้าฟอร์มเพิ่มข้อมูลสาขาวิชา 
    public function add() 
    { 
        require_once "../app/views/major/add.php"; 
    } 

    // รับค่าจากฟอร์มเพื่อบันทึกสาขาวิชาใหม่
    public function store() 
    { 
        $newMajorData = [ 
            "major_code" => trim($_POST["major_code"] ?? ""), 
            "major_name" => trim($_POST["major_name"] ?? ""), 
            "remark"     => trim($_POST["remark"] ?? "") 
        ]; 

        $this->majorModel->create($newMajorData); 
        header("Location: index.php?table=major"); 
        exit();
    } 

    // แสดงหน้าฟอร์มแก้ไขข้อมูลสาขาวิชา 
    public function edit() 
    { 
        $majorCode = $_GET["id"] ?? ""; 
        $major = $this->majorModel->getOne($majorCode); 

        require_once "../app/views/major/edit.php"; 
    } 

    // รับค่าจากฟอร์มเพื่อบันทึกการแก้ไขข้อมูลสาขาวิชา 
    public function update() 
    { 
        $updatedMajorData = [ 
            "major_code" => trim($_POST["major_code"] ?? ""), 
            "major_name" => trim($_POST["major_name"] ?? ""), 
            "remark"     => trim($_POST["remark"] ?? "") 
        ]; 

        $this->majorModel->update($updatedMajorData); 
        header("Location: index.php?table=major"); 
        exit();
    } 

    // ลบข้อมูลสาขาวิชาตาม major_code
    public function delete() 
    { 
        $majorCode = $_GET["id"] ?? ""; 
        $this->majorModel->delete($majorCode); 

        header("Location: index.php?table=major"); 
        exit();
    } 
} 
