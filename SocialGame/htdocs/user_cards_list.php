<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }

$dsn='mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user='data_user'; $password='data';

try{
    $pdo=new PDO($dsn,$user,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    // 所持カード取得
    $stmt=$pdo->prepare("
        SELECT uc.*, c.card_name, c.base_hp, c.base_atk, c.base_def
        FROM user_cards uc
        JOIN cards c ON uc.card_id=c.card_id
        WHERE uc.user_id=?
        ORDER BY c.card_id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cards=$stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(PDOException $e){
    echo "DB接続エラー: ".$e->getMessage(); exit;
}

// 強化結果モーダル用
$str_result = $_GET['strengthen_result'] ?? '';
$modal_data = [
    'card_name' => $_GET['card_name'] ?? '',
    'old_level' => $_GET['old_level'] ?? '',
    'new_level' => $_GET['new_level'] ?? '',
    'old_hp'    => $_GET['old_hp'] ?? '',
    'new_hp'    => $_GET['new_hp'] ?? '',
    'old_atk'   => $_GET['old_atk'] ?? '',
    'new_atk'   => $_GET['new_atk'] ?? '',
    'old_def'   => $_GET['old_def'] ?? '',
    'new_def'   => $_GET['new_def'] ?? ''
];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>所持カード一覧</title>
<style>
.modal { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; }
.modal-content { background:#fff; padding:20px; border-radius:5px; min-width:300px; }
.card-detail { cursor:pointer; text-decoration:underline; color:blue; }
button { margin-top:5px; }
</style>
<script>
function showCardDetail(id){
    document.getElementById('detailModal_'+id).style.display='flex';
}
function closeModal(id){
    document.getElementById('detailModal_'+id).style.display='none';
}
function closeResultModal(){
    document.getElementById('resultModal').style.display='none';
}
</script>
</head>
<body>
<nav><a href="dashboard.php">ダッシュボード</a></nav>
<h1>自分の所持カード</h1>

<?php if(count($cards)===0): ?>
<p>所持カードなし</p>
<?php else: ?>
<table border="1" cellpadding="5">
<tr><th>カード名</th><th>レベル</th><th>お気に入り</th></tr>
<?php foreach($cards as $c): ?>
<tr>
<td><span class="card-detail" onclick="showCardDetail(<?=$c['id']?>)"><?=htmlspecialchars($c['card_name'],ENT_QUOTES)?></span></td>
<td><?=$c['level']?></td>
<td><?=$c['is_favorite']?'★':''?></td>
</tr>

<!-- 詳細モーダル -->
<div id="detailModal_<?=$c['id']?>" class="modal">
  <div class="modal-content">
    <h3><?=htmlspecialchars($c['card_name'],ENT_QUOTES)?></h3>
    <p>レベル: <?=$c['level']?></p>
    <p>HP: <?=$c['base_hp']?></p>
    <p>ATK: <?=$c['base_atk']?></p>
    <p>DEF: <?=$c['base_def']?></p>

    <!-- お気に入り切替ボタン -->
    <form method="post" action="card_action.php" style="display:inline;">
      <input type="hidden" name="user_card_id" value="<?=$c['id']?>">
      <input type="hidden" name="action" value="<?=$c['is_favorite']?'unfavorite':'favorite'?>">
      <button type="submit"><?=$c['is_favorite']?'お気に入り解除':'お気に入り'?></button>
    </form>

    <!-- 強化ボタン -->
    <form method="get" action="card_strengthen.php" style="display:inline;">
      <input type="hidden" name="target_card_id" value="<?=$c['id']?>">
      <button type="submit">強化</button>
    </form>

    <br><button onclick="closeModal(<?=$c['id']?>)">閉じる</button>
  </div>
</div>

<?php endforeach; ?>
</table>
<?php endif; ?>

<!-- 強化結果モーダル -->
<?php if($str_result==='success'): ?>
<div id="resultModal" class="modal" style="display:flex;">
  <div class="modal-content">
    <h2>強化完了！</h2>
    <h3><?=htmlspecialchars($modal_data['card_name'],ENT_QUOTES)?></h3>
    <p>レベル: <?=htmlspecialchars($modal_data['old_level'])?> → <?=htmlspecialchars($modal_data['new_level'])?></p>
    <p>HP: <?=htmlspecialchars($modal_data['old_hp'])?> → <?=htmlspecialchars($modal_data['new_hp'])?></p>
    <p>ATK: <?=htmlspecialchars($modal_data['old_atk'])?> → <?=htmlspecialchars($modal_data['new_atk'])?></p>
    <p>DEF: <?=htmlspecialchars($modal_data['old_def'])?> → <?=htmlspecialchars($modal_data['new_def'])?></p>
    <button onclick="closeResultModal()">OK</button>
  </div>
</div>
<?php endif; ?>

</body>
</html>
