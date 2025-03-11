<?php
// Include the Database class
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mood-Tracker/models/Database.php';

// Create a database instance
$database = new Database();
$pdo = $database->conn;

// Check if connection was successful
if (!$pdo) {
    die("Database connection failed");
}
?> 