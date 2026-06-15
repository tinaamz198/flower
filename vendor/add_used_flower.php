<?php
session_start();
date_default_timezone_set('Asia/Bishkek');

if (!isset($_SESSION['user_id'])) {
    die("Доступ запрещен.");
}

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    
    // Обработка загрузки картинки и конвертация в бинарный BLOB (требование ТЗ)
    $imageBlob = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageBlob = file_get_contents($_FILES['image']['tmp_name']);
    }

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Вставляем букет со статусом 'на модерации' и флагом is_used = 1
        $sql = "INSERT INTO flowers (category_id, name, price, description, image_blob, is_used, status, seller_id) 
                VALUES (3, ?, ?, ?, ?, 1, 'на модерации', ?)"; // 3 - Декоративные/Другие
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $price, $description, $imageBlob, $_SESSION['user_id']]);

        // Пишем в аудит
        $stmtLog = $pdo->prepare("INSERT INTO audit_logs (user_id, action_time, action_type, result) VALUES (?, ?, 'Отправка букета б/у на модерацию', 'Успешно')");
        $stmtLog->execute([$_SESSION['SESSION_user_id'], date("Y-m-d H:i:s")]);

        echo "<script>alert('Букет успешно отправлен на модерацию!'); window.location.href='../profile.php';</script>";

    } catch (PDOException $e) {
        die("Ошибка добавления: " . $e->getMessage());
    }
}
?>
