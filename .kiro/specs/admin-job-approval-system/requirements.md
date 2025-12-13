# Admin Job Approval System with Rejection Feedback

## Introduction

This feature enhances the admin job management system to provide a complete approval workflow where admins can review all posted jobs, approve them for publication, or reject them with detailed feedback that helps employers understand what needs to be corrected.

## Requirements

### Requirement 1: Admin Job Visibility

**User Story:** As an admin, I want to see all jobs posted by employers regardless of their status, so that I can review and manage all job postings in the system.

#### Acceptance Criteria

1. WHEN an employer posts a new job THEN the admin SHALL see it immediately in the Jobs Management section
2. WHEN admin accesses Jobs Management THEN the system SHALL display all jobs with their current status (Pending, Approved, Rejected)
3. WHEN admin uses status filters THEN the system SHALL correctly filter jobs by status values (0=Pending, 1=Approved, 2=Rejected)
4. WHEN displaying job information THEN the system SHALL show job title, employer name, location, status, and posted date
5. IF a job is pending THEN the admin SHALL see "Approve" and "Reject" action buttons

### Requirement 2: Job Approval Process

**User Story:** As an admin, I want to approve pending jobs with a single click, so that I can quickly publish quality job postings for jobseekers to see.

#### Acceptance Criteria

1. WHEN admin clicks "Approve" on a pending job THEN the system SHALL change job status from 0 (Pending) to 1 (Approved)
2. WHEN a job is approved THEN the system SHALL set the approved_at timestamp to current date/time
3. WHEN a job is approved THEN the system SHALL make it visible to jobseekers immediately
4. WHEN approval is successful THEN the system SHALL show a success message "Job has been approved successfully"
5. WHEN approval fails THEN the system SHALL show an error message and maintain current status

### Requirement 3: Job Rejection with Feedback

**User Story:** As an admin, I want to reject jobs with detailed feedback explaining why they were rejected, so that employers can understand what needs to be corrected and resubmit improved job postings.

#### Acceptance Criteria

1. WHEN admin clicks "Reject" on a pending job THEN the system SHALL display a rejection feedback form
2. WHEN admin submits rejection THEN the system SHALL require a rejection reason with minimum 10 characters
3. WHEN admin submits rejection THEN the system SHALL change job status from 0 (Pending) to 2 (Rejected)
4. WHEN a job is rejected THEN the system SHALL set the rejected_at timestamp to current date/time
5. WHEN a job is rejected THEN the system SHALL store the rejection reason in the rejection_reason field
6. WHEN rejection is successful THEN the system SHALL show a success message "Job has been rejected successfully"
7. WHEN rejection form is incomplete THEN the system SHALL show validation errors

### Requirement 4: Employer Rejection Feedback Visibility

**User Story:** As an employer, I want to see detailed feedback when my job posting is rejected, so that I can understand what went wrong and make the necessary corrections before reposting.

#### Acceptance Criteria

1. WHEN employer views their jobs list THEN the system SHALL display rejection status clearly for rejected jobs
2. WHEN a job is rejected THEN the employer SHALL see a "Rejected" badge with red styling
3. WHEN employer clicks on a rejected job THEN the system SHALL display the full rejection reason provided by admin
4. WHEN displaying rejection feedback THEN the system SHALL show the rejection date and admin feedback
5. WHEN employer sees rejected job THEN the system SHALL provide option to "Edit and Resubmit" the job
6. IF job has rejection feedback THEN the system SHALL display it prominently with clear formatting

### Requirement 5: Job Status Management

**User Story:** As a system administrator, I want the job status workflow to be consistent and reliable, so that all stakeholders understand the current state of each job posting.

#### Acceptance Criteria

1. WHEN a verified employer posts a job THEN the system SHALL set status to 1 (Approved) automatically
2. WHEN an unverified employer posts a job THEN the system SHALL set status to 0 (Pending) for admin review
3. WHEN job status changes THEN the system SHALL update appropriate timestamp fields (approved_at, rejected_at)
4. WHEN displaying job status THEN the system SHALL use consistent status names and badge colors
5. WHEN jobseekers browse jobs THEN the system SHALL only show jobs with status 1 (Approved)

### Requirement 6: Admin Dashboard Integration

**User Story:** As an admin, I want to see pending jobs count and quick access to job management, so that I can efficiently manage the approval workflow.

#### Acceptance Criteria

1. WHEN admin accesses dashboard THEN the system SHALL display count of pending jobs requiring review
2. WHEN there are pending jobs THEN the system SHALL show a notification or highlight in the navigation
3. WHEN admin clicks on pending jobs notification THEN the system SHALL navigate to Jobs Management filtered by pending status
4. WHEN displaying job statistics THEN the system SHALL show breakdown by status (Pending, Approved, Rejected)
5. IF there are no pending jobs THEN the system SHALL display "No jobs pending review" message

### Requirement 7: Notification System (Future Enhancement)

**User Story:** As an employer, I want to be notified when my job posting status changes, so that I can take appropriate action quickly.

#### Acceptance Criteria

1. WHEN admin approves a job THEN the system SHOULD send notification to employer (email/in-app)
2. WHEN admin rejects a job THEN the system SHOULD send notification with rejection reason to employer
3. WHEN employer receives rejection notification THEN it SHALL include direct link to edit the job
4. WHEN sending notifications THEN the system SHALL use professional and helpful language
5. IF notification delivery fails THEN the system SHALL log the error but not block the approval/rejection process

## Success Criteria

- Admin can see all posted jobs immediately after employer submission
- Admin can approve jobs with single click
- Admin can reject jobs with mandatory feedback (minimum 10 characters)
- Employers can see rejection reasons and understand what to fix
- Job status workflow is consistent and reliable
- System maintains proper audit trail with timestamps
- User interface is intuitive and efficient for both admins and employers

## Technical Notes

- Use existing job status integer values: 0=Pending, 1=Approved, 2=Rejected
- Leverage existing rejection_reason, approved_at, and rejected_at database fields
- Ensure proper validation and error handling
- Maintain backward compatibility with existing job posting workflow
- Consider performance impact of real-time job visibility for admins