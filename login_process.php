<?php
require_once 'config.php';

// Ensure this script only runs on POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// 1. Sanitize and validate input
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Invalid username or password.";
    header('Location: login.php');
    exit();
}

try {
    global $pdo;
    
    // 2. Prepare statement to fetch user data by username
    // SELECT user_id, password, role FROM users WHERE username = ?
    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Verify user and password
    if ($user && password_verify($password, $user['password'])) {
        // Authentication successful
        
        // 4. Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        
        // Redirect to the main page
        $_SESSION['success'] = "Welcome, " . htmlspecialchars($username) . "!";
        header('Location: index.php');
        exit();
    } else {
        // Authentication failed
        $_SESSION['error'] = "Invalid username or password.";
        header('Location: login.php');
        exit();
    }
    
} catch(PDOException $e) {
    // Log error and redirect with generic message
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error'] = "A server error occurred during login. Please try again later.";
    header('Location: login.php');
    exit();
}