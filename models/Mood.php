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
     * @param int $userId User ID
     * @param string $moodType Type of mood (sad, happy, etc.)
     * @param string $moodText Optional text describing the mood
     * @return int|bool The new mood entry ID or false on failure
     */
    public function saveMood($userId, $moodType, $moodText = '') {
        try {
            $sql = "INSERT INTO mood_entries (user_id, mood_type, mood_text) 
                    VALUES (:user_id, :mood_type, :mood_text)";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':mood_type', $moodType, PDO::PARAM_STR);
            $stmt->bindParam(':mood_text', $moodText, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->db->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            // Log error
            error_log('Error saving mood: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get mood entries for a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of entries to return
     * @param int $offset Offset for pagination
     * @return array|bool Array of mood entries or false on failure
     */
    public function getUserMoods($userId, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT id, mood_type, mood_text, created_at
                    FROM mood_entries
                    WHERE user_id = :user_id
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            // Log error
            error_log('Error retrieving moods: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get mood statistics for a user
     * 
     * @param int $userId User ID
     * @param string $period 'day', 'week', 'month', or 'year'
     * @return array|bool Array of mood statistics or false on failure
     */
    public function getMoodStats($userId, $period = 'month') {
        try {
            // Define the date range based on the period
            $dateRange = '';
            switch ($period) {
                case 'day':
                    $dateRange = 'DATE(created_at) = CURDATE()';
                    break;
                case 'week':
                    $dateRange = 'created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
                    break;
                case 'month':
                    $dateRange = 'created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
                    break;
                case 'year':
                    $dateRange = 'created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)';
                    break;
                default:
                    $dateRange = 'created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
            }
            
            $sql = "SELECT mood_type, COUNT(*) as count
                    FROM mood_entries
                    WHERE user_id = :user_id AND $dateRange
                    GROUP BY mood_type
                    ORDER BY count DESC";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            // Log error
            error_log('Error retrieving mood stats: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user already has a mood entry for today
     * 
     * @param int $userId User ID
     * @return array|false The mood data for today or false if none exists
     */
    public function getTodaysMood($userId) {
        try {
            $sql = "SELECT id, mood_type, mood_text, created_at 
                    FROM mood_entries 
                    WHERE user_id = :user_id 
                    AND DATE(created_at) = CURDATE()
                    LIMIT 1";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ?: false;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Error checking today\'s mood: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing mood entry
     * 
     * @param int $moodId The ID of the mood entry to update
     * @param string $moodType Type of mood (sad, happy, etc.)
     * @param string $moodText Optional text describing the mood
     * @return bool Success or failure
     */
    public function updateMood($moodId, $moodType, $moodText = '') {
        try {
            $sql = "UPDATE mood_entries 
                    SET mood_type = :mood_type, mood_text = :mood_text, 
                        created_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':mood_type', $moodType, PDO::PARAM_STR);
            $stmt->bindParam(':mood_text', $moodText, PDO::PARAM_STR);
            $stmt->bindParam(':id', $moodId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error updating mood: ' . $e->getMessage());
            return false;
        }
    }
}
?> 