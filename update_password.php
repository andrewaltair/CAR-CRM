<?php
require_once 'config.php';

// Создаем новый хэш для пароля "admin"
$newHash = password_hash('admin', PASSWORD_BCRYPT);

// Обновляем хэш в базе данных
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$newHash]);

echo "Password updated successfully. New hash: " . $newHash;
?>