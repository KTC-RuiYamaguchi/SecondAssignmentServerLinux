<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // =========================
    // 所持カード取得
    // =========================
    $stmt = $pdo->prepare("
        SELECT uc.*,
               c.card_name,
               c.base_hp, c.base_atk, c.base_def,
               c.thumbnail,
               c.max_level,
               c.evolved_card_id,
               r.rarity_name
        FROM user_cards uc
        JOIN cards c ON uc.card_id = c.card_id
        JOIN card_rarity r ON c.rarity_id = r.rarity_id
        WHERE uc.user_id = ?
        ORDER BY c.card_id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}

/* =========================
   強化結果
========================= */
$str_result = $_GET['strengthen_result'] ?? '';
$str_data = [
    'card_name' => $_GET['card_name'] ?? '',
    'old_level' => $_GET['old_level'] ?? '',
    'new_level' => $_GET['new_level'] ?? ''
];

/* =========================
   進化結果
========================= */
$evolve_result = $_GET['evolve_result'] ?? '';
$before_card = $after_card = null;

if ($evolve_result === 'success') {
    $before_id = $_GET['before_card_id'] ?? null;
    $after_id  = $_GET['after_card_id'] ?? null;

    if ($before_id && $after_id) {
        $stmt = $pdo->prepare("SELECT card_name, thumbnail FROM cards WHERE card_id = ?");
        $stmt->execute([$before_id]);
        $before_card = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->execute([$after_id]);
        $after_card = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>所持カード一覧</title>
<link rel="stylesheet" href="footer_style.css">
<style>
body { font-family: sans-serif; margin:0; padding-bottom:70px; } 
.card-area { display:flex; flex-wrap:wrap; justify-content:center; }

.card-area {
    display: flex;
    flex-wrap: wrap;
}

.card-container {
    position: relative;
    margin: 10px;
}
.card-container img {
    width: 150px;
    height: 150px;
    border-radius: 10px;
    cursor: pointer;
}
.favorite-star {
    position: absolute;
    top: 5px;
    right: 8px;
    color: gold;
    font-size: 20px;
    display: none;
}
.card-container.favorite .favorite-star {
    display: block;
}
.level {
    position: absolute;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,.6);
    color: #fff;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
}

/* ===== モーダル ===== */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s ease;
    z-index: 9999;
}
.modal.show {
    opacity: 1;
    pointer-events: auto;
}
.modal-content {
    background: #fff;
    padding: 20px 24px;
    border-radius: 10px;
    min-width: 320px;
    transform: scale(.9);
    transition: transform .25s ease;
}
.modal.show .modal-content {
    transform: scale(1);
}
.modal-content h2,
.modal-content h3 {
    margin-top: 0;
    text-align: center;
}
.modal-content button {
    margin-top: 12px;
    padding: 6px 14px;
}
</style>

<script>
function openModal(id){
    const m = document.getElementById(id);
    if (m) m.classList.add('show');
}
function closeModal(id){
    const m = document.getElementById(id);
    if (m) m.classList.remove('show');
}
</script>
</head>
<body>

<h1 style="text-align:center;">所持カード一覧</h1>

<div class="card-area">
<?php foreach ($cards as $c): ?>
<div class="card-container <?= $c['is_favorite'] ? 'favorite' : '' ?>">
    <img src="<?=htmlspecialchars($c['thumbnail'])?>"
         onclick="openModal('detail_<?=$c['id']?>')">
    <div class="favorite-star">★</div>
    <div class="level">Lv.<?=$c['level']?></div>

    <!-- 詳細モーダル -->
    <div id="detail_<?=$c['id']?>" class="modal">
        <div class="modal-content">
            <h3><?=htmlspecialchars($c['card_name'])?></h3>
            <p>Lv <?=$c['level']?> / <?=$c['max_level']?></p>
            <p>HP <?=$c['base_hp']?></p>
            <p>ATK <?=$c['base_atk']?></p>
            <p>DEF <?=$c['base_def']?></p>
            <p><?=$c['rarity_name']?></p>

            <!-- お気に入り -->
            <form method="post" action="card_action.php">
                <input type="hidden" name="action" value="<?= $c['is_favorite'] ? 'unfavorite' : 'favorite' ?>">
                <input type="hidden" name="user_card_id" value="<?=$c['id']?>">
                <button type="submit"><?=$c['is_favorite'] ? 'お気に入り解除' : 'お気に入り'?></button>
            </form>

            <!-- 強化 -->
            <form method="get" action="card_strengthen.php">
                <input type="hidden" name="target_card_id" value="<?=$c['id']?>">
                <button type="submit">強化</button>
            </form>

            <!-- 進化 -->
            <?php if ($c['evolved_card_id'] && $c['level'] >= $c['max_level']): ?>
            <form method="post" action="card_action.php">
                <input type="hidden" name="action" value="evolve">
                <input type="hidden" name="target_card_id" value="<?=$c['id']?>">
                <button type="submit">進化</button>
            </form>
            <?php elseif ($c['evolved_card_id']): ?>
                <p>Lv<?=$c['max_level']?>で進化可能</p>
            <?php endif; ?>

            <button type="button" onclick="closeModal('detail_<?=$c['id']?>')">閉じる</button>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- 強化結果 -->
<?php if ($str_result === 'success'): ?>
<div id="strengthenResultModal" class="modal show">
    <div class="modal-content">
        <h2>強化完了</h2>
        <p><?=htmlspecialchars($str_data['card_name'])?></p>
        <p>Lv <?=$str_data['old_level']?> → <?=$str_data['new_level']?></p>
        <button type="button" onclick="closeModal('strengthenResultModal')">OK</button>
    </div>
</div>
<?php endif; ?>

<!-- 進化結果 -->
<?php if ($evolve_result === 'success' && $before_card && $after_card): ?>
<div id="evolveResultModal" class="modal show">
    <div class="modal-content">
        <h2>進化完了</h2>

        <div style="display:flex;gap:20px;justify-content:center;align-items:center;">
            <div style="text-align:center;">
                <img src="<?=htmlspecialchars($before_card['thumbnail'])?>" width="120">
                <p><?=$before_card['card_name']?></p>
            </div>
            <div style="font-size:24px;">➡</div>
            <div style="text-align:center;">
                <img src="<?=htmlspecialchars($after_card['thumbnail'])?>" width="120">
                <p><?=$after_card['card_name']?></p>
            </div>
        </div>

        <button type="button" onclick="closeModal('evolveResultModal')">OK</button>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>

</body>
</html>
