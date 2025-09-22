<?php
require_once '../config.php';
checkAuth();
checkRole(['admin']);

$page_title = "Manage Warehouses";

// Handle form submissions (CRUD)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $id = $_POST['id'] ?? null;

    if (empty($name)) {
        $_SESSION['error'] = "Warehouse name cannot be empty.";
        header('Location: manage_warehouses.php');
        exit();
    }

    try {
        if ($action == 'add') {
            $stmt = $pdo->prepare("INSERT INTO warehouses (name, location) VALUES (?, ?)");
            $stmt->execute([$name, $location]);
            $_SESSION['success'] = "Warehouse '$name' added successfully.";
        } elseif ($action == 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE warehouses SET name = ?, location = ? WHERE id = ?");
            $stmt->execute([$name, $location, $id]);
            $_SESSION['success'] = "Warehouse '$name' updated successfully.";
        } elseif ($action == 'delete' && $id) {
            $stmt = $pdo->prepare("DELETE FROM warehouses WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Warehouse deleted successfully.";
        }
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Error: Warehouse name '$name' already exists.";
        } else {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
    header('Location: manage_warehouses.php');
    exit();
}

// Fetch all warehouses
try {
    $warehouses = $pdo->query("SELECT * FROM warehouses ORDER BY name")->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching list: " . $e->getMessage();
    $warehouses = [];
}

include '../header.php';
?>

<main class="container mx-auto px-4 py-6 flex-grow">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Manage Warehouses</h2>
    
    <a href="index.php" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
        <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i> Back to Admin Panel
    </a>

    <div class="bg-white rounded-lg shadow p-6 mb-8 border-t-4 border-green-500">
        <h3 class="text-xl font-semibold mb-4">Add New Warehouse</h3>
        <form method="POST" id="warehouseForm">
            <input type="hidden" name="action" value="add" id="action">
            <input type="hidden" name="id" value="" id="warehouseId">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                </div>
                <div class="md:col-span-2">
                    <label for="location" class="block text-sm font-medium text-gray-700">Location (City, State)</label>
                    <input type="text" name="location" id="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium" id="submitButton">
                    Add Warehouse
                </button>
                <button type="button" onclick="resetForm()" class="ml-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition hidden" id="cancelButton">
                    Cancel Edit
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="min-w-full divide-y divide-gray-200">
            <div class="py-2 align-middle inline-block min-w-full">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($warehouses as $warehouse): ?>
                        <tr data-id="<?php echo $warehouse['id']; ?>" data-name="<?php echo htmlspecialchars($warehouse['name']); ?>" data-location="<?php echo htmlspecialchars($warehouse['location']); ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($warehouse['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($warehouse['location']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editItem(this)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                                <button onclick="deleteItem(<?php echo $warehouse['id']; ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../footer.php'; ?>
<script>
    feather.replace();

    function editItem(button) {
        const row = button.closest('tr');
        const id = row.dataset.id;
        const name = row.dataset.name;
        const location = row.dataset.location;

        document.getElementById('action').value = 'edit';
        document.getElementById('warehouseId').value = id;
        document.getElementById('name').value = name;
        document.getElementById('location').value = location;
        document.getElementById('submitButton').textContent = 'Save Changes';
        document.getElementById('cancelButton').classList.remove('hidden');
        document.querySelector('h3').textContent = 'Edit Warehouse: ' + name;

        document.getElementById('name').focus();
    }

    function resetForm() {
        document.getElementById('action').value = 'add';
        document.getElementById('warehouseId').value = '';
        document.getElementById('name').value = '';
        document.getElementById('location').value = '';
        document.getElementById('submitButton').textContent = 'Add Warehouse';
        document.getElementById('cancelButton').classList.add('hidden');
        document.querySelector('h3').textContent = 'Add New Warehouse';
    }

    function deleteItem(id) {
        if (confirm('Are you sure you want to delete this warehouse?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'manage_warehouses.php';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>