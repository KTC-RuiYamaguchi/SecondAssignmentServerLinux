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
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // ログイン中ユーザーの名前を取得
    $stmt = $pdo->prepare("SELECT user_name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_name = $row ? $row['user_name'] : '不明なユーザー';

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<nav>
    <!-- プレイヤー用リンクのみ -->
    <a href="user_cards_list.php">自分の所持カード</a> |
    <a href="logout.php">ログアウト</a>
</nav>

<h1>ダッシュボード</h1>
<p>ようこそ、<?= htmlspecialchars($user_name, ENT_QUOTES) ?> さん</p>

<p>ダッシュボードでは、所持カードの一覧を確認できます。</p>
<p>カードの詳細を見る場合は「自分の所持カード」リンクをクリックしてください。</p>
