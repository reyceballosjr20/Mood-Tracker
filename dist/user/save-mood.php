<?php
// Initialize session
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get the user ID from session
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
    exit;
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input data
if (!isset($data['mood_type']) || empty($data['mood_type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mood type is required']);
    exit;
}

// Sanitize inputs
$moodType = filter_var($data['mood_type'], FILTER_SANITIZE_STRING);
$moodText = isset($data['mood_text']) ? filter_var($data['mood_text'], FILTER_SANITIZE_STRING) : '';

// Load the Mood model
require_once '../../models/Mood.php';
$mood = new Mood();

// Save the mood entry
$moodId = $mood->saveMood($userId, $moodType, $moodText);

if ($moodId) {
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Mood saved successfully',
        'data' => [
            'id' => $moodId,
            'mood_type' => $moodType,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save mood entry'
    ]);
}
?> 