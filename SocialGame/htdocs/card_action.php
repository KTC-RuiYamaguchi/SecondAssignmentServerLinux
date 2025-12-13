<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $user_card_id = $_POST['user_card_id'] ?? null;

        switch ($action) {

            // =========================
            // 強化（素材カード方式）
            // =========================
            case 'strengthen':
                $target_id = $_POST['target_card_id'] ?? null;
                $material_ids = $_POST['material_card_ids'] ?? [];

                if (!$target_id || empty($material_ids)) {
                    throw new Exception("対象カードと素材カードを選択してください。");
                }

                // 対象カード取得
                $stmt = $pdo->prepare("
                    SELECT uc.*, c.card_name, c.base_hp, c.base_atk, c.base_def, c.max_level, c.material_exp
                    FROM user_cards uc
                    JOIN cards c ON uc.card_id = c.card_id
                    WHERE uc.id = ? AND uc.user_id = ?
                ");
                $stmt->execute([$target_id, $_SESSION['user_id']]);
                $target_card = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$target_card) throw new Exception("対象カードが見つかりません。");

                $old_level = $target_card['level'];
                $old_hp = $target_card['base_hp'];
                $old_atk = $target_card['base_atk'];
                $old_def = $target_card['base_def'];
                $max_level = $target_card['max_level'];

                // 素材カードの合計EXP
                $in = str_repeat('?,', count($material_ids)-1) . '?';
                $stmt = $pdo->prepare("
                    SELECT SUM(c.material_exp) AS total_exp
                    FROM user_cards uc
                    JOIN cards c ON uc.card_id = c.card_id
                    WHERE uc.id IN ($in) AND uc.user_id = ?
                ");
                $params = array_merge($material_ids, [$_SESSION['user_id']]);
                $stmt->execute($params);
                $total_exp = (int)$stmt->fetchColumn();
                if ($total_exp <= 0) throw new Exception("有効な素材カードがありません。");

                // 新しい経験値
                $new_exp = $target_card['exp'] + $total_exp;

                // レベルを判定するためにexp_tableから適切なレベルを取得
                $stmt2 = $pdo->prepare("SELECT level FROM exp_table WHERE required_exp <= ? ORDER BY required_exp DESC LIMIT 1");
                $stmt2->execute([$new_exp]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $level = $row['level'];
                } else {
                    // 経験値が最小のrequired_expにも達していない場合、レベル1のままとする
                    $level = 1;
                }

                // 対象カード更新
                $stmt = $pdo->prepare("UPDATE user_cards SET exp = ?, level = ? WHERE id = ?");
                $stmt->execute([$new_exp, $level, $target_id]);

                // 素材カード削除
                $stmt = $pdo->prepare("DELETE FROM user_cards WHERE id IN ($in) AND user_id = ?");
                $stmt->execute($params);

                // モーダル表示用パラメータ付きでリダイレクト
                header("Location: user_cards_list.php?strengthen_result=success"
                    . "&card_id={$target_id}"
                    . "&card_name=" . urlencode($target_card['card_name'])
                    . "&old_level={$old_level}&new_level={$level}"
                    . "&old_hp={$old_hp}&new_hp={$old_hp}"
                    . "&old_atk={$old_atk}&new_atk={$old_atk}"
                    . "&old_def={$old_def}&new_def={$old_def}");
                exit;
                break;



            // =========================
            // お気に入り設定
            // =========================
            case 'favorite':
            case 'unfavorite':
                if (!$user_card_id) throw new Exception("対象カードが指定されていません。");
                $flag = ($action === 'favorite') ? 1 : 0;
                $stmt = $pdo->prepare("UPDATE user_cards SET is_favorite = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$flag, $user_card_id, $_SESSION['user_id']]);
                header('Location: user_cards_list.php');
                exit;
                break;
            
            // =========================
            // 進化
            // =========================
            case 'evolve':

                $target_id = $_POST['target_card_id'] ?? null;
                if (!$target_id) {
                    throw new Exception("進化対象カードが指定されていません。");
                }

                // 対象カード取得（進化先ID・最大レベル）
                $stmt = $pdo->prepare("
                    SELECT 
                        uc.id AS user_card_id,
                        uc.card_id AS before_card_id,
                        uc.level,
                        c.evolved_card_id,
                        c.max_level
                    FROM user_cards uc
                    JOIN cards c ON uc.card_id = c.card_id
                    WHERE uc.id = ? AND uc.user_id = ?
                ");
                $stmt->execute([$target_id, $_SESSION['user_id']]);
                $card = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$card) {
                    throw new Exception("カードが見つかりません。");
                }

                if (empty($card['evolved_card_id'])) {
                    throw new Exception("このカードは進化できません。");
                }

                if ($card['level'] < $card['max_level']) {
                    throw new Exception("レベルが最大に達していません。");
                }

                $before_card_id  = $card['before_card_id'];
                $after_card_id   = $card['evolved_card_id'];

                // ===== トランザクション開始 =====
                $pdo->beginTransaction();

                try {
                    // 元カード削除
                    $stmt = $pdo->prepare("
                        DELETE FROM user_cards
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$target_id, $_SESSION['user_id']]);

                    // 進化後カード付与（Lv1, EXP0）
                    $stmt = $pdo->prepare("
                        INSERT INTO user_cards (user_id, card_id, level, exp)
                        VALUES (?, ?, 1, 0)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $after_card_id
                    ]);

                    $pdo->commit();

                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }

                // 進化結果モーダル用リダイレクト
                header(
                    'Location: user_cards_list.php'
                    . '?evolve_result=success'
                    . '&before_card_id=' . $before_card_id
                    . '&after_card_id=' . $after_card_id
                );
                exit;


            default:
                throw new Exception("不明な操作です。");
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    echo "<p><a href='user_cards_list.php'>戻る</a></p>";
    exit;
}
