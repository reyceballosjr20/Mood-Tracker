<?php
// Start session
session_start();

// Load configuration and models
require_once '../config/oauth.php';
require_once '../models/Auth.php';

// Initialize auth
$auth = new Auth();

// Check if this is an error response
if (isset($_GET['error'])) {
    // Redirect to login page with error
    header("Location: login.php?auth_error=" . urlencode($_GET['error']));
    exit();
}

// Check for authorization code
if (!isset($_GET['code'])) {
    // Redirect to login page
    header("Location: login.php?auth_error=no_code");
    exit();
}

// Get the authorization code
$code = $_GET['code'];

// Since we're not using composer, we'll implement a simple OAuth2 flow manually
// In a production environment, you should use the proper Google API Client Library

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
    header("Location: login.php?auth_error=" . urlencode("Failed to connect to Google: $error"));
    exit();
}

// Decode the response
$tokens = json_decode($response, true);

// Check for error in the response
if (isset($tokens['error'])) {
    header("Location: login.php?auth_error=" . urlencode($tokens['error']));
    exit();
}

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
    header("Location: login.php?auth_error=" . urlencode("Failed to get user info: $error"));
    exit();
}

// Decode the user info
$userInfo = json_decode($response, true);

// Check for error in the response
if (isset($userInfo['error'])) {
    header("Location: login.php?auth_error=" . urlencode($userInfo['error']));
    exit();
}

// Prepare user data
$userData = [
    'name' => $userInfo['name'],
    'email' => $userInfo['email'],
    'google_id' => $userInfo['sub'] // Google's unique identifier for the user
];

// Process the social login
$result = $auth->socialLogin($userData, 'google');

if ($result['status'] === 'success') {
    // Redirect to dashboard
    header("Location: user/dashboard.php");
    exit();
} else {
    // Redirect to login page with error
    header("Location: login.php?auth_error=" . urlencode($result['message']));
    exit();
} 