-- Add missing indexes for existing installations (idempotent).
-- Run this in phpMyAdmin / MySQL client.
--
-- Notes:
-- - Uses information_schema to check if an index exists.
-- - Works on MySQL/MariaDB.
-- - Safe to re-run; it only adds indexes that are missing.

-- If you use the default DB name from database.sql:
USE `web-mathdosman`;

SET @db := DATABASE();

-- Helper pattern:
--   SELECT COUNT(*) FROM information_schema.statistics
--   WHERE table_schema=@db AND table_name='...' AND index_name='...';
-- If 0, create the index.

-- packages indexes
SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'packages' AND index_name = 'idx_packages_status'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `packages` ADD INDEX `idx_packages_status` (`status`)', 'SELECT "idx_packages_status exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'packages' AND index_name = 'idx_packages_subject'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `packages` ADD INDEX `idx_packages_subject` (`subject_id`)', 'SELECT "idx_packages_subject exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'packages' AND index_name = 'idx_packages_subject_status'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `packages` ADD INDEX `idx_packages_subject_status` (`subject_id`, `status`)', 'SELECT "idx_packages_subject_status exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- questions indexes
SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'questions' AND index_name = 'idx_questions_subject'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `questions` ADD INDEX `idx_questions_subject` (`subject_id`)', 'SELECT "idx_questions_subject exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'questions' AND index_name = 'idx_questions_status'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `questions` ADD INDEX `idx_questions_status` (`status_soal`)', 'SELECT "idx_questions_status exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'questions' AND index_name = 'idx_questions_subject_status'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `questions` ADD INDEX `idx_questions_subject_status` (`subject_id`, `status_soal`)', 'SELECT "idx_questions_subject_status exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'questions' AND index_name = 'idx_questions_created_at'
);
SET @sql := IF(@idx = 0, 'ALTER TABLE `questions` ADD INDEX `idx_questions_created_at` (`created_at`)', 'SELECT "idx_questions_created_at exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Done
SELECT 'OK - index patch complete' AS result;
