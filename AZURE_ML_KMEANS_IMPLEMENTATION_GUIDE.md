# Azure ML K-Means Clustering Implementation Guide

## Complete Step-by-Step Implementation

This guide will walk you through implementing K-means clustering using Azure Machine Learning for your TugmaJobs portal.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Step 1: Install Python Dependencies](#step-1-install-python-dependencies)
3. [Step 2: Test Local K-means Implementation](#step-2-test-local-kmeans-implementation)
4. [Step 3: Set Up Azure Account](#step-3-set-up-azure-account)
5. [Step 4: Create Azure ML Workspace](#step-4-create-azure-ml-workspace)
6. [Step 5: Deploy K-means to Azure ML](#step-5-deploy-kmeans-to-azure-ml)
7. [Step 6: Configure Laravel Application](#step-6-configure-laravel-application)
8. [Step 7: Test the Integration](#step-7-test-the-integration)
9. [Step 8: Use Clustering in Your Application](#step-8-use-clustering-in-your-application)
10. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### What You Need:
- ✅ Python 3.13.5 (already installed)
- ✅ Laravel application (TugmaJobs - already set up)
- ✅ Azure ML configuration files (already created)
- ⬜ Azure subscription (free tier available)
- ⬜ Azure CLI installed
- ⬜ Python packages (numpy, scikit-learn, etc.)

### Estimated Time:
- **Total**: 30-45 minutes
- Setup: 15 minutes
- Deployment: 10-15 minutes
- Testing: 10-15 minutes

---

## Step 1: Install Python Dependencies

### 1.1 Create Python Virtual Environment

```bash
cd Capstone/job-portal-main/azure-ml

# Create virtual environment
python -m venv venv

# Activate it (Windows)
venv\Scripts\activate

# Or activate it (Mac/Linux)
source venv/bin/activate
```

### 1.2 Create Requirements File

Create a file named `requirements.txt` in the `azure-ml` directory:

```txt
numpy>=1.24.0
scikit-learn>=1.3.0
pandas>=2.0.0
```

### 1.3 Install Dependencies

```bash
pip install -r requirements.txt
```

### 1.4 Verify Installation

```bash
python -c "import numpy; import sklearn; print('Success!')"
```

**Expected Output**: `Success!`

---

## Step 2: Test Local K-means Implementation

### 2.1 Test the Score Script Locally

```bash
cd Capstone/job-portal-main/azure-ml
python score.py
```

**Expected Output**: JSON output with clustering results like:
```json
{
  "labels": [0, 0, 1, 1, 2, 2],
  "centroids": [[...], [...], [...]],
  "clusters": {
    "0": {"indices": [0, 1], "size": 2},
    "1": {"indices": [2, 3], "size": 2},
    "2": {"indices": [4, 5], "size": 2}
  },
  "inertia": 123.45,
  "silhouette_score": 0.65
}
```

✅ **Checkpoint**: If you see this output, your K-means implementation works locally!

---

## Step 3: Set Up Azure Account

### 3.1 Create Azure Account

1. Go to: https://azure.microsoft.com/free/
2. Click **Start free**
3. Sign in with Microsoft account (or create one)
4. Fill in your information
5. Verify with phone number
6. Add credit card (required but won't be charged for free tier)

**Free Tier Includes**:
- 12 months of free services
- Always free services
- $200 credit for 30 days

### 3.2 Install Azure CLI

**Windows:**
Download the Azure CLI MSI installer (already present):
```bash
cd Capstone/job-portal-main/azure-ml
# Run the AzureCLI.msi file
```
Or download from: https://aka.ms/installazurecliwindows

**Mac:**
```bash
brew install azure-cli
```

**Linux:**
```bash
curl -sL https://aka.ms/InstallAzureCLIDeb | sudo bash
```

### 3.3 Verify Azure CLI Installation

```bash
az --version
```

**Expected Output**: Version information

### 3.4 Login to Azure

```bash
az login
```

This will:
- Open your browser
- Ask you to sign in
- Return subscription information

### 3.5 Get Your Subscription ID

```bash
az account list --output table
```

Copy your **Subscription ID** - you'll need it later.

---

## Step 4: Create Azure ML Workspace

### 4.1 Install Azure ML Extension

```bash
az extension add -n ml
```

### 4.2 Create Resource Group

```bash
az group create --name AzureML-RG --location eastasia
```

**Location Options**: eastasia, southeastasia, eastus, westus2, westeurope

### 4.3 Create Azure ML Workspace

```bash
az ml workspace create --name tugmajobs --resource-group AzureML-RG --location eastasia
```

This takes about 2-3 minutes.

**Expected Output**:
```json
{
  "name": "tugmajobs",
  "location": "eastasia",
  "resourceGroup": "AzureML-RG",
  ...
}
```

### 4.4 Set Default Workspace

```bash
az configure --defaults group=AzureML-RG workspace=tugmajobs
```

✅ **Checkpoint**: Your Azure ML workspace is ready!

---

## Step 5: Deploy K-means to Azure ML

### 5.1 Navigate to Azure ML Directory

```bash
cd Capstone/job-portal-main/azure-ml
```

### 5.2 Run Deployment Script

```bash
python deploy.py --subscription YOUR_SUBSCRIPTION_ID
```

Replace `YOUR_SUBSCRIPTION_ID` with the ID from Step 3.5.

### What This Does:
1. ✅ Checks prerequisites
2. ✅ Creates managed online endpoint
3. ✅ Deploys the scoring script
4. ✅ Tests the endpoint
5. ✅ Returns endpoint URL and API key

**Expected Output** (at the end):
```
============================================================
ADD THESE TO YOUR .env FILE:
============================================================
AZURE_ML_ENDPOINT_URL=https://kmeans-clustering-endpoint.eastasia.inference.ml.azure.com/score
AZURE_ML_ENDPOINT_KEY=abc123xyz...
============================================================
```

⏱️ **Time**: 10-15 minutes (deployment takes time)

### 5.3 Alternative: Manual Deployment

If the script fails, deploy manually:

```bash
# Create endpoint
az ml online-endpoint create --file endpoint.yml

# Create deployment
az ml online-deployment create --file deployment.yml --all-traffic

# Get endpoint URL
az ml online-endpoint show --name kmeans-clustering-endpoint --query scoring_uri -o tsv

# Get API key
az ml online-endpoint get-credentials --name kmeans-clustering-endpoint --query primaryKey -o tsv
```

✅ **Checkpoint**: Copy the endpoint URL and API key!

---

## Step 6: Configure Laravel Application

### 6.1 Update .env File

Open `Capstone/job-portal-main/.env` and update these lines:

```env
# Azure ML Endpoint (paste your values here)
AZURE_ML_ENDPOINT_URL=https://kmeans-clustering-endpoint.eastasia.inference.ml.azure.com/score
AZURE_ML_ENDPOINT_KEY=your-actual-key-here
```

### 6.2 Verify Configuration

```bash
cd Capstone/job-portal-main
php artisan tinker
```

In Tinker, run:
```php
$service = new App\Services\AzureMLClusteringService();
$health = $service->healthCheck();
print_r($health);
exit
```

**Expected Output**:
```php
Array
(
    [configured] => 1
    [accessible] => 1
    [message] => Endpoint accessible
)
```

✅ **Checkpoint**: Configuration is correct!

---

## Step 7: Test the Integration

### 7.1 Test Job Clustering

```bash
php artisan tinker
```

```php
$service = new App\Services\AzureMLClusteringService();

// Test job clustering
$result = $service->runJobClustering(3);
print_r($result);
exit
```

**Expected Output**:
```php
Array
(
    [labels] => Array (...)
    [centroids] => Array (...)
    [clusters] => Array (...)
    [source] => azure_ml
)
```

### 7.2 Test Job Recommendations

```php
php artisan tinker
```

```php
$service = new App\Services\AzureMLClusteringService();

// Get recommendations for user ID 1
$recommendations = $service->getJobRecommendations(1, 5);
echo "Found " . $recommendations->count() . " recommendations\n";
foreach($recommendations as $job) {
    echo "- " . $job->title . " (Score: " . $job->cluster_score . ")\n";
}
exit
```

### 7.3 Test Labor Market Insights

```php
php artisan tinker
```

```php
$service = new App\Services\AzureMLClusteringService();
$insights = $service->getLaborMarketInsights();
print_r($insights);
exit
```

✅ **Checkpoint**: All tests pass? You're done with setup!

---

## Step 8: Use Clustering in Your Application

### 8.1 Get Job Recommendations (for Job Seekers)

In any controller:

```php
use App\Services\AzureMLClusteringService;

public function recommendations(Request $request)
{
    $clusteringService = new AzureMLClusteringService();

    // Get personalized job recommendations
    $recommendations = $clusteringService->getJobRecommendations(
        auth()->id(),
        10 // number of recommendations
    );

    return view('jobs.recommendations', compact('recommendations'));
}
```

### 8.2 Get Candidate Recommendations (for Employers)

```php
public function findCandidates($jobId)
{
    $clusteringService = new AzureMLClusteringService();

    // Find matching candidates for a job
    $candidates = $clusteringService->getUserRecommendations($jobId, 10);

    return view('employer.candidates', compact('candidates'));
}
```

### 8.3 Display Market Insights (Admin Dashboard)

```php
public function dashboard()
{
    $clusteringService = new AzureMLClusteringService();

    // Get comprehensive market insights
    $insights = $clusteringService->getLaborMarketInsights();

    return view('admin.dashboard', compact('insights'));
}
```

### 8.4 Find Optimal Number of Clusters

```php
public function analyzeMarket()
{
    $clusteringService = new AzureMLClusteringService();

    // Find optimal K for job clustering
    $analysis = $clusteringService->findOptimalK('job', 10);

    echo "Optimal number of clusters: " . $analysis['optimal_k'];
}
```

---

## Troubleshooting

### Issue 1: Python Module Not Found

**Error**: `ModuleNotFoundError: No module named 'numpy'`

**Solution**:
```bash
cd Capstone/job-portal-main/azure-ml
python -m venv venv
venv\Scripts\activate
pip install numpy scikit-learn pandas
```

---

### Issue 2: Azure CLI Not Found

**Error**: `'az' is not recognized as an internal or external command`

**Solution**:
1. Install Azure CLI from the MSI file in azure-ml folder
2. Restart your terminal
3. Run: `az --version`

---

### Issue 3: Deployment Failed

**Error**: `Deployment failed with error...`

**Solution**:
```bash
# Check endpoint status
az ml online-endpoint show --name kmeans-clustering-endpoint

# View logs
az ml online-deployment get-logs --name kmeans-clustering-deployment --endpoint-name kmeans-clustering-endpoint

# Delete and retry
az ml online-endpoint delete --name kmeans-clustering-endpoint --yes
python deploy.py --subscription YOUR_SUBSCRIPTION_ID
```

---

### Issue 4: Endpoint Not Configured

**Error**: `Azure ML endpoint not configured`

**Solution**:
1. Make sure you copied the endpoint URL and key from deployment
2. Update `.env` file with actual values (not empty)
3. Run: `php artisan config:clear`
4. Test: `php artisan tinker` → `config('azure-ml.endpoint_url')`

---

### Issue 5: Connection Timeout

**Error**: `Connection timeout to Azure ML`

**Solution**:
1. Check internet connection
2. Verify endpoint is running:
   ```bash
   az ml online-endpoint show --name kmeans-clustering-endpoint
   ```
3. Increase timeout in `.env`:
   ```env
   AZURE_ML_CONNECTION_TIMEOUT=60
   AZURE_ML_REQUEST_TIMEOUT=180
   ```

---

### Issue 6: Invalid API Key

**Error**: `Authentication failed` or `401 Unauthorized`

**Solution**:
```bash
# Get new API key
az ml online-endpoint get-credentials --name kmeans-clustering-endpoint --query primaryKey -o tsv

# Update in .env
AZURE_ML_ENDPOINT_KEY=your-new-key-here

# Clear config cache
php artisan config:clear
```

---

### Issue 7: Empty Clustering Results

**Error**: Clustering returns empty arrays

**Solution**:
```php
// Check if you have data in database
php artisan tinker
App\Models\Job::where('status', 1)->count() // Should be > 0
App\Models\User::where('role', 'jobseeker')->count() // Should be > 0
```

If counts are 0, add test data to your database first.

---

### Issue 8: Fallback to Local Clustering

**Message**: `source: local_fallback`

**This is OK!** It means:
- Azure ML endpoint is not responding
- System automatically used local PHP clustering
- Your app still works, just using local algorithm

To fix and use Azure ML:
1. Check endpoint status
2. Verify endpoint URL and key in `.env`
3. Run health check: `$service->healthCheck()`

---

## Cost Management

### Free Tier Limits:
- ✅ First 12 months: Many services free
- ✅ $200 credit for 30 days
- ⚠️ After free tier: ~$0.10/hour for endpoint

### To Minimize Costs:

**1. Stop endpoint when not using:**
```bash
az ml online-endpoint update --name kmeans-clustering-endpoint --set traffic={}
```

**2. Delete endpoint:**
```bash
az ml online-endpoint delete --name kmeans-clustering-endpoint --yes
```

**3. Enable caching in .env:**
```env
AZURE_ML_CACHE_ENABLED=true
AZURE_ML_CACHE_TTL=3600  # 1 hour
```

**4. Use fallback for development:**
```env
AZURE_ML_FALLBACK_ENABLED=true  # Uses local clustering if Azure fails
```

---

## Monitoring

### Check Endpoint Status

```bash
az ml online-endpoint show --name kmeans-clustering-endpoint --output table
```

### View Deployment Logs

```bash
az ml online-deployment get-logs \
    --name kmeans-clustering-deployment \
    --endpoint-name kmeans-clustering-endpoint \
    --lines 50
```

### Monitor in Laravel

```php
// In any controller or tinker
$service = new App\Services\AzureMLClusteringService();
$health = $service->healthCheck();

if ($health['accessible']) {
    echo "✅ Azure ML is working";
} else {
    echo "❌ Issue: " . $health['message'];
}
```

---

## Advanced Configuration

### Change Number of Clusters

In `.env`:
```env
AZURE_ML_DEFAULT_K=7  # Change from 5 to 7 clusters
```

### Change Clustering Algorithm

In `.env`:
```env
AZURE_ML_ALGORITHM=elkan  # Options: lloyd, elkan
```

### Disable Feature Scaling

In `.env`:
```env
AZURE_ML_SCALING_ENABLED=false
```

### Adjust Cache Duration

In `.env`:
```env
AZURE_ML_CACHE_TTL=7200  # 2 hours instead of 1
```

---

## Next Steps

1. ✅ **Integrate into UI**: Add recommendation widgets to job seeker dashboard
2. ✅ **Create Admin Panel**: Show clustering insights and market analysis
3. ✅ **Add Visualizations**: Use Chart.js to visualize clusters
4. ✅ **Schedule Updates**: Use Laravel scheduler to refresh clusters daily
5. ✅ **A/B Testing**: Compare Azure ML vs local clustering performance

---

## Useful Commands Reference

```bash
# Azure Login
az login

# List subscriptions
az account list --output table

# Set default subscription
az account set --subscription YOUR_SUBSCRIPTION_ID

# Check workspace
az ml workspace show --name tugmajobs

# List endpoints
az ml online-endpoint list

# Get endpoint details
az ml online-endpoint show --name kmeans-clustering-endpoint

# Get API key
az ml online-endpoint get-credentials --name kmeans-clustering-endpoint

# Delete endpoint (to save costs)
az ml online-endpoint delete --name kmeans-clustering-endpoint --yes

# Test deployment manually
cd Capstone/job-portal-main/azure-ml
python deploy.py --test-only --subscription YOUR_SUBSCRIPTION_ID
```

---

## Support & Resources

### Documentation:
- **Azure ML**: https://docs.microsoft.com/azure/machine-learning/
- **Laravel HTTP Client**: https://laravel.com/docs/http-client
- **scikit-learn K-means**: https://scikit-learn.org/stable/modules/clustering.html#k-means

### Contact:
- Azure Support: https://azure.microsoft.com/support/
- Laravel Forums: https://laracasts.com/discuss

---

## Summary Checklist

- [ ] Python dependencies installed
- [ ] Local K-means test passed
- [ ] Azure account created
- [ ] Azure CLI installed and logged in
- [ ] Azure ML workspace created
- [ ] K-means deployed to Azure ML
- [ ] Endpoint URL and key obtained
- [ ] Laravel .env configured
- [ ] Health check passed
- [ ] Test clustering working
- [ ] Job recommendations working
- [ ] Integration complete

---

**Congratulations! 🎉**

You've successfully implemented K-means clustering using Azure ML for your job portal!

Your application can now:
- ✅ Provide personalized job recommendations
- ✅ Match candidates to jobs intelligently
- ✅ Analyze labor market trends
- ✅ Scale to handle large datasets
- ✅ Fall back gracefully if Azure is unavailable

---

*Last Updated: January 12, 2026*
