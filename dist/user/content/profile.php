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
$successMessage = "";
$errorMessage = "";

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, bio, profile_image FROM users WHERE id = ?");
    $stmt->bindParam(1, $userId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $errorMessage = "User not found!";
    } else {
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
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
        try {
            // Update user profile information
            $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->bindParam(1, $firstName, PDO::PARAM_STR);
            $updateStmt->bindParam(2, $lastName, PDO::PARAM_STR);
            $updateStmt->bindParam(3, $bio, PDO::PARAM_STR);
            $updateStmt->bindParam(4, $userId, PDO::PARAM_INT);
            
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
                $errorMessage = "Error updating profile. Please try again.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Database error: " . $e->getMessage();
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
        try {
            // Create uploads directory if it doesn't exist - CORRECTED PATH
            $uploadDir = "../../uploads/profile_images/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename with user ID and timestamp
            $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Store database path with correct prefix for later retrieval
                $relativeImagePath = "/uploads/profile_images/" . $newFileName;
                $imageStmt = $pdo->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?");
                $imageStmt->bindParam(1, $relativeImagePath, PDO::PARAM_STR);
                $imageStmt->bindParam(2, $userId, PDO::PARAM_INT);
                
                if ($imageStmt->execute()) {
                    // Update the session with the new image path
                    $_SESSION['profile_image'] = $relativeImagePath;
                    
                    $successMessage = "Profile image updated successfully!";
                    $userData['profile_image'] = $relativeImagePath;
                } else {
                    $errorMessage = "Error updating profile image in database. Please try again.";
                }
            } else {
                $errorMessage = "Error uploading image file. Please check file permissions.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}

// Handle password change
if (isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    try {
        // Get current hashed password from database
        $passwordStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $passwordStmt->bindParam(1, $userId, PDO::PARAM_INT);
        $passwordStmt->execute();
        $passwordData = $passwordStmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify current password
        if (!$passwordData || !password_verify($currentPassword, $passwordData['password'])) {
            $errorMessage = "Current password is incorrect.";
        } elseif (strlen($newPassword) < 8) {
            $errorMessage = "New password must be at least 8 characters.";
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = "New passwords do not match.";
        } else {
            // Hash and update the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePasswordStmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $updatePasswordStmt->bindParam(1, $hashedPassword, PDO::PARAM_STR);
            $updatePasswordStmt->bindParam(2, $userId, PDO::PARAM_INT);
            
            if ($updatePasswordStmt->execute()) {
                $successMessage = "Password updated successfully!";
            } else {
                $errorMessage = "Error updating password. Please try again.";
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Track user activity
function trackUserActivity($userId, $activity) {
    global $pdo;
    
    try {
        // Update the user's updated_at timestamp
        $updateStmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $updateStmt->bindParam(1, $userId, PDO::PARAM_INT);
        $updateStmt->execute();
    } catch (PDOException $e) {
        // Silently log error but don't disrupt user experience
        error_log("Error tracking user activity: " . $e->getMessage());
    }
}

// Always track profile page visit
trackUserActivity($userId, "profile_visit");

// Simplified Profile Image Path Function
function getImagePath($path) {
    // Remove leading slash if present
    if (strpos($path, '/') === 0) {
        $path = substr($path, 1);
    }
    
    // If path contains "uploads/profile_images", simplify by targeting the known location
    if (strpos($path, 'uploads/profile_images/') !== false) {
        // Extract just the filename (e.g., profile_1_1234567890.jpg)
        $filename = basename($path);
        
        // Construct path relative to the content directory
        $imagePath = "../uploads/profile_images/{$filename}";
        
        // Check if file exists at this location
        if (file_exists($imagePath) && is_readable($imagePath)) {
            return $imagePath;
        }
    }
    
    // Fallback: return the original path or try direct path in uploads folder
    return "../" . $path;
}
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 25px; position: relative; display: inline-block; font-weight: 600;">
        My Profile
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
</div>

<?php if (!empty($successMessage)): ?>
    <div class="alert-custom success" style="background-color: #e8f5e9; color: #2e7d32; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #4caf50; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($successMessage); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMessage)): ?>
    <div class="alert-custom error" style="background-color: #ffebee; color: #c62828; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #ef5350; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px; margin-bottom: 30px;">
    <!-- Left column: Profile Image -->
    <div class="card" style="background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden; height: fit-content;">
        <div style="padding: 30px; position: relative;">
            <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: linear-gradient(135deg, rgba(209, 120, 156, 0.08), transparent); border-radius: 0 0 0 100%; z-index: 0;"></div>
            
            <h2 style="font-size: 1.25rem; margin-bottom: 25px; color: #d1789c; font-weight: 500; border-bottom: 1px solid #f9e8f0; padding-bottom: 12px; position: relative; z-index: 1;">Profile Image</h2>
            
            <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 25px; position: relative; z-index: 1;">
                <?php if (!empty($userData['profile_image'])): ?>
                    <div style="width: 160px; height: 160px; border-radius: 50%; overflow: hidden; box-shadow: 0 10px 20px rgba(209, 120, 156, 0.2); border: 4px solid #fff;">
                        <img src="<?php echo getImagePath($userData['profile_image']); ?>" 
                             alt="Profile Image" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                <?php else: ?>
                    <div style="width: 160px; height: 160px; border-radius: 50%; background: linear-gradient(135deg, #f5d7e3, #e8a1c0); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(209, 120, 156, 0.2); border: 4px solid #fff;">
                        <i class="fas fa-user" style="font-size: 60px; color: white;"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <form action="api/upload_profile_image.php" method="post" enctype="multipart/form-data" style="position: relative; z-index: 1;">
                <div style="margin-bottom: 20px;">
                    <label for="profile_image" style="display: block; margin-bottom: 10px; font-size: 0.95rem; color: #666; font-weight: 500;">Update Profile Picture</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" 
                           style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #f5d7e3; background-color: #fffcfd; font-size: 0.9rem;">
                    <div style="margin-top: 8px; font-size: 0.8rem; color: #888;">Max size: 5MB. JPG, PNG or GIF only.</div>
                </div>
                
                <button type="submit" style="background: linear-gradient(135deg, #d1789c, #e896b8); color: white; border: none; border-radius: 25px; padding: 12px 0; width: 100%; font-weight: 600; cursor: pointer; letter-spacing: 1px; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25);">
                    Update Picture
                </button>
            </form>
        </div>
    </div>
    
    <!-- Right column: Profile Information and Password Change -->
    <div style="display: flex; flex-direction: column; gap: 25px;">
        <!-- Profile Information -->
        <div class="card" style="background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden;">
            <div style="padding: 30px; position: relative;">
                <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: linear-gradient(135deg, rgba(209, 120, 156, 0.08), transparent); border-radius: 0 0 0 100%; z-index: 0;"></div>
                <div style="position: absolute; bottom: 0; left: 0; width: 120px; height: 120px; background: linear-gradient(135deg, rgba(209, 120, 156, 0.05), transparent); border-radius: 0 100% 0 0; z-index: 0;"></div>
                
                <h2 style="font-size: 1.25rem; margin-bottom: 25px; color: #d1789c; font-weight: 500; border-bottom: 1px solid #f9e8f0; padding-bottom: 12px; position: relative; z-index: 1;">Personal Information</h2>
                
                <form action="javascript:void(0);" method="post" style="position: relative; z-index: 1;" id="profile-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label for="first_name" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">First Name</label>
                            <input type="text" id="first_name" name="first_name" required
                                   value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>"
                                   style="width: 100%; border-radius: 12px; border: 1px solid #f5d7e3; padding: 14px; background-color: #fffcfd; font-size: 0.95rem; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                        </div>
                        <div>
                            <label for="last_name" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required
                                   value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>"
                                   style="width: 100%; border-radius: 12px; border: 1px solid #f5d7e3; padding: 14px; background-color: #fffcfd; font-size: 0.95rem; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label for="email" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">Email Address</label>
                        <input type="email" id="email" readonly
                               value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                               style="width: 100%; border-radius: 12px; border: 1px solid #eee; padding: 14px; background-color: #f9f9f9; font-size: 0.95rem; color: #888;">
                        <div style="margin-top: 5px; font-size: 0.8rem; color: #888;">Email cannot be changed directly for security reasons.</div>
                    </div>
                    
                    <div style="margin-bottom: 25px;">
                        <label for="bio" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">About Me</label>
                        <textarea id="bio" name="bio" rows="4"
                                  style="width: 100%; border-radius: 12px; border: 1px solid #f5d7e3; padding: 14px; background-color: #fffcfd; font-size: 0.95rem; resize: none; font-family: inherit; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" style="background: linear-gradient(135deg, #d1789c, #e896b8); color: white; border: none; border-radius: 25px; padding: 12px 32px; font-weight: 600; cursor: pointer; letter-spacing: 1px; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25);">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Password Change -->
        <div class="card" style="background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden;">
            <div style="padding: 30px; position: relative;">
                <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: linear-gradient(135deg, rgba(209, 120, 156, 0.08), transparent); border-radius: 0 0 0 100%; z-index: 0;"></div>
                
                <h2 style="font-size: 1.25rem; margin-bottom: 25px; color: #d1789c; font-weight: 500; border-bottom: 1px solid #f9e8f0; padding-bottom: 12px; position: relative; z-index: 1;">Change Password</h2>
                
                <form action="api/update_password.php" method="post" style="position: relative; z-index: 1;" id="password-form">
                    <div style="margin-bottom: 20px;">
                        <label for="current_password" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required
                               style="width: 100%; border-radius: 12px; border: 1px solid #f5d7e3; padding: 14px; background-color: #fffcfd; font-size: 0.95rem; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div>
                            <label for="new_password" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8"
                                   style="width: 100%; border-radius: 12px; border: 1px solid #f5d7e3; padding: 14px; background-color: #fffcfd; font-size: 0.95rem; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                            <div style="margin-top: 5px; font-size: 0.8rem; color: #888;">Minimum 8 characters</div>
                        </div>
                        <div>
                            <label for="confirm_password" style="display: block; margin-bottom: 8px; font-size: 0.95rem; color: #666; font-weight: 500;">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                                   style="width: 100%; border-radius: 12px; border: 1px solid #f5d7e3; padding: 14px; background-color: #fffcfd; font-size: 0.95rem; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_password" style="background: linear-gradient(135deg, #d1789c, #e896b8); color: white; border: none; border-radius: 25px; padding: 12px 32px; font-weight: 600; cursor: pointer; letter-spacing: 1px; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25);">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Form element focus styles */
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    textarea:focus {
        border-color: #d1789c;
        box-shadow: 0 0 0 3px rgba(209, 120, 156, 0.15);
        outline: none;
    }
    
    /* Button hover state */
    button[type="submit"]:hover {
        background: linear-gradient(135deg, #c76490, #e48db0);
    }
    
    /* Error message styles */
    .form-error {
        color: #c62828;
        font-size: 13px;
        margin-top: 5px;
        display: none;
    }
    
    .form-error.show {
        display: block;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 992px) {
        div[style*="grid-template-columns: 1fr 2fr"] {
            grid-template-columns: 1fr !important;
        }
        
        h1.page-title {
            font-size: 1.6rem !important;
            text-align: center;
            display: block !important;
            margin-left: auto;
            margin-right: auto;
        }
        
        h1.page-title span {
            left: 50% !important;
            transform: translateX(-50%) !important;
        }
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
            gap: 15px !important;
        }
        
        div[style*="padding: 30px"] {
            padding: 20px !important;
        }
        
        h2[style*="font-size: 1.25rem"] {
            font-size: 1.1rem !important;
            text-align: center;
        }
        
        /* Ensure content fills available width */
        form {
            width: 100%;
        }
        
        /* Center the profile image on mobile */
        div[style*="display: flex; flex-direction: column; align-items: center"] {
            margin-bottom: 15px !important;
        }
        
        /* Make save buttons full width on mobile */
        button[type="submit"] {
            width: 100%;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    }
</style>

<script>
// Add client-side validation for the forms
document.addEventListener('DOMContentLoaded', function() {
    // Create error message element
    function createErrorElement(id, message) {
        const errorDiv = document.createElement('div');
        errorDiv.id = id;
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        return errorDiv;
    }
    
    // Show error message for an input
    function showError(input, message) {
        const errorId = input.id + '-error';
        let errorElement = document.getElementById(errorId);
        
        if (!errorElement) {
            errorElement = createErrorElement(errorId, message);
            input.parentNode.appendChild(errorElement);
        } else {
            errorElement.textContent = message;
        }
        
        errorElement.classList.add('show');
        input.style.borderColor = '#ef5350';
        
        return false;
    }
    
    // Clear error for an input
    function clearError(input) {
        const errorId = input.id + '-error';
        const errorElement = document.getElementById(errorId);
        
        if (errorElement) {
            errorElement.classList.remove('show');
        }
        
        input.style.borderColor = '#f5d7e3';
    }
    
    // Validate Profile Form
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        
        firstNameInput.addEventListener('input', function() {
            clearError(firstNameInput);
        });
        
        lastNameInput.addEventListener('input', function() {
            clearError(lastNameInput);
        });
        
        profileForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate first name
            if (firstNameInput.value.trim() === '') {
                isValid = showError(firstNameInput, 'First name is required');
                e.preventDefault();
            } else {
                clearError(firstNameInput);
            }
            
            // Validate last name
            if (lastNameInput.value.trim() === '') {
                isValid = showError(lastNameInput, 'Last name is required');
                e.preventDefault();
            } else {
                clearError(lastNameInput);
            }
            
            return isValid;
        });
    }
    
    // Validate Password Form
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        const currentPasswordInput = document.getElementById('current_password');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Add input listeners to clear errors when typing
        [currentPasswordInput, newPasswordInput, confirmPasswordInput].forEach(input => {
            input.addEventListener('input', function() {
                clearError(input);
            });
        });
        
        passwordForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate current password
            if (currentPasswordInput.value === '') {
                isValid = showError(currentPasswordInput, 'Current password is required');
                e.preventDefault();
            } else {
                clearError(currentPasswordInput);
            }
            
            // Validate new password
            if (newPasswordInput.value === '') {
                isValid = showError(newPasswordInput, 'New password is required');
                e.preventDefault();
            } else if (newPasswordInput.value.length < 8) {
                isValid = showError(newPasswordInput, 'Password must be at least 8 characters');
                e.preventDefault();
            } else {
                clearError(newPasswordInput);
            }
            
            // Validate confirm password
            if (confirmPasswordInput.value === '') {
                isValid = showError(confirmPasswordInput, 'Please confirm your password');
                e.preventDefault();
            } else if (confirmPasswordInput.value !== newPasswordInput.value) {
                isValid = showError(confirmPasswordInput, 'Passwords do not match');
                e.preventDefault();
            } else {
                clearError(confirmPasswordInput);
            }
            
            return isValid;
        });
    }
    
    // Validate image upload
    const imageForm = document.querySelector('form[enctype="multipart/form-data"]');
    const imageInput = document.getElementById('profile_image');
    
    if (imageForm && imageInput) {
        imageInput.addEventListener('change', function() {
            clearError(imageInput);
        });
        
        imageForm.addEventListener('submit', function(e) {
            // Only validate if a file is selected
            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const fileSize = file.size;
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                const allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                
                // Check file type
                if (!allowedExts.includes(fileExt)) {
                    showError(imageInput, 'Only JPG, JPEG, PNG, and GIF files are allowed');
                    e.preventDefault();
                    return false;
                }
                
                // Check file size
                if (fileSize > 5000000) {
                    showError(imageInput, 'File size must be less than 5MB');
                    e.preventDefault();
                    return false;
                }
                
                clearError(imageInput);
                return true;
            } else {
                showError(imageInput, 'Please select an image to upload');
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
