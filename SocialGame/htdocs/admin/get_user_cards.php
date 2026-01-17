<?php
session_start();
if (empty($_SESSION['is_admin'])) exit;

require '../db_connect.php';

$user_id = intval($_GET['user_id']);
$stmt = $pdo->prepare("
    SELECT c.card_name, c.thumbnail 
    FROM user_cards uc
    JOIN cards c ON uc.card_id = c.card_id
    WHERE uc.user_id = ?
");
$stmt->execute([$user_id]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($cards);
