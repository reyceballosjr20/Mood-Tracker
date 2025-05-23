<?php
// Start session
session_start();

// Load models and config
require_once '../models/Auth.php';
require_once '../config/oauth.php';

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
        $loginError = "Failed to connect to Facebook: $error";
    } else {
        // Decode the response
        $token = json_decode($response, true);
        
        // Check for error in the response
        if (isset($token['error'])) {
            if ($debug) {
                echo "Token Error: " . $token['error']['message'];
                exit();
            }
            $loginError = "Facebook authentication error: " . $token['error']['message'];
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
                $loginError = "Failed to get user info: $error";
            } else {
                // Decode the user info
                $userInfo = json_decode($response, true);
                
                // Check for error in the response
                if (isset($userInfo['error'])) {
                    if ($debug) {
                        echo "User Info Error: " . $userInfo['error']['message'];
                        exit();
                    }
                    $loginError = $userInfo['error']['message'];
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
                        
                        // Redirect to dashboard
                        header("Location: user/dashboard.php");
                        exit();
                    } else {
                        $loginError = $result['message'];
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
        $loginError = "Failed to connect to Google: $error";
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
            $loginError = $tokens['error'];
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
                $loginError = "Failed to get user info: $error";
            } else {
                // Decode the user info
                $userInfo = json_decode($response, true);
                
                // Check for error in the response
                if (isset($userInfo['error'])) {
                    if ($debug) {
                        echo "User Info Error: " . $userInfo['error'];
                        exit();
                    }
                    $loginError = $userInfo['error'];
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
                        
                        // Redirect to dashboard
                        header("Location: user/dashboard.php");
                        exit();
                    } else {
                        $loginError = $result['message'];
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
        $loginError = "You declined to grant access. Please try again and allow the necessary permissions.";
    } else {
        $loginError = "Authentication error: " . htmlspecialchars($error);
    }
}

// Check success messages from URL parameters
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
    <title>Login - Mood Tracker</title>
    <link rel="stylesheet" href="../assets/styles/login-signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            border-radius: 10px;
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
        
        .flex {
            display: flex;
        }
        
        .justify-center {
            justify-content: center;
        }
        
        /* Enhanced main login button */
        .login-btn {
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
            background: linear-gradient(135deg, #8A65D9, #6A5AEC);
            box-shadow: 0 6px 15px rgba(123, 104, 238, 0.35);
            transform: translateY(-2px);
        }
        
        .login-btn:hover::before {
            transform: scaleX(1);
        }
        
        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(147, 112, 219, 0.2);
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
        
        /* Form validation styles */
        input:invalid:not(:focus):not(:placeholder-shown) {
            border-color: #d32f2f;
        }
        
        input:valid:not(:placeholder-shown) {
            border-color: #9370DB;
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
            accent-color: #9370DB;
        }
        
        .remember-me span {
            font-size: 14px;
            color: #666;
        }
        
        .forgot-password {
            color: #9370DB;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 5px; /* Increase touch area */
            margin: -5px;
        }
        
        .forgot-password:hover {
            color: #8A65D9;
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
            
            .google-btn {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 44px;
            }
            
            .google-icon {
                width: 18px;
                height: 18px;
                margin-right: 8px;
            }
            
            .form-card {
                padding: 25px 20px;
                width: 90%;
                max-width: 450px;
                margin: 0 auto;
                border-radius: 18px;
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
            
            .google-btn {
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 13px;
            }
            
            .google-icon {
                width: 16px;
                height: 16px;
                margin-right: 6px;
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
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #4caf50;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1);
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
            outline: 2px solid #9370DB;
            outline-offset: 2px;
        }
        
        /* Loading state */
        .login-btn.loading {
            background: linear-gradient(135deg, #A88BE2, #8A76DF);
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
            background: linear-gradient(135deg, #333, #9370DB);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            line-height: 1.2;
        }
        
        .already-registered {
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .already-registered a {
            color: #9370DB;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .already-registered a:hover {
            color: #8A65D9;
            text-decoration: underline;
        }
        
        /* Card container animation */
        .form-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(147, 112, 219, 0.15);
        }
        
        /* Social login buttons container */
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        
        /* Facebook button styles */
        .facebook-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #1877F2;
            border: none;
            border-radius: 10px;
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
        
        /* Mobile responsiveness for social buttons */
        @media (max-width: 768px) {
            .facebook-btn {
                padding: 12px 10px;
                font-size: 14px;
                height: 44px;
            }
            
            .facebook-icon {
                width: 18px;
                height: 18px;
                margin-right: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .social-buttons {
                gap: 10px;
            }
            
            .facebook-btn {
                padding: 11px 10px;
                height: 50px;
                font-size: 13px;
            }
            
            .facebook-icon {
                width: 16px;
                height: 16px;
                margin-right: 6px;
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
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="validation-message" id="password-validation">Please enter your password.</div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" name="login" class="login-btn" id="login-btn">Login</button>
                    
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
        // Check URL parameters for success messages
        const urlParams = new URLSearchParams(window.location.search);
        
        // Show signup success message (success messages now only handled through PHP)
        
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