<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT user_name, user_coins FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ユーザーが存在しません";
    exit;
}
$user_name = $user['user_name'];
$user_coins = $user['user_coins'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ダッシュボード</title>
<style>
body {
    margin:0;
    font-family:"Segoe UI", Roboto, sans-serif;
    background:#f4f6f8;
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
}
main {
    flex:1;
    padding:20px;
    text-align:center;
}
#coinImage {
    user-select: none;
    -webkit-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
    -ms-user-drag: none;
    cursor: pointer;
    width:150px;
    margin-top:20px;
    transition: transform 0.1s;
}
#coinImage:active {
    transform: scale(0.95);
}
#coinCount {
    font-size:24px;
    margin-top:10px;
    color:#e67e22;
    font-weight:bold;
}
.fly-text {
    position: absolute;
    color:#e67e22;
    font-weight:bold;
    font-size:20px;
    pointer-events:none;
    user-select:none;
    animation: floatUp 1s ease-out forwards;
}
@keyframes floatUp {
    0% { transform: translate(0,0) scale(1); opacity:1; }
    100% { transform: translate(0,-50px) scale(1.2); opacity:0; }
}
.message {
    color:green;
    margin-top:10px;
}
#volumeToggle {
    cursor: pointer;
    width:40px;
    margin-top:20px;
}
</style>
</head>
<body>

<?php include 'style/footer.php'; ?>

<main>
    <h1>ようこそ、<?= htmlspecialchars($user_name, ENT_QUOTES) ?> さん</h1>
    <p>画像をクリックしてコインを入手できます</p>

    <!-- クリック対象の画像 -->
    <img src="images/money_kasoutsuuka_kusa.png" alt="コイン" id="coinImage">

    <!-- 消音ボタン -->
    <div>
        <img src="images/volume_on.png" id="volumeToggle" alt="音量切替" title="クリックでON/OFF切替">
    </div>

    <div>所持コイン: <span id="coinCount"><?= $user_coins ?></span></div>
    <div class="message" id="message"></div>

    <!-- 効果音 -->
    <audio id="coinSE" src="sounds/coin_se.mp3" preload="auto" volume="0.2"></audio>
</main>

<script>
const coinImage = document.getElementById('coinImage');
const coinCount = document.getElementById('coinCount');
const coinSE = document.getElementById('coinSE');
const volumeToggle = document.getElementById('volumeToggle');

// 初期はON
let seEnabled = true;

// 消音ボタンクリックで切替
volumeToggle.addEventListener('click', () => {
    seEnabled = !seEnabled;
    volumeToggle.src = seEnabled ? 'images/volume_on.png' : 'images/volume_off.png';
});

// コイン画像クリック処理
coinImage.addEventListener('click', async (e) => {
    try {
        // SE再生
        if(seEnabled){
            const se = coinSE.cloneNode();
            se.play();
        }

        const resp = await fetch('get_coin.php', { method:'POST' });
        const data = await resp.json();
        if(data.success){
            coinCount.textContent = data.coins;

            // フライテキスト
            const fly = document.createElement('div');
            fly.className = 'fly-text';
            fly.textContent = `+${data.added}コイン`;

            const offsetX = Math.random()*60 - 30;
            fly.style.left = `${e.pageX + offsetX}px`;
            fly.style.top = `${e.pageY}px`;

            document.body.appendChild(fly);
            fly.addEventListener('animationend', ()=>fly.remove());
        }
    } catch(err){
        console.error('通信エラー', err);
    }
});
</script>

</body>
</html>
