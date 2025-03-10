<?php
// Initialize session or any PHP-specific logic here
session_start();
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
            margin: 20px 0;
            position: relative;
        }
        
        .social-login-divider::before, 
        .social-login-divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: #ddd;
        }
        
        .social-login-divider::before {
            left: 0;
        }
        
        .social-login-divider::after {
            right: 0;
        }
        
        .social-login-divider span {
            display: inline-block;
            padding: 0 10px;
            background-color: white;
            position: relative;
            color: #777;
            font-size: 14px;
        }
        
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 15px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            color: #757575;
            margin-top: 10px;
        }
        
        .google-btn:hover {
            background-color: #f8f8f8;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .google-btn img {
            margin-right: 10px;
            width: 20px;
            height: 20px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .social-login-divider::before, 
            .social-login-divider::after {
                width: 40%;
            }
            
            .google-btn {
                padding: 12px 10px;
                font-size: 14px;
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
                <h1>Welcome Back</h1>
                
                <div class="already-registered">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
                
                <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="email">EMAIL</label>
                        <input type="email" id="email" name="email" placeholder="hello@gmail.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">PASSWORD</label>
                        <input type="password" id="password" name="password" placeholder="••••••" required>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" name="login" class="signup-btn">login</button>
                    
                    <div class="social-login-divider">
                        <span>OR</span>
                    </div>
                    
                    <button type="button" class="google-btn">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google logo">
                        Continue with Google
                    </button>
                </form>
                
                <?php
                // Login processing logic would go here
                if(isset($_POST['login'])) {
                    // Process login form submission
                    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                    $password = $_POST['password'];
                    
                    // Add authentication logic here
                    // ...
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html> 