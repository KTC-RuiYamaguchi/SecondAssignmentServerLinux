<?php
require 'admin_header.php'; // ヘッダー読み込み

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

$msg = '';
$max_level = 50;

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 最大レベル
    $stmt = $pdo->query("SELECT MAX(level) AS max_level FROM exp_table");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_level = $result['max_level'] ?? 50;

    // レアリティ取得
    $rarities = $pdo->query("SELECT rarity_id, rarity_name FROM card_rarity ORDER BY rarity_id")->fetchAll(PDO::FETCH_ASSOC);
    // 既存カード取得
    $cards = $pdo->query("SELECT card_id, card_name FROM cards ORDER BY card_name")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['card_name'] ?? '';
        $rarity_id = (int)($_POST['rarity_id'] ?? 0);
        $base_hp = (int)($_POST['base_hp'] ?? 0);
        $base_atk = (int)($_POST['base_atk'] ?? 0);
        $base_def = (int)($_POST['base_def'] ?? 0);
        $per_level_hp = (int)($_POST['per_level_hp'] ?? 1);
        $per_level_atk = (int)($_POST['per_level_atk'] ?? 1);
        $per_level_def = (int)($_POST['per_level_def'] ?? 1);
        $material_exp = (int)($_POST['material_exp'] ?? 100);
        $max_level_input = (int)($_POST['max_level'] ?? $max_level);
        $thumbnail = $_POST['thumbnail'] ?? '';
        $evolved_card_id = (int)($_POST['evolved_card_id'] ?? 0);

        if ($name !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO cards 
                (rarity_id, card_name, max_level, base_hp, base_atk, base_def, 
                 material_exp, evolved_card_id, thumbnail, per_level_hp, per_level_atk, per_level_def)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $rarity_id, $name, $max_level_input, $base_hp, $base_atk, $base_def,
                $material_exp, $evolved_card_id, $thumbnail,
                $per_level_hp, $per_level_atk, $per_level_def
            ]);
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

<style>
.card-form {
    max-width:700px;
    margin:0 auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
.card-form h3 { border-bottom:1px solid #eee; padding-bottom:5px; margin-top:20px; }
.card-form label { display:block; margin-bottom:10px; }
.card-form input, .card-form select { padding:5px 8px; width:100%; max-width:300px; margin-top:3px; }
.card-form button { background:#2ecc71; color:#fff; border:none; padding:8px 14px; border-radius:6px; margin-top:10px; }
.message { text-align:center; font-weight:bold; color:#27ae60; margin-bottom:15px; }
</style>

<div class="card-form">
<h1 style="text-align:center;">新規カード作成</h1>
<?php if(!empty($msg)) echo "<div class='message'>{$msg}</div>"; ?>

<form method="post" id="card-form">
<h3>基本情報</h3>
<label>カード名: <input type="text" name="card_name" required></label>

<label>レアリティ:
<select name="rarity_id" required>
    <option value="">選択してください</option>
    <?php foreach($rarities as $r): ?>
        <option value="<?= $r['rarity_id'] ?>"><?= htmlspecialchars($r['rarity_name']) ?></option>
    <?php endforeach; ?>
</select>
</label>

<label>進化後カード:
<select name="evolved_card_id">
    <option value="0">進化なし</option>
    <?php foreach($cards as $c): ?>
        <option value="<?= $c['card_id'] ?>"><?= htmlspecialchars($c['card_name']) ?></option>
    <?php endforeach; ?>
</select>
</label>

<label>最大レベル: <input type="number" name="max_level" value="<?= $max_level ?>" min="1" max="<?= $max_level ?>" required></label>

<label>HP: <input type="number" name="base_hp" value="0"></label>
<label>ATK: <input type="number" name="base_atk" value="0"></label>
<label>DEF: <input type="number" name="base_def" value="0"></label>

<h3>レベルアップ時のステータス上昇量</h3>
<label>HP上昇量: <input type="number" name="per_level_hp" value="1"></label>
<label>ATK上昇量: <input type="number" name="per_level_atk" value="1"></label>
<label>DEF上昇量: <input type="number" name="per_level_def" value="1"></label>

<h3>強化・進化関連</h3>
<label>素材EXP: <input type="number" name="material_exp" value="100"></label>

<h3>サムネイル画像選択</h3>
<button type="button" id="openModal">サムネイル画像を選択</button>
<input type="hidden" name="thumbnail" id="selected_image">
<p>選択した画像プレビュー: <img id="image_preview" src="" style="width:100px; height:100px; display:none; border:1px solid #ccc;"></p>

<button type="button" id="showConfirmation">作成内容確認</button>
</form>
</div>

<!-- モーダルとスクリプトは前回のコードをそのまま利用 -->
<script>
// モーダル操作スクリプト（前回のコード）
// 省略（コピーしてそのまま使用可能）
</script>

<?php require 'admin_footer.php'; ?>
