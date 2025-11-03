<?php
// config.php - Database Configuration
session_start();

// Auto-detect environment
if (getenv('RAILWAY_ENVIRONMENT') || getenv('MYSQLHOST')) {
    // Railway Environment
    define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql.railway.internal');
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
    define('DB_PORT', getenv('MYSQLPORT') ?: 3306);
} else {
    // Local XAMPP Environment
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'porppad');
    define('DB_PORT', 3306);
}

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$conn) {
    // Fallback untuk local development
    if (!getenv('RAILWAY_ENVIRONMENT')) {
        $conn = mysqli_connect('localhost', 'root', '', 'porppad');
    }
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// Set charset to UTF8
mysqli_set_charset($conn, "utf8mb4");

// Base URL - otomatis detect Railway atau localhost
if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
    define('BASE_URL', 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') . '/');
} else {
    define('BASE_URL', 'http://localhost/porppad/');
}

// Helper Functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('index.php');
    }
}

function alert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($alert['message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['alert']);
    }
}

// Sanitize input
function clean($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}
?>