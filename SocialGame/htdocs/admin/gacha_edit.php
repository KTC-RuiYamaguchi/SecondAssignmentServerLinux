<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';
require 'admin_header.php'; // 共通ヘッダー読み込み

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
        $editGachaStmt = $pdo->prepare("SELECT * FROM gachas WHERE gacha_id = ?");
        $id = $gacha_id ?? $pdo->lastInsertId();
        $editGachaStmt->execute([$id]);
        $editGacha = $editGachaStmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<style>
/* コンテナ */
.container {
    max-width: 500px;
    margin: 120px auto 50px; /* ヘッダー分余白確保 */
    background:#fff; 
    padding:25px 20px; 
    border-radius:12px; 
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
}

/* タイトル */
h1 {
    text-align:center;
    color:#3498db;
    margin-bottom:25px;
}

/* フォーム */
form { display:flex; flex-direction:column; }
label { margin-bottom:15px; font-weight:bold; display:flex; flex-direction:column; }
input[type="text"], input[type="number"], textarea { 
    padding:10px; margin-top:6px; border-radius:6px; border:1px solid #ccc; width:100%; box-sizing:border-box; 
    font-size:14px;
}
textarea { resize:vertical; min-height:80px; }
input[type="checkbox"] { width:auto; margin-top:0; }

/* ボタン */
.button-group { display:flex; justify-content:space-between; margin-top:20px; }
button, .back-link { padding:10px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; font-size:14px; }
button { background:#2ecc71; color:#fff; transition:0.2s; }
button:hover { background:#27ae60; }
.back-link { text-decoration:none; text-align:center; line-height:32px; background:#bdc3c7; color:#000; transition:0.2s; }
.back-link:hover { background:#95a5a6; }

/* メッセージ */
.message { text-align:center; color:green; margin-bottom:15px; font-weight:bold; }
</style>

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
        <input type="checkbox" name="is_active" <?= (!empty($editGacha) && $editGacha['is_active']) ? 'checked' : '' ?>>
        <span style="margin-left:8px;">開催中にする</span>
    </label>

    <div class="button-group">
        <button type="submit"><?= $editGacha ? '更新' : '作成' ?></button>
        <a class="back-link" href="gacha_manage.php">一覧へ戻る</a>
    </div>
</form>
</div>

<?php require 'admin_footer.php'; ?>
