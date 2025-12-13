<?php
/**
 * card_list_template.php
 * 
 * カード一覧表示テンプレート
 * 
 * 必要な変数：
 * $cards : 表示するカードの配列
 * $modal_enabled : trueの場合、クリックでモーダル表示
 */
$modal_enabled = $modal_enabled ?? true; // デフォルトはモーダル有効
?>

<div class="card-area">
<?php foreach ($cards as $c): ?>
<div class="card-container <?= !empty($c['is_favorite']) ? 'favorite' : '' ?>">
    <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>"
         <?= $modal_enabled ? "onclick=\"openModal('detail_{$c['id']}')\"" : "" ?>>
    <div class="favorite-star">★</div>
    <div class="level">Lv.<?= $c['level'] ?></div>

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

            <!-- 強化ボタン -->
            <?php if (isset($c['id'])): ?>
            <form method="get" action="card_strengthen.php">
                <input type="hidden" name="target_card_id" value="<?= $c['id'] ?>">
                <button type="submit">強化</button>
            </form>
            <?php endif; ?>

            <!-- 進化ボタン -->
            <?php if (!empty($c['evolved_card_id'])): ?>
                <?php if ($c['level'] >= $c['max_level']): ?>
                <form method="post" action="card_action.php">
                    <input type="hidden" name="action" value="evolve">
                    <input type="hidden" name="target_card_id" value="<?= $c['id'] ?>">
                    <button type="submit">進化</button>
                </form>
                <?php else: ?>
                    <p>Lv <?= $c['max_level'] ?> で進化可能</p>
                <?php endif; ?>
            <?php endif; ?>

            <button type="button" onclick="closeModal('detail_<?= $c['id'] ?>')">閉じる</button>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

