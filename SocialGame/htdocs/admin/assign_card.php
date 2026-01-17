<?php
require 'admin_header.php'; // ヘッダー読み込み
require '../db_connect.php';

$message = '';

try {
    $cards = $pdo->query("SELECT * FROM cards ORDER BY card_id")->fetchAll(PDO::FETCH_ASSOC);
    $users = $pdo->query("SELECT * FROM users ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['card_id'])) {
        $user_id = intval($_POST['user_id']);
        $card_id = intval($_POST['card_id']);

        if ($user_id && $card_id) {
            $check = $pdo->prepare("SELECT * FROM user_cards WHERE user_id = ? AND card_id = ?");
            $check->execute([$user_id, $card_id]);
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO user_cards (user_id, card_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $card_id]);
                $message = "カードをユーザーに割り当てました。";
            } else {
                $message = "そのカードはすでに割り当て済みです。";
            }
        } else {
            $message = "ユーザーとカードを選択してください。";
        }
    }

} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
?>

<style>
.assign-form {
    max-width:700px;
    margin:0 auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
.assign-form h1 { text-align:center; color:#3498db; }
.assign-form select { margin:0 5px; padding:6px 8px; border-radius:4px; border:1px solid #ccc; }
.assign-form button { padding:6px 12px; border-radius:6px; border:none; background:#2ecc71; color:#fff; cursor:pointer; }
.assign-form button:hover { background:#27ae60; }
.message { text-align:center; margin-bottom:20px; color:#27ae60; font-weight:bold; }

#cardsContainer { display:flex; flex-wrap:wrap; justify-content:center; margin-top:20px; }
.cardItem { width:120px; margin:8px; text-align:center; background:#fff; border-radius:10px; padding:5px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
.cardItem img { width:100%; border-radius:6px; }
.cardName { font-size:14px; margin-top:4px; font-weight:bold; color:#333; }
.back-link { display:inline-block; margin-top:20px; padding:6px 12px; background:#3498db; color:#fff; border-radius:6px; text-decoration:none; }
.back-link:hover { background:#2980b9; }
</style>

<div class="assign-form">
<h1>カードをユーザーに割り当てる</h1>

<?php if($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" id="assignForm">
    ユーザー:
    <select name="user_id" id="userSelect" required>
        <option value="">選択してください</option>
        <?php foreach($users as $u): ?>
            <option value="<?= $u['user_id'] ?>">
                <?= htmlspecialchars($u['user_name']) ?> (ID: <?= $u['user_id'] ?>, 所持コイン: <?= $u['user_coins'] ?>)
            </option>
        <?php endforeach; ?>
    </select>

    カード:
    <select name="card_id" required>
        <option value="">選択してください</option>
        <?php foreach($cards as $c): ?>
            <option value="<?= $c['card_id'] ?>"><?= htmlspecialchars($c['card_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">割り当て</button>
</form>

<div id="cardsContainer">
    <p>ユーザーを選択すると所持カードがここに表示されます。</p>
</div>

<script>
// ユーザー選択時にAjaxでカード一覧を取得
document.getElementById('userSelect').addEventListener('change', function() {
    const userId = this.value;
    const container = document.getElementById('cardsContainer');
    container.innerHTML = '<p>読み込み中...</p>';

    if (!userId) {
        container.innerHTML = '<p>ユーザーを選択すると所持カードがここに表示されます。</p>';
        return;
    }

    fetch('get_user_cards.php?user_id=' + userId)
    .then(res => res.json())
    .then(data => {
        if(data.length === 0) {
            container.innerHTML = '<p>このユーザーはカードを持っていません。</p>';
            return;
        }
        container.innerHTML = '';
        data.forEach(card => {
            const div = document.createElement('div');
            div.className = 'cardItem';
            div.innerHTML = `<img src="${card.thumbnail}" alt="${card.card_name}"><div class="cardName">${card.card_name}</div>`;
            container.appendChild(div);
        });
    })
    .catch(err => {
        container.innerHTML = '<p>読み込みエラー</p>';
        console.error(err);
    });
});
</script>

<?php require 'admin_footer.php'; ?>
