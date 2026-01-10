<?php
session_start();

if (!isset($_SESSION['gacha_result'])) {
    echo "ガチャ結果がありません。";
    exit;
}

$cards       = $_SESSION['gacha_result']['cards'];
$userCoins   = $_SESSION['gacha_result']['user_coins_after']; // 更新後の所持コイン
$userCoinsBefore = $_SESSION['gacha_result']['user_coins_before'];
$gachaId     = $_SESSION['gacha_result']['gacha_id'];
$gachaName   = $_SESSION['gacha_result']['gacha_name'];
$gachaCost   = $_SESSION['gacha_result']['gacha_cost'];
$times       = $_SESSION['gacha_result']['times'];
?>

<h2 style="text-align:center;">ガチャ結果</h2>

<?php include 'style/card_list_result_template.php'; ?>

<!-- 所持コイン表示 -->
<div style="text-align:center; margin: 12px 0; font-weight:bold;">
    所持コイン<br><?= $userCoins ?>
</div>

<!-- ガチャボタン -->
<div style="text-align:center; margin-top:20px;">
    <!-- ガチャ再挑戦ボタン -->
    <button class="result-gacha-btn" 
            data-gacha="<?= $gachaId ?>" 
            data-cost="<?= $gachaCost ?>" 
            data-times="1"
            data-name="<?= htmlspecialchars($gachaName, ENT_QUOTES) ?>">
        1回引く<br><?= $gachaCost ?>
    </button>
    <button class="result-gacha-btn" 
            data-gacha="<?= $gachaId ?>" 
            data-cost="<?= $gachaCost ?>" 
            data-times="10"
            data-name="<?= htmlspecialchars($gachaName, ENT_QUOTES) ?>">
        10回引く<br><?= $gachaCost * 10 ?>
    </button>

    <!-- ガチャトップに戻るボタン（小さめ） -->
    <div style="margin-top:12px;">
        <form method="get" action="gacha_top.php" style="display:inline;">
            <button type="submit" class="result-back-btn">ガチャトップに戻る</button>
        </form>
    </div>
</div>

<style>
/* 既存のガチャボタン */
.result-gacha-btn {
    margin: 0 10px;
    padding: 12px 20px;
    font-size: 16px;
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

/* ガチャトップボタンは少し小さめ */
.result-back-btn {
    padding: 6px 14px;
    font-size: 14px;
    border-radius: 6px;
    border: none;
    background-color: #7f8c8d;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
}
.result-back-btn:hover {
    background-color: #636e72;
}
</style>



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

<style>
/* ポップアップ用スタイル */
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
</style>

<script>
const popup = document.getElementById('confirmPopup');
const popupInfo = document.getElementById('popup-info');
const popupBefore = document.getElementById('popup-before');
const popupAfter = document.getElementById('popup-after');
const popupGachaId = document.getElementById('popup_gacha_id');
const popupTimes = document.getElementById('popup_times');
const popupOkBtn = document.getElementById('popup-ok-btn');

const userCoinsBefore = parseInt(<?= $userCoinsBefore ?>);

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
