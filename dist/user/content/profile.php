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
];
?>

<div class="header">
    <h1 class="page-title">Your Profile</h1>
    <div class="search-box">
        <button style="background: #ff8fb1; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px;">
    <!-- Profile Image Section -->
    <div class="card" style="margin-bottom: 0; text-align: center;">
        <div style="padding: 20px;">
            <div style="width: 150px; height: 150px; border-radius: 50%; background-color: #ff8fb1; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 60px; font-weight: 300;">
                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
            </div>
            <button style="background: #6e3b5c; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-bottom: 10px; width: 100%;">
                <i class="fas fa-camera"></i> Change Photo
            </button>
            <button style="background: white; border: 1px solid #d3d3d3; color: #333; padding: 8px 15px; border-radius: 5px; cursor: pointer; width: 100%;">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    </div>
    
    <!-- Profile Details Section -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2 class="card-title">Personal Information</h2>
            <div class="card-icon">
                <i class="fas fa-user-edit"></i>
            </div>
        </div>
        <div style="padding: 20px;">
            <form>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">First Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Last Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Bio</label>
                    <textarea style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; height: 100px; resize: vertical;">I'm tracking my mood to improve my mental well-being and understand my emotional patterns.</textarea>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Security Settings</h2>
        <div class="card-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>
    <div style="padding: 20px;">
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 16px; margin-bottom: 15px; color: #6e3b5c;">Change Password</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Current Password</label>
                    <input type="password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div></div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">New Password</label>
                    <input type="password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #6e3b5c;">Confirm New Password</label>
                    <input type="password" placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            <button style="background: #ff8fb1; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-top: 15px;">
                Update Password
            </button>
        </div>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        
        <div>
            <h3 style="font-size: 16px; margin-bottom: 15px; color: #6e3b5c;">Two-Factor Authentication</h3>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin-bottom: 5px;">Add an extra layer of security to your account</p>
                    <p style="color: #888; font-size: 14px;">Currently disabled</p>
                </div>
                <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                    <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>
                </label>
            </div>
        </div>
    </div>
</div> 