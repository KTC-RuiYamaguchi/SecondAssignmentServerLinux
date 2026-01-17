<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: ../admin_login.php');
    exit;
}

require '../db_connect.php';

// ----------------------------
// 検索フォームの入力取得
$searchUserId = $_GET['user_id'] ?? '';
$searchGacha = $_GET['gacha_name'] ?? '';
$searchFrom = $_GET['date_from'] ?? '';
$searchTo = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 100;
$offset = ($page - 1) * $perPage;

// 件数取得
$countSql = "
    SELECT COUNT(*) AS cnt
    FROM gacha_logs gl
    JOIN users u ON gl.user_id = u.user_id
    JOIN gachas g ON gl.gacha_id = g.gacha_id
    JOIN cards c ON gl.card_id = c.card_id
    WHERE 1
";
$countParams = [];
if ($searchUserId !== '') { $countSql .= " AND u.user_id = ?"; $countParams[] = $searchUserId; }
if ($searchGacha !== '') { $countSql .= " AND g.gacha_name LIKE ?"; $countParams[] = "%$searchGacha%"; }
if ($searchFrom !== '') { $countSql .= " AND gl.created_at >= ?"; $countParams[] = $searchFrom . " 00:00:00"; }
if ($searchTo !== '') { $countSql .= " AND gl.created_at <= ?"; $countParams[] = $searchTo . " 23:59:59"; }

$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);
$totalCount = $stmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

// データ取得
$sql = "
    SELECT gl.created_at, u.user_id, u.user_name, g.gacha_name, c.card_name, gl.coins_spent
    FROM gacha_logs gl
    JOIN users u ON gl.user_id = u.user_id
    JOIN gachas g ON gl.gacha_id = g.gacha_id
    JOIN cards c ON gl.card_id = c.card_id
    WHERE 1
";
$params = [];
if ($searchUserId !== '') { $sql .= " AND u.user_id = ?"; $params[] = $searchUserId; }
if ($searchGacha !== '') { $sql .= " AND g.gacha_name LIKE ?"; $params[] = "%$searchGacha%"; }
if ($searchFrom !== '') { $sql .= " AND gl.created_at >= ?"; $params[] = $searchFrom . " 00:00:00"; }
if ($searchTo !== '') { $sql .= " AND gl.created_at <= ?"; $params[] = $searchTo . " 23:59:59"; }

$sql .= " ORDER BY gl.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k + 1, $v); }
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ガチャ履歴一覧</title>
<style>
body { font-family: Arial, sans-serif; margin:0; background:#f5f5f5; }

/* 検索フォーム固定 */
.search-bar { position: fixed; top:60px; left:0; width:100%; background:#ecf0f1; padding:10px 0; box-shadow:0 2px 6px rgba(0,0,0,0.1); z-index:999; text-align:center; }
.search-bar input { margin: 0 5px; padding:4px 6px; border-radius:4px; border:1px solid #ccc; }
.search-bar button { padding:5px 12px; border-radius:6px; border:none; background:#2ecc71; color:#fff; cursor:pointer; }
.search-bar button:hover { background:#27ae60; }

/* コンテナ */
.container { max-width:1100px; margin:140px auto 40px; padding:0 20px; }

/* テーブル */
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { padding:8px 12px; border:1px solid #ccc; text-align:center; }
th { background:#3498db; color:#fff; }
tr:nth-child(even) { background:#f9f9f9; }

/* ページネーション */
.pagination { text-align:center; margin-top:20px; }
.pagination a { margin:0 4px; text-decoration:none; padding:4px 8px; background:#3498db; color:#fff; border-radius:4px; }
.pagination strong { margin:0 4px; padding:4px 8px; background:#ccc; border-radius:4px; }
</style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="search-bar">
    <form method="get">
        ユーザーID: <input type="text" name="user_id" value="<?= htmlspecialchars($searchUserId) ?>">
        ガチャ名: <input type="text" name="gacha_name" value="<?= htmlspecialchars($searchGacha) ?>">
        日付: <input type="date" name="date_from" value="<?= htmlspecialchars($searchFrom) ?>">～
        <input type="date" name="date_to" value="<?= htmlspecialchars($searchTo) ?>">
        <button type="submit">検索</button>
    </form>
</div>

<div class="container">
<table>
    <tr>
        <th>日時</th>
        <th>ユーザーID</th>
        <th>ユーザー名</th>
        <th>ガチャ名</th>
        <th>引いたカード</th>
        <th>消費コイン</th>
    </tr>
    <?php if($logs): ?>
        <?php foreach($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at']) ?></td>
            <td><?= htmlspecialchars($log['user_id']) ?></td>
            <td><?= htmlspecialchars($log['user_name']) ?></td>
            <td><?= htmlspecialchars($log['gacha_name']) ?></td>
            <td><?= htmlspecialchars($log['card_name']) ?></td>
            <td><?= htmlspecialchars($log['coins_spent']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6">該当する履歴がありません</td></tr>
    <?php endif; ?>
</table>

<!-- ページネーション -->
<div class="pagination">
<?php if ($totalPages > 1): ?>
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php
        $query = http_build_query([
            'user_id' => $searchUserId,
            'gacha_name' => $searchGacha,
            'date_from' => $searchFrom,
            'date_to' => $searchTo,
            'page' => $p
        ]);
        ?>
        <?php if ($p == $page): ?>
            <strong><?= $p ?></strong>
        <?php else: ?>
            <a href="?<?= $query ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>
<?php endif; ?>
</div>

</div>
</body>
</html>
