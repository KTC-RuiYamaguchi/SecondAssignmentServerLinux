<?php
session_start();
require 'db_connect.php';

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 所持コイン取得
$stmt = $pdo->prepare("SELECT user_coins FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userCoins = $user['user_coins'] ?? 0;

// ガチャ一覧取得
$stmt = $pdo->query("SELECT gacha_id, gacha_name, description, cost, is_active 
                     FROM gachas 
                     WHERE is_active = 1 
                     ORDER BY gacha_id ASC");
$gachas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ガチャ一覧</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
}
h1 {
    text-align: center;
    margin-top: 20px;
}
.gacha-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    max-height: 80vh;
    overflow-y: auto;
    padding: 10px;
}
.gacha-card {
    width: 320px;
    background: #fff;
    margin: 10px 0;
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.gacha-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.18);
}
.gacha-card h2 {
    margin: 0 0 8px;
    font-size: 18px;
}
.gacha-card p {
    margin: 4px 0;
    font-size: 14px;
    text-align: center;
}

/* 横並びガチャボタン */
.result-gacha-btn {
    margin: 0 10px;
    padding: 14px 24px;
    font-size: 18px;
    border-radius: 8px;
    border: none;
    background-color: #3498db;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
}
.result-gacha-btn:hover {
    background-color: #2980b9;
}
.result-gacha-btn br {
    font-size: 14px;
}

/* ポップアップ用 */
.popup {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
    z-index: 9999;
}
.popup.show {
    opacity: 1;
    pointer-events: auto;
}
.popup-content {
    background: #fff;
    padding: 20px 24px;
    border-radius: 10px;
    min-width: 300px;
    text-align: center;
    transform: scale(0.9);
    transition: transform 0.25s ease;
}
.popup.show .popup-content {
    transform: scale(1);
}
.popup-content button {
    margin: 8px 6px 0 6px;
    padding: 6px 14px;
    cursor: pointer;
    border-radius: 6px;
    border: none;
    font-weight: bold;
}
#popup-ok-btn {
    background-color: #3498db;
    color: #fff;
}
.popup-content button[type="button"] {
    background-color: #ccc;
    color: #333;
}

/* 所持コイン表示 */
#userCoins {
    text-align: center;
    margin: 12px 0;
    font-weight: bold;
}
</style>
</head>
<body>

<h1>ガチャ一覧</h1>

<!-- 所持コイン -->
<div id="userCoins">所持コイン<br><?= $userCoins ?></div>

<div class="gacha-list">
<?php foreach ($gachas as $g): ?>
    <div class="gacha-card">
        <h2><?= htmlspecialchars($g['gacha_name'], ENT_QUOTES) ?></h2>
        <p><?= htmlspecialchars($g['description'], ENT_QUOTES) ?></p>

        <!-- 1回 / 10回ガチャボタン -->
        <div>
            <button class="result-gacha-btn" 
                    data-gacha="<?= $g['gacha_id'] ?>" 
                    data-cost="<?= $g['cost'] ?>" 
                    data-times="1"
                    data-name="<?= htmlspecialchars($g['gacha_name'], ENT_QUOTES) ?>">
                1回引く<br><?= $g['cost'] ?>
            </button>
            <button class="result-gacha-btn" 
                    data-gacha="<?= $g['gacha_id'] ?>" 
                    data-cost="<?= $g['cost'] ?>" 
                    data-times="10"
                    data-name="<?= htmlspecialchars($g['gacha_name'], ENT_QUOTES) ?>">
                10回引く<br><?= $g['cost']*10 ?>
            </button>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- 確認ポップアップ -->
<div id="confirmPopup" class="popup">
    <div class="popup-content">
        <h3>ガチャ確認</h3>
        <p id="popup-info"></p>
        <p>所持コイン<br>
            <span id="popup-before"></span> → <span id="popup-after"></span>
        </p>
        <form id="popupForm" method="post" action="gacha_execute.php">
            <input type="hidden" name="gacha_id" id="popup_gacha_id">
            <input type="hidden" name="times" id="popup_times">
            <button type="submit" id="popup-ok-btn"></button>
            <button type="button" onclick="closePopup()">キャンセル</button>
        </form>
    </div>
</div>

<script>
const popup = document.getElementById('confirmPopup');
const popupInfo = document.getElementById('popup-info');
const popupBefore = document.getElementById('popup-before');
const popupAfter = document.getElementById('popup-after');
const popupGachaId = document.getElementById('popup_gacha_id');
const popupTimes = document.getElementById('popup_times');
const popupOkBtn = document.getElementById('popup-ok-btn');

const userCoinsBefore = parseInt(<?= $userCoins ?>);

document.querySelectorAll('.result-gacha-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const gachaId = btn.dataset.gacha;
        const cost = parseInt(btn.dataset.cost);
        const times = parseInt(btn.dataset.times);
        const gachaName = btn.dataset.name;
        const totalCost = cost * times;

        popupInfo.innerHTML = `コイン <span style="color:#e67e22; font-weight:bold;">${totalCost}</span> 枚を消費して<br>「${gachaName}」を引きますか?`;

        popupBefore.textContent = userCoinsBefore;
        popupAfter.textContent = userCoinsBefore - totalCost;

        popupGachaId.value = gachaId;
        popupTimes.value = times;
        popupOkBtn.textContent = `${times}回引く`;

        popup.classList.add('show');
    });
});

function closePopup() { 
    popup.classList.remove('show'); 
}
</script>

<?php include 'style/footer.php'; ?>

