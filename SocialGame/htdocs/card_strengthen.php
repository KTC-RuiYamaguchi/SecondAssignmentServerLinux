<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$target_id = $_GET['target_card_id'] ?? null;
if (!$target_id) { 
    echo "強化対象カードが指定されていません。"; 
    exit; 
}

require 'db_connect.php'; // 共通DB接続

try {
    // 強化対象カード取得
    $stmt = $pdo->prepare("
        SELECT uc.*, c.card_name, c.base_hp, c.base_atk, c.base_def, c.thumbnail, c.max_level
        FROM user_cards uc
        JOIN cards c ON uc.card_id = c.card_id
        WHERE uc.id = ? AND uc.user_id = ?
    ");
    $stmt->execute([$target_id, $_SESSION['user_id']]);
    $target_card = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target_card) { 
        echo "対象カードが見つかりません。"; 
        exit; 
    }

    // 素材用カード取得（対象カードを除外）
    $stmt = $pdo->prepare("
        SELECT uc.*, c.card_name, c.material_exp, c.thumbnail, uc.is_favorite
        FROM user_cards uc
        JOIN cards c ON uc.card_id = c.card_id
        WHERE uc.user_id = ? AND uc.id <> ?
        ORDER BY c.card_id
    ");
    $stmt->execute([$_SESSION['user_id'], $target_id]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}

$mode = 'select'; // 強化用モード
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>強化素材選択</title>
<link rel="stylesheet" href="style/card_style.css">
<link rel="stylesheet" href="style/card_button.css">
</head>
<body>

<?php include 'style/footer.php'; ?>
<h1>
    <?= htmlspecialchars($target_card['card_name'], ENT_QUOTES) ?> の強化素材選択
</h1>

<?php if (count($cards) === 0): ?>
    <p>素材にできるカードがありません。</p>
<?php else: ?>
<form method="post" action="card_action.php">
    <input type="hidden" name="action" value="strengthen">
    <input type="hidden" name="target_card_id" value="<?= $target_card['id'] ?>">

    <?php include 'style/card_list_template.php'; ?>

    <div class="card-action-buttons">
    <button type="submit" class="card-action-button">強化実行</button>
    <a href="user_cards_list.php" class="card-action-button back">戻る</a>
</div>

</form>
<?php endif; ?>

</body>
</html>
