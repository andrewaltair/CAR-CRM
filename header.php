<?php
// Проверяем, авторизован ли пользователь. Эта функция должна быть определена в config.php
if (!function_exists('checkAuth')) {
    // Это критическая ошибка, если config.php не подключен, но здесь мы полагаемся на то,
    // что файл, который вызывает header.php, уже выполнил require_once 'config.php';
}

// **УДАЛЕНА ПРОВЕРКА АУТЕНТИФИКАЦИИ И ПЕРЕНАПРАВЛЕНИЕ,
// ПОСКОЛЬКУ ОНА ДОЛЖНА ВЫЗЫВАТЬСЯ ОДИН РАЗ В НАЧАЛЕ ФАЙЛА ЧЕРЕЗ checkAuth().**
// if (!isset($_SESSION['user_id'])) { ... }


$current_role = $_SESSION['role'] ?? 'guest';
$is_manager_or_admin = in_array($current_role, ['admin', 'vehicle_manager']);
$page_title = isset($page_title) ? $page_title : 'Vehicle Transportation Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-pending {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .scrollable-table {
            max-height: calc(100vh - 180px);
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow-md sticky top-0 z-40">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex-shrink-0">
                        <a href="index.php" class="text-2xl font-bold text-gray-900"><?php echo SITE_NAME; ?></a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-600 text-sm">Hello, **<?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>** (<?php echo htmlspecialchars($current_role); ?>)</span>
                        
                        <?php if ($current_role === 'admin'): ?>
                        <a href="admin/index.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition filter-btn text-sm">
                            Admin Panel
                        </a>
                        <?php endif; ?>

                        <?php if ($is_manager_or_admin): ?>
                        <a href="add_vehicle.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition filter-btn text-sm">
                            Add New Vehicle
                        </a>
                        <?php endif; ?>
                        
                        <button onclick="document.getElementById('logoutModal').classList.remove('hidden')" class="text-sm bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-white transition">
                            Exit
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center" aria-modal="true" role="dialog">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm">
                <h3 class="text-lg font-bold mb-4">Confirm Logout</h3>
                <p class="mb-6">Are you sure you want to log out?</p>
                <div class="flex justify-end space-x-4">
                    <button onclick="document.getElementById('logoutModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        Logout
                    </a>
                </div>
            </div>
        </div>