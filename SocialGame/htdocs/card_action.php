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
                    SELECT uc.*, c.default_name, c.base_hp, c.base_atk, c.base_def, c.max_level, c.material_exp, c.evolve_limit, c.evolved_name
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
                $evolve_limit = $target_card['evolve_limit'];
                $evolved_name = $target_card['evolved_name'];

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

                // 進化判定
                if ($evolve_limit && $level >= $max_level) {
                    if ($evolved_name) {
                        $stmt = $pdo->prepare("
                            UPDATE user_cards 
                            SET card_id = (SELECT card_id FROM cards WHERE default_name = ?) 
                            WHERE id = ? AND user_id = ?
                        ");
                        $stmt->execute([$evolved_name, $target_id, $_SESSION['user_id']]);
                        $msg = "カードが進化しました。";
                    } else {
                        throw new Exception("進化後のカード名が設定されていません。");
                    }
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
                    . "&card_name=" . urlencode($target_card['default_name'])
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

            default:
                throw new Exception("不明な操作です。");
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    echo "<p><a href='user_cards_list.php'>戻る</a></p>";
    exit;
}
