<?php
date_default_timezone_set('Asia/Bishkek');
require_once 'crypto.php';

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fio = htmlspecialchars(trim($_POST['fio']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($fio) || empty($phone) || empty($email) || empty($password)) {
        die("Ошибка: Заполните все поля!");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmtCheck = $pdo->query("SELECT email FROM users");
        $existing_users = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

        foreach ($existing_users as $row) {
    if (decryptData($row['email']) === $email) {
        die("Ошибка: Пользователь с таким Email уже зарегистрирован!");
    }
}
// ------------------------
        if ($stmtCheck->fetch()) {
            die("Ошибка: Пользователь с таким Email уже зарегистрирован!");
        }
        $sql = "INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([encryptData($fio), encryptData($email), $phone, $password_hash]);

        echo "Регистрация прошла успешно! <a href='../auth.html'>Войти</a>";

    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}
?>