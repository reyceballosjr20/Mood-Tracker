<?php
// Start session
session_start();

// Load models
require_once '../models/Auth.php';

// Initialize auth
$auth = new Auth();

// Check if user is already logged in
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: user/dashboard.php");
    exit();
}

// Define variables
$email = $password = "";
$loginError = "";
$showSuccess = false;

// Check success messages from URL parameters
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $successMessage = "You have been successfully logged out.";
    $showSuccess = true;
}

if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $successMessage = "Account created successfully. Please log in.";
    $showSuccess = true;
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Basic validation
    if (empty($email)) {
        $loginError = "Email is required";
    } elseif (empty($password)) {
        $loginError = "Password is required";
    } else {
        // Try to login
        $result = $auth->login($email, $password, $remember);
        
        if ($result['status'] === 'success') {
            // Redirect to dashboard
            header("Location: user/dashboard.php");
            exit();
        } else {
            $loginError = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mood Tracker</title>
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
            height: 48px; /* Standard touch target height */
            min-height: 44px; /* Minimum recommended touch target height */
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
        
        /* Enhanced main login button */
        .login-btn {
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
            height: 48px; /* Standard touch target height */
            min-height: 44px; /* Minimum recommended touch target height */
        }
        
        .login-btn::before {
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
        
        .login-btn:hover {
            background: linear-gradient(135deg, #5d7df9, #3959f5);
            box-shadow: 0 6px 15px rgba(74, 108, 247, 0.35);
            transform: translateY(-2px);
        }
        
        .login-btn:hover::before {
            transform: scaleX(1);
        }
        
        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(74, 108, 247, 0.2);
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
        
        /* Form validation styles */
        input:invalid:not(:focus):not(:placeholder-shown) {
            border-color: #d32f2f;
        }
        
        input:valid:not(:placeholder-shown) {
            border-color: #4caf50;
        }
        
        .validation-message {
            font-size: 12px;
            margin-top: 5px;
            color: #d32f2f;
            display: none;
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
            border-radius: 6px;
            transition: all 0.3s ease;
            height: 48px; /* Standard touch target height */
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.2);
            outline: none;
        }
        
        /* Form options (remember me and forgot password) */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        
        .remember-me input {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .remember-me span {
            font-size: 14px;
            color: #666;
        }
        
        .forgot-password {
            color: #4a6cf7;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 5px; /* Increase touch area */
            margin: -5px;
        }
        
        .forgot-password:hover {
            color: #3651d3;
            text-decoration: underline;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .social-login-divider::before, 
            .social-login-divider::after {
                width: 40%;
            }
            
            .google-btn, .login-btn {
                padding: 12px 10px;
                font-size: 15px;
            }
            
            .form-card {
                padding: 25px 20px;
                width: 90%;
                max-width: 450px;
                margin: 0 auto;
            }
            
            .split-container {
                flex-direction: column;
            }
            
            .left-side {
                display: none;
            }
            
            .right-side {
                width: 100%;
                padding: 20px 0;
            }
            
            /* Improve form options on mobile */
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .remember-me, .forgot-password {
                font-size: 15px; /* Slightly larger for better touch */
            }
            
            .form-group input, .login-btn, .google-btn {
                height: 50px; /* Slightly taller for mobile */
            }
        }
        
        @media (max-width: 480px) {
            .google-btn, .login-btn {
                padding: 12px 10px;
                font-size: 15px;
                height: 50px;
            }
            
            .form-group input {
                font-size: 16px; /* Prevent zoom on mobile */
                height: 50px;
            }
            
            .social-login-divider {
                margin: 20px 0;
            }
            
            .social-login-divider::before, 
            .social-login-divider::after {
                width: 38%;
            }
            
            h1 {
                font-size: 24px;
                margin-bottom: 15px;
            }
            
            .form-card {
                padding: 20px 15px;
                width: 95%;
            }
            
            /* Increased spacing for thumbs */
            .form-group {
                margin-bottom: 20px;
            }
            
            /* Allow better tap targets */
            .remember-me input {
                width: 22px;
                height: 22px;
                margin-right: 10px;
            }
        }
        
        /* Success message */
        .success-container {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #4caf50;
        }
        
        /* Accessibility improvements */
        .visually-hidden {
            border: 0;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }
        
        button:focus, input:focus, a:focus {
            outline: 2px solid #4a6cf7;
            outline-offset: 2px;
        }
        
        /* Loading state */
        .login-btn.loading {
            background: linear-gradient(135deg, #8ba3fa, #6b8af8);
            cursor: not-allowed;
            position: relative;
        }
        
        .login-btn.loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: rotate 1s infinite linear;
        }
        
        @keyframes rotate {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }
        
        /* General improvements */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .already-registered {
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .already-registered a {
            color: #4a6cf7;
            text-decoration: none;
            font-weight: 500;
        }
        
        .already-registered a:hover {
            text-decoration: underline;
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
                <h1>Welcome Back</h1>
                
                <div class="already-registered">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
                
                <div class="success-container" id="success-container" style="display: none;">
                    You have been successfully logged out.
                </div>
                
                <div class="error-container" id="error-container">
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list" id="error-list">
                        <!-- Error messages will be dynamically added here -->
                    </ul>
                </div>
                
                <form class="login-form" method="post" action="login.php" id="login-form" novalidate>
                    <?php if (!empty($loginError)): ?>
                    <div class="error-container" style="display: block;">
                        <strong>Login failed:</strong>
                        <ul class="error-list">
                            <li><?php echo htmlspecialchars($loginError); ?></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($showSuccess): ?>
                    <div class="success-container" id="success-container" style="display: block;">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="email">EMAIL</label>
                        <input type="email" id="email" name="email" placeholder="hello@gmail.com" required
                               aria-describedby="email-validation" autocomplete="email"
                               value="<?php echo htmlspecialchars($email); ?>">
                        <div class="validation-message" id="email-validation">Please enter a valid email address.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••" required
                                   aria-describedby="password-validation" autocomplete="current-password">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePasswordVisibility('password')">
                                <span role="img" aria-hidden="true">👁️</span>
                            </button>
                        </div>
                        <div class="validation-message" id="password-validation">Please enter your password.</div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" name="login" class="login-btn" id="login-btn">Login</button>
                    
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
        // Check URL parameters for success messages
        const urlParams = new URLSearchParams(window.location.search);
        
        // Show logout success message
        if (urlParams.get('logout') === 'success') {
            document.getElementById('success-container').style.display = 'block';
        }
        
        // Show signup success message
        if (urlParams.get('signup') === 'success') {
            const successContainer = document.getElementById('success-container');
            successContainer.textContent = 'Account created successfully. Please log in.';
            successContainer.style.display = 'block';
        }
        
        // Password visibility toggle
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = passwordInput.nextElementSibling;
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleButton.querySelector('span').textContent = '👁️‍🗨️';
                toggleButton.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = "password";
                toggleButton.querySelector('span').textContent = '👁️';
                toggleButton.setAttribute('aria-label', 'Show password');
            }
        }
        
        // Form validation
        const form = document.getElementById('login-form');
        const errorContainer = document.getElementById('error-container');
        const errorList = document.getElementById('error-list');
        const loginButton = document.getElementById('login-btn');
        
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
            
            if (input.id === 'email') {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(input.value)) {
                    input.setAttribute('aria-invalid', 'true');
                    validationMessage.style.display = 'block';
                    validationMessage.textContent = 'Please enter a valid email address.';
                    return false;
                }
            } else if (input.id === 'password') {
                if (input.value.length === 0) {
                    input.setAttribute('aria-invalid', 'true');
                    validationMessage.style.display = 'block';
                    validationMessage.textContent = 'Please enter your password.';
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
                e.preventDefault(); // Prevent form submission only if validation fails
                
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
            
            // Show loading state but don't prevent form submission
            loginButton.classList.add('loading');
            loginButton.textContent = 'Logging in...';
            
            // Allow the form to submit normally - this will process with PHP
            // Do NOT prevent default or use setTimeout to redirect
        });
        
        // Add to the beginning of your script
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize error container and error list elements
            const errorContainer = document.getElementById('error-container');
            const errorList = document.getElementById('error-list');
            
            // If they don't exist, create them
            if (!errorContainer) {
                const newErrorContainer = document.createElement('div');
                newErrorContainer.id = 'error-container';
                newErrorContainer.className = 'error-container';
                newErrorContainer.style.display = 'none';
                
                const errorHeading = document.createElement('strong');
                errorHeading.textContent = 'Please fix the following errors:';
                
                const newErrorList = document.createElement('ul');
                newErrorList.id = 'error-list';
                newErrorList.className = 'error-list';
                
                newErrorContainer.appendChild(errorHeading);
                newErrorContainer.appendChild(newErrorList);
                
                // Insert after form heading
                const form = document.getElementById('login-form');
                form.insertBefore(newErrorContainer, form.firstChild);
            }
        });
    </script>
</body>
</html> 