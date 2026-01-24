<div id="transitionOverlay"></div>

<link rel="stylesheet" href="style/footer_style.css">

<div class="footer">
    <a class="footer-button" href="logout.php">ログアウト</a>
    <a class="footer-button" href="dashboard.php">ホーム</a>
    <a class="footer-button" href="user_cards_list.php">カード一覧</a>
    <a class="footer-button" href="gacha_top.php">ガチャ</a>
    <!-- 他のボタンも追加可能 -->
</div>

<!-- フッター用CSS -->
<link rel="stylesheet" href="/style/footer_style.css">

<script>
// ページ読み込み時にフェードイン
window.addEventListener('load', () => {
    const overlay = document.getElementById('transitionOverlay');
    // 1フレーム後にfadeoutクラス追加でフェードイン開始
    requestAnimationFrame(() => {
        overlay.classList.add('fadeout');
    });
});

// フッターボタン押下でフェードアウト → 遷移
document.querySelectorAll('.footer-button').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const overlay = document.getElementById('transitionOverlay');
        overlay.classList.remove('fadeout');
        overlay.classList.add('active');

        setTimeout(() => {
            window.location.href = btn.href;
        }, 450); // CSS transitionに合わせる
    });
});
</script>
