# Implementation Plan

- [ ] 1. Database Schema Updates
  - Add `rejection_reason` column to `job_applications` table
  - Verify `application_status_histories` table exists with correct schema
  - Create database migration file
  - _Requirements: 4.4, 5.4_

- [ ] 2. Update JobApplication Model
  - [ ] 2.1 Add rejection_reason to fillable fields
    - Update the `$fillable` array in JobApplication model
    - _Requirements: 4.4_
  
  - [ ] 2.2 Add helper methods for status management
    - Create `approve($notes = null)` method
    - Create `reject($reason)` method
    - Create `getStatusBadgeClass()` method for UI styling
    - _Requirements: 3.1, 4.3, 7.3, 7.4, 7.5_
  
  - [ ] 2.3 Add query scopes for filtering
    - Create `scopePending()` scope
    - Create `scopeApproved()` scope
    - Create `scopeRejected()` scope
    - Create `scopeForEmployer($employerId)` scope
    - _Requirements: 2.4, 7.7_

- [ ] 3. Enhance EmployerController
  - [ ] 3.1 Update updateApplicationStatus method
    - Add validation for rejection reason (required when status is 'rejected')
    - Update application status
    - Create status history record
    - Send notification to job seeker
    - Return JSON response
    - _Requirements: 3.1-3.4, 4.1-4.6_
  
  - [ ] 3.2 Verify jobApplications method
    - Ensure it loads applications with user and job relationships
    - Verify filtering by status works
    - Verify filtering by job_id works
    - Verify search functionality works
    - _Requirements: 2.1-2.5_
  
  - [ ] 3.3 Verify showApplication method
    - Ensure it loads application with status history
    - Verify authorization check (employer owns job)
    - _Requirements: 2.6, 5.2-5.5_

- [ ] 4. Create Rejection Feedback Modal
  - [ ] 4.1 Create modal component in applications view
    - Add Bootstrap modal HTML structure
    - Add rejection reason textarea (required)
    - Add predefined reason checkboxes
    - Add submit and cancel buttons
    - _Requirements: 4.1, 4.2_
  
  - [ ] 4.2 Add JavaScript for modal functionality
    - Handle modal open/close
    - Validate rejection reason is provided
    - Submit rejection via AJAX
    - Show success/error messages
    - Refresh page on success
    - _Requirements: 4.1-4.3_

- [ ] 5. Update Employer Applications View
  - [ ] 5.1 Enhance applications table
    - Add status badge with color coding
    - Add Approve button (green)
    - Add Reject button (red) that opens modal
    - Add View Details link
    - _Requirements: 2.2, 3.7, 4.7_
  
  - [ ] 5.2 Add filter and search functionality
    - Add status filter dropdown
    - Add job position filter dropdown
    - Add search input for name/email
    - Implement filter submission
    - _Requirements: 2.3, 2.4, 2.5_

- [ ] 6. Create/Update Job Seeker Applications View
  - [ ] 6.1 Create applications list page
    - Display all user's applications
    - Show job title, company, application date
    - Show status badge (color-coded)
    - Show rejection feedback if rejected
    - _Requirements: 7.1-7.6_
  
  - [ ] 6.2 Add status filter
    - Add filter dropdown for status
    - Implement filter functionality
    - _Requirements: 7.7_
  
  - [ ] 6.3 Add empty state
    - Show message when no applications exist
    - Add link to browse jobs
    - _Requirements: 7.1_

- [ ] 7. Implement Notification System
  - [ ] 7.1 Create notification classes
    - Create `ApplicationApprovedNotification` class
    - Create `ApplicationRejectedNotification` class with feedback
    - Create `ApplicationSubmittedNotification` class
    - _Requirements: 8.1-8.6_
  
  - [ ] 7.2 Update controller to send notifications
    - Send notification on application submission
    - Send notification on approval
    - Send notification on rejection (include feedback)
    - _Requirements: 1.5, 3.3, 4.5_
  
  - [ ] 7.3 Create email templates
    - Create application_submitted.blade.php
    - Create application_approved.blade.php
    - Create application_rejected.blade.php (include feedback)
    - _Requirements: 8.1-8.6_

- [ ] 8. Analytics Integration
  - [ ] 8.1 Update analytics calculations
    - Verify total applications count is accurate
    - Add approved applications count
    - Add rejected applications count
    - Calculate rejection rate
    - Update hiring funnel metrics
    - _Requirements: 6.1-6.8, 9.3, 9.4_
  
  - [ ] 8.2 Add real-time updates
    - Clear analytics cache on status change
    - Trigger analytics recalculation
    - Update dashboard metrics
    - _Requirements: 1.6, 3.4, 4.8, 6.1_
  
  - [ ] 8.3 Verify per-job breakdown accuracy
    - Ensure job performance breakdown shows correct application counts
    - Verify conversion rates update correctly
    - _Requirements: 6.8_

- [ ] 9. Add Status History Display
  - [ ] 9.1 Create status history component
    - Display timeline of status changes
    - Show timestamp for each change
    - Show who made the change
    - Show notes/feedback for each change
    - _Requirements: 5.1-5.5_
  
  - [ ] 9.2 Add to application detail view
    - Include status history in employer application view
    - Style as timeline or list
    - _Requirements: 5.2-5.5_

- [ ] 10. Testing and Validation
  - [ ] 10.1 Test application submission flow
    - Submit application as job seeker
    - Verify status is 'pending'
    - Verify employer sees application
    - Verify notification sent
    - _Requirements: 1.1-1.6_
  
  - [ ] 10.2 Test approval flow
    - Approve application as employer
    - Verify status changes to 'approved'
    - Verify job seeker receives notification
    - Verify analytics update
    - _Requirements: 3.1-3.7_
  
  - [ ] 10.3 Test rejection flow
    - Attempt to reject without reason (should fail)
    - Reject with reason
    - Verify status changes to 'rejected'
    - Verify job seeker sees feedback
    - Verify notification includes feedback
    - Verify analytics update
    - _Requirements: 4.1-4.8_
  
  - [ ] 10.4 Test analytics accuracy
    - Submit multiple applications
    - Approve some, reject some
    - Verify all counts are accurate
    - Verify conversion rates are correct
    - Verify hiring funnel is accurate
    - _Requirements: 6.1-6.8, 9.3, 9.4_
  
  - [ ] 10.5 Test job seeker dashboard
    - View applications as job seeker
    - Verify all applications shown
    - Verify status badges correct
    - Verify rejection feedback visible
    - Test filtering by status
    - _Requirements: 7.1-7.7_

- [ ] 11. UI/UX Enhancements
  - [ ] 11.1 Add loading states
    - Show spinner when submitting status change
    - Show loading indicator when filtering
    - Disable buttons during processing
    - _Requirements: 10.2, 10.3_
  
  - [ ] 11.2 Add success/error messages
    - Show toast notification on successful approval
    - Show toast notification on successful rejection
    - Show error message if operation fails
    - _Requirements: 3.3, 4.5_
  
  - [ ] 11.3 Improve mobile responsiveness
    - Ensure tables are responsive
    - Ensure modals work on mobile
    - Test on various screen sizes
    - _Requirements: 10.1-10.6_

- [ ] 12. Documentation and Deployment
  - [ ] 12.1 Update API documentation
    - Document updateApplicationStatus endpoint
    - Document request/response formats
    - Document error codes
    - _Requirements: All_
  
  - [ ] 12.2 Create user guide
    - Document how employers approve/reject applications
    - Document how job seekers view application status
    - Include screenshots
    - _Requirements: All_
  
  - [ ] 12.3 Run database migration
    - Execute migration on staging
    - Verify no data loss
    - Execute migration on production
    - _Requirements: 1.1-1.3_
