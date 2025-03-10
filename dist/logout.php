<?php
// Initialize the session if not already started
session_start();

// Include auth model
require_once '../models/Auth.php';

// Create auth instance and logout
$auth = new Auth();
$auth->logout();

// Redirect to login page with a message
header("Location: login.php?logout=success");
exit;
?> 