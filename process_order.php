<?php
date_default_timezone_set('Asia/Bishkek');
require_once 'vendor/crypto.php';

define('TELEGRAM_TOKEN', '8868063431:AAGqTljQJvqVyKaq0IUKCsAVUYT3bg_Z83I');
define('TELEGRAM_CHAT_ID', '5722802183');
$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $address = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';
    $bouquetDetails = isset($_POST['bouquet_details']) ? htmlspecialchars(trim($_POST['bouquet_details'])) : '';
    $totalPrice = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;

    if (empty($name) || empty($phone) || empty($address)) {
        die("Ошибка: Заполните все поля!");
    }

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        session_start();
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

        // Вставка с учетом новых полей (is_ready=0 для заказов, kanban_status=todo)
        $stmtFlower = $pdo->prepare("INSERT INTO flowers (name, price, description, status, is_used, is_ready, kanban_status) 
                                     VALUES (?, ?, ?, 'на модерации', 0, 0, 'todo')");
        $stmtFlower->execute([$bouquetDetails, $totalPrice, 'Заказ из конструктора']);
        $flowerId = $pdo->lastInsertId();

        $sqlOrder = "INSERT INTO orders (user_id, flower_id, order_date, delivery_address, payment_method, order_status) 
                      VALUES (:user_id, :flower_id, NOW(), :address, 'Наличные', 'Принят')";
        
        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute([':user_id' => $userId, ':flower_id' => $flowerId, ':address' => $address]);

        $stmtLog = $pdo->prepare("INSERT INTO audit_logs (user_id, action_time, action_type, result) VALUES (?, NOW(), ?, 'Успешно')");
        $stmtLog->execute([$userId, 'Оформление заказа через сайт']);
    } catch (PDOException $e) {
        die("Ошибка Базы Данных: " . $e->getMessage());
    }

    $tgMessage = "🔔 *НОВЫЙ ЗАКАЗ*\n👤 Клиент: $name\n💰 Сумма: $totalPrice сом\n✅ Добавлен в Kanban (Todo)";
    $url = "https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/sendMessage";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['chat_id' => TELEGRAM_CHAT_ID, 'text' => $tgMessage, 'parse_mode' => 'Markdown']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css"> 
    <title>Ваш заказ принят</title>
    <style>
        .container { max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .btn { background: #47824b; color: #fff; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌸 Спасибо, <?php echo $name; ?>!</h1>
        <p>Ваш заказ на сумму <strong><?php echo $totalPrice; ?> сом</strong> успешно принят.</p>
        <p>Наш флорист уже получил уведомление и приступает к сборке вашего букета.</p>
        <a href="index.php" class="btn">Вернуться к конструктору</a>
    </div>
</body>
</html>
<?php } ?>
