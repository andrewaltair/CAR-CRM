<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'photo_manager']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Basic validation of inputs
    $vehicle_id = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
    $image_type = filter_input(INPUT_POST, 'image_type', FILTER_SANITIZE_STRING);

    // UPDATED: Added 'damage' to allowed types
    if (!$vehicle_id || !in_array($image_type, ['pre', 'post', 'damage', 'document'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid vehicle ID or image type.']);
        exit;
    }
    
    // Handle multiple image upload
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        // Define upload directory: uploads/image_type/vehicle_id/
        $upload_dir = 'uploads/' . $image_type . '/' . $vehicle_id . '/';
        
        // Create directory if it does not exist
        if (!file_exists($upload_dir)) {
            // Use recursive creation and safe permissions (0755 is safer than 0777)
            if (!mkdir($upload_dir, 0755, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
                exit;
            }
        }
        
        $allowed_mime_types = [
            'image/jpeg', 
            'image/png', 
            'application/pdf'
        ];
        
        $uploadedCount = 0;
        $errors = [];
        $file_keys = array_keys($_FILES['images']['name']);
        
        // Find the maximum existing image_order for this vehicle/type
        $max_order = -1;
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT MAX(image_order) FROM vehicle_images WHERE vehicle_id = ? AND image_type = ?");
            $stmt->execute([$vehicle_id, $image_type]);
            $max_order = (int)($stmt->fetchColumn() ?? -1);
        } catch(PDOException $e) {
            error_log("DB Error fetching max image order: " . $e->getMessage());
            $errors[] = "Internal database error during upload preparation.";
            echo json_encode(['success' => false, 'message' => implode("\n", $errors)]);
            exit;
        }

        foreach ($file_keys as $key) {
            $name = $_FILES['images']['name'][$key];
            $tmp_name = $_FILES['images']['tmp_name'][$key];
            $error = $_FILES['images']['error'][$key];
            $size = $_FILES['images']['size'][$key];
            
            if ($error === UPLOAD_ERR_OK) {
                // Get MIME type and extension
                $mime_type = mime_content_type($tmp_name);
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                
                if (in_array($mime_type, $allowed_mime_types)) {
                    // Create a unique file name
                    $unique_name = uniqid($image_type . '_', true) . '.' . strtolower($ext);
                    $target_file = $upload_dir . $unique_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        // Insert into database with incremental order
                        $new_order = $max_order + 1 + $uploadedCount;
                        $sql = "INSERT INTO vehicle_images (vehicle_id, image_type, image_path, uploaded_by, image_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$vehicle_id, $image_type, $target_file, $_SESSION['user_id'], $new_order]);
                        $uploadedCount++;
                    } else {
                        $errors[] = "Failed to move uploaded file: " . htmlspecialchars($name);
                    }
                } else {
                    $errors[] = "File type not allowed: " . htmlspecialchars($name) . " (MIME: " . htmlspecialchars($mime_type) . ")";
                }
            } else {
                // Handle file upload errors (e.g., file size limit)
                $errors[] = "Upload error for " . htmlspecialchars($name) . ": Error code " . $error;
            }
        }
        
        if ($uploadedCount > 0 && empty($errors)) {
            echo json_encode(['success' => true, 'message' => "$uploadedCount images uploaded successfully"]);
            exit;
        } elseif ($uploadedCount > 0 && !empty($errors)) {
             echo json_encode(['success' => true, 'message' => "$uploadedCount images uploaded successfully, but some errors occurred: " . implode("; ", $errors)]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => implode("\n", $errors)]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'No files uploaded or invalid input structure.']);
    exit;
}

// If not a POST request
echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
?>