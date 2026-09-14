<?php 
class Student 
{ 
    private $conn; 
    private $table = "tb_student"; 
 
    public $StudentID; 
    public $Student_Name; 
    public $Student_Surname; 
    public $Student_Website; 
    public $major_code; 
 
    public function __construct($db) 
    { 
        $this->conn = $db; 
    } 
 
    // GET ALL (JOIN tb_major เพื่อเอา major_name และ remark มาแสดง)
    public function getAll() 
    { 
        $sql = "SELECT s.*, m.major_name, m.remark 
                FROM {$this->table} s 
                LEFT JOIN tb_major m ON s.major_code = m.major_code 
                ORDER BY s.StudentID"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->execute(); 
        return $stmt; 
    } 
 
    // GET ONE (JOIN tb_major)
    public function getOne() 
    { 
        $sql = "SELECT s.*, m.major_name, m.remark 
                FROM {$this->table} s 
                LEFT JOIN tb_major m ON s.major_code = m.major_code 
                WHERE s.StudentID = :StudentID"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":StudentID", $this->StudentID); 
        $stmt->execute(); 
        return $stmt; 
    } 
 
    // INSERT 
    public function create() 
    { 
        $sql = "INSERT INTO {$this->table} 
            (StudentID, Student_Name, Student_Surname, Student_Website, major_code) 
            VALUES ( 
                :StudentID, 
                :Student_Name, 
                :Student_Surname, 
                :Student_Website, 
                :major_code 
            )"; 
 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":StudentID", $this->StudentID); 
        $stmt->bindParam(":Student_Name", $this->Student_Name); 
        $stmt->bindParam(":Student_Surname", $this->Student_Surname); 
        $stmt->bindParam(":Student_Website", $this->Student_Website); 
        $stmt->bindParam(":major_code", $this->major_code); 
        return $stmt->execute(); 
    } 
 
    // UPDATE 
    public function update() 
    { 
        $sql = "UPDATE {$this->table} SET  
                Student_Name = :Student_Name, 
                Student_Surname = :Student_Surname, 
                Student_Website = :Student_Website, 
                major_code = :major_code 
            WHERE StudentID = :StudentID"; 
 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":StudentID", $this->StudentID); 
        $stmt->bindParam(":Student_Name", $this->Student_Name); 
        $stmt->bindParam(":Student_Surname", $this->Student_Surname); 
        $stmt->bindParam(":Student_Website", $this->Student_Website); 
        $stmt->bindParam(":major_code", $this->major_code); 
        return $stmt->execute(); 
    } 
 
    // DELETE 
    public function delete() 
    { 
        $sql = "DELETE FROM {$this->table} 
            WHERE StudentID = :StudentID"; 
 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":StudentID", $this->StudentID); 
        return $stmt->execute(); 
    } 
} 
