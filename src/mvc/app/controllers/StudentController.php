<?php 
require_once "../app/models/StudentModel.php"; 
require_once "../app/models/MajorModel.php"; 

class StudentController 
{ 
    private $studentModel; 
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

        $this->studentModel = new StudentModel(); 
        $this->majorModel = new MajorModel();
    } 
 
    // แสดงหน้าหลัก (ตารางนักศึกษา หรือ ตารางสาขาวิชา)
    public function index() 
    { 
        $viewTable = $_GET["table"] ?? "student"; 
        $students = $this->studentModel->getAll(); 
        $majors = $this->majorModel->getAll(); 

        require_once "../app/views/student/index.php"; 
    } 
 
    // แสดงหน้าฟอร์มเพิ่มข้อมูลนักศึกษา 
    public function add() 
    { 
        $majors = $this->majorModel->getAll(); 
        require_once "../app/views/student/add.php"; 
    } 
 
    // รับค่าจากฟอร์มเพื่อบันทึกนักศึกษาใหม่
    public function store() 
    { 
        $newStudentData = [ 
            "StudentID"       => trim($_POST["StudentID"] ?? ""), 
            "Student_Name"    => trim($_POST["Student_Name"] ?? ""), 
            "Student_Surname" => trim($_POST["Student_Surname"] ?? ""), 
            "Student_Website" => trim($_POST["Student_Website"] ?? ""), 
            "major_code"      => trim($_POST["major_code"] ?? "") 
        ]; 

        $this->studentModel->create($newStudentData); 
        header("Location: index.php?table=student"); 
        exit();
    } 
 
    // แสดงหน้าฟอร์มแก้ไขข้อมูลนักศึกษา
    public function edit() 
    { 
        $studentId = $_GET["id"] ?? ""; 
        $student = $this->studentModel->getOne($studentId); 
        $majors = $this->majorModel->getAll(); 

        require_once "../app/views/student/edit.php"; 
    } 
 
    // รับค่าจากฟอร์มเพื่อบันทึกการแก้ไขข้อมูลนักศึกษา
    public function update() 
    { 
        $updatedStudentData = [ 
            "StudentID"       => trim($_POST["StudentID"] ?? ""), 
            "Student_Name"    => trim($_POST["Student_Name"] ?? ""), 
            "Student_Surname" => trim($_POST["Student_Surname"] ?? ""), 
            "Student_Website" => trim($_POST["Student_Website"] ?? ""), 
            "major_code"      => trim($_POST["major_code"] ?? "") 
        ]; 

        $this->studentModel->update($updatedStudentData); 
        header("Location: index.php?table=student"); 
        exit();
    } 
 
    // ลบข้อมูลนักศึกษาตาม StudentID
    public function delete() 
    { 
        $studentId = $_GET["id"] ?? ""; 
        $this->studentModel->delete($studentId); 

        header("Location: index.php?table=student"); 
        exit();
    } 
} 
