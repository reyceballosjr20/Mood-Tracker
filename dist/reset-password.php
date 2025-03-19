<?php
// Start session
session_start();

// Load models and config
require_once '../models/Auth.php';

// Initialize auth
$auth = new Auth();

// Check if user is already logged in
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: user/dashboard.php");
    exit();
}

// Define variables
$resetError = "";
$showSuccess = false;
$validToken = false;
$token = "";
$email = "";

// Check if token and email are provided in the URL
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    // Verify the token before showing the reset form
    $verifyResult = $auth->verifyPasswordResetToken($email, $token);
    
    if ($verifyResult['status'] === 'success') {
        $validToken = true;
    } else {
        $resetError = $verifyResult['message'];
    }
} else {
    $resetError = "Invalid password reset link. Please request a new one.";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $token = $_POST['token'];
    $email = $_POST['email'];
    
    // Basic validation
    if (empty($password)) {
        $resetError = "Password is required";
    } elseif (strlen($password) < 8) {
        $resetError = "Password must be at least 8 characters long";
    } elseif ($password !== $confirmPassword) {
        $resetError = "Passwords do not match";
    } else {
        // Reset the password
        $result = $auth->resetPassword($email, $token, $password);
        
        if ($result['status'] === 'success') {
            $showSuccess = true;
        } else {
            $resetError = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Mood Tracker</title>
    <link rel="stylesheet" href="../assets/styles/login-signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Error messages */
        .error-container {
            background-color: #ffebee;
            color: #d32f2f;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            border-left: 4px solid #d32f2f;
            box-shadow: 0 2px 8px rgba(211, 47, 47, 0.1);
        }
        
        .error-container.show {
            display: block;
        }
        
        .error-list {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        /* Success message */
        .success-container {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #4caf50;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1);
        }
        
        /* Form elements sizing */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            letter-spacing: 0.5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: all 0.3s ease;
            height: 48px; /* Standard touch target height */
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            border-color: #9370DB;
            box-shadow: 0 0 0 2px rgba(147, 112, 219, 0.2);
            outline: none;
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 5px;
            background-color: #eee;
            border-radius: 3px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-strength-text {
            font-size: 12px;
            margin-top: 5px;
            display: block;
            text-align: right;
            color: #666;
        }
        
        /* Password visibility toggle */
        .password-wrapper {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9370DB;
            font-size: 14px;
            background: transparent;
            border: none;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            min-width: 44px; /* Minimum touch target size */
            min-height: 44px;
            border-radius: 50%;
            margin-right: -8px;
        }
        
        /* Enhanced main button */
        .reset-btn {
            background: linear-gradient(135deg, #9370DB, #7B68EE);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: capitalize;
            box-shadow: 0 4px 10px rgba(147, 112, 219, 0.25);
            position: relative;
            overflow: hidden;
            height: 48px; /* Standard touch target height */
            min-height: 44px; /* Minimum recommended touch target height */
        }
        
        .reset-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .reset-btn:hover {
            background: linear-gradient(135deg, #8A65D9, #6A5AEC);
            box-shadow: 0 6px 15px rgba(123, 104, 238, 0.35);
            transform: translateY(-2px);
        }
        
        .reset-btn:hover::before {
            transform: scaleX(1);
        }
        
        .reset-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(147, 112, 219, 0.2);
        }
        
        /* Password requirements */
        .password-requirements {
            background-color: #f5f5f5;
            padding: 12px 15px;
            border-radius: 10px;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .requirement {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }
        
        .requirement i {
            margin-right: 8px;
            font-size: 14px;
        }
        
        .requirement.met i {
            color: #4caf50;
        }
        
        .requirement.unmet i {
            color: #9e9e9e;
        }
        
        /* Success page content */
        .success-page {
            text-align: center;
            padding: 20px;
        }
        
        .success-icon {
            color: #4caf50;
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .success-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .success-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .login-link {
            display: inline-block;
            background: linear-gradient(135deg, #9370DB, #7B68EE);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(147, 112, 219, 0.25);
        }
        
        .login-link:hover {
            background: linear-gradient(135deg, #8A65D9, #6A5AEC);
            box-shadow: 0 6px 15px rgba(123, 104, 238, 0.35);
            transform: translateY(-2px);
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .form-card {
                padding: 25px 20px;
                width: 90%;
                max-width: 450px;
                margin: 0 auto;
                border-radius: 18px;
            }
            
            .reset-btn {
                height: 50px; /* Slightly taller for mobile */
            }
            
            .form-group input {
                height: 50px;
            }
        }
        
        @media (max-width: 480px) {
            .form-card {
                padding: 20px 15px;
                width: 95%;
            }
            
            h1 {
                font-size: 24px;
                margin-bottom: 15px;
            }
            
            .requirement {
                font-size: 11px;
            }
            
            .success-title {
                font-size: 20px;
            }
            
            .success-icon {
                font-size: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes"></div>
    
    <div class="split-container">
        <div class="left-side">
            <div class="decorative-blob"></div>
        </div>
        
        <div class="right-side">
            <div class="form-card">
                <?php if ($showSuccess): ?>
                <!-- Success message after password reset -->
                <div class="success-page">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="success-title">Password Reset Successful</div>
                    <div class="success-message">
                        Your password has been reset successfully. You can now login with your new password.
                    </div>
                    <a href="login.php" class="login-link">Go to Login</a>
                </div>
                
                <?php elseif ($validToken): ?>
                <!-- Password reset form -->
                <h1>Create New Password</h1>
                
                <div class="error-container" id="error-container">
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list" id="error-list">
                        <!-- Error messages will be dynamically added here -->
                    </ul>
                </div>
                
                <form class="reset-form" method="post" action="reset-password.php" id="reset-form" novalidate>
                    <?php if (!empty($resetError)): ?>
                    <div class="error-container" style="display: block;">
                        <strong>Reset failed:</strong>
                        <ul class="error-list">
                            <li><?php echo htmlspecialchars($resetError); ?></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    
                    <div class="form-group">
                        <label for="password">NEW PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••••" required
                                   aria-describedby="password-validation" autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePasswordVisibility('password')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="validation-message" id="password-validation">Password must be at least 8 characters.</div>
                        
                        <div class="password-strength">
                            <div class="password-strength-meter" id="password-strength-meter"></div>
                        </div>
                        <span class="password-strength-text" id="password-strength-text">Password strength</span>
                        
                        <div class="password-requirements">
                            <div class="requirement unmet" id="req-length">
                                <i class="fas fa-circle"></i> At least 8 characters
                            </div>
                            <div class="requirement unmet" id="req-uppercase">
                                <i class="fas fa-circle"></i> At least one uppercase letter
                            </div>
                            <div class="requirement unmet" id="req-lowercase">
                                <i class="fas fa-circle"></i> At least one lowercase letter
                            </div>
                            <div class="requirement unmet" id="req-number">
                                <i class="fas fa-circle"></i> At least one number
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">CONFIRM PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required
                                   aria-describedby="confirm-password-validation" autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePasswordVisibility('confirm_password')">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="validation-message" id="confirm-password-validation">Passwords must match.</div>
                    </div>
                    
                    <button type="submit" name="reset_password" class="reset-btn" id="reset-btn">Reset Password</button>
                </form>
                
                <?php else: ?>
                <!-- Invalid or expired token message -->
                <div class="error-container" style="display: block; margin-top: 20px;">
                    <strong>Error:</strong>
                    <p><?php echo htmlspecialchars($resetError); ?></p>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="forgot-password.php" class="login-link">Request New Link</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Password visibility toggle
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = passwordInput.nextElementSibling;
            const icon = toggleButton.querySelector('i');
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                toggleButton.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                toggleButton.setAttribute('aria-label', 'Show password');
            }
        }
        
        // Form validation
        if (document.getElementById('reset-form')) {
            const form = document.getElementById('reset-form');
            const errorContainer = document.getElementById('error-container');
            const errorList = document.getElementById('error-list');
            const resetButton = document.getElementById('reset-btn');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const strengthMeter = document.getElementById('password-strength-meter');
            const strengthText = document.getElementById('password-strength-text');
            
            // Password requirements elements
            const reqLength = document.getElementById('req-length');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqNumber = document.getElementById('req-number');
            
            // Password strength calculation
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Update requirements display
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /\d/.test(password);
                
                // Update requirement indicators
                updateRequirement(reqLength, hasLength);
                updateRequirement(reqUppercase, hasUppercase);
                updateRequirement(reqLowercase, hasLowercase);
                updateRequirement(reqNumber, hasNumber);
                
                // Calculate strength percentage
                let strength = 0;
                if (password.length > 0) {
                    // Start with 25% for any input
                    strength = 25;
                    
                    // Add 25% for each requirement met
                    if (hasLength) strength += 15;
                    if (hasUppercase) strength += 20;
                    if (hasLowercase) strength += 20;
                    if (hasNumber) strength += 20;
                    
                    // Cap at 100%
                    strength = Math.min(strength, 100);
                }
                
                // Update the strength meter
                strengthMeter.style.width = strength + '%';
                
                // Update color based on strength
                if (strength <= 25) {
                    strengthMeter.style.backgroundColor = '#f44336'; // Red
                    strengthText.textContent = 'Very Weak';
                } else if (strength <= 50) {
                    strengthMeter.style.backgroundColor = '#ff9800'; // Orange
                    strengthText.textContent = 'Weak';
                } else if (strength <= 75) {
                    strengthMeter.style.backgroundColor = '#ffc107'; // Yellow
                    strengthText.textContent = 'Medium';
                } else {
                    strengthMeter.style.backgroundColor = '#4caf50'; // Green
                    strengthText.textContent = 'Strong';
                }
                
                // Check if passwords match when typing in password field
                if (confirmPasswordInput.value.length > 0) {
                    validateInput(confirmPasswordInput);
                }
            });
            
            // Check if passwords match when typing in confirm password field
            confirmPasswordInput.addEventListener('input', function() {
                validateInput(this);
            });
            
            // Function to update requirement display
            function updateRequirement(reqElement, isMet) {
                if (isMet) {
                    reqElement.classList.remove('unmet');
                    reqElement.classList.add('met');
                    reqElement.querySelector('i').classList.remove('fa-circle');
                    reqElement.querySelector('i').classList.add('fa-check-circle');
                } else {
                    reqElement.classList.remove('met');
                    reqElement.classList.add('unmet');
                    reqElement.querySelector('i').classList.remove('fa-check-circle');
                    reqElement.querySelector('i').classList.add('fa-circle');
                }
            }
            
            // Show validation messages on blur
            document.querySelectorAll('input').forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() !== '') {
                        validateInput(this);
                    }
                });
                
                input.addEventListener('input', function() {
                    if (this.hasAttribute('aria-invalid') && this.getAttribute('aria-invalid') === 'true') {
                        validateInput(this);
                    }
                });
            });
            
            function validateInput(input) {
                const validationMessage = document.getElementById(input.getAttribute('aria-describedby'));
                
                if (input.id === 'password') {
                    if (input.value.length < 8) {
                        input.setAttribute('aria-invalid', 'true');
                        validationMessage.style.display = 'block';
                        validationMessage.textContent = 'Password must be at least 8 characters.';
                        return false;
                    }
                } else if (input.id === 'confirm_password') {
                    if (input.value !== passwordInput.value) {
                        input.setAttribute('aria-invalid', 'true');
                        validationMessage.style.display = 'block';
                        validationMessage.textContent = 'Passwords do not match.';
                        return false;
                    }
                }
                
                input.setAttribute('aria-invalid', 'false');
                validationMessage.style.display = 'none';
                return true;
            }
            
            // Form submission
            form.addEventListener('submit', function(e) {
                // Validate all fields
                let errors = [];
                let isValid = true;
                
                document.querySelectorAll('input[required]').forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                        const validationMessage = document.getElementById(input.getAttribute('aria-describedby'));
                        if (validationMessage) {
                            errors.push(validationMessage.textContent);
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault(); // Prevent form submission
                    
                    // Display errors
                    errorList.innerHTML = '';
                    errors.forEach(error => {
                        const li = document.createElement('li');
                        li.textContent = error;
                        errorList.appendChild(li);
                    });
                    errorContainer.classList.add('show');
                    errorContainer.style.display = 'block';
                    
                    // Scroll to error container
                    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }
                
                // Show loading state
                resetButton.classList.add('loading');
                resetButton.textContent = 'Resetting...';
            });
        }
    </script>
</body>
</html> 