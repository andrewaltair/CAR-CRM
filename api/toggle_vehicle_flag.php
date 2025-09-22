<?php
// API Endpoint for safely toggling a boolean flag (0/1) on a vehicle record.
require_once '../config.php';
checkAuth();
checkRole(['admin', 'vehicle_manager']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// 1. Sanitize and validate input
$vehicle_id = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
$field = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
// Use strict validation for 0 or 1
$new_value = filter_input(INPUT_POST, 'new_value', FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 1)));

// Check for valid inputs
if ($vehicle_id === false || $vehicle_id === null || $new_value === false || $new_value === null || empty($field)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Vehicle ID, Field, or New Value.']);
    exit;
}

// 2. Define allowed fields (WHITE LIST)
// This is the CRITICAL security step to prevent modifying arbitrary columns.
$allowedFields = ['appointment', 'auction_reservation', 'is_archived']; // is_archived included for completeness if needed elsewhere
if (!in_array($field, $allowedFields)) {
    error_log("Security Alert: Attempted update of disallowed field: " . $field);
    echo json_encode(['success' => false, 'message' => 'Invalid field for update.']);
    exit;
}

try {
    global $pdo;
    
    // 3. Use prepared statement for security. Column name is safe due to $allowedFields check.
    $sql = "UPDATE vehicles SET {$field} = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_value, $vehicle_id]);

    if ($stmt->rowCount()) {
        $statusText = $new_value == 1 ? 'Set' : 'Removed';
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        echo json_encode(['success' => true, 'message' => "{$fieldName} status successfully {$statusText}. (Vehicle ID: {$vehicle_id})", 'new_value' => $new_value]);
    } else {
        // This is not always an error, could be that the status was already set.
        echo json_encode(['success' => true, 'message' => 'No changes made (status already set or vehicle not found).', 'new_value' => $new_value]);
    }
} catch(PDOException $e) {
    // Log the error for admin review, return generic error to user
    error_log("Database Error in toggle_vehicle_flag.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred during the update.']);
}
?>