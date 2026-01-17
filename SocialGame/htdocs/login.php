<?php
session_start();
require 'db_connect.php'; // DB接続共通化

$error = '';

// -----------------------------------
// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 新規ユーザー作成
    if (isset($_POST['new_user']) && trim($_POST['new_user_name']) !== '') {
        $stmt = $pdo->prepare("INSERT INTO users (user_name) VALUES (?)");
        $stmt->execute([trim($_POST['new_user_name'])]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header('Location: dashboard.php');
        exit;
    }

    // 既存ユーザーでログイン
    if (isset($_POST['user_id']) && $_POST['user_id'] !== '') {
        $_SESSION['user_id'] = $_POST['user_id'];
        header('Location: dashboard.php');
        exit;
    }

    $error = "ユーザーを選択または新規作成してください";
}

// -----------------------------------
// ユーザー一覧取得
$users = $pdo->query("SELECT user_id, user_name FROM users ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ログイン</title>
<style>
body {
    margin:0;
    font-family:"Segoe UI", Roboto, sans-serif;
    background:#f0f2f5;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.container {
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    max-width:400px;
    width:100%;
    text-align:center;
}
h1 {
    margin-bottom:20px;
    color:#3498db;
}
form {
    margin-bottom:20px;
}
label {
    display:block;
    text-align:left;
    margin-bottom:10px;
    font-weight:bold;
}
select, input[type="text"] {
    width:100%;
    padding:8px 10px;
    border-radius:5px;
    border:1px solid #ccc;
    margin-top:4px;
    box-sizing:border-box;
}
button {
    width:100%;
    padding:10px;
    border:none;
    border-radius:5px;
    background:#3498db;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
    margin-top:10px;
    transition: background 0.2s;
}
button:hover {
    background:#2980b9;
}
hr {
    margin:30px 0;
    border:none;
    border-top:1px solid #ccc;
}
.error {
    color:red;
    margin-bottom:15px;
}
.link {
    display:block;
    margin-top:15px;
    text-decoration:none;
    color:#3498db;
    font-weight:bold;
}
.link:hover {
    text-decoration:underline;
}
</style>
</head>
<body>

<div class="container">
    <h1>ログイン</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

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
        <label>新規ユーザー名:
            <input type="text" name="new_user_name" placeholder="例: 山田太郎" required>
        </label>
        <button type="submit" name="new_user">ユーザー作成してログイン</button>
    </form>

    <a href="admin_login.php" class="link">管理者画面へ</a>
</div>

</body>
</html>
