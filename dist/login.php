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
        }
        
        /* Success message */
        .success-container {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
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
                
                <div class="success-container" style="display: none;">
                    You have been successfully logged out.
                </div>
                
                <div class="error-container">
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list">
                        <!-- Error messages will be dynamically added here -->
                    </ul>
                </div>
                
                <form class="login-form" method="post" action="login.php">
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
                    
                    <button type="submit" name="login" class="signup-btn">Login</button>
                    
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
        // Check for 'logout=success' in URL to show success message
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('logout') === 'success') {
            document.querySelector('.success-container').style.display = 'block';
        }
        
        // Optional: Add password visibility toggle similar to signup page
        // You could add this functionality if desired
    </script>
</body>
</html> 