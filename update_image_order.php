<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'photo_manager']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON data from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check for required data fields
    if (isset($data['gallery_type'], $data['order']) && is_array($data['order'])) {
        $galleryType = $data['gallery_type'];
        $order = $data['order'];
        
        try {
            // Update the image_order field for each image
            $pdo->beginTransaction();
            foreach ($order as $index => $imageId) {
                // $index starts from 0, which is perfect for image_order
                $stmt = $pdo->prepare("UPDATE vehicle_images SET image_order = ? WHERE id = ?");
                $stmt->execute([$index, $imageId]);
            }
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Image order updated successfully']);
            exit;
        } catch(PDOException $e) {
            $pdo->rollBack();
            error_log("Database error updating image order: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit;
}
?>