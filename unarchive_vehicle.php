<?php
require_once 'config.php';
checkAuth();
// Strict Access Control: Only admin can unarchive
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vin = $_POST['vin'] ?? '';
    
    if (empty($vin)) {
        $_SESSION['error'] = "Invalid VIN provided.";
        header('Location: archived_vehicles.php');
        exit();
    }
    
    try {
        // 1. Unarchive the vehicle (set is_archived = 0 and clear archive reason)
        $stmt = $pdo->prepare("
            UPDATE vehicles 
            SET is_archived = 0, archive_reason = NULL, updated_at = NOW() 
            WHERE vin = ?
        ");
        $stmt->execute([$vin]);
        
        // 2. Remove the corresponding record from the problem_vehicles table (assuming unarchiving means problem is resolved)
        $stmt_problem = $pdo->prepare("DELETE FROM problem_vehicles WHERE vin = ?");
        $stmt_problem->execute([$vin]);

        $_SESSION['success'] = "Vehicle " . htmlspecialchars($vin) . " has been successfully unarchived!";
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error unarchiving vehicle: " . $e->getMessage();
    }
    
    header('Location: archived_vehicles.php');
    exit();
} else {
    // If not a POST request, redirect
    header('Location: archived_vehicles.php');
    exit();
}
?>