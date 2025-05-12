-- Vytvoření a výběr databáze
CREATE DATABASE IF NOT EXISTS eclipse;
USE eclipse;

-- Nastavení pro správné zpracování SQL příkazů
SET SQL_MODE = 'TRADITIONAL';
SET SESSION group_concat_max_len = 10000;
SET FOREIGN_KEY_CHECKS = 1;

-- ========== VYTVOŘENÍ TABULEK ==========

-- Vytvoření tabulky author
CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    created_at DATETIME NOT NULL
);

-- Vytvoření tabulky category
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    parent_id INT NULL
);

-- Vytvoření tabulky tag
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL
);

-- Vytvoření tabulky addon
CREATE TABLE IF NOT EXISTS addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    version VARCHAR(50) NOT NULL,
    author_id INT NOT NULL,
    category_id INT NOT NULL,
    repository_url VARCHAR(255) NULL,
    download_url VARCHAR(255) NOT NULL,
    icon_url VARCHAR(255) NULL,
    fanart_url VARCHAR(255) NULL,
    kodi_version_min VARCHAR(50) NULL,
    kodi_version_max VARCHAR(50) NULL,
    downloads_count INT NOT NULL DEFAULT 0,
    rating FLOAT NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0
);

-- Vytvoření tabulky addon_review
CREATE TABLE IF NOT EXISTS addon_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    addon_id INT NOT NULL,
    user_id INT NULL,
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    rating INT NOT NULL,
    comment TEXT NULL,
    created_at DATETIME NOT NULL
);

-- Vytvoření tabulky addon_tag
CREATE TABLE IF NOT EXISTS addon_tags (
    addon_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (addon_id, tag_id)
);

-- Vytvoření tabulky screenshot
CREATE TABLE IF NOT EXISTS screenshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    addon_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

-- ========== PŘIDÁNÍ UNIKÁTNÍCH OMEZENÍ ==========

-- Pro tabulku category
ALTER TABLE categories ADD CONSTRAINT uq_category_slug UNIQUE (slug);

-- Pro tabulku tag
ALTER TABLE tags ADD CONSTRAINT uq_tag_slug UNIQUE (slug);

-- Pro tabulku addon
ALTER TABLE addons ADD CONSTRAINT uq_addon_slug UNIQUE (slug);

-- ========== PŘIDÁNÍ CIZÍCH KLÍČŮ ==========

-- Pro tabulku category (self-reference)
ALTER TABLE categories ADD CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Pro tabulku addon
ALTER TABLE addons ADD CONSTRAINT fk_addon_author FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE RESTRICT;
ALTER TABLE addons ADD CONSTRAINT fk_addon_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT;

-- Pro tabulku addon_review
ALTER TABLE addon_reviews ADD CONSTRAINT fk_review_addon FOREIGN KEY (addon_id) REFERENCES addons(id) ON DELETE CASCADE;
ALTER TABLE addon_reviews ADD CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5);

-- Pro tabulku addon_tag
ALTER TABLE addon_tags ADD CONSTRAINT fk_addon_tag_addon FOREIGN KEY (addon_id) REFERENCES addons(id) ON DELETE CASCADE;
ALTER TABLE addon_tags ADD CONSTRAINT fk_addon_tag_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE;

-- Pro tabulku screenshot
ALTER TABLE screenshots ADD CONSTRAINT fk_screenshot_addon FOREIGN KEY (addon_id) REFERENCES addons(id) ON DELETE CASCADE;

-- ========== PŘIDÁNÍ INDEXŮ ==========

-- Pro tabulku addon
CREATE INDEX idx_addon_author ON addons(author_id);
CREATE INDEX idx_addon_category ON addons(category_id);
CREATE INDEX idx_addon_created ON addons(created_at);
CREATE INDEX idx_addon_updated ON addons(updated_at);
CREATE INDEX idx_addon_downloads ON addons(downloads_count DESC);
CREATE INDEX idx_addon_rating ON addons(rating DESC);

-- Pro tabulku category
CREATE INDEX idx_category_parent ON categories(parent_id);

-- Pro tabulku addon_review
CREATE INDEX idx_review_addon ON addon_reviews(addon_id);
CREATE INDEX idx_review_rating ON addon_reviews(rating);
CREATE INDEX idx_review_created ON addon_reviews(created_at DESC);

-- Pro tabulku screenshot
CREATE INDEX idx_screenshot_addon ON screenshots(addon_id);
CREATE INDEX idx_screenshot_sort ON screenshots(addon_id, sort_order);