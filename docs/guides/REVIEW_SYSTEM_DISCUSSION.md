# Job & Company Review System - Discussion Summary

## Feature Overview
A review system for jobseekers to rate and review jobs and companies, helping them make informed decisions before applying.

## Key Decisions Made

### Review Types
1. **Job Reviews** - Specific to individual job postings
2. **Company Reviews** - General reviews about the employer/company

### Access & Eligibility ✅ DECIDED

**Review Requirements:**
- ✅ Must be logged in as jobseeker
- ✅ Must have applied to the job through the platform
- ❌ KYC verification NOT required (but adds credibility badge)

**Why this approach:**
- Ensures reviews are from actual applicants
- Prevents spam and fake reviews
- No barrier to entry (no KYC needed)
- Easy to verify through application records
- Maintains review quality

### Badge System
Reviews will display credibility badges:
- **"Verified Applicant"** - Applied through platform (required)
- **"Verified Hire"** - Got the job (if employer confirms)
- **"KYC Verified"** - Completed identity verification (optional bonus)

## Role-Based Capabilities

### Jobseekers 👤
**Can:**
- View all approved reviews
- Write reviews for jobs they've applied to
- Edit own reviews (30-day window)
- Delete own reviews
- Report inappropriate reviews
- Post anonymously or with name

**Cannot:**
- Review without applying first
- Post multiple reviews for same job/company

### Employers 🏢
**Can:**
- View all reviews about their jobs/company
- See review statistics and trends
- Respond to reviews publicly
- Flag reviews for admin review
- Get notifications for new reviews
- View reviewer name (even if anonymous to public)

**Cannot:**
- Delete or edit reviews
- Contact reviewers directly

### Admin 👨‍💼
**Full Control:**
- Approve/reject pending reviews
- Edit reviews (fix typos, remove profanity)
- Delete reviews (spam, fake, inappropriate)
- Ban users from reviewing
- View all reviewer information
- Mediate disputes
- Set review guidelines
- View platform-wide analytics

## Pros & Cons

### Pros ✅
- Informed decision-making for jobseekers
- Transparency about companies
- Builds platform trust
- Competitive advantage
- Community engagement
- Free marketing for good employers

### Cons ⚠️
- Risk of fake/spam reviews
- Potential retaliation against reviewers
- Legal issues (defamation)
- Requires moderation resources
- Bias (only extremes review)
- Employer pushback

## Safeguards Planned
1. Verification through application records
2. Admin moderation (all reviews pending approval)
3. Time limit (6 months after application)
4. One review per user per job/company
5. Anonymous option for protection
6. Employer response capability
7. Report system for inappropriate content
8. Clear guidelines
9. 30-day edit window
10. Rating distribution display

## Implementation Approach
**Start with:** Company Reviews only (simpler, less controversial)
**Then add:** Job-specific reviews
**Moderation:** Admin approval required initially
**Privacy:** Semi-anonymous (employers see name, public sees "Verified Applicant")

## Next Steps (Tomorrow)
1. Create database migration for reviews table
2. Build Review model with relationships
3. Create ReviewController with CRUD operations
4. Design UI components (star rating, review cards, tabs)
5. Add review section to job detail page
6. Create admin moderation panel
7. Implement notification system
8. Add employer response feature

## Database Structure (Planned)
```
reviews table:
- id
- user_id (jobseeker)
- job_id
- company_id (employer_id)
- review_type (job/company)
- rating (1-5)
- title
- comment
- is_verified (worked there)
- helpful_count
- status (pending/approved/rejected)
- admin_notes
- timestamps
```

## UI Components Needed
- Star rating widget
- Review submission form
- Review listing with pagination
- Tabbed interface (Job Reviews / Company Reviews)
- Review statistics dashboard
- Admin moderation interface
- Employer response section

---

**Status:** Planning phase complete, ready for implementation
**Date:** October 30, 2025
**Next Session:** Continue with implementation
