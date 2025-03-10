<?php
require_once 'Database.php';

class Mood {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Save a new mood entry
     * 
     * @param int $user_id User ID
     * @param string $mood_type Type of mood (sad, unhappy, neutral, etc.)
     * @param int $mood_value Numerical value (1-5)
     * @param string $mood_text Additional text about what influenced the mood
     * @return bool|int Returns new record ID or false on failure
     */
    public function saveMood($user_id, $mood_type, $mood_value, $mood_text = '') {
        $sql = "INSERT INTO mood_entries (user_id, mood_type, mood_value, mood_text) VALUES (?, ?, ?, ?)";
        $params = [$user_id, $mood_type, $mood_value, $mood_text];
        
        return $this->db->insert($sql, $params);
    }
    
    /**
     * Get mood entries for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Number of entries to return
     * @param int $offset Offset for pagination
     * @return array Array of mood entries
     */
    public function getUserMoods($user_id, $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM mood_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params = [$user_id, $limit, $offset];
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get the latest mood entry for a user
     * 
     * @param int $user_id User ID
     * @return array|bool Mood entry or false if not found
     */
    public function getLatestMood($user_id) {
        $sql = "SELECT * FROM mood_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $params = [$user_id];
        
        $result = $this->db->select($sql, $params);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Get mood entries for a specific date range
     * 
     * @param int $user_id User ID
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Array of mood entries
     */
    public function getMoodsByDateRange($user_id, $start_date, $end_date) {
        $sql = "SELECT * FROM mood_entries WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at";
        $params = [$user_id, $start_date, $end_date];
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get average mood value for a date range
     * 
     * @param int $user_id User ID
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return float Average mood value
     */
    public function getAverageMood($user_id, $start_date, $end_date) {
        $sql = "SELECT AVG(mood_value) as avg_mood FROM mood_entries 
                WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?";
        $params = [$user_id, $start_date, $end_date];
        
        $result = $this->db->select($sql, $params);
        return !empty($result) ? $result[0]['avg_mood'] : 0;
    }
    
    /**
     * Delete a mood entry
     * 
     * @param int $id Mood entry ID
     * @param int $user_id User ID (for security)
     * @return bool Success or failure
     */
    public function deleteMood($id, $user_id) {
        $sql = "DELETE FROM mood_entries WHERE id = ? AND user_id = ?";
        $params = [$id, $user_id];
        
        return $this->db->execute($sql, $params);
    }
} 