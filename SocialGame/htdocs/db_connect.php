<?php
/**
 * db_connect.php
 * 
 * DB接続共通化ファイル
 * このファイルを require するだけで $pdo が使用可能
 */

$dsn = 'mysql:host=mysql;dbname=socialgame;charset=utf8mb4';
$user = 'data_user';
$password = 'data';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo "DB接続エラー: " . $e->getMessage();
    exit;
}
