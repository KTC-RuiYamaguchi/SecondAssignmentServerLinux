<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';

$message = '';

// -------------------------------
// 削除処理
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM gachas WHERE gacha_id = ?");
    $stmt->execute([$_GET['delete_id']]);
    $message = 'ガチャを削除しました';
}

// ガチャ一覧取得
$stmt = $pdo->query("SELECT * FROM gachas ORDER BY gacha_id DESC");
$gachas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ガチャ管理</title>
<style>
body { font-family: Arial; background:#f5f5f5; padding:20px; }
.container { max-width: 800px; margin:auto; background:#fff; padding:20px; border-radius:10px; }
h1 { text-align:center; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#3498db; color:#fff; }
a { text-decoration:none; color:#3498db; margin:0 5px; }
.message { text-align:center; color:green; margin-bottom:10px; }
</style>
</head>
<body>
<div class="container">
<h1>ガチャ一覧</h1>

<?php if($message): ?>
<p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<p style="text-align:right;">
    <a href="gacha_edit.php">新規ガチャ作成</a>
</p>

<table>
<tr>
    <th>ID</th>
    <th>名前</th>
    <th>コスト</th>
    <th>状態</th>
    <th>作成日</th>
    <th>操作</th>
</tr>
<?php foreach($gachas as $g): ?>
<tr>
    <td><?= $g['gacha_id'] ?></td>
    <td><?= htmlspecialchars($g['gacha_name']) ?></td>
    <td><?= $g['cost'] ?></td>
    <td><?= $g['is_active'] ? '開催中' : '非公開' ?></td>
    <td><?= $g['created_at'] ?></td>
    <td>
        <a href="gacha_edit.php?edit_id=<?= $g['gacha_id'] ?>">編集</a> |
        <a href="gacha_items_manage.php?gacha_id=<?= $g['gacha_id'] ?>">カード設定</a> |
        <a href="?delete_id=<?= $g['gacha_id'] ?>" onclick="return confirm('本当に削除しますか？')">削除</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<p style="text-align:center; margin-top:20px;">
    <a href="dashboard.php">管理画面トップへ戻る</a>
</p>

</div>
</body>
</html>
