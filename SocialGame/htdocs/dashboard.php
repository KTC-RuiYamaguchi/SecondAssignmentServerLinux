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

    // ログイン中ユーザーの名前取得
    $stmt = $pdo->prepare("SELECT user_name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_name = $row ? $row['user_name'] : '不明なユーザー';

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ダッシュボード</title>
<link rel="stylesheet" href="footer_style.css">
<style>
body {
    margin:0;
    font-family:"Segoe UI", Roboto, sans-serif;
    background:#f4f6f8;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

main {
    flex:1;
    padding:20px;
    text-align:center;
}
</style>
</head>
<body>

<main>
    <h1>ようこそ、<?= htmlspecialchars($user_name, ENT_QUOTES) ?> さん</h1>
    <p>フッターボタンから所持カード一覧やホーム、ログアウトにアクセスできます。</p>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
