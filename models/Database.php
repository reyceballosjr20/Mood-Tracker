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
    
    /**
     * Execute a select query and return the results
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|bool Array of results or false on failure
     */
    public function select($sql, $params = []) {
        try {
            // Initialize connection if not already done
            if (!$this->conn) {
                $this->getConnection();
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Log error
            error_log("Database select error: " . $e->getMessage());
            return false;
        }
    }
}
?> 