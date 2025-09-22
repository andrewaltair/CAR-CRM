<?php
require_once 'config.php';
// Уничтожаем сессию
session_destroy();
// Перенаправляем на страницу входа
header('Location: login.php');
exit();
?>