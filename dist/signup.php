<?php
// Start session
session_start();

// Load models and config
require_once '../models/Auth.php';
require_once '../config/oauth.php';

// Initialize auth
$auth = new Auth();

// Define variables
$name = $email = $password = $confirm_password = "";
$formErrors = [];
$signupSuccess = false;

// Debug mode - set to false for production
$debug = false;

// Facebook OAuth Callback Handler
if (isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] === 'facebook') {
    // Handle Facebook OAuth callback
    
    // Get the authorization code
    $code = $_GET['code'];
    
    // Exchange the authorization code for an access token
    $tokenUrl = 'https://graph.facebook.com/v17.0/oauth/access_token';
    $params = [
        'client_id' => FACEBOOK_APP_ID,
        'client_secret' => FACEBOOK_APP_SECRET,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'code' => $code
    ];
    
    // Create cURL request to get token
    $ch = curl_init($tokenUrl . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($error) {
        if ($debug) {
            echo "cURL Error: " . $error;
            exit();
        }
        $formErrors[] = "Failed to connect to Facebook: $error";
    } else {
        // Decode the response
        $token = json_decode($response, true);
        
        // Check for error in the response
        if (isset($token['error'])) {
            if ($debug) {
                echo "Token Error: " . $token['error']['message'];
                exit();
            }
            $formErrors[] = "Facebook authentication error: " . $token['error']['message'];
        } else {
            // Get the access token
            $accessToken = $token['access_token'];
            
            // Get user info using the access token
            $userInfoUrl = 'https://graph.facebook.com/v17.0/me?fields=id,name,email&access_token=' . $accessToken;
            $ch = curl_init($userInfoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // Execute request
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($error) {
                if ($debug) {
                    echo "User Info cURL Error: " . $error;
                    exit();
                }
                $formErrors[] = "Failed to get user info: $error";
            } else {
                // Decode the user info
                $userInfo = json_decode($response, true);
                
                // Check for error in the response
                if (isset($userInfo['error'])) {
                    if ($debug) {
                        echo "User Info Error: " . $userInfo['error']['message'];
                        exit();
                    }
                    $formErrors[] = $userInfo['error']['message'];
                } else {
                    // Prepare user data
                    $userData = [
                        'name' => $userInfo['name'],
                        'email' => isset($userInfo['email']) ? $userInfo['email'] : $userInfo['id'] . '@facebook.com', // Fallback if email not provided
                        'facebook_id' => $userInfo['id'],
                        'password' => bin2hex(random_bytes(16)) // Generate a random secure password for Facebook users
                    ];
                    
                    // Process the social login
                    $result = $auth->socialLogin($userData, 'facebook');
                    
                    if ($result['status'] === 'success') {
                        // Make sure session variables are set correctly
                        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
                            // If the Auth class didn't set these properly, set them here
                            $_SESSION['is_logged_in'] = true;
                            
                            if (!isset($_SESSION['user_id']) && isset($result['user_id'])) {
                                $_SESSION['user_id'] = $result['user_id'];
                            }
                        }
                        
                        // Redirect to dashboard with welcome parameter
                        header('Location: user/dashboard.php?welcome=new');
                        exit();
                    } else {
                        $formErrors[] = $result['message'];
                    }
                }
            }
        }
    }
}

// Google OAuth Callback Handler
if (isset($_GET['code']) && (!isset($_GET['state']) || $_GET['state'] !== 'facebook')) {
    // Handle Google OAuth callback
    
    // Get the authorization code
    $code = $_GET['code'];
    
    // Exchange the authorization code for tokens
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $params = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    // Create cURL request to get tokens
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    // Execute request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($error) {
        if ($debug) {
            echo "cURL Error: " . $error;
            exit();
        }
        $formErrors[] = "Failed to connect to Google: $error";
    } else {
        // Decode the response
        $tokens = json_decode($response, true);
        
        // Check for error in the response
        if (isset($tokens['error'])) {
            if ($debug) {
                echo "Token Error: " . $tokens['error'];
                if (isset($tokens['error_description'])) {
                    echo "<br>Description: " . $tokens['error_description'];
                }
                exit();
            }
            $formErrors[] = $tokens['error'];
        } else {
            // Get the ID token and access token
            $idToken = $tokens['id_token'];
            $accessToken = $tokens['access_token'];
            
            // Get user info using the access token
            $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
            $ch = curl_init($userInfoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
            
            // Execute request
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($error) {
                if ($debug) {
                    echo "User Info cURL Error: " . $error;
                    exit();
                }
                $formErrors[] = "Failed to get user info: $error";
            } else {
                // Decode the user info
                $userInfo = json_decode($response, true);
                
                // Check for error in the response
                if (isset($userInfo['error'])) {
                    if ($debug) {
                        echo "User Info Error: " . $userInfo['error'];
                        exit();
                    }
                    $formErrors[] = $userInfo['error'];
                } else {
                    // Prepare user data
                    $userData = [
                        'name' => $userInfo['name'],
                        'email' => $userInfo['email'],
                        'google_id' => $userInfo['sub'], // Google's unique identifier for the user
                        'password' => bin2hex(random_bytes(16)) // Generate a random secure password for Google users
                    ];
                    
                    // Process the social login
                    $result = $auth->socialLogin($userData, 'google');
                    
                    if ($result['status'] === 'success') {
                        // Make sure session variables are set correctly
                        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
                            // If the Auth class didn't set these properly, set them here
                            $_SESSION['is_logged_in'] = true;
                            
                            if (!isset($_SESSION['user_id']) && isset($result['user_id'])) {
                                $_SESSION['user_id'] = $result['user_id'];
                            }
                        }
                        
                        // Redirect to dashboard with welcome parameter
                        header('Location: user/dashboard.php?welcome=new');
                        exit();
                    } else {
                        $formErrors[] = $result['message'];
                    }
                }
            }
        }
    }
}

// Handle Google OAuth error response
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    if ($error === 'access_denied') {
        $formErrors[] = "You declined to grant access. Please try again and allow the necessary permissions.";
    } else {
        $formErrors[] = "Authentication error: " . htmlspecialchars($error);
    }
}

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? true : false;
    
    // Prepare form data
    $formData = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'confirm_password' => $confirm_password,
        'terms' => $terms
    ];
    
    // Register the user using the Auth class
    $result = $auth->register($formData);
    
    if ($result['status'] === 'success') {
        // Redirect to dashboard with welcome parameter
        header('Location: user/dashboard.php?welcome=new');
        exit();
    } else {
        // Handle errors
        if (isset($result['errors'])) {
            $formErrors = $result['errors'];
        } else {
            $formErrors[] = $result['message'];
        }
    }
}

// Create Google OAuth URL
function getGoogleLoginUrl() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account',
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

// Create Facebook OAuth URL
function getFacebookLoginUrl() {
    $params = [
        'client_id' => FACEBOOK_APP_ID,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email',
        'state' => 'facebook'
    ];

    return 'https://www.facebook.com/v17.0/dialog/oauth?' . http_build_query($params);
}

$googleLoginUrl = getGoogleLoginUrl();
$facebookLoginUrl = getFacebookLoginUrl();
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
        
        /* Social buttons container */
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        
        /* Enhanced Google button */
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #dadce0;
            border-radius: 8px;
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
            text-decoration: none;
            height: 48px;
            box-sizing: border-box;
        }
        
        .google-btn:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
            border-color: #c6c6c6;
            text-decoration: none;
            color: #3c4043;
        }
        
        .google-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .google-icon {
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        /* Facebook button styles */
        .facebook-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #1877F2;
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            font-size: 15px;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            height: 48px;
            box-sizing: border-box;
            margin-top: 0;
        }
        
        .facebook-btn:hover {
            background-color: #166FE5;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-1px);
            text-decoration: none;
            color: white;
        }
        
        .facebook-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .facebook-icon {
            margin-right: 12px;
            flex-shrink: 0;
            filter: brightness(100);
        }
        
        .flex {
            display: flex;
        }
        
        .justify-center {
            justify-content: center;
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
            height: 48px;
            min-height: 44px;
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
            background: transparent;
            border: none;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            min-width: 44px;
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
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .social-login-divider::before, 
            .social-login-divider::after {
                width: 40%;
            }
            
            .google-btn, .signup-btn, .facebook-btn {
                padding: 12px 10px;
                font-size: 14px;
                height: 44px;
            }
            
            .google-btn, .facebook-btn {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .google-icon, .facebook-icon {
                width: 18px;
                height: 18px;
                margin-right: 8px;
            }
            
            .form-card {
                padding: 25px 20px;
                width: 90%;
                max-width: 450px;
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
        }
        
        @media (max-width: 480px) {
            .google-btn, .signup-btn, .facebook-btn {
                padding: 11px 10px;
                height: 50px;
            }
            
            .google-btn, .facebook-btn {
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 13px;
            }
            
            .google-icon, .facebook-icon {
                width: 16px;
                height: 16px;
                margin-right: 6px;
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
            
            h1 {
                font-size: 24px;
            }
            
            .form-card {
                padding: 20px 15px;
            }
            
            .social-buttons {
                gap: 10px;
            }
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
        .signup-btn.loading {
            background: linear-gradient(135deg, #8ba3fa, #6b8af8);
            cursor: not-allowed;
            position: relative;
        }
        
        .signup-btn.loading::after {
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
        
        /* For input fields - standardize height */
        .form-group input {
            height: 48px;
            box-sizing: border-box;
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
                
                <div class="error-container" id="error-container" <?php if (!empty($formErrors)) echo 'style="display: block;"'; ?>>
                    <strong>Please fix the following errors:</strong>
                    <ul class="error-list" id="error-list">
                        <?php
                        if (!empty($formErrors)) {
                            foreach ($formErrors as $error) {
                                echo '<li>' . htmlspecialchars($error) . '</li>';
                            }
                        }
                        ?>
                        <!-- Error messages will also be dynamically added here via JavaScript -->
                    </ul>
                </div>
                
                <form class="login-form" method="post" action="signup.php" id="signup-form" novalidate>
                    <div class="form-group">
                        <label for="name">FULL NAME</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required 
                               aria-describedby="name-validation" pattern="^[A-Za-z]+(?: [A-Za-z]+)+$" 
                               value="<?php echo htmlspecialchars($name); ?>">
                        <div class="validation-message" id="name-validation">Please enter your first and last name.</div>
                    </div>
                    
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
                                   aria-describedby="password-validation" minlength="8" autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Show password" onclick="togglePasswordVisibility('password')">
                                <span role="img" aria-hidden="true">👁️</span>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-meter"></div>
                        </div>
                        <div class="strength-text">Password strength</div>
                        <div class="validation-message" id="password-validation">Password must be at least 8 characters.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">CONFIRM PASSWORD</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••" required
                                   aria-describedby="confirm-password-validation" autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Show password" onclick="togglePasswordVisibility('confirm_password')">
                                <span role="img" aria-hidden="true">👁️</span>
                            </button>
                        </div>
                        <div class="validation-message" id="confirm-password-validation">Passwords must match.</div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" name="signup" class="signup-btn" id="signup-btn">Sign Up</button>
                    
                    <div class="social-login-divider">
                        <span>OR</span>
                    </div>
                    
                    <div class="social-buttons">
                        <a href="<?php echo htmlspecialchars($googleLoginUrl); ?>" class="google-btn">
                            <svg class="google-icon" viewBox="0 0 24 24" width="20" height="20">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                            Continue with Google
                        </a>
                        
                        <a href="<?php echo htmlspecialchars($facebookLoginUrl); ?>" class="facebook-btn">
                            <svg class="facebook-icon" viewBox="0 0 24 24" width="20" height="20">
                                <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Continue with Facebook
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
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
            
            // Enhanced password strength calculation
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length < 8) {
                feedback.push("Use 8+ characters");
            } else {
                strength += 25;
            }
            
            // Uppercase check
            if (!/[A-Z]/.test(password)) {
                feedback.push("Add uppercase letters");
            } else {
                strength += 25;
            }
            
            // Number check
            if (!/[0-9]/.test(password)) {
                feedback.push("Add numbers");
            } else {
                strength += 25;
            }
            
            // Special character check
            if (!/[^A-Za-z0-9]/.test(password)) {
                feedback.push("Add special characters");
            } else {
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
        
        // Form validation
        const form = document.getElementById('signup-form');
        const errorContainer = document.getElementById('error-container');
        const errorList = document.getElementById('error-list');
        const submitButton = document.getElementById('signup-btn');
        
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
            // If the input doesn't have aria-describedby attribute, we can't validate it properly
            if (!input.hasAttribute('aria-describedby') || !input.getAttribute('aria-describedby')) {
                console.error('Input lacks aria-describedby attribute:', input.id);
                return true; // Let server-side validation handle it
            }
            
            const validationMessage = document.getElementById(input.getAttribute('aria-describedby'));
            
            // Check if validationMessage exists before trying to access its properties
            if (!validationMessage) {
                console.error('Validation message element not found for', input.id, 'with aria-describedby', input.getAttribute('aria-describedby'));
                return true; // Skip client-side validation for this field
            }
            
            if (input.id === 'name') {
                const namePattern = /^[A-Za-z]+(?: [A-Za-z]+)+$/;
                if (!namePattern.test(input.value)) {
                    input.setAttribute('aria-invalid', 'true');
                    validationMessage.style.display = 'block';
                    validationMessage.textContent = 'Please enter your first and last name.';
                    return false;
                }
            } else if (input.id === 'email') {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(input.value)) {
                    input.setAttribute('aria-invalid', 'true');
                    validationMessage.style.display = 'block';
                    validationMessage.textContent = 'Please enter a valid email address.';
                    return false;
                }
            } else if (input.id === 'password') {
                if (input.value.length < 8) {
                    input.setAttribute('aria-invalid', 'true');
                    validationMessage.style.display = 'block';
                    validationMessage.textContent = 'Password must be at least 8 characters.';
                    return false;
                }
            } else if (input.id === 'confirm_password') {
                const passwordInput = document.getElementById('password');
                if (input.value !== passwordInput.value) {
                    input.setAttribute('aria-invalid', 'true');
                    validationMessage.style.display = 'block';
                    validationMessage.textContent = 'Passwords must match.';
                    return false;
                }
            }
            
            input.setAttribute('aria-invalid', 'false');
            validationMessage.style.display = 'none';
            return true;
        }
        
        // Form submission
        form.addEventListener('submit', function(e) {
            // Only apply client-side validation for inputs that have validation messages
            let errors = [];
            let isValid = true;
            
            document.querySelectorAll('input[required]').forEach(input => {
                // Only validate if it has a valid aria-describedby attribute
                if (input.hasAttribute('aria-describedby') && input.getAttribute('aria-describedby')) {
                    const validationMessageElement = document.getElementById(input.getAttribute('aria-describedby'));
                    
                    // Only apply validation if the validation message element exists
                    if (validationMessageElement) {
                        if (!validateInput(input)) {
                            isValid = false;
                            errors.push(validationMessageElement.textContent);
                        }
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault(); // Prevent form submission
                
                // Display client-side errors
                // Clear previous errors (but keep PHP-generated ones)
                const existingPhpErrors = [];
                document.querySelectorAll('#error-list li[data-php-error]').forEach(item => {
                    existingPhpErrors.push(item.cloneNode(true));
                });
                
                errorList.innerHTML = '';
                
                // Add back PHP errors
                existingPhpErrors.forEach(item => {
                    errorList.appendChild(item);
                });
                
                // Add client-side errors
                errors.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    li.setAttribute('data-js-error', 'true');
                    errorList.appendChild(li);
                });
                
                errorContainer.classList.add('show');
                errorContainer.style.display = 'block';
                
                // Scroll to error container
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            
            // If client-side validation passes, show loading state
            // The form will submit normally to be processed by PHP
            submitButton.classList.add('loading');
            submitButton.textContent = 'Creating Account...';
        });
        
        // Add to the beginning of your script section
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Checking form elements for proper validation message setup:');
            document.querySelectorAll('input[required]').forEach(input => {
                if (!input.hasAttribute('aria-describedby') || !input.getAttribute('aria-describedby')) {
                    console.error('Input missing aria-describedby attribute:', input.id);
                } else {
                    const validationElement = document.getElementById(input.getAttribute('aria-describedby'));
                    if (!validationElement) {
                        console.error('Validation message element not found for input:', input.id, 
                                     'with aria-describedby:', input.getAttribute('aria-describedby'));
                    } else {
                        console.log('Input properly configured:', input.id);
                    }
                }
            });
        });
        
        // Add to the beginning of your script section
        window.addEventListener('error', function(e) {
            // Log error but prevent it from interrupting the user
            console.error('Caught JS error:', e.message);
            // Prevent the error from breaking the page
            e.preventDefault();
            return true;
        });
    </script>
</body>
</html> 