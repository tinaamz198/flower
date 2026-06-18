<?php
session_start();
require_once 'crypto.php'; 
if (!isset($_SESSION['user_id'])) exit("Доступ запрещен");

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    
    // Шифруем данные перед записью
    $new_fio = encryptData(trim($_POST['new_fio']));
    $new_email = encryptData(trim($_POST['new_email']));
    $new_password = $_POST['new_password'];

    if (!empty($new_password)) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$new_fio, $new_email, $hash, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->execute([$new_fio, $new_email, $_SESSION['user_id']]);
    }

    echo "<script>alert('Данные успешно обновлены!'); window.location.href='../profile.php';</script>";
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>