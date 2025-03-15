<?php
// Start session to access user data
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

// Include database connection
require_once '../../includes/db-connect.php';

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get form data
$firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

// Validate inputs
if (empty($firstName) || empty($lastName)) {
    echo json_encode([
        'success' => false,
        'message' => 'First name and last name are required.'
    ]);
    exit;
}

// Update user information in the database
try {
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bindParam(1, $firstName, PDO::PARAM_STR);
    $stmt->bindParam(2, $lastName, PDO::PARAM_STR);
    $stmt->bindParam(3, $bio, PDO::PARAM_STR);
    $stmt->bindParam(4, $userId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'bio' => $bio
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating profile. Please try again.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 