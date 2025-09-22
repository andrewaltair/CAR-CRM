<?php
require_once 'config.php';
checkAuth();

// Removed: require_once 'vendor/autoload.php'; // This line is removed as per project architecture (no Composer/vendor)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $vin = filter_input(INPUT_POST, 'vin', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) ?? ''; // Added VIN for redirect
    $image_path = null;

    if (!$vehicle_id || empty($comment)) {
        $_SESSION['error'] = "Invalid Vehicle ID or empty comment.";
        header('Location: details.php?vin=' . urlencode($vin));
        exit();
    }
    
    // Check user role for image upload permission
    $can_upload_image = in_array($_SESSION['role'] ?? '', ['admin', 'photo_manager']);
    
    // Handle image upload, if present
    if ($can_upload_image && isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/comments/' . $vehicle_id . '/';
        
        // Ensure directory exists
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['error'] = "Failed to create upload directory.";
                header('Location: details.php?vin=' . urlencode($vin));
                exit();
            }
        }

        $file_error = $_FILES['comment_image']['error'];

        if ($file_error !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'File size exceeds server limit (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE  => 'File size exceeds form limit.',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
            ];
            $_SESSION['error'] = "File upload failed: " . ($upload_errors[$file_error] ?? "Unknown error code {$file_error}.");
            header('Location: details.php?vin=' . urlencode($vin));
            exit();
        }

        // Basic file type check (MIME)
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['comment_image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mime_types)) {
            $_SESSION['error'] = "File type not allowed: " . htmlspecialchars($mime_type);
            header('Location: details.php?vin=' . urlencode($vin));
            exit();
        }

        // Determine file extension
        $extension = pathinfo($_FILES['comment_image']['name'], PATHINFO_EXTENSION);
        $safe_extension = strtolower($extension);

        if (!in_array($safe_extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            $_SESSION['error'] = "File extension not allowed.";
            header('Location: details.php?vin=' . urlencode($vin));
            exit();
        }
        
        $file_name = uniqid() . '.' . $safe_extension;
        $target_file = $upload_dir . $file_name;

        // CRITICAL: Check if file was uploaded via HTTP POST and then move it
        if (!is_uploaded_file($_FILES['comment_image']['tmp_name'])) {
            $_SESSION['error'] = "Potential attack detected (not an uploaded file).";
            header('Location: details.php?vin=' . urlencode($vin));
            exit();
        }
        
        if (move_uploaded_file($_FILES['comment_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            $_SESSION['error'] = "Failed to move uploaded file. Check directory permissions (755).";
            header('Location: details.php?vin=' . urlencode($vin));
            exit();
        }
    }

    // Save comment to database
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO vehicle_comments (vehicle_id, user_id, comment, image_path) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$vehicle_id, $_SESSION['user_id'], $comment, $image_path]);
        
        $_SESSION['success'] = "Comment added successfully!";
    } catch(PDOException $e) {
        // Log the error for admin review
        error_log("Database Error in add_comment.php: " . $e->getMessage());
        
        // If DB insertion fails, attempt to delete the uploaded file to clean up
        if ($image_path) {
            deleteFileIfExist($image_path);
        }
        
        $_SESSION['error'] = "Database error: Failed to save comment.";
    }
    
    header('Location: details.php?vin=' . urlencode($vin));
    exit();
} else {
    // Not a POST request
    header('Location: index.php');
    exit();
}
?>