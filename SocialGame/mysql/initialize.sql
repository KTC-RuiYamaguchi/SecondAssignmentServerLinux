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
    default_name VARCHAR(255) NOT NULL,  -- 進化前の名前
    evolved_name VARCHAR(255) DEFAULT NULL,  -- 進化後の名前
    max_level INT NOT NULL DEFAULT 100,   -- 最大レベルを追加
    base_hp INT NOT NULL,
    base_atk INT NOT NULL,
    base_def INT NOT NULL,

    -- ★強化・進化用のデータ
    material_exp INT NOT NULL DEFAULT 100,        -- 素材時の獲得EXP
    evolve_limit BOOLEAN NOT NULL DEFAULT TRUE,   -- 進化可能かどうか（bool型）

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
    is_evolved BOOLEAN NOT NULL DEFAULT FALSE,    -- 進化したかどうか（bool型）
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
(1, 0),      -- レベル1は経験値0でスタート
(2, 100),    -- レベル2に到達するために必要な合計経験値
(3, 200),    -- レベル3に到達するために必要な合計経験値
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
(50, 4900);  -- レベル50に到達するために必要な合計経験値

-- ----------------------------------------
-- 初期データ挿入
-- ----------------------------------------
-- usersテーブルに初期ユーザーを追加
INSERT INTO users (user_name) VALUES ('user');

-- cardsテーブルに初期カードを追加
-- rarity_idを1（Common）として、進化前・進化後の名前も設定
INSERT INTO cards 
(rarity_id, default_name, evolved_name, max_level, base_hp, base_atk, base_def, material_exp, evolve_limit, thumbnail, per_level_hp, per_level_atk, per_level_def)
VALUES
(1, 'card1', 'card1evolved', 100, 10, 5, 3, 100, TRUE, '../images/cards/card1.webp', 2, 1, 1);

-- user_cardsテーブルに所持カードを追加
INSERT INTO user_cards (user_id, card_id, level, exp, is_evolved, is_favorite)
VALUES (1, 1, 1, 0, FALSE, FALSE);

