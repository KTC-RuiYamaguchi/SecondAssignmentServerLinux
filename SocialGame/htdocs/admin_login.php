<?php
session_start();

// 固定管理者パスワード
$admin_pass = 'admin123';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['admin_pass']) && $_POST['admin_pass'] === $admin_pass) {
        $_SESSION['is_admin'] = true;
        header('Location: admin/dashboard.php');
        exit;
    } else {
        $error = 'パスワードが違います';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理者ログイン</title>
</head>
<body>
<h1>管理者ログイン</h1>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post">
    <label>管理者パスワード: <input type="password" name="admin_pass" required></label>
    <button type="submit">ログイン</button>
</form>

<p><a href="login.php">通常ログイン画面へ戻る</a></p>
</body>
</html>
