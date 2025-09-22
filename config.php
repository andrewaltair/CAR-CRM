<?php
// Включение логирования ошибок
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
ini_set('display_errors', 1); // Включаем отображение ошибок для отладки
error_reporting(E_ALL); // Выводим все типы ошибок

// Установка временной зоны
date_default_timezone_set('UTC');

// **Специфические настройки сессий, которые могут вызывать ERR_TOO_MANY_REDIRECTS, удалены.**
// Мы полагаемся на настройки PHP по умолчанию или .user.ini.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Базовые настройки
define('SITE_NAME', 'Vehicle Transportation Management');
define('SITE_URL', 'https://gemini.trendingnow.ge');

// Подключение к базе данных (PDO используется, т.к. он более гибок и поддерживает Prepared Statements)
$host = 'localhost';
$db   = 'trending_gemini';
$user = 'trending_ai'; // Замените на ваше имя пользователя БД
$pass = '455032altaiR%'; // Замените на ваш пароль БД
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Критическая ошибка: Невозможно подключиться к БД
    error_log("Database connection error: " . $e->getMessage());
    // Отображаем универсальное сообщение об ошибке, чтобы избежать утечки данных БД
    http_response_code(500);
    die('Database connection failed. Please try again later.');
}

/**
 * Генерирует и сохраняет CSRF-токен в сессии.
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверяет CSRF-токен.
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


/**
 * Проверяет, авторизован ли пользователь. Если нет, перенаправляет на страницу входа.
 * @return void
 */
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "You must log in to access this page.";
        // Перенаправляем на страницу входа
        header('Location: login.php');
        exit();
    }
}

/**
 * Проверяет, имеет ли авторизованный пользователь одну из разрешенных ролей.
 *
 * @param array $allowedRoles Массив разрешенных ролей.
 * @return void
 */
function checkRole(array $allowedRoles) {
    $current_role = $_SESSION['role'] ?? 'guest';
    if (!in_array($current_role, $allowedRoles)) {
        // Устанавливаем ошибку и перенаправляем на главную страницу
        $_SESSION['error'] = "Access denied. You do not have the required role.";
        header('Location: index.php');
        exit();
    }
}

/**
 * Fetches all auction names from the database.
 * @return array
 */
function getAuctions() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT name FROM auctions ORDER BY name");
        $stmt->execute();
        // Return an array of names (strings)
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        error_log("Error fetching auctions: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetches all warehouse names from the database.
 * @return array
 */
function getWarehouses() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT name FROM warehouses ORDER BY name");
        $stmt->execute();
        // Return an array of names (strings)
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        error_log("Error fetching warehouses: " . $e->getMessage());
        return [];
    }
}

/**
 * Safely deletes a file if it exists and is writable.
 * @param string $path Full path to the file.
 * @return bool
 */
function deleteFileIfExist(string $path): bool {
    if (file_exists($path) && is_writable($path)) {
        return unlink($path);
    }
    return true; // Return true if file doesn't exist or is not writable (to continue the script)
}