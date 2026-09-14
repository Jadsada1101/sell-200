<?php 
class Major 
{ 
    private $conn; 
    private $table = "tb_major"; 

    public $major_code;
    public $major_name;
    public $remark;
 
    public function __construct($db) 
    { 
        $this->conn = $db; 
    } 
 
    // GET ALL
    public function getAll() 
    { 
        $sql = "SELECT * FROM {$this->table} ORDER BY major_code"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->execute(); 
        return $stmt; 
    } 

    // GET ONE
    public function getOne() 
    { 
        $sql = "SELECT * FROM {$this->table} WHERE major_code = :major_code"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":major_code", $this->major_code); 
        $stmt->execute(); 
        return $stmt; 
    } 

    // INSERT
    public function create() 
    { 
        $sql = "INSERT INTO {$this->table} (major_code, major_name, remark) VALUES (:major_code, :major_name, :remark)"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":major_code", $this->major_code); 
        $stmt->bindParam(":major_name", $this->major_name); 
        $stmt->bindParam(":remark", $this->remark); 
        return $stmt->execute(); 
    } 

    // UPDATE
    public function update() 
    { 
        $sql = "UPDATE {$this->table} SET major_name = :major_name, remark = :remark WHERE major_code = :major_code"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":major_code", $this->major_code); 
        $stmt->bindParam(":major_name", $this->major_name); 
        $stmt->bindParam(":remark", $this->remark); 
        return $stmt->execute(); 
    } 

    // DELETE
    public function delete() 
    { 
        $sql = "DELETE FROM {$this->table} WHERE major_code = :major_code"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":major_code", $this->major_code); 
        return $stmt->execute(); 
    } 
} 
