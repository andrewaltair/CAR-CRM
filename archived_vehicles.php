<?php
require_once 'config.php';
checkAuth();
// Access Control: Only admin or vehicle_manager can view archived vehicles
checkRole(['admin', 'vehicle_manager']);

$page_title = "Archived Vehicles";

// Get archived vehicles list
$vehicles = [];
try {
    $sql = "SELECT * FROM vehicles WHERE is_archived = 1 ORDER BY updated_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $vehicles = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

include 'header.php';
?>

<main class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Archived Vehicles (<?php echo count($vehicles); ?>)</h2>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition">
            <i data-feather="arrow-left" class="w-4 h-4 inline-block mr-2"></i>Back to Active
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto scrollable-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            VIN / Dates
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status / Location
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Archive Reason
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Make / Model
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($vehicles) > 0): ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="details.php?vin=<?php echo urlencode($vehicle['vin']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-3 transition" title="View Details">
                                        <i data-feather="eye" class="w-4 h-4 inline-block"></i>
                                    </a>
                                    
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <form action="unarchive_vehicle.php" method="POST" onsubmit="return confirm('Are you sure you want to unarchive this vehicle?')" class="inline-block ml-2">
                                        <input type="hidden" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>">
                                        <button type="submit" class="text-green-600 hover:text-green-900 transition" title="Unarchive Vehicle">
                                            <i data-feather="upload" class="w-4 h-4 inline-block"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($vehicle['vin']); ?></div>
                                    <div class="text-xs text-gray-500">P/U: <?php echo htmlspecialchars(date('d.m.Y', strtotime($vehicle['pickup_date']))); ?></div>
                                    <div class="text-xs text-gray-500">Archived: <?php echo htmlspecialchars(date('d.m.Y', strtotime($vehicle['updated_at']))); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php
                                        $status_class = match ($vehicle['payment_status'] ?? 'pending') {
                                            'paid' => 'status-paid',
                                            'pending' => 'status-pending',
                                            'unpaid' => 'status-unpaid',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars(ucfirst($vehicle['payment_status'] ?? 'N/A')); ?>
                                    </span>
                                    
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($vehicle['from_state'] . ' to ' . $vehicle['to_state']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-xs text-wrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($vehicle['archive_reason'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($vehicle['make'] ?? '' . ' ' . $vehicle['model'] ?? '' . ' ' . $vehicle['year'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo htmlspecialchars(number_format($vehicle['price'] ?? 0, 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No vehicles in the archive.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>