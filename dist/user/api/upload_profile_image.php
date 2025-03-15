<?php
// Start session to access user data
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response = [
        'success' => false,
        'message' => 'User not authenticated'
    ];
    echo json_encode($response);
    exit;
}

// Include database connection
require_once '../../includes/db-connect.php';

// Set up response array
$response = [
    'success' => false,
    'message' => '',
    'file_path' => ''
];

// Check if file was uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    
    $errorCode = $_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $response['message'] = $errorMessages[$errorCode] ?? 'Unknown upload error';
    echo json_encode($response);
    exit;
}

// Get the file details
$file = $_FILES['profile_image'];
$fileName = $file['name'];
$fileTmpPath = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];
$fileType = $file['type'];

// Get file extension
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Define allowed file extensions
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

// Validate file extension
if (!in_array($fileExt, $allowedExtensions)) {
    $response['message'] = 'Only JPG, JPEG, PNG, and GIF files are allowed';
    echo json_encode($response);
    exit;
}

// Validate file size (5MB max)
if ($fileSize > 5 * 1024 * 1024) {
    $response['message'] = 'File size must be less than 5MB';
    echo json_encode($response);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = "../../uploads/profile_images/";
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $response['message'] = 'Failed to create upload directory';
        echo json_encode($response);
        exit;
    }
}

// Generate unique filename with user ID and timestamp
$userId = $_SESSION['user_id'];
$newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
$uploadPath = $uploadDir . $newFileName;

// Move uploaded file to the upload directory
if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
    $response['message'] = 'Failed to move uploaded file';
    echo json_encode($response);
    exit;
}

// Store the relative path in the database
$relativePath = "uploads/profile_images/" . $newFileName;

try {
    // Update user's profile image in the database
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bindParam(1, $relativePath, PDO::PARAM_STR);
    $stmt->bindParam(2, $userId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['profile_image'] = $relativePath;
        
        // Remove old profile image if exists and different from default
        if (isset($_POST['old_image']) && !empty($_POST['old_image'])) {
            $oldImage = $_POST['old_image'];
            if (strpos($oldImage, 'profile_' . $userId) !== false) {
                $oldImagePath = "../../" . $oldImage;
                if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }
        
        // Success response
        $response['success'] = true;
        $response['message'] = 'Profile image uploaded successfully';
        $response['file_path'] = $relativePath;
    } else {
        $response['message'] = 'Failed to update database';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    
    // If there's a database error, remove the uploaded file
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit; 