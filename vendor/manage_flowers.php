<?php
session_start();
if (!isset($_SESSION['user_id'])) exit("Доступ запрещен");

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $flower_id = $_POST['flower_id'];
    $stmt = $pdo->prepare("SELECT id FROM flowers WHERE id = ? AND seller_id = ?");
    $stmt->execute([$flower_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        if ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM flowers WHERE id = ?");
            $stmt->execute([$flower_id]);
        } elseif ($action == 'update') {
            $price = $_POST['price'];
            $desc = $_POST['description'];
            $stmt = $pdo->prepare("UPDATE flowers SET price = ?, description = ? WHERE id = ?");
            $stmt->execute([$price, $desc, $flower_id]);
        }
    }
}
header("Location: ../profile.php");
?>
