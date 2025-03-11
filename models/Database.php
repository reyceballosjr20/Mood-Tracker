<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'mood_tracker';
    private $username = 'root';  // Change to your database username
    private $password = 'icctvet1234';      // Change to your database password
    public $conn;
    
    /**
     * Connect to the database
     * 
     * @return PDO|null Database connection
     */
    public function __construct() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?> 