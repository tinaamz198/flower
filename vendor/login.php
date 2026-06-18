<?php
session_start(); 
date_default_timezone_set('Asia/Bishkek');
require_once 'crypto.php';

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_input = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email_input) || empty($password_input)) {
        die("Ошибка: Заполните все поля входа!");
    }

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

       $user_found = null;

        foreach ($users as $user) {
            $decrypted_email = decryptData($user['email']);
            echo "Ввел: " . $email_input . " | Расшифровал: " . $decrypted_email . "<br>";
            if ($decrypted_email === $email_input) {
                $user_found = $user;
                break;
            }
        }
        if (!$user_found) {
            die("Ошибка: Пользователь с таким Email не найден в базе данных!");
        }
        if (password_verify($password_input, $user_found['password_hash'])) {
            
            $_SESSION['user_id'] = $user_found['id'];
            $_SESSION['user_role'] = $user_found['role'];
            
            // Запись в аудит
            $stmtLog = $pdo->prepare("INSERT INTO audit_logs (user_id, action_time, action_type, result) VALUES (?, ?, 'Успешный вход в систему', 'Успешно')");
            $stmtLog->execute([$user_found['id'], date("Y-m-d H:i:s")]);

            echo "<script>
                    alert('Вход выполнен успешно!');
                    window.location.href = '../profile.php';
                  </script>";
            exit();
        } else {
            die("Ошибка: Неверный пароль!");
        }

    } catch (PDOException $e) {
        die("Ошибка Базы Данных: " . $e->getMessage());
    }
} else {
    echo "Доступ запрещен.";
}
?>
