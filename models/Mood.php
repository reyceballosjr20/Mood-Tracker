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

    /**
     * Get user moods for a specific month
     * 
     * @param int $userId User ID
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2023)
     * @return array|bool Array of mood entries or false on failure
     */
    public function getUserMoodsByMonth($userId, $month, $year) {
        try {
            $sql = "SELECT id, mood_type, mood_text, created_at
                    FROM mood_entries
                    WHERE user_id = :user_id
                    AND MONTH(created_at) = :month
                    AND YEAR(created_at) = :year
                    ORDER BY created_at ASC";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Error retrieving monthly moods: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get mood statistics for a specific month
     * 
     * @param int $userId User ID
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2023)
     * @return array|bool Array of mood statistics or false on failure
     */
    public function getMoodStatsByMonth($userId, $month, $year) {
        try {
            $sql = "SELECT mood_type, COUNT(*) as count
                    FROM mood_entries
                    WHERE user_id = :user_id
                    AND MONTH(created_at) = :month
                    AND YEAR(created_at) = :year
                    GROUP BY mood_type
                    ORDER BY count DESC";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Error retrieving mood stats: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's current logging streak
     * 
     * @param int $userId User ID
     * @return int Number of consecutive days with mood entries
     */
    public function getUserLoggingStreak($userId) {
        try {
            // Get the most recent mood entry date
            $sql = "SELECT DATE(created_at) as entry_date
                    FROM mood_entries
                    WHERE user_id = :user_id
                    ORDER BY created_at DESC
                    LIMIT 1";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if (!$stmt->execute() || $stmt->rowCount() == 0) {
                return 0; // No entries found
            }
            
            $lastEntry = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastEntryDate = new DateTime($lastEntry['entry_date']);
            $today = new DateTime(date('Y-m-d'));
            
            // If the last entry is not from today or yesterday, streak is broken
            $daysDifference = $today->diff($lastEntryDate)->days;
            if ($daysDifference > 1) {
                return 0;
            }
            
            // Count consecutive days backward from the last entry
            $streak = 1; // Start with 1 for the last entry day
            $currentDate = clone $lastEntryDate;
            
            while (true) {
                $currentDate->modify('-1 day');
                
                // Check if there's an entry for this day
                $sql = "SELECT 1
                        FROM mood_entries
                        WHERE user_id = :user_id
                        AND DATE(created_at) = :check_date
                        LIMIT 1";
                
                $stmt = $this->db->conn->prepare($sql);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $checkDate = $currentDate->format('Y-m-d');
                $stmt->bindParam(':check_date', $checkDate);
                
                if (!$stmt->execute() || $stmt->rowCount() == 0) {
                    break; // No entry for this day, streak ends
                }
                
                $streak++;
            }
            
            return $streak;
            
        } catch (PDOException $e) {
            error_log('Error calculating streak: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get the average mood score for a month
     * 
     * @param int $userId User ID
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2023)
     * @return float|bool Average mood score or false on failure
     */
    public function getAverageMoodScore($userId, $month, $year) {
        try {
            // Define mood type scoring
            $moodScores = [
                'sad' => 1,
                'unhappy' => 2,
                'anxious' => 2.5,
                'tired' => 3,
                'neutral' => 3.5,
                'focused' => 4,
                'good' => 4.5,
                'energetic' => 5,
                'excellent' => 5.5
            ];
            
            // Get all moods for the month
            $sql = "SELECT mood_type
                    FROM mood_entries
                    WHERE user_id = :user_id
                    AND MONTH(created_at) = :month
                    AND YEAR(created_at) = :year";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            if (!$stmt->execute() || $stmt->rowCount() == 0) {
                return 0; // No entries
            }
            
            $moods = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalScore = 0;
            $count = 0;
            
            foreach ($moods as $mood) {
                if (isset($moodScores[$mood['mood_type']])) {
                    $totalScore += $moodScores[$mood['mood_type']];
                    $count++;
                }
            }
            
            if ($count == 0) {
                return 0;
            }
            
            return round($totalScore / $count, 1);
            
        } catch (PDOException $e) {
            error_log('Error calculating average mood: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get the best day of the month
     * 
     * @param int $userId User ID
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2023)
     * @return array|bool Day data or false on failure
     */
    public function getBestDay($userId, $month, $year) {
        try {
            // Order moods by "happiness" level
            $moodOrder = "CASE mood_type 
                          WHEN 'excellent' THEN 1 
                          WHEN 'energetic' THEN 2 
                          WHEN 'good' THEN 3 
                          WHEN 'focused' THEN 4 
                          WHEN 'neutral' THEN 5 
                          WHEN 'tired' THEN 6
                          WHEN 'anxious' THEN 7
                          WHEN 'unhappy' THEN 8
                          WHEN 'sad' THEN 9
                          ELSE 10 END";
            
            $sql = "SELECT id, mood_type, mood_text, created_at
                    FROM mood_entries
                    WHERE user_id = :user_id
                    AND MONTH(created_at) = :month
                    AND YEAR(created_at) = :year
                    ORDER BY $moodOrder ASC, created_at DESC
                    LIMIT 1";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            if (!$stmt->execute() || $stmt->rowCount() == 0) {
                return false;
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error finding best day: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the worst day of the month
     * 
     * @param int $userId User ID
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2023)
     * @return array|bool Day data or false on failure
     */
    public function getWorstDay($userId, $month, $year) {
        try {
            // Order moods by "sadness" level
            $moodOrder = "CASE mood_type 
                          WHEN 'sad' THEN 1 
                          WHEN 'unhappy' THEN 2 
                          WHEN 'anxious' THEN 3 
                          WHEN 'tired' THEN 4 
                          WHEN 'neutral' THEN 5 
                          WHEN 'focused' THEN 6
                          WHEN 'good' THEN 7
                          WHEN 'energetic' THEN 8
                          WHEN 'excellent' THEN 9
                          ELSE 10 END";
            
            $sql = "SELECT id, mood_type, mood_text, created_at
                    FROM mood_entries
                    WHERE user_id = :user_id
                    AND MONTH(created_at) = :month
                    AND YEAR(created_at) = :year
                    ORDER BY $moodOrder ASC, created_at DESC
                    LIMIT 1";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            if (!$stmt->execute() || $stmt->rowCount() == 0) {
                return false;
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error finding worst day: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get mood patterns by day of week
     * 
     * @param int $userId User ID
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2023)
     * @return array|bool Day of week patterns or false on failure
     */
    public function getMoodPatternsByDayOfWeek($userId, $month, $year) {
        try {
            // Define mood type scoring
            $moodScores = [
                'sad' => 1,
                'unhappy' => 2,
                'anxious' => 2.5,
                'tired' => 3,
                'neutral' => 3.5,
                'focused' => 4,
                'good' => 4.5,
                'energetic' => 5,
                'excellent' => 5.5
            ];
            
            // MySQL DAYOFWEEK returns 1 for Sunday, 2 for Monday, etc.
            // We'll convert to 1 for Monday, 2 for Tuesday, etc.
            $sql = "SELECT 
                        CASE DAYOFWEEK(created_at)
                            WHEN 1 THEN 7 -- Sunday becomes 7
                            ELSE DAYOFWEEK(created_at) - 1 -- Others shift down by 1
                        END as day_of_week,
                        mood_type,
                        COUNT(*) as count
                    FROM mood_entries
                    WHERE user_id = :user_id
                    AND MONTH(created_at) = :month
                    AND YEAR(created_at) = :year
                    GROUP BY day_of_week, mood_type
                    ORDER BY day_of_week, count DESC";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                return false;
            }
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) {
                return false;
            }
            
            // Process the results to get average mood score per day of week
            $daysOfWeek = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0]; // Initialize with zeroes
            $dayCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
            
            foreach ($result as $row) {
                $dayOfWeek = $row['day_of_week'];
                $moodType = $row['mood_type'];
                $count = $row['count'];
                
                if (isset($moodScores[$moodType])) {
                    $daysOfWeek[$dayOfWeek] += $moodScores[$moodType] * $count;
                    $dayCounts[$dayOfWeek] += $count;
                }
            }
            
            // Calculate averages
            $averages = [];
            foreach ($daysOfWeek as $day => $score) {
                if ($dayCounts[$day] > 0) {
                    $averages[] = [
                        'day_of_week' => $day,
                        'avg_score' => round($score / $dayCounts[$day], 1),
                        'count' => $dayCounts[$day]
                    ];
                }
            }
            
            return $averages;
            
        } catch (PDOException $e) {
            error_log('Error calculating day of week patterns: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get mood history with optional filtering
     * 
     * @param int $userId User ID
     * @param string $period Time period (week, month, quarter, year, all)
     * @param string $search Optional search query
     * @return array|bool Array of mood entries or false on failure
     */
    public function getMoodHistory($userId, $period = 'month', $search = '') {
        try {
            // Determine date range based on period
            $dateRange = $this->getDateRangeForPeriod($period);
            
            // Build search condition if needed
            $searchCondition = '';
            $params = [':user_id' => $userId];
            
            if (!empty($search)) {
                $searchCondition = "AND (mood_type LIKE :search OR mood_text LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            $sql = "SELECT id, mood_type, mood_text, created_at
                    FROM mood_entries
                    WHERE user_id = :user_id 
                    $dateRange
                    $searchCondition
                    ORDER BY created_at DESC";
            
            $stmt = $this->db->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('Error retrieving mood history: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available year-month combinations for a user
     * 
     * @param int $userId User ID
     * @return array Array of YYYY-MM strings
     */
    public function getAvailableMonths($userId) {
        try {
            $sql = "SELECT DISTINCT YEAR(created_at) as year, MONTH(created_at) as month
                    FROM mood_entries
                    WHERE user_id = :user_id
                    ORDER BY year DESC, month DESC";
            
            $stmt = $this->db->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $months = [];
                
                foreach ($results as $row) {
                    $months[] = $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT);
                }
                
                return $months;
            }
            
            return [];
        } catch (PDOException $e) {
            error_log('Error retrieving available months: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function to get SQL date range condition
     * 
     * @param string $period Period string (week, month, etc.)
     * @return string SQL condition
     */
    private function getDateRangeForPeriod($period) {
        switch ($period) {
            case 'week':
                return 'AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
            case 'month':
                return 'AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
            case 'quarter':
                return 'AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
            case 'year':
                return 'AND created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)';
            case 'all':
                return ''; // No date restriction
            default:
                return 'AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
        }
    }
}
?> 