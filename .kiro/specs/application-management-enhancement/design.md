# Design Document

## Overview

This design document outlines the technical implementation for enhancing the Job Application Management System. The system will provide a complete application lifecycle management solution with proper status tracking, employer feedback on rejections, and real-time analytics integration.

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Job Seeker UI  │  Employer UI  │  Analytics Dashboard      │
└────────┬────────┴───────┬───────┴──────────┬────────────────┘
         │                │                   │
         ▼                ▼                   ▼
┌─────────────────────────────────────────────────────────────┐
│                    Controller Layer                          │
├─────────────────────────────────────────────────────────────┤
│  JobApplicationController  │  EmployerController             │
└────────┬───────────────────┴──────────┬─────────────────────┘
         │                              │
         ▼                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Service Layer                           │
├─────────────────────────────────────────────────────────────┤
│  ApplicationService  │  NotificationService  │  AnalyticsService │
└────────┬─────────────┴───────────┬───────────┴─────────────┘
         │                         │
         ▼                         ▼
┌─────────────────────────────────────────────────────────────┐
│                       Data Layer                             │
├─────────────────────────────────────────────────────────────┤
│  JobApplication  │  ApplicationStatusHistory  │  Job         │
└──────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Schema

#### job_applications Table (Existing - Verify)
```sql
- id (primary key)
- job_id (foreign key to jobs)
- user_id (foreign key to users)
- status (enum: 'pending', 'approved', 'rejected', 'screening', 'interview')
- cover_letter (text)
- resume_path (string)
- rejection_reason (text, nullable) -- NEW FIELD
- created_at (timestamp)
- updated_at (timestamp)
```

#### application_status_histories Table (Existing - Verify)
```sql
- id (primary key)
- job_application_id (foreign key)
- status (string)
- notes (text, nullable)
- changed_by (foreign key to users, nullable)
- created_at (timestamp)
```

### 2. Backend Controllers

#### EmployerController Methods

**updateApplicationStatus()**
```php
Purpose: Update application status with optional feedback
Input: 
  - application_id
  - status ('approved' or 'rejected')
  - notes (required for rejection)
Process:
  1. Validate employer owns the job
  2. Validate status is valid
  3. If rejecting, validate notes/feedback is provided
  4. Update application status
  5. Create status history record
  6. Send notification to job seeker
  7. Trigger analytics update
Output: JSON response with success/error
```

**showApplication()**
```php
Purpose: Display full application details
Input: application_id
Process:
  1. Verify employer owns the job
  2. Load application with user and job relationships
  3. Load status history
Output: Application view with all details
```

**jobApplications()**
```php
Purpose: List all applications for employer's jobs
Input: 
  - filters (status, job_id, search)
  - pagination params
Process:
  1. Query applications for employer's jobs
  2. Apply filters
  3. Load relationships (user, job)
  4. Paginate results
Output: Applications list view
```

### 3. Frontend Views

#### Employer Applications Page
**Location:** `resources/views/front/account/employer/applications/index.blade.php`

**Components:**
- Filter bar (status, job position, search)
- Applications table with columns:
  - Applicant name & email
  - Position applied
  - Application date
  - Current status badge
  - Actions (View, Approve, Reject)
- Pagination

#### Application Detail Modal/Page
**Location:** `resources/views/front/account/employer/applications/show.blade.php`

**Components:**
- Applicant information
- Resume viewer/download
- Cover letter display
- Status history timeline
- Action buttons:
  - Approve button (green)
  - Reject button (red) - Opens feedback modal
- Status change form

#### Rejection Feedback Modal
**Component:** Bootstrap modal

**Fields:**
- Rejection reason (required textarea)
- Predefined reasons (checkboxes):
  - Qualifications don't match
  - Position filled
  - Insufficient experience
  - Other (specify)
- Submit and Cancel buttons

#### Job Seeker Applications Page
**Location:** `resources/views/front/account/jobseeker/applications.blade.php`

**Components:**
- Applications list with cards showing:
  - Job title and company
  - Application date
  - Status badge (color-coded)
  - Rejection feedback (if rejected)
- Filter by status
- Empty state for no applications

### 4. Notification System

#### Email Templates

**Application Submitted**
```
Subject: Application Received - [Job Title]
Body:
  - Confirmation of submission
  - Job details
  - Next steps
```

**Application Approved**
```
Subject: Great News! Your Application for [Job Title] Has Been Approved
Body:
  - Congratulations message
  - Next steps in hiring process
  - Contact information
```

**Application Rejected**
```
Subject: Update on Your Application for [Job Title]
Body:
  - Thank you for applying
  - Status update (not selected)
  - Employer feedback/reason
  - Encouragement to apply for other positions
```

#### In-App Notifications
- Bell icon with notification count
- Notification list showing recent status changes
- Click to view application details

### 5. Analytics Integration

#### Real-Time Updates

**When Application Status Changes:**
```php
1. Update application record
2. Create status history entry
3. Trigger event: ApplicationStatusChanged
4. Event listener updates:
   - Total applications count (cached)
   - Status-specific counts (pending, approved, rejected)
   - Conversion rates
   - Hiring funnel metrics
5. Clear relevant cache keys
6. Broadcast to analytics dashboard (optional real-time)
```

#### Analytics Calculations

**Total Applications**
```sql
SELECT COUNT(*) FROM job_applications 
WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
```

**Approved Applications**
```sql
SELECT COUNT(*) FROM job_applications 
WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
AND status = 'approved'
```

**Rejection Rate**
```sql
SELECT 
  (COUNT(CASE WHEN status = 'rejected' THEN 1 END) * 100.0 / COUNT(*)) as rejection_rate
FROM job_applications 
WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
```

**Hiring Funnel**
```sql
SELECT 
  COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
  COUNT(CASE WHEN status = 'screening' THEN 1 END) as screening,
  COUNT(CASE WHEN status = 'interview' THEN 1 END) as interview,
  COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
  COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
FROM job_applications 
WHERE job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
```

## Data Models

### JobApplication Model

**Relationships:**
```php
- belongsTo(User, 'user_id') // Job seeker
- belongsTo(Job, 'job_id')
- hasMany(ApplicationStatusHistory, 'job_application_id')
```

**Scopes:**
```php
- scopePending($query)
- scopeApproved($query)
- scopeRejected($query)
- scopeForEmployer($query, $employerId)
```

**Methods:**
```php
- approve($notes = null) // Update status to approved
- reject($reason) // Update status to rejected with reason
- getStatusBadgeClass() // Return CSS class for status badge
- getStatusHistory() // Get all status changes
```

### ApplicationStatusHistory Model

**Relationships:**
```php
- belongsTo(JobApplication, 'job_application_id')
- belongsTo(User, 'changed_by') // Employer who made the change
```

## Error Handling

### Validation Errors

**Application Status Update:**
- Invalid status value → 422 error with message
- Missing rejection reason → 422 error "Rejection reason is required"
- Unauthorized access → 403 error "You don't own this application"

**Application Submission:**
- Missing required fields → 422 error with field-specific messages
- Invalid file format → 422 error "Resume must be PDF or DOC"
- Duplicate application → 422 error "You've already applied to this job"

### Database Errors

- Connection failure → 500 error with retry option
- Constraint violation → 422 error with user-friendly message
- Transaction rollback → Automatic retry with exponential backoff

### Notification Errors

- Email send failure → Log error, queue for retry
- SMS send failure → Log error, fallback to email
- In-app notification failure → Log error, don't block main flow

## Testing Strategy

### Unit Tests

**ApplicationService Tests:**
```php
- test_can_approve_application()
- test_can_reject_application_with_reason()
- test_cannot_reject_without_reason()
- test_updates_analytics_on_status_change()
- test_creates_status_history_record()
- test_sends_notification_on_status_change()
```

**EmployerController Tests:**
```php
- test_employer_can_view_applications()
- test_employer_can_filter_applications()
- test_employer_can_approve_application()
- test_employer_can_reject_with_feedback()
- test_employer_cannot_update_others_applications()
```

### Integration Tests

**Application Workflow:**
```php
1. Job seeker submits application
2. Verify status is 'pending'
3. Verify employer sees application
4. Employer approves application
5. Verify status changes to 'approved'
6. Verify job seeker receives notification
7. Verify analytics update
```

**Rejection Workflow:**
```php
1. Employer rejects application
2. Verify rejection reason is required
3. Submit with reason
4. Verify status changes to 'rejected'
5. Verify job seeker sees feedback
6. Verify notification includes feedback
```

### End-to-End Tests

**Complete Application Lifecycle:**
```
1. Job seeker browses jobs
2. Job seeker applies to job
3. Employer receives notification
4. Employer reviews application
5. Employer rejects with feedback
6. Job seeker sees rejection and feedback
7. Analytics dashboard updates
8. Verify all counts are accurate
```

## Security Considerations

### Authorization

- Employers can only view/update applications for their own jobs
- Job seekers can only view their own applications
- Admin users can view all applications

### Data Protection

- Resume files stored securely with access control
- Personal information encrypted at rest
- Rejection feedback visible only to applicant and employer

### Input Validation

- Sanitize all user inputs
- Validate file uploads (type, size, content)
- Prevent SQL injection with parameterized queries
- Prevent XSS with proper output escaping

## Performance Optimization

### Database Optimization

- Index on `job_applications.status`
- Index on `job_applications.job_id`
- Index on `job_applications.user_id`
- Composite index on `(job_id, status)`

### Caching Strategy

- Cache application counts per employer (TTL: 5 minutes)
- Cache analytics metrics (TTL: 10 minutes)
- Invalidate cache on status change
- Use Redis for distributed caching

### Query Optimization

- Eager load relationships to avoid N+1 queries
- Use pagination for large result sets
- Implement database query caching
- Use database views for complex analytics queries

## Deployment Considerations

### Database Migration

```php
// Add rejection_reason column if not exists
Schema::table('job_applications', function (Blueprint $table) {
    if (!Schema::hasColumn('job_applications', 'rejection_reason')) {
        $table->text('rejection_reason')->nullable()->after('status');
    }
});
```

### Rollback Plan

- Keep old status update logic as fallback
- Feature flag for new rejection feedback system
- Gradual rollout to employers
- Monitor error rates and performance

### Monitoring

- Track application status change rates
- Monitor notification delivery success
- Alert on analytics calculation errors
- Log all status changes for audit trail
