# How K-means Clustering Works in Your Job Portal 🤖

## Overview
The K-means clustering system provides **personalized job recommendations** and **labor market insights** by analyzing patterns in job data and user preferences.

## 🔄 Main Flow - How It All Works Together

```
1. Data Collection → 2. Feature Extraction → 3. Clustering → 4. Recommendations
       ↓                       ↓                    ↓              ↓
   Jobs & Users          Numeric Features        Clusters      Personalized
   from Database         for ML Algorithm        of Jobs       Job Matches
```

---

## 📊 1. Data Collection

### Jobs Data
```php
// Active jobs from database
Jobs: [
  {title: "Software Developer", category: "IT", location: "Manila", salary: "50000-70000"},
  {title: "Marketing Manager", category: "Marketing", location: "Cebu", salary: "40000-60000"},
  {title: "Data Analyst", category: "IT", location: "Manila", salary: "45000-65000"}
]
```

### User Preferences
```php
// Jobseeker preferences
User: {
  preferred_categories: [1, 3, 5],  // IT, Marketing, Finance
  preferred_job_types: [2, 4],     // Full-time, Remote
  experience_years: 3,
  location: "Manila"
}
```

---

## ⚙️ 2. Feature Extraction

The system converts text/categorical data into **numeric features** that the ML algorithm can understand:

### Job Features
```php
Job "Software Developer" becomes:
[
  'job_type_id' => 2,                    // Full-time = 2
  'location_hash' => 1234567890,         // Manila hashed to number
  'salary_range_normalized' => 0.6,      // 50k-70k normalized to 0-1 scale
  'experience_level' => 3,               // Extracted from job requirements
  'skills_hash' => 987654321             // Skills text hashed to number
]
```

### User Features
```php
User Profile becomes:
[
  'category_id' => 1,           // First preferred category (IT)
  'job_type_id' => 2,          // First preferred job type (Full-time)
  'location' => 1234567890,    // Manila hashed
  'experience' => 3,           // Years of experience
  'salary' => 0.5              // Salary expectation normalized
]
```

---

## 🎯 3. K-means Clustering Algorithm

### Step 1: Initialize Clusters
```
K = 3 clusters (configurable)
Random centroids placed in feature space:

Cluster 1: [2, 1234567890, 0.4, 2, 555555555]  // IT Jobs, Manila, Mid-salary
Cluster 2: [1, 9876543210, 0.8, 5, 111111111]  // Marketing, Cebu, High-salary  
Cluster 3: [3, 1111111111, 0.2, 1, 999999999]  // Finance, Davao, Low-salary
```

### Step 2: Assign Jobs to Clusters
```php
foreach ($jobs as $job) {
    // Calculate distance to each cluster centroid
    $distances = [
        cluster1: euclideanDistance($job_features, $centroid1),
        cluster2: euclideanDistance($job_features, $centroid2), 
        cluster3: euclideanDistance($job_features, $centroid3)
    ];
    
    // Assign to closest cluster
    $job->cluster = min($distances);
}
```

### Step 3: Recalculate Centroids
```php
// For each cluster, find the average (centroid) of all assigned jobs
foreach ($clusters as $cluster) {
    $centroid = calculateMean($cluster->jobs);
}
```

### Step 4: Repeat Until Convergence
```
Iteration 1: Jobs move between clusters
Iteration 2: Centroids adjust
Iteration 3: Fewer jobs move
...
Iteration N: Centroids stop moving (converged!)
```

---

## 🎯 4. Job Recommendations System

### How Recommendations Work
```php
function getJobRecommendations($userId) {
    // 1. Get user's profile/preferences
    $userProfile = getUserFeatures($user);
    
    // 2. Get all jobs and their features  
    $allJobs = getJobFeatures($jobs);
    
    // 3. Calculate similarity between user and each job
    foreach ($jobs as $job) {
        $similarity = calculateSimilarityScore($userProfile, $jobFeatures);
        $scores[$job->id] = $similarity;
    }
    
    // 4. Sort by highest similarity and return top N
    return topJobs($scores, $limit);
}
```

### Similarity Calculation
```php
// Example calculation for user vs job match
User Profile:    [IT=1, Full-time=2, Manila=123, Experience=3, Salary=0.5]
Job Profile:     [IT=1, Full-time=2, Manila=123, Experience=2, Salary=0.6]

Distance = sqrt((1-1)² + (2-2)² + (123-123)² + (3-2)² + (0.5-0.6)²)
Distance = sqrt(0 + 0 + 0 + 1 + 0.01) = sqrt(1.01) = 1.005

Similarity = 1 / (1 + distance) = 1 / (1 + 1.005) = 0.498

+ Bonus: Job type match = +30% = 0.498 * 1.3 = 0.647 final score
```

---

## 🚀 5. How It Works in Practice

### For Jobseekers:

#### 1. **First Visit (No Preferences)**
```
/jobs → Shows ALL jobs + prompt: "Set preferences for personalized recommendations"
```

#### 2. **After Setting Preferences**
```php
User selects: [IT, Marketing] categories + [Full-time, Remote] job types

/jobs → 
- Filters jobs to IT & Marketing categories only
- Runs K-means recommendations in background
- Shows filtered jobs + "Recommended for you" section at top
```

#### 3. **Job Detail Page**
```php
/jobs/123 →
- Shows job details
- "Related Jobs" section uses K-means clustering
- Finds jobs in same cluster or similar features
```

### For Employers:
```php
/employer/jobs/123/applicants →
- Shows all applicants
- "Recommended Candidates" uses reverse matching
- Finds users whose profiles match job requirements
```

---

## 📈 6. Labor Market Insights

The system provides analytics by clustering data:

```php
$insights = [
    'job_categories' => ['IT: 45%', 'Marketing: 25%', 'Finance: 30%'],
    'job_types' => ['Full-time: 70%', 'Part-time: 20%', 'Remote: 10%'],
    'locations' => ['Manila: 50%', 'Cebu: 30%', 'Davao: 20%'],
    'user_clusters' => [
        'Tech Professionals' => 120,
        'Marketing Specialists' => 80, 
        'Finance Experts' => 60
    ],
    'application_trends' => 'IT jobs get 3x more applications'
];
```

---

## 🔧 7. Current System Configuration

```php
// In KMeansClusteringService.php
$k = 3;                    // Creates 3 job clusters
$maxIterations = 20;       // Max 20 attempts to find optimal clusters
$defaultRecommendations = 5; // Return top 5 recommended jobs
```

### Feature Weights:
- **Job Type Match**: +30% similarity boost
- **Location Hash**: Exact location matching
- **Salary Range**: Normalized to 0-1 scale
- **Experience**: Direct numeric comparison
- **Skills**: Text hashed to numeric value

---

## 🎯 8. User Experience Flow

```
Jobseeker Journey:
1. Register/Login → Default: See all jobs
2. Set Preferences → System learns user profile  
3. Browse Jobs → Filtered + personalized recommendations
4. Apply to Jobs → System improves recommendations based on behavior
5. Get Better Matches → ML learns from applications and saves

Employer Journey:
1. Post Job → Job gets clustered with similar positions
2. View Applicants → System recommends best-fit candidates
3. Hire → System learns successful matching patterns
```

---

## 💡 9. Key Benefits

### For Jobseekers:
- ✅ **Personalized job feed** based on preferences
- ✅ **Smart recommendations** using ML clustering
- ✅ **Related jobs** on job detail pages
- ✅ **No forced category selection** - graceful fallbacks

### For Employers:
- ✅ **Qualified candidate recommendations**
- ✅ **Better job visibility** through clustering
- ✅ **Market insights** about job categories and trends

### For Platform:
- ✅ **Improved matching accuracy**
- ✅ **Higher user engagement**
- ✅ **Data-driven insights**
- ✅ **Scalable recommendation system**

---

## ⚠️ 10. Current Status & Known Issues

### ✅ Working:
- Job filtering by user preferences
- Basic recommendation algorithm
- Graceful fallbacks for users without preferences
- Labor market insights

### 🔧 Needs Fixing:
- **Distance calculation error**: String values in arithmetic operations
- **Feature extraction**: `extractExperienceLevel()` and `calculateSkillsHash()` return placeholder values
- **Skills matching**: Better NLP for skills extraction

### 🚀 Future Enhancements:
- Machine learning model training on historical data
- Advanced NLP for skills matching
- Real-time recommendation updates
- A/B testing for recommendation algorithms

---

The K-means system is **functional but needs the clustering math fixes** we discussed earlier. The main user experience works - users can browse jobs with or without preferences, and the recommendation system provides relevant job suggestions when working properly! 🎉
