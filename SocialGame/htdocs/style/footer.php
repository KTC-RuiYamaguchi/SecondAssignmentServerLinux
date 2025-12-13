<div id="transitionOverlay"></div>

<div class="footer">
    <a class="footer-button" href="dashboard.php" onclick="event.preventDefault(); transitionTo(this.href)">ホーム</a>
    <a class="footer-button" href="user_cards_list.php" onclick="event.preventDefault(); transitionTo(this.href)">カード一覧</a>
    <!-- 他のボタンも追加可能 -->
</div>

<script>
function transitionTo(url){
    const overlay = document.getElementById('transitionOverlay');
    overlay.style.pointerEvents = 'auto';
    overlay.style.opacity = '1';
    setTimeout(()=>{ window.location.href = url; }, 450);
}
</script>
