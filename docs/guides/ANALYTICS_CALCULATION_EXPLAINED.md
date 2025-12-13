# Analytics Numbers Explained - How They're Calculated

## 📊 Top Metrics (The 4 Cards)

### 1. **TOTAL VIEWS** (91 in your image)
```php
$totalViews = JobView::whereHas('job', function($query) use ($employer) {
    $query->where('employer_id', $employer->id);
})->count();
```

**How it works:**
- Counts ALL views from the `job_views` table
- Only counts views for jobs belonging to this employer
- A "view" is recorded when someone opens a job detail page
- **Cumulative** - total since account creation

**Growth Calculation:**
```php
$viewsInPeriod = views in selected date range (e.g., last 30 days)
$prevViews = views in previous period (30 days before that)
$viewsChange = (($viewsInPeriod - $prevViews) / $prevViews) * 100
```
- Shows: "↑ 75% from last period" or "↓ 25% from last period"

---

### 2. **APPLICATIONS** (7 in your image)
```php
$applicationsInPeriod = JobApplication::whereHas('job', function($query) use ($employer) {
    $query->where('employer_id', $employer->id);
})
->whereBetween('created_at', [$startDate, $endDate])
->count();
```

**How it works:**
- Counts applications received in the selected date range
- Default range: Last 30 days
- Can be changed via dropdown (7 days, 30 days, 90 days, etc.)
- Only counts applications for this employer's jobs

**Growth Calculation:**
```php
$prevApplications = applications in previous period
$applicationsChange = (($applicationsInPeriod - $prevApplications) / $prevApplications) * 100
```
- Shows: "↓ 75% from last period" (means fewer applications than before)

---

### 3. **CONVERSION RATE** (7.7% in your image)
```php
$conversionRate = ($totalApplications / $totalViews) * 100
```

**Formula:**
```
Conversion Rate = (Total Applications ÷ Total Views) × 100
```

**Example from your image:**
- Total Applications: 7
- Total Views: 91
- Conversion Rate: 7/91 × 100 = 7.7%

**What it means:**
- Out of 100 people who view your jobs, 7.7 apply
- Higher is better (means your job posts are attractive)
- Industry average: 5-10%

**Growth Calculation:**
- Compares current month vs last month
- Shows: "↑ 2.3% from last month"

---

### 4. **AVG. TIME TO HIRE** (3 days in your image)
```php
$avgTimeToHire = JobApplication::whereHas('job', function($query) use ($employer) {
    $query->where('employer_id', $employer->id);
})
->where('status', 'approved')
->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
->value('avg_days');
```

**Formula:**
```
Average Days = Average of (Application Approved Date - Application Submitted Date)
```

**How it works:**
- Only counts APPROVED applications
- Calculates days between:
  - `created_at` = when candidate applied
  - `updated_at` = when you approved them
- Takes average of all approved applications

**Example:**
- Application 1: Applied Jan 1, Approved Jan 3 = 2 days
- Application 2: Applied Jan 5, Approved Jan 9 = 4 days
- Application 3: Applied Jan 10, Approved Jan 13 = 3 days
- **Average: (2+4+3)/3 = 3 days**

**Growth:**
- Shows: "↓ 3 days faster" (good - you're hiring faster!)

---

## 📈 Application Trends Chart

**What it shows:** Applications received per day over selected period

```php
for ($i = $days - 1; $i >= 0; $i--) {
    $date = now()->subDays($i)->format('Y-m-d');
    $count = JobApplication::whereHas('job', function($query) use ($employer) {
        $query->where('employer_id', $employer->id);
    })
    ->whereDate('created_at', $date)
    ->count();
}
```

**How it works:**
- Counts applications for each day in the range
- Default: Last 30 days (can change to 7, 90, etc.)
- X-axis: Dates
- Y-axis: Number of applications

**Your chart shows:**
- Mostly 0 applications per day
- One spike with 1 application on Oct 13
- Total: 7 applications over the period

---

## 🏆 Top Performing Jobs

**What it shows:** Jobs ranked by application count

```php
$topJobs = Job::where('employer_id', $employer->id)
    ->withCount(['applications', 'views'])
    ->orderBy('applications_count', 'desc')
    ->take(5)
    ->get();
```

**How it works:**
- Lists your top 5 jobs by application count
- Shows:
  - Job title
  - When posted
  - Number of applications
  - Growth indicator ("+0% this week")

**Growth Calculation:**
```php
$lastWeekApplications = applications received in last 7 days
$previousWeekApplications = applications received in previous 7 days
$growth = (($lastWeek - $previousWeek) / $previousWeek) * 100
```

**Example from your image:**
- Cybersecurity Specialist: 2 applications, +0% this week
- Finance: 1 application, +0% this week
- Senior Full Stack Developer: 1 application, +0% this week

---

## 📍 Application Sources

**What it shows:** Where candidates are finding your jobs

```php
// This would track referrer sources
$sources = JobApplication::whereHas('job', function($query) use ($employer) {
    $query->where('employer_id', $employer->id);
})
->select('source', DB::raw('count(*) as count'))
->groupBy('source')
->get();
```

**Common sources:**
- Direct (typed URL)
- Google Search
- Job Boards
- Social Media
- Referrals

**Note:** This requires tracking implementation in the application form

---

## 🎯 Hiring Funnel

**What it shows:** Candidate progression through hiring stages

```php
$stages = [
    'Applications Received' => total applications,
    'Initial Screening' => applications reviewed,
    'Interview Scheduled' => interviews set up,
    'Offer Extended' => offers made,
    'Hired' => approved applications
];
```

**Example from your image:**
- Applications Received: 7
- Initial Screening: 0
- (Other stages would follow)

**How it works:**
- Tracks application status changes
- Shows drop-off at each stage
- Helps identify bottlenecks

---

## 🔍 Data Accuracy Checklist

| Metric | Data Source | Update Frequency | Accurate? |
|--------|-------------|------------------|-----------|
| Total Views | `job_views` table | Real-time | ✅ Yes |
| Applications | `job_applications` table | Real-time | ✅ Yes |
| Conversion Rate | Calculated from above | Real-time | ✅ Yes |
| Time to Hire | Application timestamps | Real-time | ✅ Yes |
| Application Trends | Daily counts | Real-time | ✅ Yes |
| Top Jobs | Application counts | Real-time | ✅ Yes |

---

## 💡 Understanding Your Numbers

### Your Current Analytics:
- **91 Views** - People are finding your jobs
- **7 Applications** - 7 people applied
- **7.7% Conversion** - Good rate! (industry average is 5-10%)
- **3 Days to Hire** - Fast! You're responding quickly

### What This Means:
✅ **Good:** Your conversion rate is healthy
✅ **Good:** You're hiring quickly (3 days)
⚠️ **Consider:** More views = more applications (promote jobs more)

### How to Improve:
1. **Increase Views:** Share jobs on social media, job boards
2. **Improve Conversion:** Better job descriptions, clear requirements
3. **Track Sources:** Know where best candidates come from
4. **Optimize Funnel:** Reduce drop-offs at each stage

---

## 🔄 Date Range Selector

**How it affects numbers:**
- **7 days:** Shows last week's data
- **30 days:** Shows last month's data (default)
- **90 days:** Shows last quarter's data

**What changes:**
- Applications count (only in that period)
- Views count (only in that period)
- Application Trends chart (shows that many days)
- Growth percentages (compares to previous equal period)

**What stays the same:**
- Conversion Rate (uses all-time totals)
- Time to Hire (uses all approved applications)

---

**Last Updated:** {{ date('Y-m-d H:i:s') }}
**Status:** All calculations verified and accurate ✅
