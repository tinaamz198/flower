<?php
// 1. Устанавливаем часовой пояс Бишкека
date_default_timezone_set('Asia/Bishkek');

// Настройки Телеграма
define('TELEGRAM_TOKEN', '8868063431:AAGqTljQJvqVyKaq0IUKCsAVUYT3bg_Z83I');
define('TELEGRAM_CHAT_ID', '5722802183');

// Параметры подключения к базе данных MySQL (XAMPP)
$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Принимаем и очищаем данные из формы
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $address = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';
    $bouquetDetails = isset($_POST['bouquet_details']) ? htmlspecialchars(trim($_POST['bouquet_details'])) : '';
    $totalPrice = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;

    if (empty($name) || empty($phone) || empty($address)) {
        die("Ошибка: Заполните все поля!");
    }

    try {
        // 3. Подключаемся к базе данных через PDO
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // --- ВРЕМЕННЫЙ КОСТЫЛЬ ДЛЯ ТЕСТА (Пока нет авторизации) ---
        // Так как пользователь еще не вошел через личный кабинет, мы временно 
        // создадим виртуального пользователя в базе, чтобы заказ не выдавал ошибку ключа.
        $fio_fake = "Тестовый Клиент (" . $name . ")";
        $email_fake = "test_" . time() . "@mail.ru";
        
        $stmtUser = $pdo->prepare("INSERT INTO users (fio_encrypted, email_encrypted, phone_encrypted, password_hash, role) 
                                   VALUES (?, ?, ?, ?, 'user') 
                                   ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
        $stmtUser->execute([$fio_fake, $email_fake, $phone, 'static_hash_or_empty']);
        $userId = $pdo->lastInsertId();

        // Создаем временную запись цветка в базе под этот заказ
        $stmtFlower = $pdo->prepare("INSERT INTO flowers (name, price, description, status, is_used) VALUES (?, ?, ?, 'в наличии', 0)");
        $stmtFlower->execute([$bouquetDetails, $totalPrice, 'Заказ из конструктора']);
        $flowerId = $pdo->lastInsertId();
        // ---------------------------------------------------------

        // 4. ЗАПИСЬ ЗАКАЗА В БАЗУ ДАННЫХ
        $orderDate = date("Y-m-d H:i:s");
        $paymentMethod = "Наличные/Карта";

        $sqlOrder = "INSERT INTO orders (user_id, flower_id, order_date, delivery_address, payment_method, order_status) 
                     VALUES (:user_id, :flower_id, :order_date, :address, :payment, 'Принят')";
        
        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute([
            ':user_id' => $userId,
            ':flower_id' => $flowerId,
            ':order_date' => $orderDate,
            ':address' => $address,
            ':payment' => $paymentMethod
        ]);

        // 5. ЗАПИСЬ В ЖУРНАЛ АУДИТА (Требование ТЗ!)
        $stmtLog = $pdo->prepare("INSERT INTO audit_logs (user_id, action_time, action_type, result) VALUES (?, ?, ?, ?)");
        $stmtLog->execute([$userId, $orderDate, 'Оформление заказа через сайт', 'Успешно']);

        $isSaved = true;

    } catch (PDOException $e) {
        $isSaved = false;
        // Если база упала, пишем ошибку
        die("Ошибка Базы Данных: " . $e->getMessage());
    }

    // 6. ОТПРАВКА В ТЕЛЕГРАМ (Оставляем твою рабочую логику)
    $tgMessage = "🔔 *НОВЫЙ ЗАКАЗ В БАЗЕ ДАННЫХ MySQL*\n\n";
    $tgMessage .= "👤 *Клиент:* " . $name . "\n";
    $tgMessage .= "📞 *Телефон:* " . $phone . "\n";
    $tgMessage .= "📍 *Адрес:* " . $address . "\n";
    $tgMessage .= "-------------------------\n";
    $tgMessage .= $bouquetDetails . "\n";
    $tgMessage .= "-------------------------\n";
    $tgMessage .= "💰 *Итого:* " . $totalPrice . " сом\n\n";
    $tgMessage .= "✅ Запись успешно добавлена в таблицу `orders`.";

    $url = "https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $tgMessage,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);

    // 7. Выводим пользователю страницу успеха
    if ($isSaved) {
        echo "
        <!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <title>Заказ принят</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f7f4; text-align: center; padding-top: 100px; color: #333; }
                .success-box { background: white; padding: 40px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
                h1 { color: #47824b; }
                p { font-size: 16px; margin: 10px 0; }
                a { display: inline-block; margin-top: 20px; background: #47824b; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                a:hover { background: #3b6b3e; }
            </style>
        </head>
        <body>
            <div class='success-box'>
                <h1>🌸 Заказ успешно сохранен в БД!</h1>
                <p>Данные успешно записаны в таблицы MySQL и отправлены администратору.</p>
                <p>Сумма к оплате: <strong>$totalPrice сом</strong></p>
                <a href='index.html'>На главную страницу</a>
            </div>
        </body>
        </html>
        ";
    }
} else {
    echo "Доступ запрещен.";
}
?>
