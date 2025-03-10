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

    /**
     * Execute an insert query and return the new ID
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return int|bool Last inserted ID or false on failure
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            // Log error
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}
?> 