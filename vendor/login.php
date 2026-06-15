<?php
session_start(); 
date_default_timezone_set('Asia/Bishkek');

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Очищаем ввод от случайных пробелов
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        die("Ошибка: Заполните все поля входа!");
    }

    $secret_key = "MySecretKey_Okii";
    
    // Функция шифрования (должна точь-в-точь совпадать с register.php)
    function encryptData($data, $key) {
        $method = "AES-256-CBC";
        $iv = "1234567890123456"; // Статический IV
        return openssl_encrypt($data, $method, $key, 0, $iv);
    }
    
    $email_enc = encryptData($email, $secret_key);

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ищем пользователя по зашифрованному email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email_encrypted = ?");
        $stmt->execute([$email_enc]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Проверяем результат
        if (!$user) {
            // Если не нашли, давай проверим, может пароли или почта не совпали
            die("Ошибка: Пользователь с таким Email не найден в базе данных! (Возможно, дело в шифровании)");
        }

        if (password_verify($password, $user['password_hash'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Запись в аудит
            $stmtLog = $pdo->prepare("INSERT INTO audit_logs (user_id, action_time, action_type, result) VALUES (?, ?, 'Успешный вход в систему', 'Успешно')");
            $stmtLog->execute([$user['id'], date("Y-m-d H:i:s")]);

            // Перенаправляем в личный кабинет (пока файла profile.php нет, создадим заглушку прямо тут)
            echo "
            <script>
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