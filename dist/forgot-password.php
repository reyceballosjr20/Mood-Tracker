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
$email = "";
$requestError = "";
$showSuccess = false;
$successMessage = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);
    
    // Basic validation
    if (empty($email)) {
        $requestError = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $requestError = "Please enter a valid email address";
    } else {
        // Generate a token and store in the database
        $result = $auth->requestPasswordReset($email);
        
        if ($result['status'] === 'success') {
            $showSuccess = true;
            $successMessage = "Password reset instructions have been sent to your email address.";
            $email = ""; // Clear the form
        } else {
            $requestError = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Mood Tracker</title>
    <link rel="stylesheet" href="../assets/styles/login-signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">
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
        
        /* Link back to login */
        .back-to-login {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .back-to-login a {
            color: #9370DB;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #8A65D9;
            text-decoration: underline;
        }
        
        /* Improved description */
        .reset-description {
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
            line-height: 1.6;
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
            
            .reset-description {
                font-size: 13px;
                margin-bottom: 20px;
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
                <h1>Reset Your Password</h1>
                
                <div class="reset-description">
                    Enter your email address below and we'll send you instructions to reset your password.
                </div>
                
                <div class="error-container" id="error-container">
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list" id="error-list">
                        <!-- Error messages will be dynamically added here -->
                    </ul>
                </div>
                
                <form class="reset-form" method="post" action="forgot-password.php" id="reset-form" novalidate>
                    <?php if (!empty($requestError)): ?>
                    <div class="error-container" style="display: block;">
                        <strong>Request failed:</strong>
                        <ul class="error-list">
                            <li><?php echo htmlspecialchars($requestError); ?></li>
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
                    
                    <button type="submit" name="request_reset" class="reset-btn" id="reset-btn">Send Reset Instructions</button>
                    
                    <div class="back-to-login">
                        Remember your password? <a href="login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation
        const form = document.getElementById('reset-form');
        const errorContainer = document.getElementById('error-container');
        const errorList = document.getElementById('error-list');
        const resetButton = document.getElementById('reset-btn');
        
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
            resetButton.textContent = 'Sending...';
        });
    </script>
</body>
</html> 