<?php
session_start();
date_default_timezone_set('Asia/Bishkek');

// Если пользователь не вошел, выгоняем его на страницу авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

$secret_key = "MySecretKey_Okii";
function decryptData($encrypted_data, $key) {
    $method = "AES-256-CBC";
    $iv = "1234567890123456";
    return openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Получаем данные пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $fio = decryptData($user['fio_encrypted'], $secret_key);
    $email = decryptData($user['email_encrypted'], $secret_key);

    // 2. Получаем цветы, которые этот пользователь выставил на продажу (б/у)
    $stmtFlowers = $pdo->prepare("SELECT * FROM flowers WHERE seller_id = ? ORDER BY id DESC");
    $stmtFlowers->execute([$_SESSION['user_id']]);
    $myFlowers = $stmtFlowers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка загрузки профиля: " . $e->getMessage());
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
        /* Фиксированная шапка как в ТЗ сайта */
        header { position: fixed; top: 0; width: 100%; background: #47824b; color: white; padding: 15px 0; text-align: center; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        header a { color: white; text-decoration: none; margin: 0 15px; font-weight: bold; }
        
        .container { max-width: 1000px; margin: 100px auto 50px auto; padding: 20px; display: flex; gap: 30px; }
        .sidebar { flex: 1; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); height: fit-content; }
        .main-content { flex: 2; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        h3 { color: #47824b; border-bottom: 2px solid #f0f0f0; padding-bottom: 100px; padding-bottom: 10px; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        
        .btn { background: #47824b; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn:hover { background: #3b6b3e; }
        .logout-btn { background: #d9534f; margin-top: 15px; text-align: center; display: block; color: white; text-decoration: none; padding: 10px; border-radius: 6px; font-weight: bold; }
        
        .flower-card { display: flex; align-items: center; gap: 15px; border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-moderation { background: #f0ad4e; color: white; }
        .status-approved { background: #5cb85c; color: white; }
    </style>
</head>
<body>

    <header>
        <a href="index.php">Главная</a>
        <a href="profile.php" style="border-bottom: 2px solid white;">Личный кабинет</a>
    </header>

    <div class="container">
        <div class="sidebar">
            <h3>👤 Мой профиль</h3>
            <p><strong>ФИО:</strong> <?php echo $fio; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Статус:</strong> Клиент</p>
            <a href="vendor/logout.php" class="logout-btn">Выйти из аккаунта</a>
        </div>

        <div class="main-content">
            <h3>🌸 Функция «Вторые руки» — Продать свой букет</h3>
            <p style="color: #666; font-size: 14px;">Вам подарили букет, и вы хотите подарить ему вторую жизнь? Выставите его на продажу по сниженной цене. После модерации админом он появится на главной странице сайта!</p>
            
            <form action="vendor/add_used_flower.php" method="POST" enctype="multipart/form-data" style="margin-bottom: 40px;">
                <div class="form-group">
                    <label>Название букета / Состав</label>
                    <input type="text" name="name" required placeholder="Например: Букет из 21 красной розы">
                </div>
                <div class="form-group">
                    <label>Желаемая цена (сом)</label>
                    <input type="number" name="price" required placeholder="500">
                </div>
                <div class="form-group">
                    <label>Описание состояния / Сколько дней букету</label>
                    <textarea name="description" rows="3" required placeholder="Подарили вчера, цветы свежие, стоят в прохладе."></textarea>
                </div>
                <div class="form-group">
                    <label>Фотография букета (.jpg / .png)</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <button type="submit" class="btn">Отправить на модерацию</button>
            </form>

            <h3>📋 Мои объявления</h3>
            <?php if (empty($myFlowers)): ?>
                <p style="color: #999;">Вы еще не выставляли букеты на продажу.</p>
            <?php else: ?>
                <?php foreach ($myFlowers as $flower): ?>
                    <div class="flower-card">
                        <div>
                            <strong><?php echo $flower['name']; ?></strong> — <?php echo $flower['price']; ?> сом
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #666;"><?php echo $flower['description']; ?></p>
                        </div>
                        <div style="margin-left: auto;">
                            <?php if ($flower['status'] == 'на модерации'): ?>
                                <span class="status status-moderation">На модерации</span>
                            <?php else: ?>
                                <span class="status status-approved">Одобрен</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
