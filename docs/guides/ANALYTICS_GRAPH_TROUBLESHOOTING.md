# Jobseeker Analytics Graph - Troubleshooting Guide

## How to Make the Graph Work

### Step 1: Clear Cache
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### Step 2: Verify Route Access
Navigate to: `/account/analytics`

The route should be: `http://your-domain.com/account/analytics`

### Step 3: Check Browser Console
1. Open the analytics page
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Look for any errors

**Common errors and fixes:**

#### Error: "Chart is not defined"
**Fix:** Chart.js didn't load properly
- Hard refresh: Ctrl+Shift+R (or Cmd+Shift+R on Mac)
- Check internet connection (Chart.js loads from CDN)

#### Error: "Cannot read property 'getContext' of null"
**Fix:** Canvas element not found
- The page might not have loaded completely
- Check if `applicationTrendsChart` canvas exists

#### Error: Network error loading Chart.js
**Fix:** CDN blocked or slow
- Check if `https://cdn.jsdelivr.net/npm/chart.js` is accessible
- Try alternative CDN or download Chart.js locally

### Step 4: Verify Data
Check if data is being passed correctly:

1. View page source (Ctrl+U)
2. Search for "applicationTrendsChart"
3. You should see something like:
```javascript
labels: ["Jan 15","Jan 16","Jan 17"...]
data: [0,2,1,0,3...]
```

### Step 5: Test with Sample Data

If the graph still doesn't show, test with hardcoded data:

**Temporary test (in analytics.blade.php):**
```javascript
// Replace the data lines with:
labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
data: [2, 4, 1, 3, 5],
```

If this works, the issue is with the backend data.

## Common Issues & Solutions

### Issue 1: Graph Not Visible
**Symptoms:** Blank space where graph should be
**Solutions:**
- Check canvas height: Should be 300px
- Verify Chart.js loaded: Check Network tab in DevTools
- Check for JavaScript errors in Console

### Issue 2: Graph Shows But No Data
**Symptoms:** Empty graph with axes but no line
**Solutions:**
- Verify you have applications in database
- Check date range (last 30 days)
- Verify `user_id` matches logged-in user

### Issue 3: Graph Looks Broken
**Symptoms:** Weird layout, overlapping elements
**Solutions:**
- Clear browser cache
- Check responsive container
- Verify Bootstrap CSS loaded

### Issue 4: Dates Not Showing
**Symptoms:** X-axis labels missing or cut off
**Solutions:**
- Check label rotation (should be 45 degrees)
- Increase chart container height
- Reduce number of labels shown

## Verification Checklist

✅ **Backend:**
- [ ] Route `/account/analytics` exists and accessible
- [ ] Controller method `jobSeekerAnalytics()` returns data
- [ ] `$applicationTrends` variable has data
- [ ] User has at least one application in database

✅ **Frontend:**
- [ ] Chart.js CDN loads (check Network tab)
- [ ] Canvas element exists with id `applicationTrendsChart`
- [ ] JavaScript executes without errors
- [ ] Data is properly JSON encoded

✅ **Browser:**
- [ ] JavaScript enabled
- [ ] No ad blockers blocking CDN
- [ ] Console shows no errors
- [ ] Hard refresh performed (Ctrl+Shift+R)

## Quick Test Commands

### Test 1: Check if route works
```bash
php artisan route:list | grep analytics
```
Should show: `GET|HEAD  account/analytics`

### Test 2: Check if user has applications
```sql
SELECT COUNT(*) FROM job_applications WHERE user_id = YOUR_USER_ID;
```

### Test 3: Check if data is generated
Add this temporarily to controller:
```php
dd($applicationTrends); // Before return view
```

## Expected Behavior

When working correctly:
1. Page loads with 4 metric cards at top
2. Graph appears below showing line chart
3. Line shows application activity over 30 days
4. Hovering over points shows tooltips
5. Recent applications list shows on the right

## Still Not Working?

### Debug Mode
Add this to the analytics page (temporarily):

```html
<script>
console.log('Chart.js loaded:', typeof Chart !== 'undefined');
console.log('Canvas element:', document.getElementById('applicationTrendsChart'));
console.log('Application trends data:', {!! json_encode($applicationTrends) !!});
</script>
```

This will show:
- If Chart.js loaded
- If canvas element exists
- What data is being passed

### Contact Points
If still having issues, check:
1. Browser console errors (F12 → Console)
2. Network tab for failed requests (F12 → Network)
3. Laravel logs: `storage/logs/laravel.log`

## Alternative: Use Local Chart.js

If CDN is the issue, download Chart.js locally:

1. Download from: https://cdn.jsdelivr.net/npm/chart.js
2. Save to: `public/assets/js/chart.min.js`
3. Update analytics.blade.php:
```html
<script src="{{ asset('assets/js/chart.min.js') }}"></script>
```

## Success Indicators

✅ Graph displays with smooth line
✅ All 30 days shown on X-axis
✅ Y-axis shows integer values
✅ Tooltips work on hover
✅ No console errors
✅ Data matches application count
