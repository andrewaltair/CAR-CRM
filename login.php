<?php
// Include configuration
require_once 'config.php';

// If user is already authenticated, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Redirect POST request to login_process.php, which handles authentication via PDO
    // This file (login.php) is now solely responsible for displaying the form (View).
    // The actual login logic is centralized in login_process.php.
    
    // We pass the error from the session if the previous login attempt failed
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    
    // Fallback if no error is in session but a POST was made without success
    // This specific POST block is kept to simply check if we need to display an error
    // after a failed redirect/process, but the actual processing is offloaded.
    // Given that login_process.php redirects back to login.php on error, 
    // we only need to pull the error from the session here.
    
    // Re-check for session error in case login_process redirected here
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    
} else {
    // On GET request, clear any residual errors from session
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }
}

// Set page title for header
$page_title = 'Login';
// Include a simplified header for the login page (no navigation)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body class="bg-gray-100">
<main class="container mx-auto px-4 py-6 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Login</h1>
        
        <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded" role="alert" aria-atomic="true">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="login_process.php" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div>
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Log In
            </button>
        </form>
    </div>
</main>
</body>
</html>