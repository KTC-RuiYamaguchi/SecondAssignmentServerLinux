<?php
/**
 * card_list_template.php
 * 
 * 共通カード一覧テンプレート
 * 
 * 必須変数：
 * $cards : 表示するカード配列
 * $mode  : 'display'（従来一覧） or 'select'（強化素材選択）
 */

$mode = $mode ?? 'display';
$modal_enabled = ($mode === 'display');
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

    <!-- 画像はここだけ -->
    <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>"
         <?= $modal_enabled ? "onclick=\"openModal('detail_{$c['id']}')\"" : "" ?>>

    <div class="favorite-star">★</div>
    <div class="level">Lv.<?= $c['level'] ?></div>

    <?php if ($select_enabled): ?>
        <div class="select-overlay">選択中</div>
        <input type="hidden" name="material_card_ids[]" value="<?= $c['id'] ?>" disabled>
    <?php endif; ?>

    <?php if ($modal_enabled): ?>
        <!-- 詳細モーダル -->
        <div id="detail_<?= $c['id'] ?>" class="modal">
            <div class="modal-content">
                <h3><?= htmlspecialchars($c['card_name'], ENT_QUOTES) ?></h3>
                <p>Lv <?= $c['level'] ?> / <?= $c['max_level'] ?></p>
                <p>HP <?= $c['base_hp'] ?></p>
                <p>ATK <?= $c['base_atk'] ?></p>
                <p>DEF <?= $c['base_def'] ?></p>
                <p><?= $c['rarity_name'] ?? '' ?></p>

                <!-- お気に入り -->
                <?php if (isset($c['is_favorite'])): ?>
                <form method="post" action="card_action.php">
                    <input type="hidden" name="action" value="<?= $c['is_favorite'] ? 'unfavorite' : 'favorite' ?>">
                    <input type="hidden" name="user_card_id" value="<?= $c['id'] ?>">
                    <button type="submit"><?= $c['is_favorite'] ? 'お気に入り解除' : 'お気に入り' ?></button>
                </form>
                <?php endif; ?>

                <!-- 強化 -->
                <?php if (!empty($c['max_level'])): ?>
                <form method="get" action="card_strengthen.php">
                    <input type="hidden" name="target_card_id" value="<?= $c['id'] ?>">
                    <button type="submit">強化</button>
                </form>
                <?php endif; ?>

                <!-- 進化 -->
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
    <?php endif; ?>

</div>
<?php endforeach; ?>
</div>

<?php if ($select_enabled): ?>
<script>
function toggleSelect(el, id) {
    const input = el.querySelector('input[name="material_card_ids[]"]');
    if (el.classList.contains('selected')) {
        el.classList.remove('selected');
        input.disabled = true;
    } else {
        el.classList.add('selected');
        input.disabled = false;
    }
}
</script>
<?php endif; ?>

