<?php
$mode = $mode ?? 'display';
$modal_enabled  = ($mode === 'display');
$select_enabled = ($mode === 'select');
?>

<!-- =========================
     カードエリア
========================= -->
<div class="card-area">
<?php foreach ($cards as $c):
    $is_disabled = !empty($c['is_favorite']) && $select_enabled;
    $container_class = 'card-container';
    if (!empty($c['is_favorite'])) $container_class .= ' favorite';
    if ($is_disabled) $container_class .= ' disabled-card';
?>
    <div class="<?= $container_class ?>"
        <?= ($select_enabled && !$is_disabled) ? "onclick=\"toggleSelect(this)\"" : "" ?>>

        <div class="card-image-wrapper">
            <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>"
                 <?= $modal_enabled ? "onclick=\"openModal('detail_{$c['id']}')\"" : "" ?>>
            <canvas class="holo-canvas" data-mask="/images/holomask.png"></canvas>
        </div>

        <div class="favorite-star">★</div>
        <div class="level">Lv.<?= (int)$c['level'] ?></div>

        <?php if ($select_enabled): ?>
            <div class="select-overlay">選択中</div>
            <input type="hidden"
                   name="material_card_ids[]"
                   value="<?= (int)$c['id'] ?>"
                   disabled>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<!-- =========================
     モーダル（displayモードのみ）
========================= -->
<?php if ($modal_enabled): ?>
<?php foreach ($cards as $c): ?>
<div id="detail_<?= $c['id'] ?>" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal('detail_<?= $c['id'] ?>')">×</button>

        <div class="modal-card-image">
            <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($c['card_name'], ENT_QUOTES) ?>">
        </div>

        <h3 class="modal-card-name"><?= htmlspecialchars($c['card_name'], ENT_QUOTES) ?></h3>
        <p class="modal-card-rarity"><?= htmlspecialchars($c['rarity_name'] ?? '', ENT_QUOTES) ?></p>

        <div class="modal-stats">
            <div class="stat"><span>Lv</span> <?= (int)$c['level'] ?> / <?= (int)($c['max_level'] ?? 0) ?></div>
            <div class="stat"><span>HP</span> <?= (int)($c['base_hp'] ?? 0) ?></div>
            <div class="stat"><span>ATK</span> <?= (int)($c['base_atk'] ?? 0) ?></div>
            <div class="stat"><span>DEF</span> <?= (int)($c['base_def'] ?? 0) ?></div>
        </div>

        <div class="modal-actions">
            <?php if (isset($c['is_favorite'])): ?>
            <form method="post" action="card_action.php">
                <input type="hidden" name="action" value="<?= $c['is_favorite'] ? 'unfavorite' : 'favorite' ?>">
                <input type="hidden" name="user_card_id" value="<?= (int)$c['id'] ?>">
                <button class="action-btn"><?= $c['is_favorite'] ? 'お気に入り解除' : 'お気に入り' ?></button>
            </form>
            <?php endif; ?>

            <?php if (!empty($c['max_level'])): ?>
            <form method="get" action="card_strengthen.php">
                <input type="hidden" name="target_card_id" value="<?= (int)$c['id'] ?>">
                <button class="action-btn strengthen">強化</button>
            </form>
            <?php endif; ?>

            <?php if (!empty($c['evolved_card_id']) && $c['level'] >= $c['max_level']): ?>
            <form method="post" action="card_action.php">
                <input type="hidden" name="action" value="evolve">
                <input type="hidden" name="target_card_id" value="<?= (int)$c['id'] ?>">
                <button class="action-btn evolve">進化</button>
            </form>
            <?php elseif (!empty($c['evolved_card_id'])): ?>
            <p class="evolve-note">Lv<?= (int)$c['max_level'] ?>で進化可能</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- =========================
     スタイル
========================= -->
<style>
.card-area {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding-bottom: 160px;
}
.card-container {
    width:150px;
    height:150px;
    margin:10px;
    position:relative;
    cursor:pointer;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.card-container:hover { transform: scale(1.05); }
.card-container.disabled-card { pointer-events:none; opacity:0.5; }

.card-image-wrapper,
.card-image-wrapper img { width:100%; height:100%; object-fit:cover; }

.holo-canvas {
    position:absolute;
    inset:0;
    pointer-events:none;
    mix-blend-mode:screen;
    opacity:0.5;
}

.favorite-star {
    position:absolute;
    top:5px;
    right:8px;
    color:gold;
    font-size:20px;
    display:none;
}
.card-container.favorite .favorite-star { display:block; }

.level {
    position:absolute;
    bottom:6px;
    left:50%;
    transform:translateX(-50%);
    background:rgba(0,0,0,0.6);
    color:#fff;
    padding:2px 6px;
    border-radius:4px;
    font-size:13px;
}

.select-overlay {
    position:absolute;
    inset:0;
    background:rgba(0,128,255,0.45);
    display:flex;
    justify-content:center;
    align-items:center;
    opacity:0;
    font-weight:bold;
}
.card-container.selected .select-overlay { opacity:1; }

/* モーダル */
.modal {
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    display:flex;
    justify-content:center;
    align-items:center;
    opacity:0;
    pointer-events:none;
    z-index:10000;
    transition: opacity 0.3s ease;
}
.modal.show { opacity:1; pointer-events:auto; }

.modal-content {
    background:#1e1e2f;
    border-radius:12px;
    padding:20px;
    width:320px;
    max-width:90%;
    max-height:80vh;
    overflow-y:auto;
    position: relative;
    text-align:center;
    color:#fff;
    transform: scale(0.8);
    transition: transform 0.3s ease;
}
.modal.show .modal-content { transform: scale(1); }

.modal-close {
    position:absolute;
    top:10px;
    right:10px;
    background:transparent;
    border:none;
    color:#fff;
    font-size:24px;
    cursor:pointer;
}

.modal-card-image img {
    width:150px;
    height:150px;
    border-radius:10px;
    border:2px solid gold;
    margin-bottom:10px;
}

.modal-card-name { font-size:18px; margin:5px 0; }
.modal-card-rarity { font-size:14px; margin-bottom:10px; color:#ffdd57; }

.modal-stats {
    display:flex;
    justify-content:space-around;
    margin-bottom:15px;
    font-size:13px;
    background: rgba(255,255,255,0.05);
    padding:6px 0;
    border-radius:6px;
}
.modal-stats .stat span { display:block; font-size:11px; color:#aaa; }

.modal-actions { display:flex; flex-direction:column; gap:6px; }
.action-btn {
    padding:6px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    background: #3498db;
    color:#fff;
    font-weight:bold;
    transition: background 0.2s ease;
}
.action-btn:hover { background:#2980b9; }
.action-btn.strengthen { background:#2ecc71; }
.action-btn.strengthen:hover { background:#27ae60; }
.action-btn.evolve { background:#e67e22; }
.action-btn.evolve:hover { background:#d35400; }

.evolve-note { font-size:12px; color:#ffcc00; margin-top:4px; }
</style>

<!-- =========================
     スクリプト
========================= -->
<script>
<?php if ($select_enabled): ?>
function toggleSelect(el){
    const input = el.querySelector('input[name="material_card_ids[]"]');
    el.classList.toggle('selected');
    input.disabled = !el.classList.contains('selected');
}
<?php endif; ?>

<?php if ($modal_enabled): ?>
function openModal(id){
    document.getElementById(id)?.classList.add('show');
}
function closeModal(id){
    document.getElementById(id)?.classList.remove('show');
}
<?php endif; ?>

// =========================
// Holo Canvas描画
// =========================
document.querySelectorAll(".holo-canvas").forEach(canvas => {
    const ctx = canvas.getContext("2d");
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

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
    const angle = Math.PI / 6;
    const sweepEndY = sweepEndX * Math.tan(angle);

    const duration = 2200;
    const restTime = 1000;
    let resting = false;
    let progress = 0;
    let lastTime = performance.now();

    function easeInSine(t){ return 1 - Math.cos((t*Math.PI)/2); }

    maskImg.onload = function(){
        function frame(){
            const now = performance.now();
            const delta = now - lastTime;
            lastTime = now;

            if(!resting){
                progress += delta / duration;
                if(progress >= 1){
                    progress = 1;
                    resting = true;
                    setTimeout(()=>{progress=0; resting=false;}, restTime);
                }
            }

            const t = easeInSine(progress);
            const sweepX = sweepStartX + (sweepEndX - sweepStartX) * t;
            const sweepY = sweepStartY + (sweepEndY - sweepStartY) * t;

            ctx.clearRect(0,0,W,H);
            ctx.globalCompositeOperation="source-over";
            ctx.fillStyle="rgba(255,255,255,0.2)";
            ctx.fillRect(0,0,W,H);

            if(!resting){
                const grad = ctx.createLinearGradient(
                    sweepX, sweepY,
                    sweepX+sweepWidth,
                    sweepY+sweepWidth*Math.tan(angle)
                );
                grad.addColorStop(0,"rgba(255,119,115,0)");
                grad.addColorStop(0.2,"rgba(255,237,95,0.85)");
                grad.addColorStop(0.4,"rgba(168,255,95,0.85)");
                grad.addColorStop(0.6,"rgba(131,255,247,0.85)");
                grad.addColorStop(0.8,"rgba(216,117,255,0.85)");
                grad.addColorStop(1,"rgba(255,119,115,0)");

                ctx.fillStyle = grad;
                ctx.fillRect(0,0,W,H);
            }

            ctx.globalCompositeOperation="destination-in";
            ctx.drawImage(maskImg,0,0,W,H);

            requestAnimationFrame(frame);
        }
        frame();
    };
});
</script>
