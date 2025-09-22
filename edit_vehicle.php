<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'vehicle_manager']);

// Get VIN from URL parameter
$vin = isset($_GET['vin']) ? $_GET['vin'] : '';
// Fetch vehicle information from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE vin = ?");
    $stmt->execute([$vin]);
    $vehicle = $stmt->fetch();
    // If vehicle is not found, redirect to home
    if (!$vehicle) {
        header('Location: index.php');
        exit();
    }
} catch(PDOException $e) {
    die("Data fetch error: " . $e->getMessage());
}

$page_title = "Edit Vehicle - " . $vin;
include 'header.php';
?>
<main class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Edit Vehicle: <?php echo htmlspecialchars($vehicle['vin']); ?></h2>
            
            <form action="save_vehicle.php" method="POST" id="vehicleForm">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                <input type="hidden" name="vin" value="<?php echo htmlspecialchars($vehicle['vin']); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="vin_display" class="block text-gray-700 text-sm font-bold mb-2">VIN (Read-only)</label>
                        <input type="text" id="vin_display" value="<?php echo htmlspecialchars($vehicle['vin']); ?>" disabled class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md">
                    </div>
                    <div>
                        <label for="make" class="block text-gray-700 text-sm font-bold mb-2">Make</label>
                        <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($vehicle['make']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="model" class="block text-gray-700 text-sm font-bold mb-2">Model</label>
                        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="year" class="block text-gray-700 text-sm font-bold mb-2">Year</label>
                        <input type="number" id="year" name="year" value="<?php echo htmlspecialchars($vehicle['year']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price ($)</label>
                        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($vehicle['price']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="from_location" class="block text-gray-700 text-sm font-bold mb-2">From Location (City)</label>
                        <input type="text" id="from_location" name="from_location" value="<?php echo htmlspecialchars($vehicle['from_location']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="from_state" class="block text-gray-700 text-sm font-bold mb-2">From State (2-letter code)</label>
                        <input type="text" id="from_state" name="from_state" value="<?php echo htmlspecialchars($vehicle['from_state']); ?>" required maxlength="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="to_location" class="block text-gray-700 text-sm font-bold mb-2">To Location (City)</label>
                        <input type="text" id="to_location" name="to_location" value="<?php echo htmlspecialchars($vehicle['to_location']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="to_state" class="block text-gray-700 text-sm font-bold mb-2">To State (2-letter code)</label>
                        <input type="text" id="to_state" name="to_state" value="<?php echo htmlspecialchars($vehicle['to_state']); ?>" required maxlength="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="distance" class="block text-gray-700 text-sm font-bold mb-2">Distance (e.g., 500 miles)</label>
                        <input type="text" id="distance" name="distance" value="<?php echo htmlspecialchars($vehicle['distance']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="estimated_time" class="block text-gray-700 text-sm font-bold mb-2">Estimated Time (e.g., 2-3 days)</label>
                        <input type="text" id="estimated_time" name="estimated_time" value="<?php echo htmlspecialchars($vehicle['estimated_time']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="pickup_date" class="block text-gray-700 text-sm font-bold mb-2">Pickup Date</label>
                        <input type="text" id="pickup_date" name="pickup_date" value="<?php echo htmlspecialchars($vehicle['pickup_date']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-input">
                    </div>
                    <div>
                        <label for="delivery_date" class="block text-gray-700 text-sm font-bold mb-2">Delivery Date (Optional)</label>
                        <input type="text" id="delivery_date" name="delivery_date" value="<?php echo htmlspecialchars($vehicle['delivery_date']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-input">
                    </div>
                    <div>
                        <label for="payment_status" class="block text-gray-700 text-sm font-bold mb-2">Payment Status</label>
                        <select id="payment_status" name="payment_status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="paid" <?php echo $vehicle['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="pending" <?php echo $vehicle['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="unpaid" <?php echo $vehicle['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                    <div>
                        <label for="fines" class="block text-gray-700 text-sm font-bold mb-2">Fines ($)</label>
                        <input type="number" step="0.01" id="fines" name="fines" value="<?php echo htmlspecialchars($vehicle['fines']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="transporter_contact" class="block text-gray-700 text-sm font-bold mb-2">Transporter Contact Person</label>
                        <input type="text" id="transporter_contact" name="transporter_contact" value="<?php echo htmlspecialchars($vehicle['transporter_contact']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="transporter_phone" class="block text-gray-700 text-sm font-bold mb-2">Transporter Phone</label>
                        <input type="text" id="transporter_phone" name="transporter_phone" value="<?php echo htmlspecialchars($vehicle['transporter_phone']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="transporter_email" class="block text-gray-700 text-sm font-bold mb-2">Transporter Email</label>
                        <input type="email" id="transporter_email" name="transporter_email" value="<?php echo htmlspecialchars($vehicle['transporter_email']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="auction" class="block text-gray-700 text-sm font-bold mb-2">Auction</label>
                        <input type="text" id="auction" name="auction" value="<?php echo htmlspecialchars($vehicle['auction']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="warehouse" class="block text-gray-700 text-sm font-bold mb-2">Warehouse</label>
                        <input type="text" id="warehouse" name="warehouse" value="<?php echo htmlspecialchars($vehicle['warehouse']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2 flex space-x-8 pt-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="appointment" name="appointment" <?php echo $vehicle['appointment'] ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="appointment" class="ml-2 block text-sm font-medium text-gray-700">Appointment Set</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="auction_reservation" name="auction_reservation" <?php echo $vehicle['auction_reservation'] ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="auction_reservation" class="ml-2 block text-sm font-medium text-gray-700">Auction Reservation</label>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($vehicle['notes']); ?></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="details.php?vin=<?php echo urlencode($vehicle['vin']); ?>" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="btn-gradient bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        Save Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// Flatpickr calendar initialization
flatpickr("#pickup_date", {
    dateFormat: "Y-m-d", // YYYY-MM-DD format for database compatibility
    altInput: true,
    altFormat: "d.m.Y",
    onChange: function(selectedDates, dateStr, instance) {
        instance.element.value = dateStr;
    }
});

flatpickr("#delivery_date", {
    dateFormat: "Y-m-d", // YYYY-MM-DD format for database compatibility
    altInput: true,
    altFormat: "d.m.Y",
    allowInput: true,
    onChange: function(selectedDates, dateStr, instance) {
        instance.element.value = dateStr;
    }
});

AOS.init(); // Initialize AOS library
</script>
</html>