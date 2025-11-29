<?php
session_start();

// 管理者チェック
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

$msg = '';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 全カードを取得
    $cards = $pdo->query("SELECT * FROM cards ORDER BY card_id")->fetchAll(PDO::FETCH_ASSOC);

    // 全ユーザーを取得
    $users = $pdo->query("SELECT * FROM users ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $card_id = $_POST['card_id'] ?? '';
        $user_id = $_POST['user_id'] ?? '';

        if ($card_id && $user_id) {
            $stmt = $pdo->prepare("INSERT INTO user_cards (user_id, card_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $card_id]);
            $msg = "カードをユーザーに割り当てました。";
        } else {
            $msg = "ユーザーとカードを選択してください。";
        }
    }
} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<nav>
<a href="dashboard.php">管理者ダッシュボード</a> |
<a href="../logout.php">ログアウト</a>
</nav>

<h1>カードをユーザーに割り当てる</h1>

<?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

<form method="post">
<label>ユーザー:
<select name="user_id">
<option value="">選択してください</option>
<?php foreach ($users as $u): ?>
<option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?></option>
<?php endforeach; ?>
</select>
</label>
<br><br>
<label>カード:
<select name="card_id">
<option value="">選択してください</option>
<?php foreach ($cards as $c): ?>
<option value="<?= $c['card_id'] ?>"><?= htmlspecialchars($c['default_name'], ENT_QUOTES) ?></option>
<?php endforeach; ?>
</select>
</label>
<br><br>
<button type="submit">割り当て</button>
</form>
