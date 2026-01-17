<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';
require 'admin_header.php'; // 固定ヘッダー読み込み

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
            $check = $pdo->prepare("SELECT * FROM gacha_items WHERE gacha_id = ? AND card_id = ?");
            $check->execute([$gacha_id, $card_id]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare("UPDATE gacha_items SET weight = ? WHERE gacha_id = ? AND card_id = ?");
                $stmt->execute([$weight, $gacha_id, $card_id]);
                $message = "カードの重みを更新しました";
            } else {
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
// すべてのカード取得
$stmt = $pdo->query("SELECT card_id, card_name, thumbnail FROM cards ORDER BY card_id");
$all_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.container {
    max-width: 900px;
    margin: 120px auto 50px; /* ヘッダー分余白確保 */
    background:#fff;
    padding:25px 20px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
}

h1 {
    text-align:center;
    color:#3498db;
    margin-bottom:25px;
}

.message {
    text-align:center;
    color:green;
    margin-bottom:15px;
    font-weight:bold;
}

form label { display:block; margin-bottom:10px; font-weight:bold; }
form select, form input[type="number"] {
    width:100%;
    max-width:350px;
    padding:8px 10px;
    margin-bottom:15px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

button, .back-link {
    padding:8px 14px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
    font-size:14px;
    transition:0.2s;
}

button { background:#2ecc71; color:#fff; }
button:hover { background:#27ae60; }

.back-link {
    text-decoration:none;
    background:#bdc3c7;
    color:#000;
    display:inline-block;
    text-align:center;
}
.back-link:hover { background:#95a5a6; }

table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    font-size:14px;
}

th, td {
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}

th { background:#3498db; color:#fff; }
tr:nth-child(even) { background:#f9f9f9; }
img { width:60px; height:auto; }

form.inline { display:inline; }
input[type="number"] { width:60px; }
</style>

<div class="container">
<h1>「<?= htmlspecialchars($gacha['gacha_name']) ?>」カード設定</h1>

<?php if($message): ?>
<p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<!-- 追加・更新フォーム -->
<form method="post">
    <label>カード選択：
        <select name="card_id" required>
        <?php foreach($all_cards as $c): 
            $existing = array_filter($gacha_cards, function($gc) use ($c) {
                return $gc['card_id'] == $c['card_id'];
            });
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

<p style="text-align:center; margin-top:15px;">
    <a class="back-link" href="gacha_manage.php">ガチャ一覧へ戻る</a>
</p>

</div>

<?php require 'admin_footer.php'; ?>
