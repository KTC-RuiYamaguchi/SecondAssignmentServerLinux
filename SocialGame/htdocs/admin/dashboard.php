<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理者ダッシュボード</title>
</head>
<body>
<h1>管理者ダッシュボード</h1>

<ul>
    <li><a href="add_card.php">カード新規作成</a></li>
    <li><a href="assign_card.php">ユーザーへのカード付与</a></li>
    <li><a href="assign_coin.php">ユーザーへのコイン付与</a></li>
    <li><a href="gacha_manage.php">ガチャ編集画面</a></li>
    <li><a href="gacha_logs_list.php">ガチャ利用履歴</a></li>
    <li><a href="../logout.php">ログアウト</a></li>
</ul>
</body>
</html>
