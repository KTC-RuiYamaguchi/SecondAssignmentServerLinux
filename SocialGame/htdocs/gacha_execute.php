<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php'; // DB接続

$user_id = $_SESSION['user_id'];
$gacha_id = $_POST['gacha_id'] ?? null;
$times = intval($_POST['times'] ?? 1); // デフォルト1回

if (!$user_id || !$gacha_id) {
    echo "ユーザーまたはガチャが未指定です。";
    exit;
}

// 1～10回まで制限
$times = max(1, min($times, 10));

// -----------------------
// 1. ユーザー情報取得
// -----------------------
$stmt = $pdo->prepare("SELECT user_coins FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ユーザーが存在しません。";
    exit;
}

// -----------------------
// 2. ガチャ情報取得
// -----------------------
$stmt = $pdo->prepare("SELECT gacha_name, cost FROM gachas WHERE gacha_id = ? AND is_active = 1");
$stmt->execute([$gacha_id]);
$gacha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gacha) {
    echo "ガチャが存在しないか非アクティブです。";
    exit;
}

// 合計コスト
$total_cost = $gacha['cost'] * $times;

// -----------------------
// 3. コインチェック
// -----------------------
if ($user['user_coins'] < $total_cost) {
    echo "コインが不足しています。";
    exit;
}

// -----------------------
// 4. ガチャ対象カード取得
// -----------------------
$stmt = $pdo->prepare("
    SELECT gi.card_id, gi.weight, c.card_name, c.thumbnail 
    FROM gacha_items gi
    JOIN cards c ON gi.card_id = c.card_id
    WHERE gi.gacha_id = ?
");
$stmt->execute([$gacha_id]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cards) {
    echo "ガチャにカードが設定されていません。";
    exit;
}

// -----------------------
// 5. 重み付きランダム関数
// -----------------------
function weighted_random($items) {
    $totalWeight = array_sum(array_column($items, 'weight'));
    $rand = mt_rand(1, $totalWeight);
    foreach ($items as $item) {
        $rand -= $item['weight'];
        if ($rand <= 0) return $item;
    }
    return end($items); // 万一
}

// -----------------------
// 6. トランザクション開始
// -----------------------
$pdo->beginTransaction();
try {
    // コイン減算
    $stmt = $pdo->prepare("UPDATE users SET user_coins = user_coins - ? WHERE user_id = ?");
    $stmt->execute([$total_cost, $user_id]);

    $won_cards = [];
    $insertCard = $pdo->prepare("INSERT INTO user_cards (user_id, card_id, level, exp, is_favorite) VALUES (?, ?, 1, 0, FALSE)");
    $insertLog  = $pdo->prepare("INSERT INTO gacha_logs (user_id, gacha_id, card_id, coins_spent) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < $times; $i++) {
        $won_card = weighted_random($cards);

        $insertCard->execute([$user_id, $won_card['card_id']]);
        $insertLog->execute([$user_id, $gacha_id, $won_card['card_id'], $gacha['cost']]);

        $won_cards[] = [
            'card_id' => $won_card['card_id'],
            'card_name' => $won_card['card_name'],
            'thumbnail' => $won_card['thumbnail']
        ];
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ガチャ処理中にエラーが発生しました: " . $e->getMessage();
    exit;
}

// -----------------------
// 7. 結果をセッションに格納
// -----------------------
$_SESSION['gacha_result'] = [
    'gacha_id'           => $gacha_id,
    'gacha_name'         => $gacha['gacha_name'],
    'gacha_cost'         => $gacha['cost'],
    'times'              => $times,
    'cards'              => $won_cards,
    'user_coins_before'  => $user['user_coins'],
    'user_coins_after'   => $user['user_coins'] - $total_cost
];

// -----------------------
// 8. リダイレクト
// -----------------------
header("Location: gacha_result.php");
exit;
