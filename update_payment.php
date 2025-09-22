<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'payment_manager']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = $_POST['vehicle_id'];
    $payment_status = $_POST['payment_status'];
    $stmt = $pdo->prepare("
        UPDATE vehicles 
        SET payment_status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$payment_status, $vehicle_id]);
    $_SESSION['success'] = "Payment status updated successfully!";
    header('Location: details.php?vin=' . $_GET['vin']);
    exit();
}
?>