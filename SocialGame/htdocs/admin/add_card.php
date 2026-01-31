<?php
require 'admin_header.php';

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

$msg = '';
$max_level = 50;

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("SELECT MAX(level) AS max_level FROM exp_table");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_level = $result['max_level'] ?? 50;

    $rarities = $pdo->query("SELECT rarity_id, rarity_name FROM card_rarity ORDER BY rarity_id")->fetchAll(PDO::FETCH_ASSOC);
    $cards = $pdo->query("SELECT card_id, card_name FROM cards ORDER BY card_name")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_name'])) {
        $stmt = $pdo->prepare("
            INSERT INTO cards
            (rarity_id, card_name, max_level, base_hp, base_atk, base_def,
             material_exp, evolved_card_id, thumbnail, per_level_hp, per_level_atk, per_level_def)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['rarity_id'],
            $_POST['card_name'],
            $_POST['max_level'],
            $_POST['base_hp'],
            $_POST['base_atk'],
            $_POST['base_def'],
            $_POST['material_exp'],
            $_POST['evolved_card_id'],
            $_POST['thumbnail'],
            $_POST['per_level_hp'],
            $_POST['per_level_atk'],
            $_POST['per_level_def']
        ]);

        $msg = "カードを作成しました。";
    }
} catch (PDOException $e) {
    echo "DBエラー: " . $e->getMessage();
    exit;
}
?>

<style>
.card-form {
    max-width:720px;
    margin:0 auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
.card-form h3 { margin-top:25px; border-bottom:1px solid #ddd; }
.card-form label { display:block; margin:10px 0; }
.card-form input, .card-form select {
    width:100%;
    max-width:300px;
    padding:6px;
}
.card-form button {
    padding:8px 14px;
    border:none;
    border-radius:6px;
    background:#2ecc71;
    color:#fff;
    cursor:pointer;
}
.card-form button:hover { background:#27ae60; }

.message {
    text-align:center;
    font-weight:bold;
    color:#27ae60;
    margin-bottom:15px;
}

/* ===== モーダル共通 ===== */
.modal-overlay {
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:1000;
}
.modal {
    background:#fff;
    width:650px;
    max-height:80vh;
    padding:20px;
    border-radius:10px;
    overflow-y:auto;
}

/* ===== 画像選択 ===== */
.image-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(100px,1fr));
    gap:10px;
}
.image-grid img {
    width:100%;
    height:100px;
    object-fit:cover;
    cursor:pointer;
    border-radius:6px;
    border:2px solid transparent;
}
.image-grid img:hover {
    border-color:#3498db;
}

/* ===== 確認モーダル ===== */
.confirm-list p {
    margin:6px 0;
}
.modal-actions {
    text-align:right;
    margin-top:15px;
}
</style>

<div class="card-form">
<h1 style="text-align:center;">新規カード作成</h1>

<?php if($msg): ?>
<div class="message"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post" id="cardForm">
<h3>基本情報</h3>

<label>カード名
<input type="text" name="card_name" required>
</label>

<label>レアリティ
<select name="rarity_id" required>
    <option value="">選択してください</option>
    <?php foreach($rarities as $r): ?>
        <option value="<?= $r['rarity_id'] ?>"><?= htmlspecialchars($r['rarity_name']) ?></option>
    <?php endforeach; ?>
</select>
</label>

<label>進化後カード
<select name="evolved_card_id">
    <option value="0">進化なし</option>
    <?php foreach($cards as $c): ?>
        <option value="<?= $c['card_id'] ?>"><?= htmlspecialchars($c['card_name']) ?></option>
    <?php endforeach; ?>
</select>
</label>

<label>最大レベル
<input type="number" name="max_level" value="<?= $max_level ?>">
</label>

<label>HP <input type="number" name="base_hp" value="0"></label>
<label>ATK <input type="number" name="base_atk" value="0"></label>
<label>DEF <input type="number" name="base_def" value="0"></label>

<h3>レベルアップ上昇量</h3>
<label>HP <input type="number" name="per_level_hp" value="1"></label>
<label>ATK <input type="number" name="per_level_atk" value="1"></label>
<label>DEF <input type="number" name="per_level_def" value="1"></label>

<h3>強化関連</h3>
<label>素材EXP <input type="number" name="material_exp" value="100"></label>

<h3>サムネイル</h3>
<button type="button" id="openImageModal">画像を選択</button>
<input type="hidden" name="thumbnail" id="thumbnail">
<p>
<img id="preview" style="width:100px; display:none; border:1px solid #ccc;">
</p>

<button type="button" id="openConfirm">作成内容確認</button>
</form>
</div>

<!-- ===== 画像選択モーダル ===== -->
<div id="imageModal" class="modal-overlay">
<div class="modal">
<h2>画像を選択</h2>
<div class="image-grid">
<?php
$dir = '../images/cards/';
foreach (scandir($dir) as $file) {
    if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $file)) {
        echo "<img src='{$dir}{$file}' data-src='{$dir}{$file}'>";
    }
}
?>
</div>
<div class="modal-actions">
<button type="button" onclick="closeImageModal()">閉じる</button>
</div>
</div>
</div>

<!-- ===== 確認モーダル ===== -->
<div id="confirmModal" class="modal-overlay">
<div class="modal">
<h2>作成内容確認</h2>
<div class="confirm-list" id="confirmList"></div>
<div class="modal-actions">
<button type="button" onclick="closeConfirm()">戻る</button>
<button type="submit" form="cardForm">確定</button>
</div>
</div>
</div>

<script>
const imageModal = document.getElementById('imageModal');
const confirmModal = document.getElementById('confirmModal');
const thumbnailInput = document.getElementById('thumbnail');
const preview = document.getElementById('preview');

document.getElementById('openImageModal').onclick = () => imageModal.style.display = 'flex';
function closeImageModal(){ imageModal.style.display = 'none'; }

document.querySelectorAll('.image-grid img').forEach(img => {
    img.onclick = () => {
        thumbnailInput.value = img.dataset.src;
        preview.src = img.dataset.src;
        preview.style.display = 'block';
        closeImageModal();
    };
});

document.getElementById('openConfirm').onclick = () => {
    const data = new FormData(document.getElementById('cardForm'));
    let html = '';
    data.forEach((v,k)=> html += `<p><strong>${k}</strong>: ${v}</p>`);
    document.getElementById('confirmList').innerHTML = html;
    confirmModal.style.display = 'flex';
};

function closeConfirm(){ confirmModal.style.display = 'none'; }
</script>

<?php require 'admin_footer.php'; ?>
