# Azure ML K-Means Clustering Integration

This directory contains files for deploying K-Means clustering to Azure Machine Learning.

## Overview

The integration provides:
- **Azure ML Managed Online Endpoint**: Scalable, production-ready clustering API
- **K-Means Clustering**: Job and user segmentation for recommendations
- **Automatic Fallback**: Falls back to local PHP implementation if Azure ML is unavailable

## Prerequisites

1. **Azure Subscription** with Azure ML workspace created
2. **Azure CLI** installed: https://docs.microsoft.com/cli/azure/install-azure-cli
3. **Azure ML CLI extension**:
   ```bash
   az extension add -n ml
   ```

## Quick Start

### Step 1: Deploy to Azure ML

```bash
# Login to Azure
az login

# Run the deployment script
cd azure-ml
python deploy.py --subscription YOUR_SUBSCRIPTION_ID
```

### Step 2: Configure Laravel

Add the endpoint credentials to your `.env` file:

```env
AZURE_ML_ENDPOINT_URL=https://kmeans-clustering-endpoint.eastasia.inference.ml.azure.com/score
AZURE_ML_ENDPOINT_KEY=your-endpoint-key-here
```

### Step 3: Use in Your Application

```php
use App\Services\AzureMLClusteringService;

$clusteringService = new AzureMLClusteringService();

// Get job recommendations for a user
$recommendations = $clusteringService->getJobRecommendations($userId, 10);

// Run job clustering
$clusters = $clusteringService->runJobClustering();

// Get labor market insights
$insights = $clusteringService->getLaborMarketInsights();

// Find optimal number of clusters
$optimalK = $clusteringService->findOptimalK('job', 10);
```

## Files

| File | Description |
|------|-------------|
| `score.py` | Python scoring script for the Azure ML endpoint |
| `environment.yml` | Conda environment specification |
| `endpoint.yml` | Managed online endpoint configuration |
| `deployment.yml` | Deployment configuration |
| `deploy.py` | Automated deployment script |

## Manual Deployment Steps

If you prefer manual deployment:

### 1. Create the Endpoint

```bash
az ml online-endpoint create --file endpoint.yml \
    --resource-group AzureML-RG \
    --workspace-name tugmajobs
```

### 2. Create the Deployment

```bash
az ml online-deployment create --file deployment.yml \
    --resource-group AzureML-RG \
    --workspace-name tugmajobs \
    --all-traffic
```

### 3. Get Endpoint Credentials

```bash
# Get endpoint URL
az ml online-endpoint show --name kmeans-clustering-endpoint \
    --resource-group AzureML-RG \
    --workspace-name tugmajobs \
    --query scoring_uri -o tsv

# Get API key
az ml online-endpoint get-credentials --name kmeans-clustering-endpoint \
    --resource-group AzureML-RG \
    --workspace-name tugmajobs \
    --query primaryKey -o tsv
```

## API Reference

### Request Format

```json
{
    "data": [
        {"feature1": 1.0, "feature2": 2.0, ...},
        ...
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

### Response Format

```json
{
    "labels": [0, 1, 2, 0, 1, ...],
    "centroids": [[...], [...], ...],
    "clusters": {
        "0": {"indices": [0, 3, ...], "size": 10},
        "1": {"indices": [1, 4, ...], "size": 8},
        ...
    },
    "inertia": 123.45,
    "silhouette_score": 0.65
}
```

## Configuration Options

| Environment Variable | Default | Description |
|---------------------|---------|-------------|
| `AZURE_ML_DEFAULT_K` | 5 | Default number of clusters |
| `AZURE_ML_MAX_ITERATIONS` | 100 | Maximum K-means iterations |
| `AZURE_ML_ALGORITHM` | lloyd | K-means algorithm (lloyd, elkan) |
| `AZURE_ML_SCALING_ENABLED` | true | Enable feature scaling |
| `AZURE_ML_CACHE_ENABLED` | true | Cache clustering results |
| `AZURE_ML_CACHE_TTL` | 3600 | Cache TTL in seconds |
| `AZURE_ML_FALLBACK_ENABLED` | true | Use local fallback on failure |

## Monitoring

### Check Endpoint Status

```bash
az ml online-endpoint show --name kmeans-clustering-endpoint \
    --resource-group AzureML-RG \
    --workspace-name tugmajobs
```

### View Logs

```bash
az ml online-deployment get-logs --name kmeans-clustering-deployment \
    --endpoint-name kmeans-clustering-endpoint \
    --resource-group AzureML-RG \
    --workspace-name tugmajobs
```

### Health Check in PHP

```php
$clusteringService = new AzureMLClusteringService();
$health = $clusteringService->healthCheck();

if ($health['accessible']) {
    echo "Azure ML endpoint is healthy";
} else {
    echo "Issue: " . $health['message'];
}
```

## Costs

- **Endpoint**: ~$0.10/hour for Standard_DS2_v2 instance
- **Storage**: Minimal (only scoring script)
- **Compute**: Pay per request when using serverless

To reduce costs, consider:
- Scaling to 0 instances when not in use
- Using smaller instance types
- Enabling auto-scaling

## Troubleshooting

### Endpoint Not Responding

1. Check endpoint status: `az ml online-endpoint show ...`
2. View deployment logs: `az ml online-deployment get-logs ...`
3. Verify API key is correct

### Slow Response Times

1. Enable caching in Laravel configuration
2. Increase instance count for high traffic
3. Use smaller feature sets

### Authentication Errors

1. Verify endpoint key in `.env`
2. Check key hasn't expired
3. Regenerate key if needed: `az ml online-endpoint regenerate-keys ...`

## Support

For issues related to:
- **Azure ML**: https://docs.microsoft.com/azure/machine-learning/
- **Laravel Integration**: Check `app/Services/AzureMLClusteringService.php`
- **Deployment**: Run `deploy.py` with `--help` flag
