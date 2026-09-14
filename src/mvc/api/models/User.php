<?php 
class User 
{ 
    private $conn; 
    private $table = "tb_users"; 
 
    public $username; 
    public $password; 
 
    public function __construct($db) 
    { 
        $this->conn = $db; 
    } 
 
    // Verify login credentials
    public function login() 
    { 
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1"; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->bindParam(":username", $this->username); 
        $stmt->execute(); 
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            // Check hashed password or plain text fallback
            if (password_verify($this->password, $user['password']) || $this->password === $user['password']) {
                unset($user['password']); // remove password before returning
                return $user;
            }
        }
        return false; 
    } 
} 
