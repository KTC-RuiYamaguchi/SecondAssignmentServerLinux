<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';
try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $cards = $pdo->query("SELECT * FROM cards ORDER BY card_id")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { echo "DB接続エラー: ".$e->getMessage(); exit; }
?>

<nav><a href="dashboard.php">ダッシュボード</a></nav>
<h1>カード一覧</h1>
<table border="1" cellpadding="5">
<tr><th>ID</th><th>カード名</th><th>キャラクターID</th><th>HP</th><th>ATK</th><th>DEF</th></tr>
<?php foreach($cards as $c): ?>
<tr>
<td><?= $c['card_id'] ?></td>
<td><?= htmlspecialchars($c['card_name'],ENT_QUOTES) ?></td>
<td><?= $c['charactor_id'] ?></td>
<td><?= $c['card_hp'] ?></td>
<td><?= $c['card_atk'] ?></td>
<td><?= $c['card_def'] ?></td>
</tr>
<?php endforeach; ?>
</table>
