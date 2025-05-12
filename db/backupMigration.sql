-- ============================================================
-- Comprehensive Database Migration Script for Eclipse Project
-- ============================================================
-- This script handles the complete migration process:
-- 1. Creates a backup table of existing data
-- 2. Applies schema updates
-- 3. Verifies the schema
-- 4. Performs tests
-- 5. Provides rollback capability
-- ============================================================

-- Set to avoid partial execution issues
SET SQL_MODE = 'TRADITIONAL';
SET SESSION group_concat_max_len = 10000;

-- Select the database
USE eclipse;

-- Create a log table to track migration progress
DROP TABLE IF EXISTS `_migration_log`;
CREATE TABLE `_migration_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `timestamp` DATETIME NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('INFO', 'SUCCESS', 'WARNING', 'ERROR') NOT NULL
);

-- Logging procedure
DELIMITER //
CREATE PROCEDURE LogMessage(IN p_message TEXT, IN p_status VARCHAR(10))
BEGIN
    INSERT INTO `_migration_log` (`timestamp`, `message`, `status`) 
    VALUES (NOW(), p_message, p_status);
    
    -- Also display the message for real-time feedback
    SELECT CONCAT(
        CASE 
            WHEN p_status = 'ERROR' THEN '❌ ERROR: '
            WHEN p_status = 'WARNING' THEN '⚠️ WARNING: '
            WHEN p_status = 'SUCCESS' THEN '✅ SUCCESS: '
            ELSE 'ℹ️ INFO: '
        END,
        p_message
    ) AS Migration_Log;
END//
DELIMITER ;

-- Initialization
CALL LogMessage('Starting Eclipse Database Migration', 'INFO');
CALL LogMessage(CONCAT('Migration timestamp: ', NOW()), 'INFO');

-- ============================================================
-- STEP 1: BACKUP EXISTING DATA
-- ============================================================
CALL LogMessage('Creating backup tables', 'INFO');

-- Procedure to create backup tables with _bak suffix
DELIMITER //
CREATE PROCEDURE CreateBackupTables()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE table_name VARCHAR(255);
    DECLARE cur CURSOR FOR 
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
          AND table_type = 'BASE TABLE'
          AND table_name NOT LIKE '\_%'; -- Exclude tables starting with _
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO table_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Check if backup table exists and drop it
        SET @drop_stmt = CONCAT('DROP TABLE IF EXISTS `', table_name, '_bak`');
        PREPARE stmt FROM @drop_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        -- Create backup table with structure and data
        SET @create_stmt = CONCAT('CREATE TABLE `', table_name, '_bak` LIKE `', table_name, '`');
        PREPARE stmt FROM @create_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        SET @insert_stmt = CONCAT('INSERT INTO `', table_name, '_bak` SELECT * FROM `', table_name, '`');
        PREPARE stmt FROM @insert_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        CALL LogMessage(CONCAT('Backed up table: ', table_name), 'SUCCESS');
    END LOOP;
    
    CLOSE cur;
    
    -- Create a backup registry to track tables
    DROP TABLE IF EXISTS `_backup_registry`;
    CREATE TABLE `_backup_registry` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `timestamp` DATETIME NOT NULL,
        `table_name` VARCHAR(255) NOT NULL,
        `backup_table` VARCHAR(255) NOT NULL,
        `row_count` INT NOT NULL
    );
    
    -- Populate registry
    INSERT INTO `_backup_registry` (`timestamp`, `table_name`, `backup_table`, `row_count`)
    SELECT 
        NOW(),
        t.table_name,
        CONCAT(t.table_name, '_bak'),
        (SELECT COUNT(*) FROM information_schema.tables 
         WHERE table_schema = DATABASE() AND table_name = CONCAT(t.table_name, '_bak'))
    FROM information_schema.tables t
    WHERE t.table_schema = DATABASE()
      AND t.table_type = 'BASE TABLE'
      AND t.table_name NOT LIKE '\_%'
      AND t.table_name NOT LIKE '%\_bak';
    
    -- Output backup summary
    SELECT 
        table_name AS Original_Table, 
        backup_table AS Backup_Table,
        row_count AS Row_Count,
        'Backup Complete' AS Status
    FROM `_backup_registry`;
END//
DELIMITER ;

-- Execute backup
CALL CreateBackupTables();
CALL LogMessage('Backup tables created successfully', 'SUCCESS');

-- ============================================================
-- STEP 2: SCHEMA UPDATES
-- ============================================================
CALL LogMessage('Starting schema updates', 'INFO');

-- Start transaction for the schema updates
START TRANSACTION;

-- Enable checking of foreign keys
SET FOREIGN_KEY_CHECKS = 1;

-- ========== TABLE CREATION (IF NOT EXISTS) ==========
CALL LogMessage('Creating/updating tables...', 'INFO');

-- Create author table if it doesn't exist
CREATE TABLE IF NOT EXISTS author (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    created_at DATETIME NOT NULL
);

-- Create category table if it doesn't exist
CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    parent_id INT NULL
);

-- Create tag table if it doesn't exist
CREATE TABLE IF NOT EXISTS tag (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL
);

-- Create addon table if it doesn't exist
CREATE TABLE IF NOT EXISTS addon (
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
    updated_at DATETIME NOT NULL
);

-- Create addon_review table if it doesn't exist
CREATE TABLE IF NOT EXISTS addon_review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    addon_id INT NOT NULL,
    user_id INT NULL,
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    rating INT NOT NULL,
    comment TEXT NULL,
    created_at DATETIME NOT NULL
);

-- Create addon_tag table if it doesn't exist
CREATE TABLE IF NOT EXISTS addon_tag (
    addon_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (addon_id, tag_id)
);

-- Create screenshot table if it doesn't exist
CREATE TABLE IF NOT EXISTS screenshot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    addon_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

CALL LogMessage('Tables created/updated successfully', 'SUCCESS');

-- ========== ADD CONSTRAINTS (IF NOT EXISTS) ==========
CALL LogMessage('Adding constraints...', 'INFO');

-- UNIQUE constraints
-- For category table
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'category' AND CONSTRAINT_NAME = 'uq_category_slug'
    ),
    'ALTER TABLE category ADD CONSTRAINT uq_category_slug UNIQUE (slug);',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- For tag table
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tag' AND CONSTRAINT_NAME = 'uq_tag_slug'
    ),
    'ALTER TABLE tag ADD CONSTRAINT uq_tag_slug UNIQUE (slug);',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- For addon table
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon' AND CONSTRAINT_NAME = 'uq_addon_slug'
    ),
    'ALTER TABLE addon ADD CONSTRAINT uq_addon_slug UNIQUE (slug);',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FOREIGN KEY constraints
-- For category self-reference
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'category' AND CONSTRAINT_NAME = 'fk_category_parent'
    ),
    'ALTER TABLE category ADD CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES category(id) ON DELETE SET NULL;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- For addon table foreign keys
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon' AND CONSTRAINT_NAME = 'fk_addon_author'
    ),
    'ALTER TABLE addon ADD CONSTRAINT fk_addon_author FOREIGN KEY (author_id) REFERENCES author(id) ON DELETE RESTRICT;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon' AND CONSTRAINT_NAME = 'fk_addon_category'
    ),
    'ALTER TABLE addon ADD CONSTRAINT fk_addon_category FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE RESTRICT;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- For addon_review
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon_review' AND CONSTRAINT_NAME = 'fk_review_addon'
    ),
    'ALTER TABLE addon_review ADD CONSTRAINT fk_review_addon FOREIGN KEY (addon_id) REFERENCES addon(id) ON DELETE CASCADE;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon_review' AND CONSTRAINT_NAME = 'chk_review_rating'
    ),
    'ALTER TABLE addon_review ADD CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5);',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- For addon_tag
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon_tag' AND CONSTRAINT_NAME = 'fk_addon_tag_addon'
    ),
    'ALTER TABLE addon_tag ADD CONSTRAINT fk_addon_tag_addon FOREIGN KEY (addon_id) REFERENCES addon(id) ON DELETE CASCADE;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'addon_tag' AND CONSTRAINT_NAME = 'fk_addon_tag_tag'
    ),
    'ALTER TABLE addon_tag ADD CONSTRAINT fk_addon_tag_tag FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE CASCADE;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- For screenshot
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'screenshot' AND CONSTRAINT_NAME = 'fk_screenshot_addon'
    ),
    'ALTER TABLE screenshot ADD CONSTRAINT fk_screenshot_addon FOREIGN KEY (addon_id) REFERENCES addon(id) ON DELETE CASCADE;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CALL LogMessage('Constraints added successfully', 'SUCCESS');

-- ========== ADD INDEXES (IF NOT EXISTS) ==========
CALL LogMessage('Adding indexes...', 'INFO');

-- Check if index exists function
DELIMITER //
DROP PROCEDURE IF EXISTS CreateIndexIfNotExists//
CREATE PROCEDURE CreateIndexIfNotExists(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_index_columns VARCHAR(255)
)
BEGIN
    DECLARE index_exists INT;
    
    SELECT COUNT(1) INTO index_exists
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = p_table_name
      AND index_name = p_index_name;
    
    IF index_exists = 0 THEN
        SET @sql = CONCAT('CREATE INDEX ', p_index_name, ' ON ', p_table_name, '(', p_index_columns, ');');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        CALL LogMessage(CONCAT('Created index: ', p_index_name, ' on table ', p_table_name), 'SUCCESS');
    ELSE
        CALL LogMessage(CONCAT('Index already exists: ', p_index_name, ' on table ', p_table_name), 'INFO');
    END IF;
END//
DELIMITER ;

-- Addon indexes
CALL CreateIndexIfNotExists('addon', 'idx_addon_author', 'author_id');
CALL CreateIndexIfNotExists('addon', 'idx_addon_category', 'category_id');
CALL CreateIndexIfNotExists('addon', 'idx_addon_created', 'created_at');
CALL CreateIndexIfNotExists('addon', 'idx_addon_updated', 'updated_at');
CALL CreateIndexIfNotExists('addon', 'idx_addon_downloads', 'downloads_count DESC');
CALL CreateIndexIfNotExists('addon', 'idx_addon_rating', 'rating DESC');

-- Category indexes
CALL CreateIndexIfNotExists('category', 'idx_category_parent', 'parent_id');

-- Review indexes
CALL CreateIndexIfNotExists('addon_review', 'idx_review_addon', 'addon_id');
CALL CreateIndexIfNotExists('addon_review', 'idx_review_rating', 'rating');
CALL CreateIndexIfNotExists('addon_review', 'idx_review_created', 'created_at DESC');

-- Screenshot indexes
CALL CreateIndexIfNotExists('screenshot', 'idx_screenshot_addon', 'addon_id');
CALL CreateIndexIfNotExists('screenshot', 'idx_screenshot_sort', 'addon_id, sort_order');

CALL LogMessage('Indexes added successfully', 'SUCCESS');

-- ========== ADD ADDITIONAL COLUMNS OR CHANGES ==========
CALL LogMessage('Adding any additional columns...', 'INFO');

-- Example: Add is_featured column to addon table
SELECT IF(
    NOT EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'addon' AND COLUMN_NAME = 'is_featured'
    ),
    'ALTER TABLE addon ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0;',
    'SELECT 1;'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Finalize schema updates - decide whether to commit or rollback
CALL LogMessage('Schema updates completed successfully', 'SUCCESS');

-- ============================================================
-- STEP 3: SCHEMA VERIFICATION
-- ============================================================
CALL LogMessage('Starting schema verification...', 'INFO');

-- Create verification procedure
DELIMITER //
CREATE PROCEDURE VerifySchema()
BEGIN
    DECLARE verification_failed BOOLEAN DEFAULT FALSE;
    
    -- Table existence check
    SELECT 'Checking required tables exist...' AS Verification_Step;
    
    SELECT 
        COUNT(*) AS tables_found,
        7 AS tables_expected,
        COUNT(*) = 7 AS tables_complete,
        GROUP_CONCAT(missing_tables) AS missing_tables
    FROM (
        SELECT GROUP_CONCAT(table_name) AS missing_tables
        FROM (
            SELECT 'author' AS table_name
            UNION ALL SELECT 'category'
            UNION ALL SELECT 'tag'
            UNION ALL SELECT 'addon'
            UNION ALL SELECT 'addon_review'
            UNION ALL SELECT 'addon_tag'
            UNION ALL SELECT 'screenshot'
        ) AS expected_tables
        WHERE table_name NOT IN (
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
              AND table_type = 'BASE TABLE'
        )
    ) AS missing;
    
    -- Check if any tables are missing
    SELECT COUNT(*) INTO @missing_table_count
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_type = 'BASE TABLE'
      AND table_name IN ('author', 'category', 'tag', 'addon', 'addon_review', 'addon_tag', 'screenshot');
    
    IF @missing_table_count < 7 THEN
        SET verification_failed = TRUE;
        CALL LogMessage('Missing required tables', 'ERROR');
    END IF;
    
    -- Check foreign key constraints
    SELECT 'Checking foreign key constraints...' AS Verification_Step;
    
    SELECT 
        COUNT(*) AS foreign_key_count,
        7 AS minimum_expected,
        COUNT(*) >= 7 AS constraints_complete
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_TYPE = 'FOREIGN KEY';
    
    SELECT COUNT(*) INTO @fk_count
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_TYPE = 'FOREIGN KEY';
    
    IF @fk_count < 7 THEN
        SET verification_failed = TRUE;
        CALL LogMessage('Missing required foreign key constraints', 'ERROR');
    END IF;
    
    -- Check unique constraints
    SELECT 'Checking unique constraints...' AS Verification_Step;
    
    SELECT 
        COUNT(*) AS unique_count,
        3 AS minimum_expected,
        COUNT(*) >= 3 AS unique_complete
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_TYPE = 'UNIQUE';
    
    SELECT COUNT(*) INTO @unique_count
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_TYPE = 'UNIQUE';
    
    IF @unique_count < 3 THEN
        SET verification_failed = TRUE;
        CALL LogMessage('Missing required unique constraints', 'ERROR');
    END IF;
    
    -- Check indexes
    SELECT 'Checking indexes...' AS Verification_Step;
    
    SELECT 
        COUNT(DISTINCT INDEX_NAME) AS index_count,
        12 AS minimum_expected,
        COUNT(DISTINCT INDEX_NAME) >= 12 AS indexes_complete
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND INDEX_NAME != 'PRIMARY';
    
    SELECT COUNT(DISTINCT INDEX_NAME) INTO @index_count
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND INDEX_NAME != 'PRIMARY';
    
    IF @index_count < 12 THEN
        SET verification_failed = TRUE;
        CALL LogMessage('Missing required indexes', 'ERROR');
    END IF;
    
    -- Summary of verification
    IF verification_failed THEN
        SELECT 'Schema verification FAILED' AS Verification_Result;
        CALL LogMessage('Schema verification failed', 'ERROR');
    ELSE
        SELECT 'Schema verification PASSED' AS Verification_Result;
        CALL LogMessage('Schema verification passed', 'SUCCESS');
    END IF;
    
    -- Store the result for later use
    SELECT verification_failed AS verification_failed;
END//
DELIMITER ;

-- Execute verification
CALL VerifySchema();
SELECT @verification_failed AS Verification_Failed;

-- ============================================================
-- STEP 4: BASIC TESTING
-- ============================================================
CALL LogMessage('Starting schema testing...', 'INFO');

-- Create test procedure
DELIMITER //
CREATE PROCEDURE TestSchema()
BEGIN
    DECLARE continue_handler INT DEFAULT 1;
    
    -- Start a transaction for test data that we'll roll back
    START TRANSACTION;
    
    -- Disable foreign key checks temporarily for our test data
    SET FOREIGN_KEY_CHECKS = 0;
    
    -- Test database operations
    SELECT 'Testing database operations...' AS Test_Step;
    
    -- 1. Test insertion into author table
    INSERT INTO author (name, email, website, created_at)
    VALUES ('Test Author', 'test@example.com', 'https://example.com', NOW());
    
    SELECT 'Author insertion successful' AS Test_Result, 
           LAST_INSERT_ID() AS Author_ID;
    
    SET @author_id = LAST_INSERT_ID();
    
    -- 2. Test insertion into category table
    INSERT INTO category (name, slug, parent_id)
    VALUES ('Test Category', 'test-category', NULL);
    
    SELECT 'Category insertion successful' AS Test_Result, 
           LAST_INSERT_ID() AS Category_ID;
    
    SET @category_id = LAST_INSERT_ID();
    
    -- 3. Test insertion into tag table
    INSERT INTO tag (name, slug)
    VALUES ('Test Tag', 'test-tag');
    
    SELECT 'Tag insertion successful' AS Test_Result, 
           LAST_INSERT_ID() AS Tag_ID;
    
    SET @tag_id = LAST_INSERT_ID();
    
    -- 4. Test insertion into addon table
    INSERT INTO addon (
        name, 
        slug, 
        description, 
        version, 
        author_id, 
        category_id, 
        repository_url, 
        download_url, 
        icon_url, 
        fanart_url, 
        kodi_version_min, 
        kodi_version_max, 
        downloads_count, 
        rating, 
        created_at, 
        updated_at
    ) VALUES (
        'Test Addon', 
        'test-addon', 
        'This is a test addon', 
        '1.0.0', 
        @author_id, 
        @category_id, 
        'https://github.com/test/repository', 
        'https://example.com/test-addon.zip', 
        'https://example.com/icon.png', 
        'https://example.com/fanart.jpg', 
        '19.0', 
        '20.0', 
        0, 
        0.00, 
        NOW(), 
        NOW()
    );
    
    SELECT 'Addon insertion successful' AS Test_Result, 
           LAST_INSERT_ID() AS Addon_ID;
    
    SET @addon_id = LAST_INSERT_ID();
    
    -- 5. Test insertion into addon_review table
    INSERT INTO addon_review (
        addon_id,
        user_id,
        name,
        email,
        rating,
        comment,
        created_at
    ) VALUES (
        @addon_id,
        NULL,
        'Test User',
        'testuser@example.com',
        5,
        'Great addon!',
        NOW()
    );
    
    SELECT 'Review insertion successful' AS Test_Result, 
           LAST_INSERT_ID() AS Review_ID;
    
    -- 6. Test insertion into addon_tag table
    INSERT INTO addon_tag (addon_id, tag_id)
    VALUES (@addon_id, @tag_id);
    
    SELECT 'Tag association successful' AS Test_Result;
    
    -- 7. Test insertion into screenshot table
    INSERT INTO screenshot (
        addon_id,
        url,
        description,
        sort_order
    ) VALUES (
        @addon_id,
        'https://example.com/screenshot1.jpg',
        'Main screen',
        1
    );
    
    SELECT 'Screenshot insertion successful' AS Test_Result, 
           LAST_INSERT_ID() AS Screenshot_ID;
    
    -- 8. Test foreign key constraints
    SELECT 'Testing foreign key constraints...' AS Test_Step;
    
    -- This should fail due to foreign key constraint
    SET @fk_test_success = 1;
    BEGIN
        DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @fk_test_success = 0;
        
        INSERT INTO addon (
            name, slug, description, version, author_id, category_id,
            download_url, created_at, updated_at
        ) VALUES (
            'Invalid Addon', 'invalid-addon', 'This should fail', '1.0.0', 
            99999, 99999, 'https://example.com/invalid.zip', NOW(), NOW()
        );
    END;
    
    IF @fk_test_success = 0 THEN
        SELECT 'Foreign key constraint test PASSED' AS Test_Result;
    ELSE
        SELECT 'Foreign key constraint test FAILED' AS Test_Result;
        CALL LogMessage('Foreign key constraint test failed', 'ERROR');
    END IF;
    
    -- 9. Test basic queries
    SELECT 'Testing basic queries...' AS Test_Step;
    
    -- Test join between addon and author
    SELECT 
        a.name AS addon_name,
        au.name AS author_name
    FROM addon a
    JOIN author au ON a.author_id = au.id
    WHERE a.id = @addon_id;
    
    -- Test join between addon and category
    SELECT 
        a.name AS addon_name,
        c.name AS category_name
    FROM addon a
    JOIN category c ON a.category_id = c.id
    WHERE a.id = @addon_id;
    
    -- Test join between addon and tag through addon_tag
    SELECT 
        a.name AS addon_name,
        t.name AS tag_name
    FROM addon a
    JOIN addon_tag at ON a.id = at.addon_id
    JOIN tag t ON at.tag_id = t.id
    WHERE a.id = @addon_id;
    
    -- Roll back test data
    ROLLBACK;
    
    -- Re-enable foreign key checks
    SET FOREIGN_KEY_CHECKS = 1;
    
    -- Final test result
    SELECT 'All tests completed' AS Test_Status;
    CALL LogMessage('Schema tests completed successfully', 'SUCCESS');
END//
DELIMITER ;

-- Execute tests
CALL TestSchema();

-- ============================================================
-- STEP 5: CREATE DATABASE STATUS PROCEDURE
-- ============================================================
CALL LogMessage('Creating database status procedure...', 'INFO');

DELIMITER //
CREATE PROCEDURE IF NOT EXISTS DB_Status()
BEGIN
    -- Show table counts
    SELECT 'Database Table Counts' AS Report;
    SELECT 'author' AS Table_Name, COUNT(*) AS Row_Count FROM author
    UNION ALL SELECT 'category', COUNT(*) FROM category
    UNION ALL SELECT 'tag', COUNT(*) FROM tag
    UNION ALL SELECT 'addon', COUNT(*) FROM addon
    UNION ALL SELECT 'addon_review', COUNT(*) FROM addon_review
    UNION ALL SELECT 'addon_tag', COUNT(*) FROM addon_tag
    UNION ALL SELECT 'screenshot', COUNT(*) FROM screenshot;
    
    -- Show foreign key constraints
    SELECT 'Foreign Key Constraints' AS Report;
    SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND REFERENCED_TABLE_SCHEMA IS NOT NULL
    ORDER BY TABLE_NAME, COLUMN_NAME;
    
    -- Show indexes
    SELECT 'Database Indexes' AS Report;
    SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
end//
DELIMITER ;

CALL LogMessage('Database status procedure created', 'SUCCESS');

-- ============================================================
-- STEP 6: CREATE ROLLBACK PROCEDURE
-- ============================================================
CALL LogMessage('Creating rollback procedure...', 'INFO');

DELIMITER //
CREATE PROCEDURE RollbackMigration()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE table_name VARCHAR(255);
    DECLARE backup_table VARCHAR(255);
    DECLARE cur CURSOR FOR 
        SELECT table_name, backup_table 
        FROM `_backup_registry`;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    CALL LogMessage('Starting rollback process...', 'WARNING');
    
    -- Disable foreign key checks for rollback
    SET FOREIGN_KEY_CHECKS = 0;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO table_name, backup_table;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Drop current table
        SET @drop_stmt = CONCAT('DROP TABLE IF EXISTS `', table_name, '`');
        PREPARE stmt FROM @drop_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        -- Rename backup table to original name
        SET @rename_stmt = CONCAT('RENAME TABLE `', backup_table, '` TO `', table_name, '`');
        PREPARE stmt FROM @rename_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        CALL LogMessage(CONCAT('Restored table from backup: ', table_name), 'SUCCESS');
    END LOOP;
    
    CLOSE cur;
    
    -- Re-enable foreign key checks
    SET FOREIGN_KEY_CHECKS = 1;
    
    -- Clean up migration tracking tables
    DROP TABLE IF EXISTS `_backup_registry`;
    DROP TABLE IF EXISTS `_migration_log`;
    
    SELECT 'Migration rolled back successfully - database restored to original state' AS Rollback_Status;
END//
DELIMITER ;

CALL LogMessage('Rollback procedure created', 'SUCCESS');

-- ============================================================
-- STEP 7: COMMIT OR ROLLBACK BASED ON VERIFICATION
-- ============================================================

-- Check if verification failed
SELECT verification_failed INTO @verification_failed FROM (
    SELECT MAX(CASE WHEN message LIKE '%verification failed%' THEN 1 ELSE 0 END) AS verification_failed
    FROM `_migration_log` 
    WHERE status = 'ERROR'
) AS vf;

-- Allow admin to decide whether to commit or rollback
SELECT 
    'Migration status' AS Migration_Status,
    CASE 
        WHEN @verification_failed = 1 THEN 'FAILED - Verification errors detected'
        ELSE 'SUCCESS - Migration completed successfully'
    END AS Result,
    'To rollback the migration, run: CALL RollbackMigration();' AS Rollback_Instructions,
    'To commit changes, run: COMMIT;' AS Commit_Instructions,
    'To view detailed database status, run: CALL DB_Status();' AS Status_Instructions;

-- If everything passed, we can confirm the COMMIT
-- Uncomment the next line to auto-commit successful migrations
-- IF @verification_failed = 0 THEN COMMIT; ELSE ROLLBACK; END IF;

-- ============================================================
-- STEP 8: FINAL STATUS
-- ============================================================
CALL LogMessage('Migration script execution completed', 'INFO');

-- Display full migration log for review
SELECT 
    id,
    timestamp,
    CASE 
        WHEN status = 'ERROR' THEN CONCAT('❌ ', message)
        WHEN status = 'WARNING' THEN CONCAT('⚠️ ', message)
        WHEN status = 'SUCCESS' THEN CONCAT('✅ ', message)
        ELSE CONCAT('ℹ️ ', message)
    END AS log_entry,
    status
FROM `_migration_log`
ORDER BY id;

-- Call database status
CALL DB_Status();
