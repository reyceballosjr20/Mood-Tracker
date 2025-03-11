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

// Load the Mood model
require_once '../../models/Mood.php';
$mood = new Mood();

// Get today's mood if it exists
$todaysMood = $mood->getTodaysMood($userId);

if ($todaysMood) {
    echo json_encode([
        'success' => true,
        'hasExistingMood' => true,
        'data' => $todaysMood
    ]);
} else {
    echo json_encode([
        'success' => true,
        'hasExistingMood' => false
    ]);
}
?> 