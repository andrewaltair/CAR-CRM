<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'vehicle_manager']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token. Please try again.";
        header('Location: add_vehicle.php');
        exit();
    }
    
    // 2. Sanitize and validate inputs
    $vin = filter_input(INPUT_POST, 'vin', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    
    $from_location = filter_input(INPUT_POST, 'from_location', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $from_state = filter_input(INPUT_POST, 'from_state', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $to_location = filter_input(INPUT_POST, 'to_location', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $to_state = filter_input(INPUT_POST, 'to_state', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    
    $pickup_date = filter_input(INPUT_POST, 'pickup_date', FILTER_SANITIZE_STRING);
    $delivery_date = filter_input(INPUT_POST, 'delivery_date', FILTER_SANITIZE_STRING) ?: null;

    $transporter_phone = filter_input(INPUT_POST, 'transporter_phone', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) ?? '';
    $auction = filter_input(INPUT_POST, 'auction', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $warehouse = filter_input(INPUT_POST, 'warehouse', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    
    $appointment = isset($_POST['appointment']) ? 1 : 0;
    $auction_reservation = isset($_POST['auction_reservation']) ? 1 : 0;
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) ?? '';
    
    // Basic server-side validation for required fields
    if (empty($vin) || empty($make) || empty($model) || $year === false || $price === false || empty($from_location) || empty($to_location)) {
        $_SESSION['error'] = "Validation error: Please fill in all required fields correctly.";
        header('Location: add_vehicle.php');
        exit();
    }

    try {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO vehicles (
                vin, make, model, year, price, 
                from_location, from_state, to_location, to_state,
                pickup_date, delivery_date,
                transporter_phone,
                auction, warehouse, appointment, auction_reservation, notes,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $vin, $make, $model, $year, $price,
            $from_location, $from_state, $to_location, $to_state,
            $pickup_date, $delivery_date,
            $transporter_phone,
            $auction, $warehouse, $appointment, $auction_reservation, $notes
        ]);
        
        $_SESSION['success'] = "Vehicle " . htmlspecialchars($vin) . " added successfully!";
        header('Location: index.php');
        exit();
        
    } catch(PDOException $e) {
        // Log the error for debugging
        error_log("Error saving new vehicle: " . $e->getMessage());
        
        if ($e->getCode() === '23000') {
            $_SESSION['error'] = "Error: Vehicle with VIN " . htmlspecialchars($vin) . " already exists.";
        } else {
            $_SESSION['error'] = "Error saving vehicle: A database error occurred.";
        }
        
        header('Location: add_vehicle.php');
        exit();
    }
} else {
    header('Location: add_vehicle.php');
    exit();
}