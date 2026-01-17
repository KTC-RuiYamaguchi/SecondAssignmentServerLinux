<?php
require 'admin_header.php'; // 共通ヘッダー読み込み
require '../db_connect.php';

$message = '';

try {
    // ユーザー一覧取得
    $stmt = $pdo->query("SELECT user_id, user_name, user_coins FROM users ORDER BY user_id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // コイン付与処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = intval($_POST['user_id']);
        $coins = intval($_POST['coins']);

        if ($coins > 0) {
            $stmt = $pdo->prepare("UPDATE users SET user_coins = user_coins + ? WHERE user_id = ?");
            $stmt->execute([$coins, $userId]);
            $message = "ユーザーID {$userId} に {$coins} コインを付与しました。";
        } else {
            $message = "付与するコイン数は正の整数で入力してください。";
        }
    }

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin:0;
    padding-top: 80px; /* ヘッダー固定分 */
}

.assign-container {
    max-width: 500px;
    margin: 0 auto;
    background: #fff;
    padding: 30px 20px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.assign-container h1 {
    text-align: center;
    color: #3498db;
    margin-bottom: 25px;
}

.assign-container form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.assign-container select,
.assign-container input[type="number"] {
    width: 80%;
    margin: 10px 0;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.assign-container button {
    padding: 10px 20px;
    margin-top: 15px;
    border: none;
    border-radius: 6px;
    background: #2ecc71;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s;
}

.assign-container button:hover {
    background: #27ae60;
}

.message {
    text-align: center;
    margin-bottom: 20px;
    color: #27ae60;
    font-weight: bold;
}
</style>

<div class="assign-container">
    <h1>ユーザーにコインを付与</h1>

    <?php if($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <select name="user_id" required>
            <option value="">ユーザーを選択してください</option>
            <?php foreach($users as $user): ?>
                <option value="<?= $user['user_id'] ?>">
                    <?= htmlspecialchars($user['user_name']) ?> (ID: <?= $user['user_id'] ?>, 所持コイン: <?= $user['user_coins'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="coins" min="1" placeholder="付与するコイン数" required>

        <button type="submit">コインを付与する</button>
    </form>
</div>

<?php require 'admin_footer.php'; ?>
