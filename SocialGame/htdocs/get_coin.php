<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false]);
    exit;
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// ランダムで1~10コイン
$added = random_int(1,10);

$stmt = $pdo->prepare("UPDATE users SET user_coins = user_coins + ? WHERE user_id = ?");
if($stmt->execute([$added, $user_id])) {
    // 更新後の所持コイン取得
    $stmt2 = $pdo->prepare("SELECT user_coins FROM users WHERE user_id = ?");
    $stmt2->execute([$user_id]);
    $coins = $stmt2->fetchColumn();

    echo json_encode([
        'success' => true,
        'added' => $added,
        'coins' => $coins
    ]);
} else {
    echo json_encode(['success'=>false]);
}
?>
