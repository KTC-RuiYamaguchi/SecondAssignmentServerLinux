<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $msg = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = $_POST['card_name'] ?? '';
        $char  = $_POST['charactor_id'] ?? 0;
        $hp    = $_POST['base_hp'] ?? 0;
        $atk   = $_POST['base_atk'] ?? 0;
        $def   = $_POST['base_def'] ?? 0;
        $exp   = $_POST['material_exp'] ?? 100;
        $limit = $_POST['evolve_limit'] ?? 1;
        $multi = $_POST['evolve_multiplier'] ?? 1.10;

        if ($name) {
            $stmt = $pdo->prepare("
                INSERT INTO cards 
                (card_name, charactor_id, base_hp, base_atk, base_def, material_exp, evolve_limit, evolve_multiplier)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $char, $hp, $atk, $def, $exp, $limit, $multi]);
            $msg = "カードを作成しました。";
        } else {
            $msg = "カード名は必須です。";
        }
    }

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<nav><a href="dashboard.php">ダッシュボード</a> | <a href="cards.php">カード一覧</a></nav>

<h1>新規カード作成</h1>
<?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

<form method="post">
    <label>カード名: <input type="text" name="card_name" required></label><br><br>
    <label>キャラクターID: <input type="number" name="charactor_id" value="0"></label><br><br>
    <label>HP: <input type="number" name="base_hp" value="0"></label><br><br>
    <label>ATK: <input type="number" name="base_atk" value="0"></label><br><br>
    <label>DEF: <input type="number" name="base_def" value="0"></label><br><br>
    <label>素材EXP: <input type="number" name="material_exp" value="100"></label><br><br>
    <label>進化上限: <input type="number" name="evolve_limit" value="1"></label><br><br>
    <label>進化係数: <input type="number" step="0.01" name="evolve_multiplier" value="1.10"></label><br><br>
    <button type="submit">作成</button>
</form>
