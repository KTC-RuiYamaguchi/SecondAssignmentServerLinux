<?php
$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'root';
$password = 'p@ssword';

try {
    $pdo = new PDO($dsn, $user, $password);
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー一覧</title>
</head>
<body>
    <h1>ユーザー一覧</h1>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>名前</th>
        </tr>

        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['user_id'], ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
