<?php
require_once 'config.php';
checkAuth();
checkRole(['admin', 'vehicle_manager']);

// Generate CSRF token for the form
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Vehicle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <?php include 'header.php'; ?>
        <main class="container mx-auto px-4 py-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-6">Add New Vehicle</h2>
                    <form action="save_new_vehicle.php" method="POST" id="vehicleForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="vin" class="block text-sm font-medium text-gray-700 mb-1">VIN (17 characters)</label>
                                <input type="text" name="vin" id="vin" required maxlength="17" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter VIN">
                            </div>
                            <div>
                                <label for="make" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
                                <input type="text" name="make" id="make" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Toyota">
                            </div>
                            <div>
                                <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                                <input type="text" name="model" id="model" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Camry">
                            </div>
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                <input type="number" name="year" id="year" required min="1900" max="<?php echo date('Y') + 1; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 2023">
                            </div>
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                                <input type="number" name="price" id="price" required step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 500.00">
                            </div>
                            <div class="md:col-span-2 border-t pt-4">
                                <h3 class="text-lg font-semibold mb-3">Pickup Location</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="from_location" class="block text-sm font-medium text-gray-700 mb-1">Location (City/Address)</label>
                                        <input type="text" name="from_location" id="from_location" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., New York City">
                                    </div>
                                    <div>
                                        <label for="from_state" class="block text-sm font-medium text-gray-700 mb-1">State (2-Letter Code)</label>
                                        <input type="text" name="from_state" id="from_state" required maxlength="2" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., NY">
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-2 border-t pt-4">
                                <h3 class="text-lg font-semibold mb-3">Delivery Location</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="to_location" class="block text-sm font-medium text-gray-700 mb-1">Location (City/Address)</label>
                                        <input type="text" name="to_location" id="to_location" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Los Angeles">
                                    </div>
                                    <div>
                                        <label for="to_state" class="block text-sm font-medium text-gray-700 mb-1">State (2-Letter Code)</label>
                                        <input type="text" name="to_state" id="to_state" required maxlength="2" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., CA">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2 border-t pt-4">
                                <h3 class="text-lg font-semibold mb-3">Dates and Contacts</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="pickup_date" class="block text-sm font-medium text-gray-700 mb-1">Pickup Date</label>
                                        <input type="text" name="pickup_date" id="pickup_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="YYYY-MM-DD">
                                    </div>
                                    <div>
                                        <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date (Optional)</label>
                                        <input type="text" name="delivery_date" id="delivery_date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="YYYY-MM-DD">
                                    </div>
                                    <div>
                                        <label for="transporter_phone" class="block text-sm font-medium text-gray-700 mb-1">Transporter Phone (Optional)</label>
                                        <input type="text" name="transporter_phone" id="transporter_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., +1234567890">
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 border-t pt-4">
                                <h3 class="text-lg font-semibold mb-3">Logistics</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="auction" class="block text-sm font-medium text-gray-700 mb-1">Auction/Source</label>
                                        <select name="auction" id="auction" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="" disabled selected>Select an Auction</option>
                                            <?php foreach (fetchAuctions() as $id => $name): ?>
                                                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-1">Warehouse/Destination</label>
                                        <select name="warehouse" id="warehouse" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="" disabled selected>Select a Warehouse</option>
                                            <?php foreach (fetchWarehouses() as $id => $name): ?>
                                                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2 border-t pt-4">
                                <h3 class="text-lg font-semibold mb-3">Flags</h3>
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center">
                                        <input id="appointment" name="appointment" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="appointment" class="ml-2 block text-sm text-gray-700">Appointment Scheduled</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="auction_reservation" name="auction_reservation" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="auction_reservation" class="ml-2 block text-sm text-gray-700">Auction Reserved</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2 border-t pt-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any relevant notes or special instructions."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="window.location.href='index.php'" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                Add Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <?php include 'footer.php'; ?>
    </div>
    
    <script>
        // Check VIN and populate fields from API
        document.getElementById('vin').addEventListener('change', function() {
            const vin = this.value.trim().toUpperCase();
            if (vin.length === 17) {
                // Use a simple fetch/axios for API call
                axios.get('nhtsa_api.php?vin=' + vin)
                    .then(response => {
                        if (response.data.success) {
                            // Fill in form fields based on API response
                            document.getElementById('make').value = response.data.data.make || '';
                            document.getElementById('model').value = response.data.data.model || '';
                            document.getElementById('year').value = response.data.data.year || '';
                        } else {
                            console.error('API Error:', response.data.message);
                            // Optionally show an error notification
                            if (typeof showNotification !== 'undefined') {
                                showNotification('VIN lookup failed: ' + response.data.message, 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                         if (typeof showNotification !== 'undefined') {
                            showNotification('Failed to connect to VIN API.', 'error');
                        }
                    });
            }
        });

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
    </script>
</body>
</html>