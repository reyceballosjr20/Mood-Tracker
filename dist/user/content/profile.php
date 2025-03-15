<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$successMessage = "";
$errorMessage = "";

// Get current user data
$stmt = $conn->prepare("SELECT first_name, last_name, email, bio, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $errorMessage = "User not found!";
} else {
    $userData = $result->fetch_assoc();
}

// Handle profile update form submission
if (isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $bio = trim($_POST['bio']);
    
    // Validate inputs
    if (empty($firstName) || empty($lastName)) {
        $errorMessage = "First name and last name are required.";
    } else {
        // Update user profile information
        $updateStmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, bio = ? WHERE id = ?");
        $updateStmt->bind_param("sssi", $firstName, $lastName, $bio, $userId);
        
        if ($updateStmt->execute()) {
            // Update session data
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            
            $successMessage = "Profile updated successfully!";
            
            // Refresh user data
            $userData['first_name'] = $firstName;
            $userData['last_name'] = $lastName;
            $userData['bio'] = $bio;
        } else {
            $errorMessage = "Error updating profile: " . $conn->error;
        }
    }
}

// Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $fileName = $_FILES['profile_image']['name'];
    $fileSize = $_FILES['profile_image']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file extension and size
    if (!in_array($fileExt, $allowed)) {
        $errorMessage = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } elseif ($fileSize > 5000000) { // 5MB limit
        $errorMessage = "File size must be less than 5MB.";
    } else {
        // Create uploads directory if it doesn't exist
        $uploadDir = "../uploads/profile_images/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $newFileName = uniqid('profile_') . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
            // Update profile image in database
            $relativeImagePath = "uploads/profile_images/" . $newFileName;
            $imageStmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $imageStmt->bind_param("si", $relativeImagePath, $userId);
            
            if ($imageStmt->execute()) {
                $successMessage = "Profile image updated successfully!";
                $userData['profile_image'] = $relativeImagePath;
            } else {
                $errorMessage = "Error updating profile image: " . $conn->error;
            }
        } else {
            $errorMessage = "Error uploading image.";
        }
    }
}

// Handle password change
if (isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Get current hashed password from database
    $passwordStmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $passwordStmt->bind_param("i", $userId);
    $passwordStmt->execute();
    $passwordResult = $passwordStmt->get_result();
    $passwordData = $passwordResult->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $passwordData['password'])) {
        $errorMessage = "Current password is incorrect.";
    } elseif (strlen($newPassword) < 8) {
        $errorMessage = "New password must be at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New passwords do not match.";
    } else {
        // Hash and update the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePasswordStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updatePasswordStmt->bind_param("si", $hashedPassword, $userId);
        
        if ($updatePasswordStmt->execute()) {
            $successMessage = "Password updated successfully!";
        } else {
            $errorMessage = "Error updating password: " . $conn->error;
        }
    }
}
?>

<div class="container py-4">
    <h1 class="mb-4">My Profile</h1>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <!-- Left column: Profile Image -->
        <div class="col-lg-3 col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Profile Image</h5>
                    <div class="profile-image-container mb-3">
                        <?php if (!empty($userData['profile_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($userData['profile_image']); ?>" 
                                 class="img-fluid rounded-circle profile-image" 
                                 alt="Profile Image" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="default-profile-image rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; margin: 0 auto;">
                                <i class="bi bi-person-fill text-white" style="font-size: 72px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Change Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <div class="form-text">Max size: 5MB. JPG, PNG or GIF only.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Upload Image</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Right column: Profile Information and Password Change -->
        <div class="col-lg-9 col-md-8">
            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" readonly>
                            <div class="form-text">Email cannot be changed directly for security reasons.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
            
            <!-- Password Change -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       minlength="8" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional responsive styling */
@media (max-width: 767.98px) {
    .profile-image-container {
        margin-bottom: 1.5rem;
    }
    
    .profile-image, .default-profile-image {
        width: 120px !important;
        height: 120px !important;
    }
}
</style>
