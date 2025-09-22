<?php
require_once 'config.php';
checkAuth();
// Access Control: Only admin can archive vehicles due to severity of action
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $vin = $_POST['vin'] ?? '';
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING) ?? '';
    $other_reason = filter_input(INPUT_POST, 'other_reason', FILTER_SANITIZE_STRING) ?? '';
    $driver_phone = filter_input(INPUT_POST, 'driver_phone', FILTER_SANITIZE_STRING) ?? '';

    if (empty($vin) || empty($reason)) {
        $_SESSION['error'] = "Invalid VIN or archive reason.";
        header('Location: index.php');
        exit();
    }

    // Determine the final reason text
    $final_reason_text = ($reason === 'other') ? $other_reason : $reason;

    try {
        global $pdo;
        
        // 1. Archive the vehicle: SET is_archived = 1, archive_reason = final_reason_text
        $stmt_archive = $pdo->prepare("
            UPDATE vehicles 
            SET is_archived = 1, archive_reason = ?, updated_at = NOW() 
            WHERE vin = ?
        ");
        $stmt_archive->execute([$final_reason_text, $vin]);

        // 2. Insert into problem_vehicles (using 'reason' as problem_type)
        $stmt_problem = $pdo->prepare("
            INSERT INTO problem_vehicles (vin, driver_phone, problem_type, description, reported_by) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_problem->execute([
            $vin, 
            $driver_phone, 
            $reason, // problem_type (e.g., 'no_show', 'stuck_at_auction')
            $final_reason_text, // detailed description
            $_SESSION['user_id']
        ]);

        $_SESSION['success'] = "Vehicle " . htmlspecialchars($vin) . " archived successfully and logged as a problem.";
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error archiving vehicle: " . $e->getMessage();
    }
    
    // Redirect back to the main list (or details page if preferred)
    header('Location: index.php');
    exit();
} else {
    // Prevent direct access to the action file
    header('Location: index.php');
    exit();
}
?>