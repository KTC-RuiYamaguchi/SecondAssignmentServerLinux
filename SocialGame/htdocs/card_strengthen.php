<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$target_id = $_GET['target_card_id'] ?? null;
if (!$target_id) { 
    echo "強化対象カードが指定されていません。"; 
    exit; 
}

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // 強化対象カード取得
    $stmt = $pdo->prepare("
        SELECT uc.*, c.default_name, c.evolved_name, c.base_hp, c.base_atk, c.base_def, c.thumbnail, c.max_level, uc.is_evolved
        FROM user_cards uc
        JOIN cards c ON uc.card_id = c.card_id
        WHERE uc.id = ? AND uc.user_id = ?
    ");
    $stmt->execute([$target_id, $_SESSION['user_id']]);
    $target_card = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target_card) { 
        echo "対象カードが見つかりません。"; 
        exit; 
    }

    // 所持カード取得（対象カードを除外）
    $stmt = $pdo->prepare("
    SELECT uc.*, c.default_name, c.evolved_name, c.material_exp, c.thumbnail, uc.is_favorite
    FROM user_cards uc
    JOIN cards c ON uc.card_id = c.card_id
    WHERE uc.user_id = ? AND uc.id <> ?
    ORDER BY c.card_id
    ");
    $stmt->execute([$_SESSION['user_id'], $target_id]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>強化素材選択</title>
    <style>
        .disabled {
            color: #999;
            pointer-events: none; /* クリック無効化 */
        }
        .card-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 5px;
            border: 2px solid transparent;
        }
        .disabled-card {
            filter: grayscale(100%); /* お気に入りカードをグレーアウト */
            opacity: 0.5;
        }
        .card-level {
            font-weight: bold;
            margin-top: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
<h1>
    <?php
    // カードが進化しているかどうかで表示する名前を決定
    $card_name_display = !empty($target_card['evolved_name']) && $target_card['is_evolved']
        ? htmlspecialchars($target_card['evolved_name'], ENT_QUOTES)
        : htmlspecialchars($target_card['default_name'], ENT_QUOTES);
    echo $card_name_display;
    ?>
    の強化素材選択
</h1>

<form method="post" action="card_action.php">
    <input type="hidden" name="action" value="strengthen">
    <input type="hidden" name="target_card_id" value="<?= $target_card['id'] ?>">

    <?php if (count($cards) === 0): ?>
        <p>素材にできるカードがありません。</p>
    <?php else: ?>
        <ul style="display: flex; flex-wrap: wrap;">
            <?php foreach ($cards as $c): 
                $disabled = $c['is_favorite'] ? 'disabled' : ''; 
                $class = $c['is_favorite'] ? 'disabled-card' : ''; 
            ?>
                <li style="list-style-type: none; margin: 10px;">
                    <label class="<?= $disabled ?>">
                        <input type="checkbox" name="material_card_ids[]" value="<?= $c['id'] ?>" <?= $c['is_favorite'] ? 'disabled' : '' ?>>
                        <div class="<?= $class ?>">
                            <img src="<?= htmlspecialchars($c['thumbnail'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($c['card_name'], ENT_QUOTES) ?>" class="card-image">
                        </div>
                        <div style="text-align: center;">
                            <?php
                            // 進化前後のカード名を選択して表示
                            $card_name_display = !empty($c['evolved_name']) && $c['is_evolved']
                                ? htmlspecialchars($c['evolved_name'], ENT_QUOTES)
                                : htmlspecialchars($c['default_name'], ENT_QUOTES);
                            echo $card_name_display;
                            ?>
                            <div class="card-level">Lv.<?= $c['level'] ?></div>
                        </div>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="submit">強化実行</button>
    </form>
    <?php endif; ?>

    <p><a href="user_cards_list.php">戻る</a></p>
</body>
</html>
