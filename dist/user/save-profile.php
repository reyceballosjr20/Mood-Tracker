<?php
// Add this at the top of the file for debugging if needed
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Initialize session
session_start();

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Authorization required']);
    exit;
}

// Include database connection
require_once '../includes/db-connect.php';

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user session']);
    exit;
}

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action']) && !isset($_FILES['profile_image'])) {
    // Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    // Validate data
    if (empty($first_name) || empty($last_name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists for another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already in use by another account']);
        exit;
    }
    
    // Update user profile
    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, bio = ? WHERE id = ?");
        $result = $stmt->execute([$first_name, $last_name, $email, $bio, $user_id]);
        
        if ($result) {
            // Update session data
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            $_SESSION['bio'] = $bio;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Profile updated successfully',
                'data' => [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Process password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required']);
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }
    
    if (strlen($new_password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit;
    }
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Update password
    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashed_password, $user_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Process profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $upload_dir = '../uploads/profile_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Validate file
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG, and GIF files are allowed']);
        exit;
    }
    
    if ($_FILES['profile_image']['size'] > 5000000) { // 5MB limit
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
        exit;
    }
    
    // Generate unique filename
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Upload file
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
        // Update database with just the filename
        try {
            // Store only the filename, not the full path
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $result = $stmt->execute([$new_filename, $user_id]);
            
            if ($result) {
                // Update session data - store the filename only
                $_SESSION['profile_image'] = $new_filename;
                
                // Return the full path for the frontend
                $image_path = 'uploads/profile_images/' . $new_filename;
                
                // Check if this is a form submission with redirect
                if (isset($_POST['redirect'])) {
                    // Redirect to the specified page
                    header('Location: ' . $_POST['redirect']);
                    exit;
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Profile image updated successfully',
                    'image_path' => $image_path,
                    'filename' => $new_filename,
                    'debug_info' => [
                        'upload_dir' => $upload_dir,
                        'new_filename' => $new_filename,
                        'full_path' => $upload_path,
                        'exists' => file_exists($upload_path) ? 'yes' : 'no'
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update profile image in database']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to upload image',
            'debug_info' => [
                'upload_error' => $_FILES['profile_image']['error'],
                'tmp_name' => $_FILES['profile_image']['tmp_name'],
                'upload_path' => $upload_path,
                'dir_exists' => file_exists($upload_dir) ? 'yes' : 'no',
                'dir_writable' => is_writable($upload_dir) ? 'yes' : 'no'
            ]
        ]);
    }
    exit;
}

// Process profile image removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_image') {
    try {
        // Get current image filename
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['profile_image'])) {
            // Delete file if it exists
            $file_path = '../uploads/profile_images/' . $user['profile_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Update database to remove image path
        $stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result) {
            // Update session data
            $_SESSION['profile_image'] = '';
            
            echo json_encode(['success' => true, 'message' => 'Profile image removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove profile image']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// If we get here, it's an invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request']); 