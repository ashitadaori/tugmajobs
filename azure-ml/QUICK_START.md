# Azure ML K-Means Clustering - Quick Start

## 🚀 Fast Setup (15 minutes)

### 1. Install Python Dependencies (2 minutes)

```bash
cd Capstone/job-portal-main/azure-ml

# Windows
setup.bat

# Mac/Linux
chmod +x setup.sh
./setup.sh
```

### 2. Set Up Azure (5 minutes)

```bash
# Login to Azure
az login

# Get subscription ID
az account list --output table

# Create workspace
az group create --name AzureML-RG --location eastasia
az ml workspace create --name tugmajobs --resource-group AzureML-RG --location eastasia
```

### 3. Deploy K-Means (5 minutes)

```bash
python deploy.py --subscription YOUR_SUBSCRIPTION_ID
```

Copy the output:
```
AZURE_ML_ENDPOINT_URL=https://...
AZURE_ML_ENDPOINT_KEY=...
```

### 4. Configure Laravel (1 minute)

Add to your `.env` file:
```env
AZURE_ML_ENDPOINT_URL=https://kmeans-clustering-endpoint.eastasia.inference.ml.azure.com/score
AZURE_ML_ENDPOINT_KEY=your-key-here
```

### 5. Test It (2 minutes)

```bash
cd ..
php artisan tinker
```

```php
$service = new App\Services\AzureMLClusteringService();
$recommendations = $service->getJobRecommendations(1, 5);
echo "Found " . $recommendations->count() . " recommendations!";
exit
```

---

## 📖 Full Documentation

See: [AZURE_ML_KMEANS_IMPLEMENTATION_GUIDE.md](AZURE_ML_KMEANS_IMPLEMENTATION_GUIDE.md)

---

## ⚡ Common Commands

### Azure ML

```bash
# Check endpoint status
az ml online-endpoint show --name kmeans-clustering-endpoint

# View logs
az ml online-deployment get-logs --name kmeans-clustering-deployment --endpoint-name kmeans-clustering-endpoint

# Get credentials
az ml online-endpoint get-credentials --name kmeans-clustering-endpoint

# Delete endpoint (save costs)
az ml online-endpoint delete --name kmeans-clustering-endpoint --yes
```

### Laravel/PHP

```php
// Health check
$service = new App\Services\AzureMLClusteringService();
$health = $service->healthCheck();

// Get recommendations
$jobs = $service->getJobRecommendations($userId, 10);

// Run clustering
$clusters = $service->runJobClustering(5);

// Market insights
$insights = $service->getLaborMarketInsights();

// Clear cache
$service->clearCache();
```

---

## 🔧 Troubleshooting

### Python modules not found
```bash
cd azure-ml
python -m venv venv
venv\Scripts\activate  # Windows
source venv/bin/activate  # Mac/Linux
pip install -r requirements.txt
```

### Azure CLI not found
- Windows: Run `AzureCLI.msi`
- Mac: `brew install azure-cli`
- Linux: `curl -sL https://aka.ms/InstallAzureCLIDeb | sudo bash`

### Endpoint not configured
1. Make sure you deployed: `python deploy.py --subscription YOUR_ID`
2. Copy URL and key to `.env`
3. Run: `php artisan config:clear`

### Connection timeout
```env
# In .env, increase timeouts
AZURE_ML_CONNECTION_TIMEOUT=60
AZURE_ML_REQUEST_TIMEOUT=180
```

---

## 💰 Cost Management

```bash
# Stop endpoint when not using
az ml online-endpoint update --name kmeans-clustering-endpoint --set traffic={}

# Delete endpoint
az ml online-endpoint delete --name kmeans-clustering-endpoint --yes
```

Enable fallback in `.env`:
```env
AZURE_ML_FALLBACK_ENABLED=true  # Uses local clustering if Azure fails
AZURE_ML_CACHE_ENABLED=true     # Cache results for 1 hour
```

---

## 📊 Usage Examples

### Job Seeker: Get Personalized Recommendations

```php
use App\Services\AzureMLClusteringService;

public function recommendations()
{
    $service = new AzureMLClusteringService();
    $jobs = $service->getJobRecommendations(auth()->id(), 10);

    return view('jobs.recommendations', compact('jobs'));
}
```

### Employer: Find Matching Candidates

```php
public function findCandidates($jobId)
{
    $service = new AzureMLClusteringService();
    $candidates = $service->getUserRecommendations($jobId, 10);

    return view('employer.candidates', compact('candidates'));
}
```

### Admin: Market Analysis

```php
public function dashboard()
{
    $service = new AzureMLClusteringService();
    $insights = $service->getLaborMarketInsights();

    return view('admin.dashboard', compact('insights'));
}
```

---

## ✅ Verification Checklist

- [ ] Python dependencies installed (`setup.bat` or `setup.sh`)
- [ ] Azure CLI installed (`az --version`)
- [ ] Logged into Azure (`az login`)
- [ ] Azure ML workspace created
- [ ] K-means deployed (`python deploy.py`)
- [ ] `.env` updated with endpoint URL and key
- [ ] Health check passed (`$service->healthCheck()`)
- [ ] Test clustering works
- [ ] Recommendations working

---

## 📚 Key Files

| File | Purpose |
|------|---------|
| `score.py` | K-means clustering algorithm |
| `deploy.py` | Automated deployment script |
| `endpoint.yml` | Endpoint configuration |
| `deployment.yml` | Deployment configuration |
| `requirements.txt` | Python dependencies |
| `setup.bat` | Windows setup script |
| `setup.sh` | Mac/Linux setup script |
| `AZURE_ML_KMEANS_IMPLEMENTATION_GUIDE.md` | Full guide |

---

## 🆘 Need Help?

1. Check the full guide: `AZURE_ML_KMEANS_IMPLEMENTATION_GUIDE.md`
2. View logs: `az ml online-deployment get-logs ...`
3. Test locally: `python score.py`
4. Check health: `$service->healthCheck()`

---

**Ready to start? Run:** `setup.bat` (Windows) or `./setup.sh` (Mac/Linux)
