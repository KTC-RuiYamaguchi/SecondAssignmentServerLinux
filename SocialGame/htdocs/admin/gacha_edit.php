<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';

$message = '';
$editGacha = null;

// -------------------------------
// 編集用データ取得
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM gachas WHERE gacha_id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $editGacha = $stmt->fetch(PDO::FETCH_ASSOC);
}

// -------------------------------
// POST処理（新規作成 or 更新）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gacha_id = $_POST['gacha_id'] ?? null;
    $name = trim($_POST['gacha_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $cost = (int)($_POST['cost'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '' || $cost <= 0) {
        $message = 'ガチャ名と消費コインは必須です';
    } else {
        if ($gacha_id) {
            // 更新
            $stmt = $pdo->prepare("
                UPDATE gachas
                SET gacha_name = ?, description = ?, cost = ?, is_active = ?
                WHERE gacha_id = ?
            ");
            $stmt->execute([$name, $desc, $cost, $active, $gacha_id]);
            $message = 'ガチャ情報を更新しました';
        } else {
            // 新規作成
            $stmt = $pdo->prepare("
                INSERT INTO gachas (gacha_name, description, cost, is_active)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $desc, $cost, $active]);
            $message = 'ガチャを作成しました';
        }
        // 編集用データを再取得
        $editGacha = $pdo->prepare("SELECT * FROM gachas WHERE gacha_id = ?");
        $id = $gacha_id ?? $pdo->lastInsertId();
        $editGacha->execute([$id]);
        $editGacha = $editGacha->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ガチャ作成 / 編集</title>
<style>
body { font-family: Arial; background:#f5f5f5; padding:20px; }
.container { max-width: 500px; margin:auto; background:#fff; padding:20px; border-radius:10px; }
h1 { text-align:center; }
form { display:flex; flex-direction:column; }
label { margin-bottom:10px; font-weight:bold; display:flex; flex-direction:column; }
input[type="text"], input[type="number"], textarea { padding:8px; margin-top:4px; border-radius:5px; border:1px solid #ccc; width:100%; box-sizing:border-box; }
input[type="checkbox"] { width:auto; margin-top:8px; }
.button-group { display:flex; justify-content:space-between; margin-top:20px; }
button, .back-link { padding:10px 16px; border:none; border-radius:5px; cursor:pointer; font-weight:bold; }
button { background:#3498db; color:#fff; }
button:hover { background:#2980b9; }
.back-link { text-decoration:none; text-align:center; line-height:32px; background:#ccc; color:#000; }
.message { text-align:center; color:green; margin-bottom:10px; }
</style>
</head>
<body>

<div class="container">
<h1><?= $editGacha ? 'ガチャ編集' : '新規ガチャ作成' ?></h1>

<?php if($message): ?>
<p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <?php if($editGacha): ?>
        <input type="hidden" name="gacha_id" value="<?= $editGacha['gacha_id'] ?>">
    <?php endif; ?>

    <label>
        ガチャ名
        <input type="text" name="gacha_name" required value="<?= htmlspecialchars($editGacha['gacha_name'] ?? '') ?>">
    </label>

    <label>
        説明
        <textarea name="description"><?= htmlspecialchars($editGacha['description'] ?? '') ?></textarea>
    </label>

    <label>
        消費コイン
        <input type="number" name="cost" min="1" required value="<?= htmlspecialchars($editGacha['cost'] ?? 100) ?>">
    </label>

    <label style="flex-direction:row; align-items:center; font-weight:normal;">
        <input type="checkbox" name="is_active" <?= (isset($editGacha) || $editGacha['is_active']) ? 'checked' : '' ?>>
        <span style="margin-left:8px;">開催中にする</span>
    </label>

    <div class="button-group">
        <button type="submit"><?= $editGacha ? '更新' : '作成' ?></button>
        <a class="back-link" href="gacha_manage.php">一覧へ戻る</a>
    </div>
</form>

</div>

</body>
</html>
