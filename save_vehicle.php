<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'vehicle_manager']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $vehicle_id = $_POST['vehicle_id'];
    $vin = $_POST['vin'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    // Removed $color
    $price = $_POST['price'];
    $from_location = $_POST['from_location'];
    $from_state = $_POST['from_state'];
    $to_location = $_POST['to_location'];
    $to_state = $_POST['to_state'];
    $distance = $_POST['distance'];
    $estimated_time = $_POST['estimated_time'];
    $pickup_date = $_POST['pickup_date'];
    $delivery_date = $_POST['delivery_date'] ?: null;
    $payment_status = $_POST['payment_status'];
    $fines = $_POST['fines'];
    $transporter_contact = $_POST['transporter_contact'];
    $transporter_phone = $_POST['transporter_phone'];
    $transporter_email = $_POST['transporter_email'];
    $auction = $_POST['auction'];
    $warehouse = $_POST['warehouse'];
    // Checkboxes logic restored
    $appointment = isset($_POST['appointment']) ? 1 : 0;
    $auction_reservation = isset($_POST['auction_reservation']) ? 1 : 0;
    $notes = $_POST['notes'] ?? '';
    
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            UPDATE vehicles SET
                vin = ?,
                make = ?,
                model = ?,
                year = ?,
                price = ?,
                from_location = ?,
                from_state = ?,
                to_location = ?,
                to_state = ?,
                distance = ?,
                estimated_time = ?,
                pickup_date = ?,
                delivery_date = ?,
                payment_status = ?,
                fines = ?,
                transporter_contact = ?,
                transporter_phone = ?,
                transporter_email = ?,
                auction = ?,
                warehouse = ?,
                appointment = ?,
                auction_reservation = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $vin, $make, $model, $year, $price,
            $from_location, $from_state, $to_location, $to_state,
            $distance, $estimated_time, $pickup_date, $delivery_date,
            $payment_status, $fines, $transporter_contact, $transporter_phone, $transporter_email,
            $auction, $warehouse, $appointment, $auction_reservation, $notes,
            $vehicle_id
        ]);
        
        $_SESSION['success'] = "Vehicle updated successfully!";
    } catch(PDOException $e) {
        error_log("Error updating vehicle: " . $e->getMessage());
        $_SESSION['error'] = "Error updating vehicle: A database error occurred.";
    }
    // Redirect using VIN, not ID
    header('Location: details.php?vin=' . urlencode($vin));
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>