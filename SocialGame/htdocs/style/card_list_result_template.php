<div class="card-area">
<?php foreach ($cards as $c): ?>
    <div class="card-container">
        <!-- 画像 -->
        <div class="card-image-wrapper">
            <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>" alt="">
        </div>
    </div>
<?php endforeach; ?>
</div>

<style>
.card-area {
    display: flex;
    flex-wrap: wrap;        /* 複数行に折り返し */
    justify-content: center; /* 中央寄せ */
    gap: 10px;               /* カード間の余白 */
    max-width: 830px;        /* 横5枚を想定 */
    margin: 0 auto;
}

.card-container {
    width: 150px;   /* カード幅 */
    height: 150px;  /* カード高さ */
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    flex-shrink: 0; /* サイズ固定 */
}

.card-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 10px;
}
</style>
