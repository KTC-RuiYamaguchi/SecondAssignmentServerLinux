<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

$stmt = $pdo->prepare("
    SELECT uc.*, c.card_name, c.base_hp, c.base_atk, c.base_def,
           c.thumbnail, c.max_level, c.evolved_card_id, r.rarity_name
    FROM user_cards uc
    JOIN cards c ON uc.card_id = c.card_id
    JOIN card_rarity r ON c.rarity_id = r.rarity_id
    WHERE uc.user_id = ?
    ORDER BY c.card_id
");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$str_result = $_GET['strengthen_result'] ?? '';
$str_data = [
    'card_name' => $_GET['card_name'] ?? '',
    'old_level' => $_GET['old_level'] ?? '',
    'new_level' => $_GET['new_level'] ?? ''
];

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

$mode = 'display';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>所持カード一覧</title>

<link rel="stylesheet" href="style/card_style.css">
<script>
function openModal(id){ document.getElementById(id)?.classList.add('show'); }
function closeModal(id){ document.getElementById(id)?.classList.remove('show'); }
</script>
</head>
<body>

<?php include 'style/footer.php'; ?>

<h1 style="text-align:center;">所持カード一覧</h1>

<?php include 'style/card_list_template.php'; ?>

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


</body>
</html>
