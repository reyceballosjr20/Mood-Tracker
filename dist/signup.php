<?php
// Initialize session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: user/dashboard.php");
    exit;
}

// Include auth model
require_once '../models/Auth.php';

// Initialize variables
$errors = [];
$success = false;
$first_name = $last_name = $email = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Create auth instance
    $auth = new Auth();
    
    // Get form data
    $userData = [
        'first_name' => $_POST['name'] ? explode(' ', $_POST['name'], 2)[0] : '',
        'last_name' => $_POST['name'] && strpos($_POST['name'], ' ') !== false ? explode(' ', $_POST['name'], 2)[1] : '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'auth_provider' => 'local'
    ];
    
    // Validate confirm password
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = "Passwords do not match";
    } else {
        // Register user
        $result = $auth->register($userData);
        
        if ($result['success']) {
            // Redirect to dashboard
            header("Location: user/index.php");
            exit;
        } else {
            $errors = $result['errors'];
            
            // Keep entered data for form
            $first_name = $userData['first_name'];
            $last_name = $userData['last_name'];
            $email = $userData['email'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Mood Tracker</title>
    <link rel="stylesheet" href="../assets/styles/login-signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">
    <style>
        /* Google button styles */
        .social-login-divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .social-login-divider::before, 
        .social-login-divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .social-login-divider::before {
            left: 0;
        }
        
        .social-login-divider::after {
            right: 0;
        }
        
        .social-login-divider span {
            display: inline-block;
            padding: 0 12px;
            background-color: white;
            position: relative;
            color: #888;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Enhanced Google button */
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #dadce0;
            border-radius: 6px;
            padding: 12px 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            font-size: 15px;
            color: #3c4043;
            margin-top: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        
        .google-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.02);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .google-btn:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
            border-color: #c6c6c6;
        }
        
        .google-btn:hover::before {
            transform: scaleX(1);
        }
        
        .google-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            background-color: #f1f3f4;
        }
        
        .google-btn img {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        /* Enhanced main signup button */
        .signup-btn {
            background: linear-gradient(135deg, #6e8efb, #4a6cf7);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 13px 20px;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: capitalize;
            box-shadow: 0 4px 10px rgba(74, 108, 247, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        .signup-btn::before {
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
        
        .signup-btn:hover {
            background: linear-gradient(135deg, #5d7df9, #3959f5);
            box-shadow: 0 6px 15px rgba(74, 108, 247, 0.35);
            transform: translateY(-2px);
        }
        
        .signup-btn:hover::before {
            transform: scaleX(1);
        }
        
        .signup-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(74, 108, 247, 0.2);
        }
        
        /* Password strength meter */
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            height: 4px;
            border-radius: 2px;
            background: #e0e0e0;
            margin-bottom: 10px;
        }
        
        .password-strength-meter {
            height: 100%;
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #777;
            display: none;
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
            color: #777;
            font-size: 14px;
        }
        
        /* Error messages */
        .error-container {
            background-color: #ffebee;
            color: #d32f2f;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .error-container.show {
            display: block;
        }
        
        .error-list {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .social-login-divider::before, 
            .social-login-divider::after {
                width: 40%;
            }
            
            .google-btn, .signup-btn {
                padding: 12px 10px;
                font-size: 14px;
            }
            
            .form-card {
                padding: 25px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .google-btn, .signup-btn {
                padding: 11px 10px;
            }
            
            .social-login-divider {
                margin: 20px 0;
            }
            
            .social-login-divider::before, 
            .social-login-divider::after {
                width: 38%;
            }
            
            .form-group {
                margin-bottom: 15px;
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
                <h1>Create Account</h1>
                
                <div class="already-registered">
                    Already have an account? <a href="login.php">Login</a>
                </div>
                
                <?php if (!empty($errors)): ?>
                <div class="error-container show">
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="name">FULL NAME</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required value="<?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">EMAIL</label>
                        <input type="email" id="email" name="email" placeholder="hello@gmail.com" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••" required>
                            <span class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</span>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-meter"></div>
                        </div>
                        <div class="strength-text">Password strength</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">CONFIRM PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••" required>
                            <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">👁️</span>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" name="signup" class="signup-btn">Sign Up</button>
                    
                    <div class="social-login-divider">
                        <span>OR</span>
                    </div>
                    
                    <button type="button" class="google-btn">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google logo">
                        Continue with Google
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Password visibility toggle
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
        
        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.querySelector('.password-strength-meter');
            const strengthText = document.querySelector('.strength-text');
            
            if (password.length === 0) {
                strengthMeter.style.width = '0%';
                strengthMeter.style.backgroundColor = '#e0e0e0';
                strengthText.style.display = 'none';
                return;
            }
            
            strengthText.style.display = 'block';
            
            // Simple password strength calculation
            let strength = 0;
            
            // Length check
            if (password.length >= 8) {
                strength += 25;
            }
            
            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 25;
            }
            
            // Number check
            if (/[0-9]/.test(password)) {
                strength += 25;
            }
            
            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 25;
            }
            
            // Update the UI
            strengthMeter.style.width = strength + '%';
            
            if (strength < 25) {
                strengthMeter.style.backgroundColor = '#ff4d4d';
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#ff4d4d';
            } else if (strength < 50) {
                strengthMeter.style.backgroundColor = '#ffa64d';
                strengthText.textContent = 'Fair password';
                strengthText.style.color = '#ffa64d';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#ffff4d';
                strengthText.textContent = 'Good password';
                strengthText.style.color = '#aaaa00';
            } else {
                strengthMeter.style.backgroundColor = '#4dff4d';
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#00aa00';
            }
        });
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password !== confirmPassword) {
                    this.setCustomValidity("Passwords don't match");
                } else {
                    this.setCustomValidity('');
                }
            }
        });
    </script>
</body>
</html> 