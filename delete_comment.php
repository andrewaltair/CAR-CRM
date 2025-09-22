<?php
require_once 'config.php';
checkAuth();
// Access Control: Only admin or vehicle manager can delete comments
checkRole(['admin', 'vehicle_manager']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

if (!$commentId) {
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID.']);
    exit;
}

try {
    global $pdo;
    
    $pdo->beginTransaction();

    // 1. Get the image path for file deletion (if any)
    $stmt = $pdo->prepare("SELECT image_path FROM vehicle_comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Comment not found in database.']);
        exit;
    }
    
    $imagePath = $comment['image_path'];

    // 2. Delete the comment record from the database
    $stmt = $pdo->prepare("DELETE FROM vehicle_comments WHERE id = ?");
    $stmt->execute([$commentId]);

    if ($stmt->rowCount() > 0) {
        // 3. Delete the associated file from the filesystem (if it exists)
        if ($imagePath) {
            deleteFileIfExist($imagePath);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully.']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: Comment not deleted.']);
    }
} catch(PDOException $e) {
    $pdo->rollBack();
    error_log("Database Error in delete_comment.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A critical database error occurred.']);
}