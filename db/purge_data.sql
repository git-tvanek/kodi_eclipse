-- ============================================================
-- Database Data Purge Script for Eclipse Kodi Repository
-- ============================================================
-- This script removes ALL DATA from all tables but preserves
-- the database schema (tables, constraints, indexes, etc.)
-- USE WITH EXTREME CAUTION - ALL DATA WILL BE LOST!
-- ============================================================

-- Set variables to avoid partial execution issues
SET SQL_MODE = 'TRADITIONAL';

-- Select the database
USE eclipse;

-- Create purge log table if it doesn't exist
CREATE TABLE IF NOT EXISTS `_purge_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `timestamp` DATETIME NOT NULL,
    `operation` VARCHAR(255) NOT NULL,
    `details` TEXT NULL
);

-- Log function
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS LogPurge(IN p_operation VARCHAR(255), IN p_details TEXT)
BEGIN
    INSERT INTO `_purge_log` (`timestamp`, `operation`, `details`) 
    VALUES (NOW(), p_operation, p_details);
    
    -- Display message for real-time feedback
    SELECT CONCAT(p_operation, IF(p_details IS NOT NULL, CONCAT(' - ', p_details), '')) AS Purge_Log;
END//
DELIMITER ;

-- Safety confirmation procedure
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS ConfirmPurge()
BEGIN
    -- Create a confirmation table
    DROP TABLE IF EXISTS `_purge_confirmation`;
    CREATE TABLE `_purge_confirmation` (
        `id` INT PRIMARY KEY,
        `confirmation_text` VARCHAR(255),
        `confirmed` BOOLEAN DEFAULT FALSE
    );
    
    -- Insert default row
    INSERT INTO `_purge_confirmation` (`id`, `confirmation_text`) VALUES (1, 'TYPE_CONFIRM_PURGE_TO_PROCEED');
    
    -- Display instructions
    SELECT 'WARNING! This script will DELETE ALL DATA in your database but preserve the schema.' AS WARNING;
    SELECT 'To continue, run: UPDATE `_purge_confirmation` SET `confirmed` = TRUE, `confirmation_text` = "CONFIRM_PURGE" WHERE id = 1;' AS Confirmation_Required;
    SELECT 'After confirming, run: CALL ExecutePurge();' AS Next_Step;
    
    -- Display confirmation status
    SELECT * FROM `_purge_confirmation`;
END//
DELIMITER ;

-- Main purge procedure
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS ExecutePurge()
BEGIN
    DECLARE is_confirmed BOOLEAN;
    
    -- Check confirmation
    SELECT `confirmed` INTO is_confirmed FROM `_purge_confirmation` WHERE id = 1;
    
    IF is_confirmed = TRUE THEN
        -- Start transaction
        START TRANSACTION;
        
        -- Log start of purge
        CALL LogPurge('Data purge started', 'Beginning database data purge while preserving schema');
        
        -- Temporarily disable foreign key checks
        SET FOREIGN_KEY_CHECKS = 0;
        
        -- Truncate all tables (preserves schema but removes all data)
        -- The order doesn't matter since foreign key checks are disabled
        
        -- Truncate relationship tables first
        TRUNCATE TABLE `addon_tag`;
        CALL LogPurge('Table truncated', 'addon_tag');
        
        TRUNCATE TABLE `addon_review`;
        CALL LogPurge('Table truncated', 'addon_review');
        
        TRUNCATE TABLE `screenshot`;
        CALL LogPurge('Table truncated', 'screenshot');
        
        -- Then truncate main tables
        TRUNCATE TABLE `addon`;
        CALL LogPurge('Table truncated', 'addon');
        
        TRUNCATE TABLE `category`;
        CALL LogPurge('Table truncated', 'category');
        
        TRUNCATE TABLE `tag`;
        CALL LogPurge('Table truncated', 'tag');
        
        TRUNCATE TABLE `author`;
        CALL LogPurge('Table truncated', 'author');
        
        -- Re-enable foreign key checks
        SET FOREIGN_KEY_CHECKS = 1;
        
        -- Commit changes
        COMMIT;
        
        -- Log completion
        CALL LogPurge('Data purge completed', 'All data has been purged while preserving the database schema');
        
        -- Drop confirmation table
        DROP TABLE IF EXISTS `_purge_confirmation`;
        
        -- Show summary
        SELECT 'Database purge completed successfully. All data has been removed while preserving the schema.' AS Purge_Status;
        SELECT 'The database is now empty and ready for fresh data.' AS Next_Steps;
    ELSE
        -- Not confirmed
        SELECT 'Purge not confirmed. No action taken.' AS Purge_Status;
        SELECT 'To confirm the purge, run: UPDATE `_purge_confirmation` SET `confirmed` = TRUE, `confirmation_text` = "CONFIRM_PURGE" WHERE id = 1;' AS Confirmation_Required;
    END IF;
END//
DELIMITER ;

-- Start the confirmation process
CALL ConfirmPurge();
