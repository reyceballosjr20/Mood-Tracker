<?php
// Start the session at the beginning of the file
session_start();

// Include database connection
require_once '../../includes/db-connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Handle password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($currentPassword)) {
        $_SESSION['error_message'] = "Current password is required.";
    } elseif (empty($newPassword)) {
        $_SESSION['error_message'] = "New password is required.";
    } elseif (strlen($newPassword) < 8) {
        $_SESSION['error_message'] = "New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = "New passwords do not match.";
    } else {
        try {
            // Get current hashed password from database
            $passwordStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $passwordStmt->bindParam(1, $userId, PDO::PARAM_INT);
            $passwordStmt->execute();
            
            if ($passwordStmt->rowCount() === 0) {
                $_SESSION['error_message'] = "User not found.";
            } else {
                $passwordData = $passwordStmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify current password
                if (!password_verify($currentPassword, $passwordData['password'])) {
                    $_SESSION['error_message'] = "Current password is incorrect.";
                } else {
                    // Check if new password is the same as current
                    if (password_verify($newPassword, $passwordData['password'])) {
                        $_SESSION['error_message'] = "New password must be different from current password.";
                    } else {
                        // Hash and update the new password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $updatePasswordStmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                        $updatePasswordStmt->bindParam(1, $hashedPassword, PDO::PARAM_STR);
                        $updatePasswordStmt->bindParam(2, $userId, PDO::PARAM_INT);
                        
                        if ($updatePasswordStmt->execute()) {
                            $_SESSION['success_message'] = "Password updated successfully!";
                            
                            // Optional: Log the password change
                            $logStmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, activity_description, ip_address) VALUES (?, 'password_change', 'User changed their password', ?)");
                            $logStmt->bindParam(1, $userId, PDO::PARAM_INT);
                            $logStmt->bindParam(2, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
                            $logStmt->execute();
                        } else {
                            $_SESSION['error_message'] = "Error updating password. Please try again.";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
    }
    
    // Redirect back to profile page
    header("Location: ../content/profile.php");
    exit();
}

// If someone accesses this file directly without POST data
$_SESSION['error_message'] = "Invalid request method.";
header("Location: ../content/profile.php");
exit();
?> 