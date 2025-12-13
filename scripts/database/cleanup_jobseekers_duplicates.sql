-- JOBSEEKERS DUPLICATE CLEANUP SCRIPT
-- Generated on: 2025-08-11 13:35:50

-- IMPORTANT: CREATE BACKUP FIRST!
-- mysqldump -u root -p job_portal jobseekers > jobseekers_backup.sql

-- User ID 3: Keep profile 1, remove 2
DELETE FROM jobseekers WHERE id = 2;

