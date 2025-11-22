<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$target_id = $_GET['target_card_id'] ?? null;
if (!$target_id) { header('Location: user_cards_list.php'); exit; }

$dsn='mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user='data_user'; $password='data';

try {
    $pdo = new PDO($dsn,$user,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    // 強化対象カード取得
    $stmt = $pdo->prepare("SELECT uc.*, c.card_name FROM user_cards uc JOIN cards c ON uc.card_id=c.card_id WHERE uc.id=? AND uc.user_id=?");
    $stmt->execute([$target_id, $_SESSION['user_id']]);
    $target_card = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$target_card) { header('Location: user_cards_list.php'); exit; }

    // 素材候補（対象カード除外）
    $stmt = $pdo->prepare("SELECT uc.*, c.card_name FROM user_cards uc JOIN cards c ON uc.card_id=c.card_id WHERE uc.user_id=? AND uc.id!=? ORDER BY uc.card_id");
    $stmt->execute([$_SESSION['user_id'], $target_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e){ echo "DB接続エラー: ".$e->getMessage(); exit; }
?>

<nav><a href="user_cards_list.php">カード一覧</a></nav>
<h1>強化対象: <?= htmlspecialchars($target_card['card_name'], ENT_QUOTES) ?> (Lv<?= $target_card['level'] ?>)</h1>

<?php if(empty($materials)): ?>
<p>素材カードがありません。</p>
<?php else: ?>
<form method="post" action="card_action.php">
    <input type="hidden" name="target_card_id" value="<?= $target_card['id'] ?>">
    <h3>素材カードを選択（複数可）</h3>
    <?php foreach($materials as $m): ?>
    <label>
        <input type="checkbox" name="material_card_ids[]" value="<?= $m['id'] ?>">
        <?= htmlspecialchars($m['card_name'], ENT_QUOTES) ?> (Lv<?= $m['level'] ?>)
    </label><br>
    <?php endforeach; ?>
    <br><button type="submit" name="action" value="strengthen">強化実行</button>
</form>
<?php endif; ?>
