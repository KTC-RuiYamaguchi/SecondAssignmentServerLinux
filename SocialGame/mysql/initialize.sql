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

-- ----------------------------------------
-- users テーブル
-- ----------------------------------------
CREATE TABLE users(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- cards（カードマスタ）
-- ----------------------------------------
CREATE TABLE cards(
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    card_name VARCHAR(255) NOT NULL,
    charactor_id INT NOT NULL,
    base_hp INT NOT NULL,
    base_atk INT NOT NULL,
    base_def INT NOT NULL,

    -- ★強化・進化用のデータ
    material_exp INT NOT NULL DEFAULT 100,        -- 素材時の獲得EXP
    evolve_limit INT NOT NULL DEFAULT 1,          -- 進化可能な回数
    evolve_multiplier DECIMAL(5,2) DEFAULT 1.10, -- 進化時のステータス補正係数

    -- ★サムネイル画像
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT 'カードサムネイル画像のパス',

    -- ★レベルアップ時のステータス上昇量
    per_level_hp INT NOT NULL DEFAULT 1 COMMENT 'レベルアップ時のHP上昇量',
    per_level_atk INT NOT NULL DEFAULT 1 COMMENT 'レベルアップ時のATK上昇量',
    per_level_def INT NOT NULL DEFAULT 1 COMMENT 'レベルアップ時のDEF上昇量'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- user_cards（ユーザーの所持カード）
-- ----------------------------------------
CREATE TABLE user_cards(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_id INT NOT NULL,

    level INT NOT NULL DEFAULT 1,
    exp INT NOT NULL DEFAULT 0,
    evolve_count INT NOT NULL DEFAULT 0,
    is_favorite TINYINT(1) NOT NULL DEFAULT 0,   -- お気に入り（ロック）

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES cards(card_id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------------------
-- 経験値テーブル（レベルアップ用）
-- ----------------------------------------
CREATE TABLE exp_table(
    level INT PRIMARY KEY,
    required_exp INT NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 初期レベル係数（例：一定値）
INSERT INTO exp_table (level, required_exp)
VALUES 
(1, 100),
(2, 100),
(3, 100),
(4, 100),
(5, 100);
-- 必要なら後で100まで追加する

-- ----------------------------------------
-- 初期データ挿入
-- ----------------------------------------
INSERT INTO users (user_name) VALUES ('user');

INSERT INTO cards 
(card_name, charactor_id, base_hp, base_atk, base_def, material_exp, evolve_limit, evolve_multiplier, thumbnail, per_level_hp, per_level_atk, per_level_def)
VALUES
('card1', 1, 10, 5, 3, 100, 1, 1.10, 'card1.webp', 2, 1, 1);
