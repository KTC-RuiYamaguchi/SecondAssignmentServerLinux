<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';

$gacha_id = $_GET['gacha_id'] ?? null;
if (!$gacha_id) {
    echo "ガチャが指定されていません。";
    exit;
}

// ---------------------------
// ガチャ情報取得
$stmt = $pdo->prepare("SELECT * FROM gachas WHERE gacha_id = ?");
$stmt->execute([$gacha_id]);
$gacha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$gacha) {
    echo "指定されたガチャが存在しません。";
    exit;
}

// ---------------------------
// POST処理（カード追加・重み更新・削除）
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_or_update_card'])) {
        $card_id = (int)$_POST['card_id'];
        $weight = (int)$_POST['weight'];
        if ($card_id && $weight > 0) {
            // すでに追加済みかチェック
            $check = $pdo->prepare("SELECT * FROM gacha_items WHERE gacha_id = ? AND card_id = ?");
            $check->execute([$gacha_id, $card_id]);
            if ($check->fetch()) {
                // 既存カードなら重みを更新
                $stmt = $pdo->prepare("UPDATE gacha_items SET weight = ? WHERE gacha_id = ? AND card_id = ?");
                $stmt->execute([$weight, $gacha_id, $card_id]);
                $message = "カードの重みを更新しました";
            } else {
                // 新規追加
                $stmt = $pdo->prepare("INSERT INTO gacha_items (gacha_id, card_id, weight) VALUES (?, ?, ?)");
                $stmt->execute([$gacha_id, $card_id, $weight]);
                $message = "カードを追加しました";
            }
        }
    } elseif (isset($_POST['delete_card'])) {
        $item_id = (int)$_POST['item_id'];
        $stmt = $pdo->prepare("DELETE FROM gacha_items WHERE gacha_item_id = ?");
        $stmt->execute([$item_id]);
        $message = "カードを削除しました";
    }
}

// ---------------------------
// ガチャに設定済みカード取得
$stmt = $pdo->prepare("
    SELECT gi.gacha_item_id, c.card_id, c.card_name, c.thumbnail, gi.weight
    FROM gacha_items gi
    JOIN cards c ON gi.card_id = c.card_id
    WHERE gi.gacha_id = ?
    ORDER BY gi.gacha_item_id ASC
");
$stmt->execute([$gacha_id]);
$gacha_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------
// すべてのカード取得（追加済みも含む）
$stmt = $pdo->query("SELECT card_id, card_name, thumbnail FROM cards ORDER BY card_id");
$all_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ガチャカード設定</title>
<style>
body { font-family: Arial; background:#f5f5f5; padding:20px; }
.container { max-width:800px; margin:auto; background:#fff; padding:20px; border-radius:10px; }
h1 { text-align:center; }
.message { text-align:center; color:green; margin-bottom:10px; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#3498db; color:#fff; }
img { width:60px; height:auto; }
form.inline { display:inline; }
input[type="number"] { width:60px; }
button { padding:5px 10px; cursor:pointer; border:none; border-radius:4px; background:#3498db; color:#fff; }
button:hover { background:#2980b9; }
a.back-link { display:inline-block; margin-top:20px; padding:6px 12px; background:#ccc; color:#000; text-decoration:none; border-radius:5px; }
label { display:block; margin-bottom:8px; }
input, select { margin-bottom:10px; }
</style>
</head>
<body>

<div class="container">
<h1>「<?= htmlspecialchars($gacha['gacha_name']) ?>」カード設定</h1>

<?php if($message): ?>
<p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<!-- 追加・更新フォーム -->
<form method="post">
    <label>カード選択：
        <select name="card_id" required style="width:300px;">
            <?php foreach($all_cards as $c): ?>
            <?php
            $existing = array_filter($gacha_cards, function($gc) use ($c) {
                return $gc['card_id'] == $c['card_id'];
            });
            $weight = !empty($existing) ? reset($existing)['weight'] : 1;
            ?>
            <option value="<?= $c['card_id'] ?>">
                <?= htmlspecialchars($c['card_name']) ?><?= !empty($existing) ? ' (追加済み)' : '' ?>
            </option>
        <?php endforeach; ?>

        </select>
    </label>
    <label>重み：
        <input type="number" name="weight" min="1" value="1" required>
    </label>
    <button type="submit" name="add_or_update_card">追加 / 更新</button>
</form>

<!-- 設定済みカード一覧 -->
<table>
<tr>
    <th>ID</th>
    <th>カード名</th>
    <th>サムネイル</th>
    <th>重み</th>
    <th>操作</th>
</tr>
<?php foreach($gacha_cards as $gc): ?>
<tr>
    <td><?= $gc['gacha_item_id'] ?></td>
    <td><?= htmlspecialchars($gc['card_name']) ?></td>
    <td><img src="<?= htmlspecialchars($gc['thumbnail']) ?>" alt=""></td>
    <td><?= $gc['weight'] ?></td>
    <td>
        <form method="post" class="inline">
            <input type="hidden" name="item_id" value="<?= $gc['gacha_item_id'] ?>">
            <button type="submit" name="delete_card">削除</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<p style="text-align:center;">
    <a class="back-link" href="gacha_manage.php">ガチャ一覧へ戻る</a>
</p>

</div>
</body>
</html>
