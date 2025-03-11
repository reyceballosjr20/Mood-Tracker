<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}

// Get user data from session
$user = [
    'first_name' => $_SESSION['first_name'] ?? 'User',
    'last_name' => $_SESSION['last_name'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'bio' => $_SESSION['bio'] ?? 'I\'m tracking my mood to improve my mental well-being and understand my emotional patterns.',
    'profile_image' => $_SESSION['profile_image'] ?? '',
];

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? 0;

// Get user bio and profile image from database if not in session
if ($user_id > 0) {
    try {
        // Include database connection
        require_once '../../includes/db-connect.php';
        
        $stmt = $pdo->prepare("SELECT bio, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $user['bio'] = $userData['bio'] ?? $user['bio'];
            $user['profile_image'] = $userData['profile_image'] ?? '';
        }
    } catch (PDOException $e) {
        // Silently fail and use session data
    }
}
?>

<div class="header">
    <h1 class="page-title" style="color: #d1789c; font-size: 1.8rem; margin-bottom: 15px; position: relative; display: inline-block; font-weight: 600;">
        Your Profile
        <span style="position: absolute; bottom: -8px; left: 0; width: 40%; height: 3px; background: linear-gradient(90deg, #d1789c, #f5d7e3); border-radius: 3px;"></span>
    </h1>
    <div class="search-box">
        <button id="saveChangesBtn" style="background: linear-gradient(135deg, #d1789c, #e896b8); border: none; color: white; padding: 10px 20px; border-radius: 25px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25); display: flex; align-items: center; transition: all 0.3s ease;">
            <i class="fas fa-save" style="margin-right: 8px;"></i> 
            <span>Save Changes</span>
            <div id="saveSpinner" style="display: none; margin-left: 8px; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 0.8s linear infinite;"></div>
        </button>
    </div>
</div>

<div id="profileAlert" class="alert" style="display: none; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;"></div>

<div class="profile-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px;">
    <!-- Profile Image Section -->
    <div class="card" style="margin-bottom: 0; text-align: center; background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden;">
        <div style="padding: 20px;">
            <div id="profileImageContainer" style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #d1789c, #e896b8); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 60px; font-weight: 300; overflow: hidden; position: relative;">
                <?php 
                // Get profile image from session or database
                $profile_image = $user['profile_image'] ?? '';
                
                if (!empty($profile_image)): 
                    // Check if it's just a filename or a full path
                    if (strpos($profile_image, '/') !== false) {
                        // It's already a path
                        $image_path = $profile_image;
                    } else {
                        // It's just a filename, construct the path
                        $image_path = 'uploads/profile_images/' . $profile_image;
                    }
                    
                    // Check if file exists
                    if (file_exists('../../' . $image_path)):
                ?>
                    <img src="../../<?php echo htmlspecialchars($image_path); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                <?php endif; ?>
                <?php else: ?>
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            
            <!-- Make sure the file input is properly defined -->
            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="position: absolute; left: -9999px; visibility: hidden;">
            
            <!-- Use a regular link for the change photo button as a fallback -->
            <button id="changePhotoBtn" class="profile-btn primary-btn" style="background: linear-gradient(135deg, #d1789c, #e896b8); border: none; color: white; padding: 10px 15px; border-radius: 25px; cursor: pointer; margin-bottom: 10px; width: 100%; font-weight: 600; box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25);">
                <i class="fas fa-camera"></i> Change Photo
            </button>
            
            <!-- Add a direct file input as a fallback -->
            <label for="directFileInput" style="display: none;">Or select a file directly:</label>
            <input type="file" id="directFileInput" name="direct_profile_image" accept="image/*" style="display: none; margin-bottom: 10px;">
            
            <button id="removePhotoBtn" class="profile-btn secondary-btn" style="background: white; border: 1px solid #f5d7e3; color: #6e3b5c; padding: 10px 15px; border-radius: 25px; cursor: pointer; width: 100%; font-weight: 500;">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    </div>
    
    <!-- Profile Details Section -->
    <div class="card" style="margin-bottom: 0; background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden;">
        <div class="card-header" style="padding: 15px 20px; border-bottom: 1px solid #f9e8f0;">
            <h2 class="card-title" style="font-size: 1.25rem; color: #d1789c; font-weight: 500; margin: 0;">Personal Information</h2>
            <div class="card-icon">
                <i class="fas fa-user-edit" style="color: #d1789c;"></i>
            </div>
        </div>
        <div style="padding: 20px;">
            <form id="profileForm">
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">First Name</label>
                        <input type="text" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; background-color: #fffcfd;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Last Name</label>
                        <input type="text" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; background-color: #fffcfd;">
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; background-color: #fffcfd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Bio</label>
                    <textarea id="bio" name="bio" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; height: 100px; resize: vertical; background-color: #fffcfd; font-family: inherit;"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card" style="background-color: #fff; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border-radius: 16px; transition: none; overflow: hidden;">
    <div class="card-header" style="padding: 15px 20px; border-bottom: 1px solid #f9e8f0;">
        <h2 class="card-title" style="font-size: 1.25rem; color: #d1789c; font-weight: 500; margin: 0;">Security Settings</h2>
        <div class="card-icon">
            <i class="fas fa-shield-alt" style="color: #d1789c;"></i>
        </div>
    </div>
    <div style="padding: 20px;">
        <div>
            <h3 style="font-size: 16px; margin-bottom: 15px; color: #6e3b5c;">Change Password</h3>
            <form id="passwordForm">
                <input type="hidden" name="action" value="change_password">
                <div class="password-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Current Password</label>
                        <input type="password" id="currentPassword" name="current_password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; background-color: #fffcfd;">
                    </div>
                    <div class="password-spacer"></div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">New Password</label>
                        <input type="password" id="newPassword" name="new_password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; background-color: #fffcfd;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #f5d7e3; border-radius: 8px; background-color: #fffcfd;">
                    </div>
                </div>
                <button id="updatePasswordBtn" type="button" style="background: linear-gradient(135deg, #d1789c, #e896b8); border: none; color: white; padding: 10px 20px; border-radius: 25px; cursor: pointer; margin-top: 15px; font-weight: 600; box-shadow: 0 4px 10px rgba(209, 120, 156, 0.25);">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* Card styling */
    .card {
        transition: none !important;
    }
    
    .card:hover {
        transform: none !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.07) !important;
    }
    
    /* Form input focus states */
    input:focus, textarea:focus {
        border-color: #d1789c !important;
        box-shadow: 0 0 0 3px rgba(209, 120, 156, 0.15) !important;
        outline: none !important;
    }
    
    /* Toggle switch styling */
    .toggle-switch span:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    .toggle-switch input:checked + span {
        background-color: #d1789c;
    }
    
    .toggle-switch input:checked + span:before {
        transform: translateX(26px);
    }
    
    /* Button hover states */
    .primary-btn:hover {
        background: linear-gradient(135deg, #c76490, #e48db0) !important;
    }
    
    .secondary-btn:hover {
        background: #f9f9f9 !important;
        border-color: #d1789c !important;
    }
    
    #saveChangesBtn:hover, #updatePasswordBtn:hover {
        background: linear-gradient(135deg, #c76490, #e48db0) !important;
    }
    
    /* Alert styling */
    .alert {
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }
    
    .alert-danger {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 992px) {
        .profile-grid {
            gap: 15px !important;
        }
    }
    
    @media (max-width: 768px) {
        /* Fix the gap between sidebar and content */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .container-fluid,
        .row,
        main,
        .content-area {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        /* Ensure the sidebar connects directly with content */
        .sidebar {
            margin-right: 0;
            padding-right: 0;
        }
        
        /* Adjust profile grid for mobile */
        .profile-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        
        /* Center the page title on mobile */
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
        
        /* Adjust header layout */
        .header {
            flex-direction: column;
            align-items: center;
        }
        
        .search-box {
            margin-top: 15px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        
        #saveChangesBtn {
            width: 100%;
            max-width: 250px;
            padding: 12px 20px;
        }
        
        /* Adjust password grid */
        .password-grid {
            grid-template-columns: 1fr !important;
        }
        
        .password-spacer {
            display: none;
        }
    }
    
    @media (max-width: 576px) {
        /* Further adjustments for very small screens */
        .form-row {
            grid-template-columns: 1fr !important;
        }
        
        .card-header {
            padding: 15px !important;
        }
        
        div[style*="padding: 20px"] {
            padding: 15px !important;
        }
        
        h2.card-title {
            font-size: 1.1rem !important;
        }
    }
    
    /* Add this to your existing styles */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    #saveChangesBtn {
        position: relative;
        overflow: hidden;
    }
    
    #saveChangesBtn:hover {
        background: linear-gradient(135deg, #c76490, #e48db0) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(209, 120, 156, 0.35) !important;
    }
    
    #saveChangesBtn:active {
        transform: translateY(0);
        box-shadow: 0 4px 8px rgba(209, 120, 156, 0.25) !important;
    }
    
    #saveChangesBtn.saving {
        background: linear-gradient(135deg, #b85980, #d485a7) !important;
        pointer-events: none;
    }
    
    /* Pulse animation for the save button when saving */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(209, 120, 156, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(209, 120, 156, 0); }
        100% { box-shadow: 0 0 0 0 rgba(209, 120, 156, 0); }
    }
    
    .pulse {
        animation: pulse 1.5s infinite;
    }
</style>

<form id="fallbackUploadForm" action="../save-profile.php" method="post" enctype="multipart/form-data" style="display: none;">
    <input type="file" name="profile_image" id="fallbackFileInput" accept="image/*">
    <input type="submit" value="Upload">
</form>

<script>
// Add this inline script as a last resort
document.addEventListener('DOMContentLoaded', function() {
    // Last resort fallback
    const changeBtn = document.getElementById('changePhotoBtn');
    if (changeBtn) {
        changeBtn.addEventListener('click', function() {
            const fallbackInput = document.getElementById('fallbackFileInput');
            if (fallbackInput) {
                fallbackInput.click();
            }
        });
    }
    
    const fallbackInput = document.getElementById('fallbackFileInput');
    if (fallbackInput) {
        fallbackInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                document.getElementById('fallbackUploadForm').submit();
            }
        });
    }
});
</script> 