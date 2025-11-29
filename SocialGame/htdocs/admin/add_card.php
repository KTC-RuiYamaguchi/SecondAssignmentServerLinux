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
$max_level = 50; // デフォルトの最大レベルを50に設定

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 最大レベルをexp_tableから取得
    $stmt = $pdo->query("SELECT MAX(level) AS max_level FROM exp_table");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_level = $result['max_level'] ?? 50; // 最大レベルが取得できない場合は50を使用

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name        = $_POST['card_name'] ?? '';
        $rarity_id   = (int)($_POST['rarity_id'] ?? 0);
        $base_hp     = (int)($_POST['base_hp'] ?? 0);
        $base_atk    = (int)($_POST['base_atk'] ?? 0);
        $base_def    = (int)($_POST['base_def'] ?? 0);

        $per_level_hp   = (int)($_POST['per_level_hp'] ?? 1);
        $per_level_atk  = (int)($_POST['per_level_atk'] ?? 1);
        $per_level_def  = (int)($_POST['per_level_def'] ?? 1);

        $material_exp   = (int)($_POST['material_exp'] ?? 100);
        $max_level      = (int)($_POST['max_level'] ?? $max_level); // 最大レベルの取得

        $thumbnail = $_POST['thumbnail'] ?? '';
        $evolved_card_id = (int)($_POST['evolved_card_id'] ?? 0); // 進化後のカードID

        if ($name !== '') {
            // 新しいカードを挿入
            $stmt = $pdo->prepare("
                INSERT INTO cards 
                (rarity_id, card_name, max_level, base_hp, base_atk, base_def, 
                 material_exp, evolved_card_id, thumbnail, per_level_hp, per_level_atk, per_level_def)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $rarity_id, $name, $max_level, $base_hp, $base_atk, $base_def, 
                $material_exp, $evolved_card_id, $thumbnail, 
                $per_level_hp, $per_level_atk, $per_level_def
            ]);

            // メッセージ
            $msg = "カードを作成しました。";

            // 進化後のカードIDを進化前カードに設定
            if ($evolved_card_id) {
                // 進化後のカードがある場合、進化前のカードに進化後のカードIDを設定
                $stmt = $pdo->prepare("UPDATE cards SET evolved_card_id = ? WHERE card_name = ?");
                $stmt->execute([$evolved_card_id, $name]);
            }
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

<form method="post" id="card-form">
<h3>基本情報</h3>
<label>カード名: <input type="text" name="card_name" required></label><br><br>

<label>レアリティ: 
<select name="rarity_id" required>
    <option value="">選択してください</option>
    <?php
    // レアリティの選択肢をデータベースから取得
    $stmt = $pdo->query("SELECT rarity_id, rarity_name FROM card_rarity");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value=\"{$row['rarity_id']}\">{$row['rarity_name']}</option>";
    }
    ?>
</select>
</label><br><br>

<label>進化後カード: 
<select name="evolved_card_id">
    <option value="0">進化なし</option>
    <?php
    // すべてのカードを選択肢として表示（進化後カードも含む）
    $stmt = $pdo->query("SELECT card_id, card_name FROM cards ORDER BY card_name"); // カード名でアルファベット順に並べる
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value=\"{$row['card_id']}\">{$row['card_name']}</option>";
    }
    ?>
</select>
</label><br><br>

<label>最大レベル: <input type="number" name="max_level" value="<?= $max_level ?>" min="1" max="<?= $max_level ?>" required></label><br><br>

<label>HP: <input type="number" name="base_hp" value="0"></label><br><br>
<label>ATK: <input type="number" name="base_atk" value="0"></label><br><br>
<label>DEF: <input type="number" name="base_def" value="0"></label><br><br>

<h3>レベルアップ時のステータス上昇量</h3>
<label>HP上昇量: <input type="number" name="per_level_hp" value="1"></label><br><br>
<label>ATK上昇量: <input type="number" name="per_level_atk" value="1"></label><br><br>
<label>DEF上昇量: <input type="number" name="per_level_def" value="1"></label><br><br>

<h3>強化・進化関連</h3>
<label>素材EXP: <input type="number" name="material_exp" value="100"></label><br><br>

<h3>サムネイル画像選択</h3>
<button type="button" id="openModal">サムネイル画像を選択</button>

<!-- ポップアップ（モーダル） -->
<div id="imageModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5); z-index:1000;">
    <div style="background: white; padding: 20px; max-width: 600px; margin: 50px auto; overflow-y:auto;">
        <h3>画像を選択してください</h3>
        <div id="image-selection" style="display:flex; flex-wrap:wrap; justify-content:space-between; max-height: 400px; overflow-y:auto;">
            <?php
            $images = glob('../images/cards/*.webp'); // 画像フォルダ
            sort($images); // 画像をアルファベット順にソート
            foreach ($images as $img):
            ?>
                <img src="<?= $img ?>" class="thumbnail" data-path="<?= $img ?>" 
                     style="width:100px;cursor:pointer;margin:5px;border:2px solid transparent;">
            <?php endforeach; ?>
        </div>
        <p>選択中: <span id="selected_label">なし</span></p>
        <button type="button" id="closeModal">閉じる</button>
    </div>
</div>

<input type="hidden" name="thumbnail" id="selected_image">
<p>選択した画像プレビュー: <img id="image_preview" src="" style="width:100px; height:100px; display:none; border: 1px solid #ccc;"></p>

<button type="button" id="showConfirmation">作成内容確認</button>

<!-- 確認ポップアップ -->
<div id="confirmationModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.5); z-index:1000;">
    <div style="background: white; padding: 20px; max-width: 600px; margin: 50px auto;">
        <h3>作成内容を確認</h3>
        <div id="confirmationContent"></div>
        <button type="button" id="confirmCreate">作成</button>
        <button type="button" id="cancelCreate">キャンセル</button>
    </div>
</div>

</form>

<script>
// 画像選択モーダルの表示
const openModalBtn = document.getElementById('openModal');
const imageModal = document.getElementById('imageModal');
const closeModalBtn = document.getElementById('closeModal');
const thumbnails = document.querySelectorAll('.thumbnail');
const selectedInput = document.getElementById('selected_image');
const selectedLabel = document.getElementById('selected_label');
const imagePreview = document.getElementById('image_preview');

openModalBtn.addEventListener('click', () => {
    imageModal.style.display = 'block';
});

closeModalBtn.addEventListener('click', () => {
    imageModal.style.display = 'none';
});

thumbnails.forEach(img => {
    img.addEventListener('click', () => {
        thumbnails.forEach(i => i.style.border = '2px solid transparent');
        img.style.border = '2px solid blue';
        selectedInput.value = img.dataset.path;
        selectedLabel.textContent = img.dataset.path;
        imagePreview.src = img.dataset.path;  // プレビュー画像を更新
        imagePreview.style.display = 'block';  // プレビュー画像を表示
    });
});

// 作成内容確認
const showConfirmationBtn = document.getElementById('showConfirmation');
const confirmationModal = document.getElementById('confirmationModal');
const confirmationContent = document.getElementById('confirmationContent');
const confirmCreateBtn = document.getElementById('confirmCreate');
const cancelCreateBtn = document.getElementById('cancelCreate');

showConfirmationBtn.addEventListener('click', () => {
    // 確認内容を設定
    confirmationContent.innerHTML = `
        <p><strong>カード名:</strong> ${document.querySelector('[name="card_name"]').value}</p>
        <p><strong>レアリティ:</strong> ${document.querySelector('[name="rarity_id"]').options[document.querySelector('[name="rarity_id"]').selectedIndex].text}</p>
        <p><strong>進化後カード:</strong> ${document.querySelector('[name="evolved_card_id"]').value == 0 ? 'なし' : 'あり'}</p>
        <p><strong>最大レベル:</strong> ${document.querySelector('[name="max_level"]').value}</p>
        <p><strong>選択した画像:</strong> <img src="${selectedInput.value}" style="width:100px;"></p>
    `;
    confirmationModal.style.display = 'block';
});

confirmCreateBtn.addEventListener('click', () => {
    document.getElementById('card-form').submit();  // フォームを送信
});

cancelCreateBtn.addEventListener('click', () => {
    confirmationModal.style.display = 'none';  // キャンセルでモーダルを閉じる
});
</script>

