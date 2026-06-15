<?php
date_default_timezone_set('Asia/Bishkek');

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fio = isset($_POST['fio']) ? htmlspecialchars(trim($_POST['fio'])) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($fio) || empty($phone) || empty($email) || empty($password)) {
        die("Ошибка: Заполните все поля!");
    }

    // Хэшируем пароль встроенной надежной функцией PHP (SHA-256/bcrypt)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // ПО ТЗ: Персональные данные должны шифроваться (например, AES).
    // Для ЛР мы можем использовать простую функцию шифрования строк на основе ключа:
    $secret_key = "MySecretKey_Okii";
    
    // Функция шифрования AES-256
    function encryptData($data, $key) {
        $method = "AES-256-CBC";
        $iv_length = openssl_cipher_iv_length($method);
        $iv = "1234567890123456"; // Статический IV для простоты ЛР
        return openssl_encrypt($data, $method, $key, 0, $iv);
    }

    $fio_enc = encryptData($fio, $secret_key);
    $email_enc = encryptData($email, $secret_key);
    $phone_enc = encryptData($phone, $secret_key);

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Проверяем, существует ли уже такой email
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email_encrypted = ?");
        $stmtCheck->execute([$email_enc]);
        if ($stmtCheck->fetch()) {
            die("Ошибка: Пользователь с таким Email уже зарегистрирован!");
        }

        // Записываем в базу данных
        $sql = "INSERT INTO users (fio_encrypted, email_encrypted, phone_encrypted, password_hash, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fio_enc, $email_enc, $phone_enc, $password_hash]);
        $new_user_id = $pdo->lastInsertId();

        // Пишем в журнал аудита
        $stmtLog = $pdo->prepare("INSERT INTO audit_logs (user_id, action_time, action_type, result) VALUES (?, ?, 'Регистрация нового пользователя', 'Успешно')");
        $stmtLog->execute([$new_user_id, date("Y-m-d H:i:s")]);

        // Перенаправляем на страницу успешного входа
        echo "<script>alert('Регистрация успешна! Войдите под своими данными.'); window.location.href='../auth.html';</script>";

    } catch (PDOException $e) {
        die("Ошибка сохранения: " . $e->getMessage());
    }
}
?>
