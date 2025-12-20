<?php
$mode = $mode ?? 'display';
$modal_enabled  = ($mode === 'display');
$select_enabled = ($mode === 'select');
?>

<div class="card-area">
<?php foreach ($cards as $c):
    $is_disabled = !empty($c['is_favorite']) && $select_enabled;
    $container_class = 'card-container';
    if (!empty($c['is_favorite'])) $container_class .= ' favorite';
    if ($is_disabled) $container_class .= ' disabled-card';
?>
    <div class="<?= $container_class ?>"
        <?= $select_enabled && !$is_disabled ? "onclick=\"toggleSelect(this, {$c['id']})\"" : "" ?>>

        <!-- 画像 + ホロCanvas -->
        <div class="card-image-wrapper">
            <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>"
                <?= $modal_enabled ? "onclick=\"openModal('detail_{$c['id']}')\"" : "" ?>>

            <canvas class="holo-canvas" data-mask="/images/holomask.png"></canvas>
        </div>

        <!-- UI -->
        <div class="favorite-star">★</div>
        <div class="level">Lv.<?= $c['level'] ?></div>

        <?php if ($select_enabled): ?>
            <div class="select-overlay">選択中</div>
            <input type="hidden" name="material_card_ids[]" value="<?= $c['id'] ?>" disabled>
        <?php endif; ?>

    </div>
<?php endforeach; ?>
</div>

<style>
.card-area {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}
.card-container {
    width: 150px;
    height: 150px;
    position: relative;
    margin: 10px;
    cursor: pointer;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 14px rgba(0,0,0,0.18);
}
.card-container.disabled-card {
    pointer-events: none;
    opacity: 0.5;
}
.card-image-wrapper {
    width: 100%;
    height: 100%;
    position: relative;
}
.card-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
    display: block;
}
.holo-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    border-radius: 10px;
    mix-blend-mode: screen;
    opacity: 0.5;
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
    background: rgba(0,0,0,0.6);
    color: #fff;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
}
.select-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,128,255,0.45);
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 10px;
    opacity: 0;
    font-weight: bold;
    transition: opacity 0.2s ease;
}
.card-container.selected .select-overlay {
    opacity: 1;
}

/* モーダル */
.modal {
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
.modal.show {
    opacity: 1;
    pointer-events: auto;
}
.modal-content {
    background: #fff;
    padding: 20px 24px;
    border-radius: 10px;
    min-width: 320px;
    transform: scale(0.9);
    transition: transform 0.25s ease;
}
.modal.show .modal-content {
    transform: scale(1);
}
.modal-content h2,h3 {
    margin-top:0;
    text-align:center;
}
.modal-content button {
    margin-top:12px;
    padding:6px 14px;
}
</style>

<script>
// =========================
// 選択切替
// =========================
<?php if ($select_enabled): ?>
function toggleSelect(el, id) {
    const input = el.querySelector('input[name="material_card_ids[]"]');
    el.classList.toggle('selected');
    input.disabled = !el.classList.contains('selected');
}
<?php endif; ?>

// =========================
// モーダル開閉
// =========================
<?php if ($modal_enabled): ?>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
<?php endif; ?>

// =========================
// Holo Canvas描画
// =========================
document.querySelectorAll(".holo-canvas").forEach(canvas => {
    const ctx = canvas.getContext("2d");
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    // Canvasサイズ設定
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const W = rect.width;
    const H = rect.height;
    const maskImg = new Image();
    maskImg.src = canvas.dataset.mask;

    const sweepWidth = W * 0.6;
    const sweepStartX = -sweepWidth;
    const sweepStartY = 0;
    const sweepEndX = W;
    const angle = Math.PI / 6; // 30度
    const sweepEndY = sweepEndX * Math.tan(angle);

    const duration = 2200; // 1回のスイープ時間（ms）
    const restTime = 1000; // 休憩時間（ms）
    let resting = false;
    let progress = 0;      // 0～1
    let lastTime = performance.now();

    // イーズインアウト関数
    function easeInSine(t) {
        return 1 - Math.cos((t*Math.PI)/2);
    }

    maskImg.onload = function draw() {
        function frame() {
            const now = performance.now();
            const delta = now - lastTime;
            lastTime = now;

            // スイープ進行
            if (!resting) {
                progress += delta / duration;
                if (progress >= 1) {
                    progress = 1;
                    resting = true;
                    setTimeout(() => {
                        progress = 0;
                        resting = false;
                    }, restTime);
                }
            }

            // イーズ適用
            const t = easeInSine(progress);
            const sweepX = sweepStartX + (sweepEndX - sweepStartX) * t;
            const sweepY = sweepStartY + (sweepEndY - sweepStartY) * t;

            ctx.clearRect(0, 0, W, H);

            // 1. カード全体の薄い光沢
            ctx.globalCompositeOperation = "source-over";
            ctx.fillStyle = "rgba(255,255,255,0.2)";
            ctx.fillRect(0, 0, W, H);

            // 2. スイープ虹グラデーション
            if (!resting) {
                ctx.save();
                ctx.globalCompositeOperation = "source-over";

                const grad = ctx.createLinearGradient(
                    sweepX, sweepY,
                    sweepX + sweepWidth, sweepY + sweepWidth * Math.tan(angle)
                );

                grad.addColorStop(0, "rgba(255,119,115,0)");
                grad.addColorStop(0.2, "rgba(255,237,95,0.85)");
                grad.addColorStop(0.4, "rgba(168,255,95,0.85)");
                grad.addColorStop(0.6, "rgba(131,255,247,0.85)");
                grad.addColorStop(0.8, "rgba(216,117,255,0.85)");
                grad.addColorStop(1, "rgba(255,119,115,0)");

                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, W, H);
                ctx.restore();
            }

            // 3. カード形状マスク
            ctx.globalCompositeOperation = "destination-in";
            ctx.drawImage(maskImg, 0, 0, W, H);

            requestAnimationFrame(frame);
        }
        frame();
    }
});


</script>

<?php if ($modal_enabled): ?>
<?php foreach ($cards as $c): ?>
<div id="detail_<?= $c['id'] ?>" class="modal">
    <div class="modal-content">
        <h3><?= htmlspecialchars($c['card_name'], ENT_QUOTES) ?></h3>
        <p>Lv <?= $c['level'] ?> / <?= $c['max_level'] ?></p>
        <p>HP <?= $c['base_hp'] ?></p>
        <p>ATK <?= $c['base_atk'] ?></p>
        <p>DEF <?= $c['base_def'] ?></p>
        <p><?= $c['rarity_name'] ?? '' ?></p>

        <?php if (isset($c['is_favorite'])): ?>
        <form method="post" action="card_action.php">
            <input type="hidden" name="action" value="<?= $c['is_favorite'] ? 'unfavorite' : 'favorite' ?>">
            <input type="hidden" name="user_card_id" value="<?= $c['id'] ?>">
            <button type="submit">
                <?= $c['is_favorite'] ? 'お気に入り解除' : 'お気に入り' ?>
            </button>
        </form>
        <?php endif; ?>

        <?php if (!empty($c['max_level'])): ?>
        <form method="get" action="card_strengthen.php">
            <input type="hidden" name="target_card_id" value="<?= $c['id'] ?>">
            <button type="submit">強化</button>
        </form>
        <?php endif; ?>

        <?php if (!empty($c['evolved_card_id']) && $c['level'] >= $c['max_level']): ?>
        <form method="post" action="card_action.php">
            <input type="hidden" name="action" value="evolve">
            <input type="hidden" name="target_card_id" value="<?= $c['id'] ?>">
            <button type="submit">進化</button>
        </form>
        <?php elseif (!empty($c['evolved_card_id'])): ?>
            <p>Lv<?= $c['max_level'] ?>で進化可能</p>
        <?php endif; ?>

        <button type="button" onclick="closeModal('detail_<?= $c['id'] ?>')">閉じる</button>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
