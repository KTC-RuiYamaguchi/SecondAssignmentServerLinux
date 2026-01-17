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

require 'db_connect.php';

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

    // 素材用カード取得
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

$mode = 'select';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>強化素材選択</title>

<link rel="stylesheet" href="style/card_style.css">
<link rel="stylesheet" href="style/card_button.css">

<style>
/* =========================
   全体レイアウト
========================= */
body {
    margin: 0;
    padding-bottom: 110px; /* fixed footer 分 */
    background: #f5f5f5;
    font-family: Arial, sans-serif;
}

h1 {
    text-align: center;
    margin: 16px 0;
}

/* =========================
   カード一覧スクロール領域
========================= */
.card-area {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
    padding-bottom: 20px;
}

/* =========================
   操作ボタン
========================= */
.card-action-buttons {
    position: sticky;
    bottom: 90px; /* フッター高さ分 */
    display: flex;
    justify-content: center;
    gap: 14px;
    margin: 20px 0;
    z-index: 200;
}

.card-action-button {
    padding: 10px 22px;
    border-radius: 8px;
    border: none;
    font-size: 16px;
    cursor: pointer;
}

.card-action-button.back {
    background: #777;
    color: #fff;
}
</style>

</head>
<body>

<h1>
    <?= htmlspecialchars($target_card['card_name'], ENT_QUOTES) ?> の強化素材選択
</h1>

<?php if (count($cards) === 0): ?>
    <p style="text-align:center;">素材にできるカードがありません。</p>
<?php else: ?>

<form method="post" action="card_action.php">
    <input type="hidden" name="action" value="strengthen">
    <input type="hidden" name="target_card_id" value="<?= $target_card['id'] ?>">

    <!-- カード一覧 -->
    <?php include 'style/card_list_template.php'; ?>

    <!-- 操作ボタン -->
    <div class="card-action-buttons">
        <button type="submit" class="card-action-button">
            強化実行
        </button>
        <a href="user_cards_list.php" class="card-action-button back">
            戻る
        </a>
    </div>

</form>

<?php endif; ?>

<!-- footer は body の最後 -->
<?php include 'style/footer.php'; ?>

</body>
</html>
