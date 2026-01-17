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
<link rel="stylesheet" href="style/common.css"> <!-- 共通CSS -->
<style>
/* ページ固有スタイル */
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.login-container {
    background-color: #fff;
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    width: 360px;
    text-align: center;
}

.login-container h1 {
    margin-bottom: 24px;
    font-size: 24px;
    color: #333;
}

.login-container form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.login-container input[type="password"] {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
}

.login-container button {
    padding: 12px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    background-color: #4CAF50;
    color: #fff;
    cursor: pointer;
    transition: 0.2s;
}

.login-container button:hover {
    background-color: #45a049;
}

.error-message {
    color: red;
    margin-bottom: 12px;
}

.login-container a {
    display: block;
    margin-top: 16px;
    color: #555;
    text-decoration: none;
    font-size: 14px;
}

.login-container a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="login-container">
    <h1>管理者ログイン</h1>

    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="admin_pass" value="admin123" required>
        <button type="submit">ログイン</button>
    </form>

    <a href="login.php">通常ログイン画面へ戻る</a>
</div>
</body>
</html>
