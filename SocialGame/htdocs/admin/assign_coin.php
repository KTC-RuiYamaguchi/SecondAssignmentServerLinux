<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';

$message = '';

// ユーザー一覧取得
$stmt = $pdo->query("SELECT user_id, user_name, user_coins FROM users ORDER BY user_id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// コイン付与処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $coins = intval($_POST['coins']);

    if ($coins > 0) {
        $stmt = $pdo->prepare("UPDATE users SET user_coins = user_coins + ? WHERE user_id = ?");
        $stmt->execute([$coins, $userId]);
        $message = "ユーザーID {$userId} に {$coins} コインを付与しました。";
    } else {
        $message = "付与するコイン数は正の整数で入力してください。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ユーザーにコインを付与</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
h1 { text-align: center; }
form { text-align: center; margin-bottom: 20px; }
form select, form input { margin: 0 5px; padding: 4px 6px; border-radius: 4px; border: 1px solid #ccc; }
form button { padding: 5px 12px; border-radius: 6px; border: none; background:#2ecc71; color:#fff; cursor:pointer; }
.message { text-align: center; margin-bottom: 20px; color: #e74c3c; font-weight: bold; }
table { width: 50%; margin: 0 auto; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: center; }
th { background: #3498db; color: #fff; }
tr:nth-child(even) { background: #f9f9f9; }
</style>
</head>
<body>

<h1>ユーザーにコインを付与</h1>

<?php if ($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post">
    ユーザー: 
    <select name="user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['user_id'] ?>">
                <?= htmlspecialchars($user['user_name']) ?> (ID: <?= $user['user_id'] ?>, 所持コイン: <?= $user['user_coins'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    付与コイン数: <input type="number" name="coins" min="1" required>
    <button type="submit">付与する</button>
</form>

<div style="text-align:center; margin-top:20px;">
    <a href="dashboard.php" style="padding:6px 12px; background:#3498db; color:#fff; border-radius:6px; text-decoration:none;">管理画面トップへ戻る</a>
</div>

</body>
</html>
