<?php
require_once '../config.php';
checkAuth();
checkRole(['admin']);

$page_title = "Admin Panel";
include '../header.php';
?>

<main class="container mx-auto px-4 py-6 flex-grow">
    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-2">Admin Panel</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <a href="manage_auctions.php" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 p-6 border-l-4 border-blue-500">
            <div class="flex items-center space-x-4">
                <i data-feather="list" class="w-8 h-8 text-blue-500"></i>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Manage Auctions</h3>
                    <p class="text-gray-500 text-sm">Add, edit, or remove auction names.</p>
                </div>
            </div>
        </a>

        <a href="manage_warehouses.php" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 p-6 border-l-4 border-green-500">
            <div class="flex items-center space-x-4">
                <i data-feather="truck" class="w-8 h-8 text-green-500"></i>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Manage Warehouses</h3>
                    <p class="text-gray-500 text-sm">Add, edit, or remove warehouse locations.</p>
                </div>
            </div>
        </a>

        <div class="block bg-white rounded-lg shadow-lg p-6 border-l-4 border-gray-400">
            <div class="flex items-center space-x-4">
                <i data-feather="users" class="w-8 h-8 text-gray-400"></i>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Manage Users</h3>
                    <p class="text-gray-500 text-sm">Control user roles and accounts.</p>
                </div>
            </div>
        </div>
        
    </div>

</main>

<?php include '../footer.php'; ?>
<script>
    feather.replace();
</script>