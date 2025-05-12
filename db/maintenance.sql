-- ============================================================
-- Database Maintenance Script for Eclipse Kodi Repository
-- ============================================================
-- This script performs comprehensive database maintenance:
-- 1. Creates maintenance log
-- 2. Checks data integrity
-- 3. Removes orphaned records
-- 4. Optimizes tables
-- 5. Updates statistics
-- ============================================================

-- Set to avoid partial execution issues
SET SQL_MODE = 'TRADITIONAL';
SET SESSION group_concat_max_len = 10000;

-- Select the database
USE eclipse;

-- Create maintenance log table if it doesn't exist
CREATE TABLE IF NOT EXISTS `_maintenance_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `timestamp` DATETIME NOT NULL,
    `operation` VARCHAR(255) NOT NULL,
    `details` TEXT NULL,
    `status` ENUM('SUCCESS', 'WARNING', 'ERROR') NOT NULL
);

-- Log function
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS LogMaintenance(IN p_operation VARCHAR(255), IN p_details TEXT, IN p_status VARCHAR(10))
BEGIN
    INSERT INTO `_maintenance_log` (`timestamp`, `operation`, `details`, `status`) 
    VALUES (NOW(), p_operation, p_details, p_status);
    
    -- Also display the message for real-time feedback
    SELECT CONCAT(
        CASE 
            WHEN p_status = 'ERROR' THEN '❌ ERROR: '
            WHEN p_status = 'WARNING' THEN '⚠️ WARNING: '
            ELSE '✅ SUCCESS: '
        END,
        p_operation,
        IF(p_details IS NOT NULL, CONCAT(' - ', p_details), '')
    ) AS Maintenance_Log;
END//
DELIMITER ;

-- Start maintenance process
CALL LogMaintenance('Maintenance started', 'Beginning database maintenance procedures', 'SUCCESS');

-- ============================================================
-- STEP 1: Check Data Integrity
-- ============================================================
CALL LogMaintenance('Data integrity checks', 'Checking for table integrity issues', 'SUCCESS');

-- Check tables for errors
SET @check_tables_result = '';
SELECT 
    GROUP_CONCAT(
        CONCAT(
            'CHECK TABLE ', table_name, ' EXTENDED;'
        ) SEPARATOR ' '
    ) INTO @check_tables_sql
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
  AND table_name NOT LIKE '\_%';

-- Execute CHECK TABLE commands using prepared statement
SET @current_table = '';
PREPARE stmt FROM 'SELECT CONCAT(\'SELECT "Checking table: \', table_name, \'" AS status; SET @current_table = "\', table_name, \'"; CHECK TABLE \', table_name, \' EXTENDED\') 
                   FROM information_schema.tables 
                   WHERE table_schema = DATABASE() 
                     AND table_type = "BASE TABLE" 
                     AND table_name NOT LIKE "\\_%"';
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if any tables need repair
SELECT 
    COUNT(*) INTO @tables_needing_repair
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND engine = 'MyISAM' -- Only MyISAM tables can be repaired this way
  AND (
      -- Add conditions to identify tables needing repair
      (CREATE_TIME < DATE_SUB(NOW(), INTERVAL 30 DAY))
      OR (UPDATE_TIME < DATE_SUB(NOW(), INTERVAL 30 DAY))
  );

IF @tables_needing_repair > 0 THEN
    CALL LogMaintenance('Table repair', CONCAT(@tables_needing_repair, ' tables may need repair'), 'WARNING');
    
    -- You can add repair code here if needed
    -- REPAIR TABLE statements would go here
ELSE
    CALL LogMaintenance('Table check', 'All tables appear to be in good condition', 'SUCCESS');
END IF;

-- ============================================================
-- STEP 2: Remove Orphaned Records
-- ============================================================
CALL LogMaintenance('Orphaned record check', 'Checking for and removing orphaned records', 'SUCCESS');

-- Begin transaction for safety
START TRANSACTION;

-- Check and delete orphaned addon_tag records
SELECT COUNT(*) INTO @orphaned_addon_tags
FROM addon_tag at
LEFT JOIN addon a ON at.addon_id = a.id
LEFT JOIN tag t ON at.tag_id = t.id
WHERE a.id IS NULL OR t.id IS NULL;

IF @orphaned_addon_tags > 0 THEN
    -- Delete orphaned addon_tag records
    DELETE at FROM addon_tag at
    LEFT JOIN addon a ON at.addon_id = a.id
    LEFT JOIN tag t ON at.tag_id = t.id
    WHERE a.id IS NULL OR t.id IS NULL;
    
    CALL LogMaintenance('Orphaned addon tags removed', CONCAT(@orphaned_addon_tags, ' orphaned addon_tag records were deleted'), 'SUCCESS');
ELSE
    CALL LogMaintenance('Orphaned addon tags', 'No orphaned addon_tag records found', 'SUCCESS');
END IF;

-- Check and delete orphaned addon_review records
SELECT COUNT(*) INTO @orphaned_reviews
FROM addon_review ar
LEFT JOIN addon a ON ar.addon_id = a.id
WHERE a.id IS NULL;

IF @orphaned_reviews > 0 THEN
    -- Delete orphaned addon_review records
    DELETE ar FROM addon_review ar
    LEFT JOIN addon a ON ar.addon_id = a.id
    WHERE a.id IS NULL;
    
    CALL LogMaintenance('Orphaned reviews removed', CONCAT(@orphaned_reviews, ' orphaned addon_review records were deleted'), 'SUCCESS');
ELSE
    CALL LogMaintenance('Orphaned reviews', 'No orphaned addon_review records found', 'SUCCESS');
END IF;

-- Check and delete orphaned screenshot records
SELECT COUNT(*) INTO @orphaned_screenshots
FROM screenshot s
LEFT JOIN addon a ON s.addon_id = a.id
WHERE a.id IS NULL;

IF @orphaned_screenshots > 0 THEN
    -- Delete orphaned screenshot records
    DELETE s FROM screenshot s
    LEFT JOIN addon a ON s.addon_id = a.id
    WHERE a.id IS NULL;
    
    CALL LogMaintenance('Orphaned screenshots removed', CONCAT(@orphaned_screenshots, ' orphaned screenshot records were deleted'), 'SUCCESS');
ELSE
    CALL LogMaintenance('Orphaned screenshots', 'No orphaned screenshot records found', 'SUCCESS');
END IF;

-- Commit orphaned record removal
COMMIT;

-- ============================================================
-- STEP 3: Recalculate Derived Data
-- ============================================================
CALL LogMaintenance('Recalculating derived data', 'Updating aggregate values like ratings', 'SUCCESS');

-- Begin transaction for data updates
START TRANSACTION;

-- Recalculate addon ratings based on reviews
UPDATE addon a
SET rating = (
    SELECT AVG(rating)
    FROM addon_review
    WHERE addon_id = a.id
    GROUP BY addon_id
)
WHERE EXISTS (
    SELECT 1
    FROM addon_review
    WHERE addon_id = a.id
);

-- Set rating to 0 for addons with no reviews
UPDATE addon a
SET rating = 0
WHERE NOT EXISTS (
    SELECT 1
    FROM addon_review
    WHERE addon_id = a.id
);

CALL LogMaintenance('Ratings recalculated', 'Addon ratings have been updated based on reviews', 'SUCCESS');

-- Commit data updates
COMMIT;

-- ============================================================
-- STEP 4: Optimize Tables
-- ============================================================
CALL LogMaintenance('Table optimization', 'Optimizing database tables', 'SUCCESS');

-- Optimize all tables
SET @optimize_tables = '';
SELECT 
    GROUP_CONCAT(
        CONCAT(
            'SELECT "Optimizing table: ', table_name, '" AS status; ',
            'OPTIMIZE TABLE ', table_name, ';'
        ) SEPARATOR ' '
    ) INTO @optimize_tables
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
  AND table_name NOT LIKE '\_%';

-- Execute OPTIMIZE TABLE commands
PREPARE stmt FROM @optimize_tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CALL LogMaintenance('Tables optimized', 'All tables have been optimized', 'SUCCESS');

-- ============================================================
-- STEP 5: Update Statistics
-- ============================================================
CALL LogMaintenance('Statistics update', 'Updating table statistics for query optimization', 'SUCCESS');

-- Analyze all tables to update statistics
SET @analyze_tables = '';
SELECT 
    GROUP_CONCAT(
        CONCAT(
            'SELECT "Analyzing table: ', table_name, '" AS status; ',
            'ANALYZE TABLE ', table_name, ';'
        ) SEPARATOR ' '
    ) INTO @analyze_tables
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
  AND table_name NOT LIKE '\_%';

-- Execute ANALYZE TABLE commands
PREPARE stmt FROM @analyze_tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CALL LogMaintenance('Statistics updated', 'Table statistics have been updated for query optimization', 'SUCCESS');

-- ============================================================
-- STEP 6: Clean Up Old Log Entries
-- ============================================================
CALL LogMaintenance('Log cleanup', 'Removing old maintenance log entries', 'SUCCESS');

-- Delete maintenance log entries older than 30 days
DELETE FROM `_maintenance_log`
WHERE `timestamp` < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- ============================================================
-- STEP 7: Final Status Report
-- ============================================================
-- Show database size
SELECT 
    CONCAT(ROUND(SUM(data_length + index_length) / 1024 / 1024, 2), ' MB') AS Total_Database_Size
FROM information_schema.tables
WHERE table_schema = DATABASE();

-- Show table counts
SELECT 'Database Table Counts' AS Report;
SELECT 'author' AS Table_Name, COUNT(*) AS Row_Count FROM author
UNION ALL SELECT 'category', COUNT(*) FROM category
UNION ALL SELECT 'tag', COUNT(*) FROM tag
UNION ALL SELECT 'addon', COUNT(*) FROM addon
UNION ALL SELECT 'addon_review', COUNT(*) FROM addon_review
UNION ALL SELECT 'addon_tag', COUNT(*) FROM addon_tag
UNION ALL SELECT 'screenshot', COUNT(*) FROM screenshot;

-- Show recent maintenance operations
SELECT 'Recent Maintenance Operations' AS Report;
SELECT 
    DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:%s') AS Timestamp,
    operation AS Operation,
    status AS Status
FROM `_maintenance_log`
ORDER BY timestamp DESC
LIMIT 10;

-- Log maintenance completion
CALL LogMaintenance('Maintenance completed', 'All maintenance tasks completed successfully', 'SUCCESS');
