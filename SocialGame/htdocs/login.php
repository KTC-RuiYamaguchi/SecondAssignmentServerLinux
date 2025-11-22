<?php
session_start();
$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // POST処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 新規ユーザー作成
        if (isset($_POST['new_user']) && $_POST['new_user_name'] != '') {
            $stmt = $pdo->prepare("INSERT INTO users (user_name) VALUES (?)");
            $stmt->execute([$_POST['new_user_name']]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: dashboard.php');
            exit;
        }

        // 既存ユーザーでログイン
        if (isset($_POST['user_id']) && $_POST['user_id'] != '') {
            $_SESSION['user_id'] = $_POST['user_id'];
            header('Location: dashboard.php');
            exit;
        }

        $error = "ユーザーを選択してください";
    }

    // ユーザー一覧取得
    $users = $pdo->query("SELECT user_id, user_name FROM users ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン</title>
</head>
<body>
<h1>ログイン</h1>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<!-- 既存ユーザーでログイン -->
<form method="post">
    <label>ユーザー選択:
        <select name="user_id">
            <option value="">選択してください</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">ログイン</button>
</form>

<hr>

<!-- 新規ユーザー作成 -->
<form method="post">
    <label>新規ユーザー名: <input type="text" name="new_user_name" required></label>
    <button type="submit" name="new_user">ユーザー作成してログイン</button>
</form>

</body>
</html>
