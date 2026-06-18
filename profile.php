<?php
session_start();
require_once 'vendor/crypto.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $fio_decrypted = decryptData($user['full_name']);
    $email_decrypted = decryptData($user['email']);
} else {
    header("Location: auth.html"); 
    exit();
}

    $stmtFlowers = $pdo->prepare("SELECT * FROM flowers WHERE seller_id = ? ORDER BY id DESC");
    $stmtFlowers->execute([$_SESSION['user_id']]);
    $myFlowers = $stmtFlowers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет - Цветочный Окии</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f4; margin: 0; padding: 0; }
        header { position: fixed; top: 0; width: 100%; background: #47824b; color: white; padding: 15px 0; text-align: center; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .container { max-width: 1000px; margin: 100px auto 50px auto; padding: 20px; display: flex; gap: 30px; }
        .sidebar, .main-content { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .sidebar { flex: 1; height: fit-content; }
        .main-content { flex: 2; }
        h3 { color: #47824b; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        .btn { background: #47824b; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; }
        .logout-btn { background: #d9534f; margin-top: 15px; text-align: center; display: block; color: white; text-decoration: none; padding: 10px; border-radius: 6px; }
        .flower-card { border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .btn-sm { padding: 5px 10px; font-size: 12px; width: auto; }
        .btn-danger { background: #d9534f; }
    </style>
</head>
<body>
    <header><a href="index.php" style="color:white; text-decoration:none;">Главная</a></header>
    <div class="container">
        <div class="sidebar">
            <h3>👤 Мой профиль</h3>
            <p><strong>ФИО:</strong> <?php echo htmlspecialchars($fio_decrypted); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email_decrypted); ?></p>
            <a href="vendor/logout.php" class="logout-btn">Выйти из аккаунта</a>
            <hr>
            <h3>Редактировать профиль</h3>
            <form action="vendor/update_profile.php" method="POST">
                <div class="form-group"><label>ФИО:</label><input type="text" name="new_fio" value="<?php echo htmlspecialchars($fio_decrypted); ?>" required></div>
                <div class="form-group"><label>Email:</label><input type="email" name="new_email" value="<?php echo htmlspecialchars($email_decrypted); ?>" required></div>
                <div class="form-group">
                    <label>Новый пароль:</label>
                    <input type="password" name="new_password" placeholder="******">
                </div>
                <button type="submit" class="btn">Сохранить данные</button>
            </form>
        </div>
        <div class="main-content">
            <h3>🌸 Добавить букет</h3>
            <form action="vendor/add_used_flower.php" method="POST" enctype="multipart/form-data">
                <div class="form-group"><input type="text" name="name" placeholder="Название" required></div>
                <div class="form-group"><input type="number" name="price" placeholder="Цена" required></div>
                <div class="form-group"><textarea name="description" placeholder="Описание"></textarea></div>
                <div class="form-group"><input type="file" name="image" required></div>
                <button type="submit" class="btn">Отправить на модерацию</button>
            </form>
            <h3>📋 Мои объявления</h3>
            <?php foreach ($myFlowers as $flower): ?>
                <div class="flower-card">
                    <form action="vendor/manage_flowers.php" method="POST">
                        <input type="hidden" name="flower_id" value="<?php echo $flower['id']; ?>">
                        <strong>Название:</strong> <?php echo htmlspecialchars($flower['name']); ?><br>
                        <strong>Цена:</strong> <input type="number" name="price" value="<?php echo $flower['price']; ?>" style="width: 80px;"> сом<br>
                        <strong>Статус:</strong> <?php echo $flower['status']; ?><br>
                        <textarea name="description" style="width: 100%; margin: 5px 0;"><?php echo htmlspecialchars($flower['description']); ?></textarea>
                        <button type="submit" name="action" value="update" class="btn btn-sm">Сохранить</button>
                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
