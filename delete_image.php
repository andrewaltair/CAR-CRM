<?php
require_once 'config.php';
checkAuth();
// Access Control: Only admin or photo manager can delete images
checkRole(['admin', 'photo_manager']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$imageId = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);

if (!$imageId) {
    echo json_encode(['success' => false, 'message' => 'Invalid image ID.']);
    exit;
}

try {
    global $pdo;
    
    $pdo->beginTransaction();

    // 1. Get the image path for file deletion
    $stmt = $pdo->prepare("SELECT image_path FROM vehicle_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Image not found in database.']);
        exit;
    }
    
    $imagePath = $image['image_path'];

    // 2. Delete the record from the database
    $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE id = ?");
    $stmt->execute([$imageId]);

    if ($stmt->rowCount() > 0) {
        // 3. Delete the file from the filesystem
        deleteFileIfExist($imagePath);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully.']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: Image not deleted.']);
    }
} catch(PDOException $e) {
    $pdo->rollBack();
    error_log("Database Error in delete_image.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A critical database error occurred.']);
}