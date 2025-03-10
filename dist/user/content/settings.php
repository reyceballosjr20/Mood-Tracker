<?php
// Initialize session if needed
session_start();

// Check if user is logged in
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Authorization required";
    exit;
}
?>

<div class="header">
    <h1 class="page-title">Settings</h1>
    <div class="search-box">
        <button style="background: #ff8fb1; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
    <!-- Appearance Settings -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Appearance</h2>
            <div class="card-icon">
                <i class="fas fa-paint-brush"></i>
            </div>
        </div>
        <div style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin-bottom: 15px; color: #6e3b5c;">Theme</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px;">
                    <div style="text-align: center; cursor: pointer;">
                        <div style="height: 80px; background: linear-gradient(to bottom, #f5d7e3, #f8dfeb); border-radius: 8px; margin-bottom: 5px; border: 2px solid #ff8fb1;"></div>
                        <span>Pink</span>
                    </div>
                    <div style="text-align: center; cursor: pointer;">
                        <div style="height: 80px; background: linear-gradient(to bottom, #d7e3f5, #dfebf8); border-radius: 8px; margin-bottom: 5px;"></div>
                        <span>Blue</span>
                    </div>
                    <div style="text-align: center; cursor: pointer;">
                        <div style="height: 80px; background: linear-gradient(to bottom, #d7f5e3, #dff8eb); border-radius: 8px; margin-bottom: 5px;"></div>
                        <span>Green</span>
                    </div>
                    <div style="text-align: center; cursor: pointer;">
                        <div style="height: 80px; background: linear-gradient(to bottom, #333, #444); border-radius: 8px; margin-bottom: 5px;"></div>
                        <span>Dark</span>
                    </div>
                </div>
            </div>
            
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            
            <div>
                <h3 style="font-size: 16px; margin-bottom: 15px; color: #6e3b5c;">Dark Mode</h3>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin-bottom: 5px;">Enable dark mode for the application</p>
                        <p style="color: #888; font-size: 14px;">Currently using light mode</p>
                    </div>
                    <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                        <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notification Settings -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Notifications</h2>
            <div class="card-icon">
                <i class="fas fa-bell"></i>
            </div>
        </div>
        <div style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 5px; color: #6e3b5c;">Daily Mood Reminders</h3>
                        <p style="color: #888; font-size: 14px;">Get a daily reminder to log your mood</p>
                    </div>
                    <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                        <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ff8fb1; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 5px; color: #6e3b5c;">Weekly Reports</h3>
                        <p style="color: #888; font-size: 14px;">Receive a weekly summary of your mood patterns</p>
                    </div>
                    <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                        <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ff8fb1; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 5px; color: #6e3b5c;">Tips & Recommendations</h3>
                        <p style="color: #888; font-size: 14px;">Get personalized tips based on your mood</p>
                    </div>
                    <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                        <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Settings -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Privacy & Data</h2>
            <div class="card-icon">
                <i class="fas fa-lock"></i>
            </div>
        </div>
        <div style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="font-size: 16px; margin-bottom: 5px; color: #6e3b5c;">Data Sharing</h3>
                        <p style="color: #888; font-size: 14px;">Allow anonymous data sharing for research</p>
                    </div>
                    <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                        <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button style="background: #f44336; border: none; color: white; padding: 10px 15px; border-radius: 5px; cursor: pointer; display: block; width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-download"></i> Download My Data
                </button>
                <button style="background: white; border: 1px solid #f44336; color: #f44336; padding: 10px 15px; border-radius: 5px; cursor: pointer; display: block; width: 100%;">
                    <i class="fas fa-trash"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div> 