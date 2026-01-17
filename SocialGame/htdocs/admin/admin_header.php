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
<title>管理画面</title>
<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}
header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background:#3498db;
    color:#fff;
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
header h1 { font-size:20px; margin:0; }
header nav a { color:#fff; text-decoration:none; margin-left:15px; font-weight:bold; }
header nav a:hover { text-decoration:underline; }
main {
    padding:20px;
    margin-top:70px; /* ヘッダー固定のため余白確保 */
}
button {
    cursor:pointer;
}
</style>
</head>
<body>
<header>
    <h1>管理者画面</h1>
    <nav>
        <a href="dashboard.php">ダッシュボード</a>
        <a href="cards.php">カード一覧</a>
        <a href="gacha_manage.php">ガチャ管理</a>
        <a href="user_manage.php">ユーザー管理</a>
        <a href="../logout.php">ログアウト</a>
    </nav>
</header>
<main>
