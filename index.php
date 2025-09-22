<?php
require_once 'config.php';
checkAuth();

// Fetch search parameters
$searchParams = [
    'vin' => $_GET['vin'] ?? '',
    'make' => $_GET['make'] ?? '',
    'model' => $_GET['model'] ?? '',
    'year' => $_GET['year'] ?? '',
    'status' => $_GET['status'] ?? '',
    'from_location' => $_GET['from_location'] ?? '',
    'to_location' => $_GET['to_location'] ?? '',
    'pickup_date' => $_GET['pickup_date'] ?? '',
    'delivery_date' => $_GET['delivery_date'] ?? '',
];

// Fetch list of vehicles
$vehicles = [];
try {
    global $pdo;
    $sql = "SELECT * FROM vehicles WHERE is_archived = 0";
    $conditions = [];
    $bindings = [];
    
    if (!empty($searchParams['vin'])) {
        $conditions[] = "vin LIKE ?";
        $bindings[] = "%" . $searchParams['vin'] . "%";
    }
    
    if (!empty($searchParams['make'])) {
        $conditions[] = "make LIKE ?";
        $bindings[] = "%" . $searchParams['make'] . "%";
    }
    
    if (!empty($searchParams['model'])) {
        $conditions[] = "model LIKE ?";
        $bindings[] = "%" . $searchParams['model'] . "%";
    }
    
    if (!empty($searchParams['year'])) {
        $conditions[] = "year = ?";
        $bindings[] = $searchParams['year'];
    }
    
    if (!empty($searchParams['status'])) {
        $conditions[] = "payment_status = ?";
        $bindings[] = $searchParams['status'];
    }
    
    if (!empty($searchParams['from_location'])) {
        $conditions[] = "from_location LIKE ? OR from_state LIKE ?";
        $bindings[] = "%" . $searchParams['from_location'] . "%";
        $bindings[] = "%" . $searchParams['from_location'] . "%";
    }
    
    if (!empty($searchParams['to_location'])) {
        $conditions[] = "to_location LIKE ? OR to_state LIKE ?";
        $bindings[] = "%" . $searchParams['to_location'] . "%";
        $bindings[] = "%" . $searchParams['to_location'] . "%";
    }
    
    if (!empty($searchParams['pickup_date'])) {
        $conditions[] = "pickup_date = ?";
        $bindings[] = $searchParams['pickup_date'];
    }
    
    if (!empty($searchParams['delivery_date'])) {
        $conditions[] = "delivery_date = ?";
        $bindings[] = $searchParams['delivery_date'];
    }
    
    if (count($conditions) > 0) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);
    $vehicles = $stmt->fetchAll();
    
} catch(PDOException $e) {
    // In a production environment, only log the error, don't display it
    $_SESSION['error'] = "Database error: Failed to load vehicles.";
    error_log("Error fetching vehicles: " . $e->getMessage());
}

$page_title = "Vehicle List";
include 'header.php';
?>
<main class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Active Vehicle List</h2>

    <form method="GET" class="bg-white p-4 rounded-lg shadow-md mb-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" onsubmit="return validateSearch()">
        <div>
            <label for="vin" class="block text-sm font-medium text-gray-700">VIN</label>
            <input type="text" id="vin" name="vin" value="<?php echo htmlspecialchars($searchParams['vin']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="make" class="block text-sm font-medium text-gray-700">Make</label>
            <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($searchParams['make']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
            <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($searchParams['model']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
            <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($searchParams['year']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Payment Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Any Status</option>
                <option value="paid" <?php echo $searchParams['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="pending" <?php echo $searchParams['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="unpaid" <?php echo $searchParams['status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
            </select>
        </div>
        <div>
            <label for="from_location" class="block text-sm font-medium text-gray-700">From Location</label>
            <input type="text" id="from_location" name="from_location" value="<?php echo htmlspecialchars($searchParams['from_location']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="to_location" class="block text-sm font-medium text-gray-700">To Location</label>
            <input type="text" id="to_location" name="to_location" value="<?php echo htmlspecialchars($searchParams['to_location']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        <div>
            <label for="pickup_date" class="block text-sm font-medium text-gray-700">Pickup Date</label>
            <input type="text" id="pickup_date" name="pickup_date" value="<?php echo htmlspecialchars($searchParams['pickup_date']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm flatpickr-input">
        </div>
        <div>
            <label for="delivery_date" class="block text-sm font-medium text-gray-700">Delivery Date</label>
            <input type="text" id="delivery_date" name="delivery_date" value="<?php echo htmlspecialchars($searchParams['delivery_date']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm flatpickr-input">
        </div>
        <div class="col-span-2 md:col-span-4 lg:col-span-6 flex justify-end space-x-3 mt-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition filter-btn">
                Search
            </button>
            <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                Reset
            </a>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto scrollable-table">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        VIN / Details
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Vehicle
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Price
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        From
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        To
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Dates (P/D)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($vehicles) > 0): ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="details.php?vin=<?php echo urlencode($vehicle['vin']); ?>" class="text-sm font-medium text-blue-600 hover:text-blue-900 transition hover:underline">
                                <?php echo htmlspecialchars($vehicle['vin']); ?>
                            </a>
                            <?php if (!empty($vehicle['auction'])): ?>
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($vehicle['auction']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['warehouse'])): ?>
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($vehicle['warehouse']); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo htmlspecialchars(number_format($vehicle['price'], 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($vehicle['from_state']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($vehicle['to_state']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            P: <?php echo htmlspecialchars($vehicle['pickup_date']); ?><br>
                            D: <?php echo $vehicle['delivery_date'] ? htmlspecialchars($vehicle['delivery_date']) : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <span class="status-badge status-<?php echo htmlspecialchars($vehicle['payment_status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($vehicle['payment_status'])); ?>
                            </span>
                            <?php if ($vehicle['appointment']): ?>
                                <span class="ml-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" title="Appointment Set">
                                    <i data-feather="calendar" class="w-3 h-3"></i>
                                </span>
                            <?php endif; ?>
                            <?php if ($vehicle['auction_reservation']): ?>
                                <span class="ml-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Auction Reservation">
                                    <i data-feather="tag" class="w-3 h-3"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="details.php?vin=<?php echo urlencode($vehicle['vin']); ?>" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                <i data-feather="eye" class="w-5 h-5 inline-block"></i>
                            </a>
                            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'vehicle_manager'): ?>
                            <a href="edit_vehicle.php?vin=<?php echo urlencode($vehicle['vin']); ?>" class="ml-4 text-yellow-600 hover:text-yellow-900" title="Edit Vehicle">
                                <i data-feather="edit" class="w-5 h-5 inline-block"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No active vehicles found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include 'footer.php'; ?>
<script>
    // Initialize Flatpickr for date inputs
    flatpickr("#pickup_date, #delivery_date", {
        dateFormat: "Y-m-d", // YYYY-MM-DD format
        altInput: true,
        altFormat: "d.m.Y",
        allowInput: true
    });

    // Function to ensure at least one filter is applied if search button is clicked
    function validateSearch() {
        const params = [
            'vin', 'make', 'model', 'year', 'status', 
            'from_location', 'to_location', 'pickup_date', 'delivery_date'
        ];
        
        let hasValue = false;
        for (const param of params) {
            const element = document.getElementById(param);
            if (element && element.value.trim() !== '') {
                hasValue = true;
                break;
            }
        }
        
        // Removed validation to allow searching by one field
        return true; 
    }
</script>
</body>
</html>