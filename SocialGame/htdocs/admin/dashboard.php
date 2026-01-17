<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require 'admin_header.php'; // ヘッダー共通化
?>

<style>
.dashboard-container {
    max-width: 600px;
    margin: 100px auto 50px; /* ヘッダー分の余白を確保 */
    background: #fff;
    padding: 30px 20px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
}

.dashboard-container h1 {
    text-align: center;
    color: #3498db;
    margin-bottom: 25px;
}

.dashboard-container ul {
    list-style: none;
    padding: 0;
}

.dashboard-container ul li {
    margin: 12px 0;
}

.dashboard-container ul li a {
    display: block;
    padding: 10px 15px;
    border-radius: 8px;
    background: #2ecc71;
    color: #fff;
    text-decoration: none;
    text-align: center;
    transition: 0.2s;
}

.dashboard-container ul li a:hover {
    background: #27ae60;
}
</style>

<div class="dashboard-container">
    <h1>管理者ダッシュボード</h1>

    <ul>
        <li><a href="add_card.php">カード新規作成</a></li>
        <li><a href="assign_card.php">ユーザーへのカード付与</a></li>
        <li><a href="assign_coin.php">ユーザーへのコイン付与</a></li>
        <li><a href="gacha_manage.php">ガチャ編集画面</a></li>
        <li><a href="gacha_logs_list.php">ガチャ利用履歴</a></li>
        <li><a href="../logout.php">ログアウト</a></li>
    </ul>
</div>

<?php require 'admin_footer.php'; ?>
