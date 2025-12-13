# 🚀 Review System - Quick Start Guide

## For Testing the Review System

### 1. As a Jobseeker (Writing Reviews)

**Step 1:** Login as a jobseeker
```
URL: /login
```

**Step 2:** Apply to a job
```
URL: /jobs/{id}
Click: "Apply Now" button
```

**Step 3:** Go to My Applications
```
URL: /account/my-job-applications
```

**Step 4:** Write a review
```
Click: "Write Review" button on any application
Choose: Job Review or Company Review
Fill: Rating (1-5 stars), Title, Comment
Optional: Check "Post Anonymously"
Submit: Click "Submit Review"
```

**Step 5:** Manage your reviews
```
View: Your reviews appear in the applications list
Edit: Click "Edit" on your review
Delete: Click "Delete" on your review
```

---

### 2. As an Employer (Managing Reviews)

**Step 1:** Login as an employer
```
URL: /employer/login
```

**Step 2:** Navigate to Reviews
```
Sidebar: Click "Reviews" (shows rating badge if you have reviews)
URL: /employer/reviews
```

**Step 3:** View your statistics
```
See: Total Reviews, Average Rating, Job Reviews, Company Reviews
```

**Step 4:** Filter reviews
```
Click: "All Reviews" | "Job Reviews" | "Company Reviews"
```

**Step 5:** Respond to a review
```
Find: Review without a response
Type: Your professional response (10-1000 characters)
Click: "Post Response"
```

**Step 6:** Manage responses
```
Edit: Click "Edit" button on your response
Delete: Click "Delete" button on your response
```

---

### 3. As a Public Visitor (Viewing Reviews)

**Step 1:** Browse companies
```
URL: /companies
```

**Step 2:** View company profile
```
Click: Any company card
URL: /companies/{id}
```

**Step 3:** View reviews
```
Click: "View Reviews" button
Scroll: To reviews section
Filter: By "All" | "Job Reviews" | "Company Reviews"
```

**Step 4:** Read reviews
```
See: Star ratings, review titles, comments
See: Employer responses (if any)
See: Verified badges
```

---

## Quick URLs

### Jobseeker
- My Applications: `/account/my-job-applications`
- Write Review: Click button in applications list

### Employer
- Reviews Dashboard: `/employer/reviews`
- Filter by Job: `/employer/reviews?type=job`
- Filter by Company: `/employer/reviews?type=company`

### Public
- Companies List: `/companies`
- Company Profile: `/companies/{id}`
- Reviews Section: `/companies/{id}#reviews`

---

## API Endpoints (AJAX)

### Jobseeker
```javascript
// Check eligibility
GET /account/reviews/check-eligibility/{jobId}/{reviewType}

// Store review
POST /account/reviews/store
{
    job_id: 123,
    review_type: 'job',
    rating: 5,
    title: 'Great experience',
    comment: 'Really enjoyed...',
    is_anonymous: false
}

// Update review
PUT /account/reviews/{id}
{
    rating: 4,
    title: 'Updated title',
    comment: 'Updated comment'
}

// Delete review
DELETE /account/reviews/{id}
```

### Employer
```javascript
// Respond to review
POST /employer/reviews/{id}/respond
{
    response: 'Thank you for your feedback...'
}

// Update response
PUT /employer/reviews/{id}/response
{
    response: 'Updated response...'
}

// Delete response
DELETE /employer/reviews/{id}/response
```

---

## Common Issues & Solutions

### Issue: "You must apply to this job first"
**Solution:** Apply to the job before writing a review

### Issue: "You have already reviewed this"
**Solution:** Edit your existing review instead of creating a new one

### Issue: "Review not found"
**Solution:** Make sure you're logged in and the review belongs to you

### Issue: Rating badge not showing
**Solution:** Clear cache with `php artisan view:clear`

### Issue: AJAX not working
**Solution:** Check browser console for errors, ensure jQuery is loaded

---

## Database Queries (For Debugging)

### Check reviews for a user
```sql
SELECT * FROM reviews WHERE user_id = 1;
```

### Check reviews for an employer
```sql
SELECT * FROM reviews WHERE employer_id = 2;
```

### Check reviews for a job
```sql
SELECT * FROM reviews WHERE job_id = 3;
```

### Get average rating for employer
```sql
SELECT AVG(rating) FROM reviews WHERE employer_id = 2;
```

### Count reviews by type
```sql
SELECT review_type, COUNT(*) 
FROM reviews 
WHERE employer_id = 2 
GROUP BY review_type;
```

---

## Testing Scenarios

### Scenario 1: Happy Path
1. Jobseeker applies to job ✅
2. Jobseeker writes positive review ✅
3. Employer sees review ✅
4. Employer responds professionally ✅
5. Public sees review and response ✅

### Scenario 2: Anonymous Review
1. Jobseeker applies to job ✅
2. Jobseeker writes anonymous review ✅
3. Employer sees "Anonymous User" ✅
4. Public sees "Anonymous User" ✅

### Scenario 3: Review Management
1. Jobseeker writes review ✅
2. Jobseeker edits review ✅
3. Employer responds ✅
4. Employer edits response ✅
5. Employer deletes response ✅

### Scenario 4: Validation
1. Try to review without applying ❌ (blocked)
2. Try to submit empty review ❌ (validation)
3. Try to submit duplicate review ❌ (blocked)
4. Try to edit someone else's review ❌ (blocked)

---

## Performance Tips

1. **Use pagination** - Reviews are paginated (15 per page)
2. **Cache statistics** - Consider caching employer stats
3. **Eager load relationships** - Already implemented
4. **Index database** - Already indexed on key columns

---

## Security Checklist

- ✅ Authentication required for writing reviews
- ✅ Authorization checks (can only edit own reviews)
- ✅ Validation on all inputs
- ✅ CSRF protection on forms
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS prevention (Blade escaping)

---

## Support

If you encounter any issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Clear cache: `php artisan cache:clear`
3. Clear views: `php artisan view:clear`
4. Clear routes: `php artisan route:clear`
5. Check browser console for JavaScript errors

---

**Last Updated:** November 3, 2025  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
