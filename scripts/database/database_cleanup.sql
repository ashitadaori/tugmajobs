-- GENERATED DATABASE CLEANUP SCRIPT
-- Generated on: 2025-08-11 13:28:12
-- Database: job_portal

-- IMPORTANT: CREATE BACKUP BEFORE RUNNING!
-- Run: mysqldump -u root -p job_portal > job_portal_backup_2025-08-11.sql

-- SAFE TO DROP (Empty unused tables)
-- These tables are empty and not used by the application
DROP TABLE IF EXISTS `password_resets`;

-- REVIEW REQUIRED (Unused tables with data)
-- These tables have data but seem unused - review before dropping
-- DROP TABLE IF EXISTS `company_sizes`; -- Contains 6 records - REVIEW FIRST!
-- DROP TABLE IF EXISTS `industries`; -- Contains 10 records - REVIEW FIRST!
-- DROP TABLE IF EXISTS `job_application_status_histories`; -- Contains 3 records - REVIEW FIRST!
-- DROP TABLE IF EXISTS `job_categories`; -- Contains 10 records - REVIEW FIRST!
-- DROP TABLE IF EXISTS `job_skills`; -- Contains 17 records - REVIEW FIRST!
-- DROP TABLE IF EXISTS `locations`; -- Contains 10 records - REVIEW FIRST!
-- DROP TABLE IF EXISTS `saved_jobs`; -- Contains 1 records - REVIEW FIRST!

