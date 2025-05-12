-- ============================================================
-- Seed Script for Eclipse Kodi Addon Repository
-- Categories and Tags Only
-- ============================================================
-- This script populates the database with official Kodi
-- categories and tags for proper organization
-- ============================================================

-- Start transaction for all operations
START TRANSACTION;

-- ============================================================
-- Step 1: Insert Categories
-- ============================================================
-- Main categories based on official Kodi addon types
INSERT INTO category (id, name, slug, parent_id) VALUES
(1, 'Video Addons', 'video-addons', NULL),
(2, 'Audio Addons', 'audio-addons', NULL),
(3, 'Program Addons', 'program-addons', NULL),
(4, 'Service Addons', 'service-addons', NULL),
(5, 'Skins', 'skins', NULL),
(6, 'Script Addons', 'script-addons', NULL),
(7, 'Repository Addons', 'repository-addons', NULL),
(8, 'Picture Addons', 'picture-addons', NULL);

-- Video subcategories
INSERT INTO category (id, name, slug, parent_id) VALUES
(9, 'Movies', 'movies', 1),
(10, 'TV Shows', 'tv-shows', 1),
(11, 'Live TV', 'live-tv', 1),
(12, 'Sports', 'sports', 1),
(13, 'News', 'news', 1),
(14, 'Documentaries', 'documentaries', 1),
(15, 'Kids', 'kids', 1),
(16, 'Anime', 'anime', 1);

-- Audio subcategories
INSERT INTO category (id, name, slug, parent_id) VALUES
(17, 'Music', 'music', 2),
(18, 'Radio', 'radio', 2),
(19, 'Podcasts', 'podcasts', 2),
(20, 'Audiobooks', 'audiobooks', 2),
(21, 'Karaoke', 'karaoke', 2);

-- Program subcategories
INSERT INTO category (id, name, slug, parent_id) VALUES
(22, 'Utilities', 'utilities', 3),
(23, 'Maintenance', 'maintenance', 3),
(24, 'Weather', 'weather', 3),
(25, 'Gaming', 'gaming', 3);

-- Service subcategories
INSERT INTO category (id, name, slug, parent_id) VALUES
(26, 'PVR Backends', 'pvr-backends', 4),
(27, 'Subtitles', 'subtitles', 4),
(28, 'Metadata', 'metadata', 4),
(29, 'Library', 'library', 4);

-- ============================================================
-- Step 2: Insert Tags
-- ============================================================
-- These tags represent common features/content types
INSERT INTO tag (id, name, slug) VALUES
(1, 'Free', 'free'),
(2, 'Premium', 'premium'),
(3, 'Official', 'official'),
(4, 'Unofficial', 'unofficial'),
(5, 'Movies', 'movies'),
(6, 'TV', 'tv'),
(7, 'Music', 'music'),
(8, 'Sports', 'sports'),
(9, 'News', 'news'),
(10, 'Weather', 'weather'),
(11, 'Live Streaming', 'live-streaming'),
(12, 'Kids', 'kids'),
(13, 'Documentary', 'documentary'),
(14, 'Comedy', 'comedy'),
(15, 'Drama', 'drama'),
(16, 'Action', 'action'),
(17, 'Sci-Fi', 'sci-fi'),
(18, 'Horror', 'horror'),
(19, 'Educational', 'educational'),
(20, 'Gaming', 'gaming'),
(21, 'International', 'international'),
(22, 'Utilities', 'utilities'),
(23, 'Tools', 'tools'),
(24, 'Maintenance', 'maintenance'),
(25, 'Customization', 'customization'),
(26, 'Interface', 'interface'),
(27, 'IPTV', 'iptv'),
(28, 'PVR', 'pvr'),
(29, 'Library', 'library'),
(30, 'Metadata', 'metadata');

-- Commit all changes
COMMIT;

-- Display summary of inserted data
SELECT 'Seed data for categories and tags inserted successfully' AS Status;
SELECT 'Categories inserted:' AS Entity, COUNT(*) AS Count FROM category
UNION ALL
SELECT 'Tags inserted:', COUNT(*) FROM tag;
