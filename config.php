<?php
session_start();

// Database configuration for Railway
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_port = getenv('MYSQLPORT') ?: '3306';
$db_name = getenv('MYSQLDATABASE') ?: 'porppad';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';

// Create connection dengan error handling
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Fallback untuk development
    error_log("Database error: " . $e->getMessage());
    
    // Jika di local development, gunakan local connection
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        $conn = new mysqli('localhost', 'root', '', 'porppad', 3306);
        if ($conn->connect_error) {
            die("Local connection failed: " . $conn->connect_error);
        }
    } else {
        die("Database connection error. Please try again later.");
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>