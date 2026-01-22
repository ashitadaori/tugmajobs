# Azure ML K-Means Clustering Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      TugmaJobs Application                      │
│                         (Laravel/PHP)                           │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP Request
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              AzureMLClusteringService.php                       │
│  ┌───────────────────────────────────────────────────────┐     │
│  │ • getJobRecommendations()                             │     │
│  │ • getUserRecommendations()                            │     │
│  │ • runJobClustering()                                  │     │
│  │ • getLaborMarketInsights()                            │     │
│  │ • findOptimalK()                                      │     │
│  └───────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
              ✅ Azure ML          ❌ Azure ML
              Available            Unavailable
                    │                   │
                    ▼                   ▼
    ┌───────────────────────┐  ┌───────────────────────┐
    │   Azure ML Endpoint   │  │ Local PHP Clustering  │
    │   (Cloud-based)       │  │   (Fallback)          │
    └───────────────────────┘  └───────────────────────┘
                │
                ▼
    ┌───────────────────────┐
    │   score.py            │
    │   (K-means Logic)     │
    │   • scikit-learn      │
    │   • numpy             │
    │   • pandas            │
    └───────────────────────┘
                │
                ▼
    ┌───────────────────────┐
    │   Clustering Results  │
    │   • Labels            │
    │   • Centroids         │
    │   • Metrics           │
    └───────────────────────┘
```

---

## Data Flow

### 1. Job Recommendations Flow

```
User Login (Job Seeker)
    │
    ▼
Controller calls getJobRecommendations(userId, limit)
    │
    ▼
Extract User Features
    ├─ Category preferences
    ├─ Job type preferences
    ├─ Location preferences
    ├─ Expected salary
    ├─ Experience years
    └─ Remote preference
    │
    ▼
Fetch All Active Jobs from Database
    │
    ▼
Extract Job Features for Each Job
    ├─ Category ID
    ├─ Job type ID
    ├─ Location hash
    ├─ Salary normalized
    ├─ Experience level
    ├─ Remote flag
    ├─ Days since posted
    └─ Skills score
    │
    ▼
Send to Azure ML for Clustering
    │
    ├─ POST Request
    ├─ JSON Payload: {data, k, algorithm, scaling}
    ├─ Authorization: Bearer token
    │
    ▼
Azure ML Processes Request
    │
    ├─ Feature scaling (StandardScaler)
    ├─ K-means clustering
    ├─ Calculate centroids
    ├─ Assign cluster labels
    └─ Calculate metrics (silhouette score, inertia)
    │
    ▼
Return Clustering Results
    │
    └─ {labels: [0,1,2,...], centroids: [[...]], clusters: {...}}
    │
    ▼
Find User's Cluster
    │
    └─ Calculate distance to each centroid
    └─ Assign user to nearest cluster
    │
    ▼
Filter Jobs in Same Cluster
    │
    └─ Match cluster labels
    └─ Filter by user preferences (categories)
    └─ Calculate cluster score
    │
    ▼
Expand to Nearby Clusters (if needed)
    │
    └─ Calculate cluster distances
    └─ Get jobs from closest clusters
    │
    ▼
Sort by Score & Return Top N Recommendations
    │
    ▼
Display to User
```

---

## Component Details

### 1. Laravel Service Layer

**File**: `app/Services/AzureMLClusteringService.php`

**Key Methods**:
```php
runJobClustering($k)
  └─ Clusters all active jobs into K groups

runUserClustering($k)
  └─ Clusters all job seekers into K groups

getJobRecommendations($userId, $limit)
  └─ Returns personalized job recommendations

getUserRecommendations($jobId, $limit)
  └─ Returns matching candidates for a job

getLaborMarketInsights()
  └─ Returns market analysis and trends

findOptimalK($type, $maxK)
  └─ Finds best number of clusters using elbow method
```

**Features**:
- ✅ Automatic caching (configurable TTL)
- ✅ Fallback to local clustering
- ✅ Health check endpoint
- ✅ Error logging
- ✅ Feature extraction and normalization

---

### 2. Azure ML Endpoint

**Components**:
```
Managed Online Endpoint
    │
    └─ kmeans-clustering-endpoint
        │
        ├─ Deployment: kmeans-clustering-deployment
        │   ├─ Compute: Standard_DS2_v2
        │   ├─ Instances: 1
        │   └─ Scaling: Manual
        │
        ├─ Scoring Script: score.py
        │   ├─ init() - Initialize scalers
        │   └─ run(raw_data) - Process clustering request
        │
        └─ Environment
            ├─ Python 3.8+
            ├─ numpy
            ├─ scikit-learn
            └─ pandas
```

**Request Format**:
```json
{
  "data": [
    {"category_id": 1, "job_type_id": 2, "salary": 50000, ...},
    {"category_id": 2, "job_type_id": 1, "salary": 40000, ...}
  ],
  "k": 5,
  "max_iterations": 100,
  "tolerance": 0.0001,
  "algorithm": "lloyd",
  "init_method": "k-means++",
  "scaling": {
    "enabled": true,
    "method": "standard"
  },
  "include_metrics": true
}
```

**Response Format**:
```json
{
  "labels": [0, 1, 2, 0, 1],
  "centroids": [
    [1.2, 2.3, 50000, ...],
    [2.1, 1.5, 40000, ...]
  ],
  "clusters": {
    "0": {"indices": [0, 3], "size": 2},
    "1": {"indices": [1, 4], "size": 2},
    "2": {"indices": [2], "size": 1}
  },
  "inertia": 123.45,
  "silhouette_score": 0.65,
  "n_iterations": 12
}
```

---

### 3. K-Means Algorithm (score.py)

**Process**:
```
1. Parse Input
   └─ Extract data, k, parameters

2. Convert to NumPy Array
   └─ Handle dict or array format
   └─ Extract feature names

3. Feature Scaling (Optional)
   ├─ StandardScaler (default)
   ├─ MinMaxScaler
   └─ RobustScaler

4. Handle Edge Cases
   ├─ Adjust k if needed (k > samples)
   └─ Replace NaN/Inf values

5. Run K-Means Clustering
   ├─ Initialize centroids (k-means++)
   ├─ Assign points to clusters
   ├─ Update centroids
   ├─ Iterate until convergence
   └─ Max iterations: 100

6. Calculate Metrics
   ├─ Inertia (within-cluster sum of squares)
   └─ Silhouette score (cluster quality)

7. Build Response
   ├─ Cluster labels for each point
   ├─ Cluster centroids
   ├─ Cluster information (size, indices)
   └─ Performance metrics

8. Return JSON Response
```

---

## Feature Engineering

### Job Features (8 dimensions)

```
Job → Feature Vector [8 dimensions]

1. category_id        → Normalized category (float)
2. job_type_id        → Normalized job type (float)
3. location_hash      → Location encoded (0-1000)
4. salary_normalized  → Parsed salary (float)
5. experience_level   → Years extracted from text (0-10)
6. is_remote          → Boolean flag (0 or 1)
7. days_since_posted  → Recency score (float)
8. skills_score       → Weighted skill count (float)
```

**Example**:
```php
Job: "Senior PHP Developer in Manila, Remote OK, $60k-80k, 5+ years"

Feature Vector:
[
    1.0,        // category_id: IT/Software
    2.0,        // job_type_id: Full-time
    547.0,      // location_hash: crc32('manila') % 1000
    70000.0,    // salary_normalized: (60000 + 80000) / 2
    5.0,        // experience_level: extracted '5+'
    1.0,        // is_remote: Yes
    7.0,        // days_since_posted: Posted 7 days ago
    28.0        // skills_score: PHP(10) + Laravel(8) + MySQL(6) + Docker(4)
]
```

### User Features (7 dimensions)

```
User → Feature Vector [7 dimensions]

1. category_preference   → Primary preferred category
2. job_type_preference   → Primary preferred job type
3. location_hash         → Preferred location encoded
4. expected_salary       → Average expected salary
5. experience_years      → Total years of experience
6. open_to_remote        → Boolean flag (0 or 1)
7. skills_score          → Weighted skill count
```

---

## Clustering Process

### Step 1: Initialization (k-means++)

```
1. Choose first centroid randomly
2. For each remaining centroid:
   - Calculate distance from each point to nearest centroid
   - Choose next centroid with probability proportional to distance²
3. Repeat until K centroids selected
```

### Step 2: Assignment

```
For each data point:
    Calculate distance to each centroid
    Assign to nearest centroid cluster
```

### Step 3: Update

```
For each cluster:
    Calculate mean of all points in cluster
    Move centroid to this mean position
```

### Step 4: Convergence

```
Repeat Steps 2-3 until:
    - Centroids don't move (< tolerance)
    - OR max iterations reached (100)
```

---

## Caching Strategy

```
┌─────────────────────────────────────────────────────────────┐
│                    Request Received                         │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
                ┌──────────────────────┐
                │  Check Cache         │
                │  Key: azure_ml_*     │
                └──────────────────────┘
                            │
                ┌───────────┴───────────┐
                │                       │
           Cache Hit              Cache Miss
                │                       │
                ▼                       ▼
        Return Cached           Call Azure ML
            Result                     │
                │                      ▼
                │              Store in Cache
                │              TTL: 3600s (1h)
                │                      │
                └──────────┬───────────┘
                           ▼
                    Return Result
```

**Cache Keys**:
- `azure_ml_job_clusters_{k}` - Job clustering results
- `azure_ml_user_clusters_{k}` - User clustering results
- `azure_ml_access_token` - Azure AD token

**Benefits**:
- ⚡ Fast response times (< 10ms vs 1-2s)
- 💰 Reduced Azure ML costs
- 🔧 Less API calls
- 📊 Consistent results within TTL

---

## Fallback Mechanism

```
┌─────────────────────────────────────────────────────────────┐
│                    Call Azure ML                            │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
                ┌──────────────────────┐
            ┌───│   Try Connection     │
            │   └──────────────────────┘
            │               │
            │        ┌──────┴──────┐
            │        │             │
            │    Success        Timeout
            │        │          Network Error
            │        │          Auth Error
            │        │             │
            │        ▼             ▼
            │   Return Result  Check Fallback
            │        │         Enabled?
            │        │             │
            │        │      ┌──────┴──────┐
            │        │      │             │
            │        │     Yes            No
            │        │      │             │
            │        │      ▼             ▼
            │        │  Local PHP     Return
            │        │  Clustering     Error
            │        │      │
            │        │      │
            │        ├──────┘
            │        │
            └────────┴──────────────────────────────┐
                     │                              │
                     ▼                              ▼
            source: 'azure_ml'           source: 'local_fallback'
```

---

## Security

### Authentication Flow

```
Laravel App
    │
    ├─ Azure AD Service Principal
    │   ├─ Tenant ID
    │   ├─ Client ID
    │   └─ Client Secret
    │
    ▼
Request OAuth Token
    │
    └─ POST https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token
        └─ grant_type: client_credentials
        └─ scope: https://ml.azure.com/.default
    │
    ▼
Receive Access Token (valid 1 hour)
    │
    ▼
Cache Token (59 minutes)
    │
    ▼
Use Token for API Calls
    │
    └─ Authorization: Bearer {token}
```

### Endpoint Security

- ✅ **Key-based authentication**: Bearer token required
- ✅ **HTTPS only**: All traffic encrypted
- ✅ **IP restrictions**: Optional firewall rules
- ✅ **Rate limiting**: Built into Azure ML
- ✅ **Token expiration**: Auto-refresh after 59 minutes

---

## Performance Optimization

### Request Optimization

```
Without Caching:
    User Request → Azure ML → 1-2 seconds → Response

With Caching:
    First Request → Azure ML → 1-2 seconds → Cache → Response
    Subsequent Requests → Cache → < 10ms → Response

Cache Hit Rate Target: 90%+
Cache TTL: 1 hour (adjustable)
```

### Batch Processing

```php
// Instead of calling Azure ML for each user
foreach ($users as $user) {
    $recommendations = $service->getJobRecommendations($user->id);
}

// Cluster once, reuse for all users
$jobClusters = $service->runJobClustering(5);  // Cache for 1 hour
foreach ($users as $user) {
    $recommendations = $this->findFromClusters($user, $jobClusters);
}
```

---

## Monitoring & Observability

### Laravel Logs

```
Log::info('Azure ML clustering started', [
    'user_id' => $userId,
    'k' => $k,
    'timestamp' => now()
]);

Log::error('Azure ML failed', [
    'error' => $exception->getMessage(),
    'fallback_used' => true
]);
```

### Azure ML Metrics

```bash
# View deployment logs
az ml online-deployment get-logs \
    --name kmeans-clustering-deployment \
    --endpoint-name kmeans-clustering-endpoint

# Check metrics
az monitor metrics list \
    --resource-type Microsoft.MachineLearningServices/workspaces/onlineEndpoints \
    --metric-names RequestLatency,RequestsPerMinute
```

### Health Check

```php
$health = $service->healthCheck();
// [
//     'configured' => true,
//     'accessible' => true,
//     'message' => 'Endpoint accessible'
// ]
```

---

## Cost Analysis

### Azure ML Costs

```
Endpoint Costs:
    Compute Instance: Standard_DS2_v2
    ├─ Base: ~$0.10/hour
    ├─ Per request: $0.0001
    └─ Data transfer: Minimal

Monthly Estimate (moderate usage):
    Base compute: $72/month (24/7)
    Requests (10k/month): $1
    Total: ~$73/month

Cost Reduction Strategies:
    ✓ Enable caching (90% reduction)
    ✓ Scale to 0 when not used
    ✓ Use smaller instance
    ✓ Batch requests
    ✓ Use fallback for dev/test
```

---

## Testing Strategy

### Unit Tests

```php
// Test feature extraction
public function test_extract_job_features()
{
    $job = Job::factory()->create([
        'category_id' => 1,
        'salary_range' => '$50,000 - $60,000',
    ]);

    $features = $service->extractJobFeatures($job);

    $this->assertEquals(1.0, $features['category_id']);
    $this->assertEquals(55000.0, $features['salary_normalized']);
}
```

### Integration Tests

```php
// Test Azure ML endpoint
public function test_azure_ml_clustering()
{
    $service = new AzureMLClusteringService();
    $result = $service->runJobClustering(3);

    $this->assertArrayHasKey('labels', $result);
    $this->assertArrayHasKey('centroids', $result);
    $this->assertEquals('azure_ml', $result['source']);
}
```

### Load Tests

```bash
# Simulate 100 concurrent requests
ab -n 1000 -c 100 https://your-app.com/api/recommendations/user/1
```

---

## Deployment Checklist

- [ ] Python dependencies installed
- [ ] Azure account created
- [ ] Azure ML workspace created
- [ ] K-means endpoint deployed
- [ ] Endpoint URL in .env
- [ ] Endpoint key in .env
- [ ] Health check passed
- [ ] Local clustering tested
- [ ] Azure clustering tested
- [ ] Caching verified
- [ ] Fallback tested
- [ ] Logs configured
- [ ] Monitoring set up
- [ ] Documentation reviewed

---

## Future Enhancements

### Planned Improvements

1. **Real-time Updates**
   - Incremental clustering
   - Stream processing
   - WebSocket notifications

2. **Advanced Algorithms**
   - DBSCAN for density-based clustering
   - Hierarchical clustering
   - Gaussian Mixture Models

3. **Feature Expansion**
   - Natural language processing for job descriptions
   - Image analysis for company logos
   - Sentiment analysis for reviews

4. **Optimization**
   - GPU-accelerated clustering
   - Distributed computing
   - Auto-scaling based on load

5. **Analytics**
   - A/B testing framework
   - Cluster visualization dashboard
   - Performance metrics tracking

---

**Last Updated**: January 12, 2026
