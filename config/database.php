<?php
$host = 'localhost';
$username = 'root'; 
$password = 'icctvet1234'; 
$dbname = 'mood_tracker';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
