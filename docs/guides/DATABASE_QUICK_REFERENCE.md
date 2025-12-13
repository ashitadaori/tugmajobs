# Job Portal Database Quick Reference

## 📊 Database Overview
- **Total Tables**: 24
- **Total Records**: 286
- **Database**: job_portal
- **Generated**: August 11, 2025

## 🏢 Core System Tables

| Table | Records | Purpose |
|-------|---------|---------|
| `users` | 11 | Main user accounts for all user types (admin, employer, jobseeker) |
| `jobs` | 2 | Job postings created by employers |
| `job_applications` | 2 | Applications submitted by job seekers |
| `categories` | 20 | Job categories for organizing postings |
| `job_types` | 8 | Employment types (full-time, part-time, etc.) |

## 👤 User Profile Tables

| Table | Records | Purpose |
|-------|---------|---------|
| `employers` | 2 | Extended employer profiles and company details |
| `jobseekers` | 5 | Comprehensive job seeker profiles |
| `admins` | 1 | Administrative user profiles with permissions |
| `employer_documents` | 6 | Business documents for employer verification |
| `employer_profiles` | 3 | Legacy employer profile data |

## 🔐 Verification & Security

| Table | Records | Purpose |
|-------|---------|---------|
| `kyc_verifications` | 0 | KYC verification sessions |
| `kyc_data` | 2 | Stored KYC verification data |
| `personal_access_tokens` | 0 | API authentication tokens |

## 📈 Analytics & Tracking

| Table | Records | Purpose |
|-------|---------|---------|
| `job_views` | 99 | Job post view tracking for analytics |
| `notifications` | 31 | System notifications and alerts |
| `saved_jobs` | 1 | User bookmarked jobs |
| `job_user` | 0 | Many-to-many pivot for saved jobs |

## 🗄️ System & Audit Tables

| Table | Records | Purpose |
|-------|---------|---------|
| `migrations` | 37 | Laravel migration history |
| `job_application_status_histories` | 3 | Audit trail for application status changes |

## 📦 Reference Data Tables (Deprecated)

| Table | Records | Status |
|-------|---------|--------|
| `company_sizes` | 6 | Reference data - may be unused |
| `industries` | 10 | Reference data - may be unused |
| `job_categories` | 10 | Reference data - may be unused |
| `job_skills` | 17 | Reference data - may be unused |
| `locations` | 10 | Reference data - may be unused |

## 🔗 Key Relationships

### User Relationships
- `users` → `employers` (1:1)
- `users` → `jobseekers` (1:1)
- `users` → `admins` (1:1)
- `users` → `jobs` (1:many) - employer creates jobs
- `users` → `job_applications` (1:many) - user applies to jobs

### Job Relationships
- `categories` → `jobs` (1:many)
- `job_types` → `jobs` (1:many)
- `jobs` → `job_applications` (1:many)
- `jobs` → `job_views` (1:many)
- `users` ↔ `jobs` (many:many) - through saved_jobs/job_user

### Verification Relationships
- `users` → `kyc_data` (1:many)
- `users` → `kyc_verifications` (1:many)
- `users` → `employer_documents` (1:many)

## 📋 Important Data Types

### Common Fields
- **id**: `bigint(20) unsigned` - Primary keys
- **user_id**: `bigint(20) unsigned` - Foreign key to users
- **created_at/updated_at**: `timestamp` - Laravel timestamps
- **status**: Various ENUM types for status tracking
- **JSON fields**: `longtext` for flexible data storage

### Key JSON Fields
- `users.skills` - User skills array
- `users.education` - Education history
- `jobseekers.work_experience` - Work history
- `jobseekers.certifications` - Certifications
- `admins.permissions` - Admin permissions
- `notifications.data` - Notification metadata

## 🎯 Usage Notes

1. **Active Tables**: Core system tables are actively used
2. **Reference Tables**: Some tables may contain legacy reference data
3. **Duplicates Fixed**: jobseekers table duplicates were cleaned up
4. **Admin Status**: Admin table is in good condition
5. **Relationships**: Foreign key constraints maintain data integrity

## 📄 Full Documentation
For complete details including all columns, data types, and descriptions, see:
- `DATABASE_DOCUMENTATION.md` - Complete markdown documentation
- `database_documentation.html` - HTML version with styling

---
*Last Updated: August 11, 2025*
