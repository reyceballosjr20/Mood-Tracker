<?php
class Database {
    private $host = "localhost";
    private $db_name = "mood_tracker";
    private $username = "root";
    private $password = "icctvet1234";
    private $conn;
    
    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?> 