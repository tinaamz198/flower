<?php
// Настройки Телеграма (Вставь сюда свои данные!)
define('TELEGRAM_TOKEN', '8868063431:AAGqTljQJvqVyKaq0IUKCsAVUYT3bg_Z83I');
define('TELEGRAM_CHAT_ID', '5722802183');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Принимаем данные из формы
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $address = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';
    $bouquetDetails = isset($_POST['bouquet_details']) ? htmlspecialchars(trim($_POST['bouquet_details'])) : '';
    $totalPrice = isset($_POST['total_price']) ? htmlspecialchars(trim($_POST['total_price'])) : '';

    if (empty($name) || empty($phone) || empty($address)) {
        die("Ошибка: Заполните все поля!");
    }
    date_default_timezone_set('Asia/Bishkek');
    // 2. Формируем текст для TXT-файла
    $fileContent = "=== НОВЫЙ ЗАКАЗ ЦВЕТОВ ===\n";
    $fileContent .= "Дата и время: " . date("Y-m-d H:i") . "\n";
    $fileContent .= "Имя клиента: " . $name . "\n";
    $fileContent .= "Телефон: " . $phone . "\n";
    $fileContent .= "Адрес доставки: " . $address . "\n";
    $fileContent .= "-------------------------\n";
    $fileContent .= $bouquetDetails . "\n";
    $fileContent .= "-------------------------\n";
    $fileContent .= "ИТОГОВАЯ СТОИМОСТЬ: " . $totalPrice . " сом\n";

    // 3. Сохраняем в папку order на компьютере (Проверь имя папки: order или orders!)
    $fileName = "order/order_" . time() . "_" . rand(100, 999) . ".txt";
    
    // ВОЗВРАЩАЕМ СТЕРТУЮ СТРОЧКУ НА МЕСТО:
    $isSaved = file_put_contents($fileName, $fileContent);

    // 4. ОТПРАВКА В ТЕЛЕГРАМ ИЗ PHP (БЭКЕНД)
    $tgMessage = "🔔 *НОВЫЙ ЗАКАЗ НА СЕРВЕРЕ*\n\n";
    $tgMessage .= "👤 *Клиент:* " . $name . "\n";
    $tgMessage .= "📞 *Телефон:* " . $phone . "\n";
    $tgMessage .= "📍 *Адрес:* " . $address . "\n";
    $tgMessage .= "-------------------------\n";
    $tgMessage .= $bouquetDetails . "\n";
    $tgMessage .= "-------------------------\n";
    $tgMessage .= "💰 *Итого:* " . $totalPrice . " сом\n\n";
    $tgMessage .= "📁 Файл сохранен как: `" . $fileName . "`";

    // Отправляем запрос на сервера Telegram через cURL
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключаем проверку SSL для локалки
    $tgResponse = curl_exec($ch);
    curl_close($ch);

    // 5. Выводим пользователю страницу успеха
    if ($isSaved !== false) {
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
                <h1>🌸 Заказ успешно оформлен!</h1>
                <p>Данные записаны в базу сервера и отправлены администратору в Telegram.</p>
                <p>Сумма к оплате: <strong>$totalPrice сом</strong></p>
                <a href='index.html'>На главную страницу</a>
            </div>
        </body>
        </html>
        ";
    } else {
        echo "Ошибка: Не удалось сохранить файл заказа. Проверьте, существует ли папка.";
    }
} else {
    echo "Доступ запрещен.";
}
?>
