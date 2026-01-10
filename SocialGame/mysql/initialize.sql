-- ----------------------------------------
-- MySQLユーザーと権限設定
-- ----------------------------------------
CREATE USER IF NOT EXISTS 'data_user'@'%' IDENTIFIED WITH mysql_native_password BY 'data';
GRANT ALL PRIVILEGES ON *.* TO 'data_user'@'%';

ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'p@ssword';
FLUSH PRIVILEGES;

-- ----------------------------------------
-- 文字コード設定
-- ----------------------------------------
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ----------------------------------------
-- データベース作成
-- ----------------------------------------
CREATE DATABASE IF NOT EXISTS socialgame CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE socialgame;

-- ----------------------------------------
-- 既存テーブル削除（依存順）
-- ----------------------------------------
DROP TABLE IF EXISTS user_cards;
DROP TABLE IF EXISTS cards;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS exp_table;
DROP TABLE IF EXISTS card_rarity;

-- ----------------------------------------
-- users テーブル
-- ----------------------------------------
CREATE TABLE users(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    user_coins INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- card_rarity（カードレアリティ）テーブル
-- ----------------------------------------
CREATE TABLE card_rarity(
    rarity_id INT AUTO_INCREMENT PRIMARY KEY,
    rarity_name VARCHAR(50) NOT NULL,
    description TEXT DEFAULT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- レアリティの例として以下を挿入
INSERT INTO card_rarity (rarity_name, description) VALUES
('Common', 'C'),
('Rare', 'R'),
('Epic', 'Epic'),
('Legendary', 'Legendary');

-- ----------------------------------------
-- cards（カードマスタ）テーブル
-- ----------------------------------------
CREATE TABLE cards(
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    rarity_id INT NOT NULL,  -- カードのレアリティ
    card_name VARCHAR(255) NOT NULL,  -- カード名
    max_level INT NOT NULL DEFAULT 50,   -- 最大レベルを追加
    base_hp INT NOT NULL,
    base_atk INT NOT NULL,
    base_def INT NOT NULL,

    -- ★強化・進化用のデータ
    material_exp INT NOT NULL DEFAULT 100,        -- 素材時の獲得EXP
    evolved_card_id INT DEFAULT NULL,             -- 進化先カードID（NULLなら進化しない）

    -- ★サムネイル画像
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT 'カードサムネイル画像のパス',

    -- ★レベルアップ時のステータス上昇量
    per_level_hp INT NOT NULL DEFAULT 1 COMMENT 'レベルアップ時のHP上昇量',
    per_level_atk INT NOT NULL DEFAULT 1 COMMENT 'レベルアップ時のATK上昇量',
    per_level_def INT NOT NULL DEFAULT 1 COMMENT 'レベルアップ時のDEF上昇量',

    FOREIGN KEY (rarity_id) REFERENCES card_rarity(rarity_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- user_cards（ユーザーの所持カード）テーブル
-- ----------------------------------------
CREATE TABLE user_cards(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_id INT NOT NULL,
    level INT NOT NULL DEFAULT 1,
    exp INT NOT NULL DEFAULT 0,
    is_favorite BOOLEAN NOT NULL DEFAULT FALSE,   -- お気に入り（ロック）（bool型）
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES cards(card_id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- 経験値テーブル（レベルアップ用）
-- ----------------------------------------
CREATE TABLE exp_table(
    level INT PRIMARY KEY,
    required_exp INT NOT NULL -- 各レベルに到達するために必要な合計経験値
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- 経験値テーブルのデータを手動で挿入（ストアドプロシージャなし）
-- ----------------------------------------
INSERT INTO exp_table (level, required_exp)
VALUES 
(1, 0),
(2, 100),
(3, 200),
(4, 300),
(5, 400),
(6, 500),
(7, 600),
(8, 700),
(9, 800),
(10, 900),
(11, 1000),
(12, 1100),
(13, 1200),
(14, 1300),
(15, 1400),
(16, 1500),
(17, 1600),
(18, 1700),
(19, 1800),
(20, 1900),
(21, 2000),
(22, 2100),
(23, 2200),
(24, 2300),
(25, 2400),
(26, 2500),
(27, 2600),
(28, 2700),
(29, 2800),
(30, 2900),
(31, 3000),
(32, 3100),
(33, 3200),
(34, 3300),
(35, 3400),
(36, 3500),
(37, 3600),
(38, 3700),
(39, 3800),
(40, 3900),
(41, 4000),
(42, 4100),
(43, 4200),
(44, 4300),
(45, 4400),
(46, 4500),
(47, 4600),
(48, 4700),
(49, 4800),
(50, 4900);

-- ----------------------------------------
-- ガチャのマスターテーブル
-- ----------------------------------------
CREATE TABLE gachas (
    gacha_id INT AUTO_INCREMENT PRIMARY KEY,
    gacha_name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    cost INT NOT NULL DEFAULT 0 COMMENT '消費ポイント（仮）',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- ガチャの排出内容、重み設定用
-- ----------------------------------------
CREATE TABLE gacha_items (
    gacha_item_id INT AUTO_INCREMENT PRIMARY KEY,
    gacha_id INT NOT NULL,
    card_id INT NOT NULL,
    weight INT NOT NULL COMMENT '抽選重み',

    FOREIGN KEY (gacha_id) REFERENCES gachas(gacha_id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES cards(card_id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- ガチャ履歴用テーブル
-- ----------------------------------------
CREATE TABLE gacha_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gacha_id INT NOT NULL,
    card_id INT NOT NULL,
    coins_spent INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (gacha_id) REFERENCES gachas(gacha_id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES cards(card_id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- 初期データ挿入
-- ----------------------------------------
-- usersテーブルに初期ユーザーを追加
INSERT INTO users 
(user_name, user_coins) 
VALUES ('ユーザー1', 10000);

-- cardsテーブルに初期カードを追加
INSERT INTO cards 
(rarity_id, card_name, max_level, base_hp, base_atk, base_def, material_exp, evolved_card_id, thumbnail, per_level_hp, per_level_atk, per_level_def)
VALUES
(1, 'カード1', 50, 10, 5, 3, 100, NULL, '../images/cards/card9.webp', 2, 1, 1),
(2, '超カード1', 50, 20, 10, 6, 100, NULL, '../images/cards/card6.webp', 4, 2, 2),
(3, '強化カード', 1, 10, 10, 10, 5000, NULL, '../images/cards/card8.webp', 0, 0, 0),
(1, 'アリサ・ブロッサム', 50, 12, 6, 4, 100, NULL, '../images/cards/card1.webp', 2, 1, 1),
(2, 'ミユキ・スノウ', 50, 15, 7, 5, 100, NULL, '../images/cards/card2.webp', 3, 2, 2),
(1, 'サクラ・リリィ', 50, 13, 5, 5, 100, NULL, '../images/cards/card3.webp', 2, 1, 1),
(3, 'ユイ・スターライト', 50, 20, 10, 8, 200, NULL, '../images/cards/card4.webp', 4, 3, 2),
(2, 'ハナ・ローズ', 50, 18, 8, 6, 100, NULL, '../images/cards/card5.webp', 3, 2, 2),
(1, 'リナ・チェリー', 50, 14, 5, 5, 100, NULL, '../images/cards/card6.webp', 2, 1, 1),
(4, 'カレン・ムーン', 50, 25, 12, 10, 300, NULL, '../images/cards/card7.webp', 5, 4, 3),
(3, 'アイリ・フェアリー', 50, 22, 9, 7, 150, NULL, '../images/cards/card8.webp', 4, 3, 2),
(2, 'ノゾミ・ブロッサム', 50, 19, 8, 6, 100, NULL, '../images/cards/card9.webp', 3, 2, 2),
(1, 'ヒカリ・スカイ', 50, 13, 6, 5, 100, NULL, '../images/cards/card10.webp', 2, 1, 1);

-- 進化前カードに進化後カードIDを設定
UPDATE cards
SET evolved_card_id = 2  -- card1の進化後カードIDとしてcard1evolvedのcard_id（2）を設定
WHERE card_id = 1;

-- user_cardsテーブルに所持カードを追加
INSERT INTO user_cards (user_id, card_id, level, exp, is_favorite)
VALUES 
(1, 1, 1, 0, FALSE),
(1, 3, 1, 0, FALSE);

-- ----------------------------------------
-- ガチャ初期データ
-- ----------------------------------------
-- 単発ガチャを作成
INSERT INTO gachas (gacha_name, description, cost, is_active)
VALUES ('テストガチャ', 'テスト用の単発ガチャ', 100, TRUE);

-- 作成したガチャIDを取得（今回は1固定でもOK）
SET @gacha_id = LAST_INSERT_ID();

-- gachas_items にカードを登録（重み付き）
INSERT INTO gacha_items (gacha_id, card_id, weight) VALUES
(@gacha_id, 4, 10),  -- アリサ・ブロッサム
(@gacha_id, 5, 9),   -- ミユキ・スノウ
(@gacha_id, 6, 8),   -- サクラ・リリィ
(@gacha_id, 7, 7),   -- ユイ・スターライト
(@gacha_id, 8, 6),   -- ハナ・ローズ
(@gacha_id, 9, 5),   -- リナ・チェリー
(@gacha_id, 10, 4),  -- カレン・ムーン
(@gacha_id, 11, 3),  -- アイリ・フェアリー
(@gacha_id, 12, 2),  -- ノゾミ・ブロッサム
(@gacha_id, 13, 1);  -- ヒカリ・スカイ