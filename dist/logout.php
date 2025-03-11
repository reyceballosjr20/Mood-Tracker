<?php
// Start session
session_start();

// Load auth class
require_once '../models/Auth.php';

// Initialize auth
$auth = new Auth();

// Logout the user
$auth->logout();

// Redirect to login page with success message
header("Location: login.php?logout=success");
exit();
?> 