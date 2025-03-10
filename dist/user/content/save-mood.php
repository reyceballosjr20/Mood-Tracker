<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An error occurred'
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }
    
    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    // Validate incoming data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['mood_type']) || !isset($data['mood_text'])) {
        throw new Exception('Invalid mood data');
    }
    
    // Map mood types to numerical values
    $moodValues = [
        'sad' => 1,
        'unhappy' => 2,
        'neutral' => 3,
        'good' => 4,
        'excellent' => 5,
        'anxious' => 2,
        'tired' => 2,
        'focused' => 4,
        'energetic' => 5
    ];
    
    $mood_type = $data['mood_type'];
    $mood_value = $moodValues[$mood_type] ?? 3; // Default to neutral if unknown mood
    $mood_text = $data['mood_text'];
    $user_id = $_SESSION['user_id'];
    
    // Insert mood entry
    $stmt = $conn->prepare('INSERT INTO mood_entries (user_id, mood_type, mood_value, mood_text) 
                           VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isis', $user_id, $mood_type, $mood_value, $mood_text);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Mood logged successfully';
    } else {
        throw new Exception('Failed to save mood entry: ' . $conn->error);
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Error in save-mood.php: ' . $e->getMessage());
}

echo json_encode($response);
exit;
?> 