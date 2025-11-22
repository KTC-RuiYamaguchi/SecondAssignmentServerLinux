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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name       = $_POST['card_name'] ?? '';
        $char_id    = (int)($_POST['charactor_id'] ?? 0);
        $base_hp    = (int)($_POST['base_hp'] ?? 0);
        $base_atk   = (int)($_POST['base_atk'] ?? 0);
        $base_def   = (int)($_POST['base_def'] ?? 0);

        $per_level_hp  = (int)($_POST['per_level_hp'] ?? 1);
        $per_level_atk = (int)($_POST['per_level_atk'] ?? 1);
        $per_level_def = (int)($_POST['per_level_def'] ?? 1);

        $material_exp     = (int)($_POST['material_exp'] ?? 100);
        $evolve_limit     = (int)($_POST['evolve_limit'] ?? 1);
        $evolve_multiplier= (float)($_POST['evolve_multiplier'] ?? 1.10);

        $thumbnail = $_POST['thumbnail'] ?? '';

        if ($name !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO cards 
                (card_name, charactor_id, base_hp, base_atk, base_def, 
                 per_level_hp, per_level_atk, per_level_def,
                 material_exp, evolve_limit, evolve_multiplier, thumbnail)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name, $char_id, $base_hp, $base_atk, $base_def,
                $per_level_hp, $per_level_atk, $per_level_def,
                $material_exp, $evolve_limit, $evolve_multiplier, $thumbnail
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

<nav>
<a href="dashboard.php">管理者ダッシュボード</a> | 
<a href="cards.php">カード一覧</a> | 
<a href="../logout.php">ログアウト</a>
</nav>

<h1>新規カード作成</h1>
<?php if(!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

<form method="post">
<h3>基本情報</h3>
<label>カード名: <input type="text" name="card_name" required></label><br><br>
<label>キャラクターID: <input type="number" name="charactor_id" value="0"></label><br><br>
<label>HP: <input type="number" name="base_hp" value="0"></label><br><br>
<label>ATK: <input type="number" name="base_atk" value="0"></label><br><br>
<label>DEF: <input type="number" name="base_def" value="0"></label><br><br>

<h3>レベルアップ時のステータス上昇量</h3>
<label>HP上昇量: <input type="number" name="per_level_hp" value="1"></label><br><br>
<label>ATK上昇量: <input type="number" name="per_level_atk" value="1"></label><br><br>
<label>DEF上昇量: <input type="number" name="per_level_def" value="1"></label><br><br>

<h3>強化・進化関連</h3>
<label>素材EXP: <input type="number" name="material_exp" value="100"></label><br><br>
<label>進化上限: <input type="number" name="evolve_limit" value="1"></label><br><br>
<label>進化係数: <input type="number" step="0.01" name="evolve_multiplier" value="1.10"></label><br><br>

<h3>サムネイル画像選択</h3>
<p>クリックして選択してください</p>
<div id="image-selection" style="display:flex; flex-wrap:wrap;">
    <?php
    $images = glob('../images/cards/*.webp'); // 画像フォルダ
    foreach ($images as $img):
    ?>
        <img src="<?= $img ?>" class="thumbnail" data-path="<?= $img ?>" 
             style="width:100px;cursor:pointer;margin:5px;border:2px solid transparent;">
    <?php endforeach; ?>
</div>
<input type="hidden" name="thumbnail" id="selected_image">
<p>選択中: <span id="selected_label">なし</span></p>

<script>
const thumbnails = document.querySelectorAll('.thumbnail');
const selectedInput = document.getElementById('selected_image');
const selectedLabel = document.getElementById('selected_label');

thumbnails.forEach(img => {
    img.addEventListener('click', () => {
        thumbnails.forEach(i => i.style.border = '2px solid transparent');
        img.style.border = '2px solid blue';
        selectedInput.value = img.dataset.path;
        selectedLabel.textContent = img.dataset.path;
    });
});
</script>

<button type="submit">作成</button>
</form>
