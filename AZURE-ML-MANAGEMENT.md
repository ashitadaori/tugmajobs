# Azure ML K-Means Clustering - Enable/Disable Guide

## Current Status

**Azure ML is DISABLED** ✓
- Monthly cost: **$0**
- Using: Free local clustering
- Performance: Good for development/testing

---

## Quick Commands

### Check Current Status
```bash
php test-clustering.php
```

### Enable Azure ML (Redeploy)
**Windows:**
```bash
redeploy-azure-ml.bat
```

**Manual:**
```bash
cd azure-ml
python deploy.py --workspace tugmajobs --resource-group AzureML-RG --subscription aa977c5d-add1-4908-9851-21c3e6aaa939
```

**Time:** 10-15 minutes
**Cost after:** $102/month

### Disable Azure ML (Delete)
**Windows:**
```bash
delete-azure-ml.bat
```

**Manual:**
```bash
az ml online-endpoint delete --name kmeans-clustering-endpoint --resource-group AzureML-RG --workspace-name tugmajobs --yes
```

**Time:** 1-2 minutes
**Cost after:** $0/month

---

## When to Enable Azure ML

✅ **Enable for:**
- Production deployment
- Presenting to stakeholders/investors
- Demonstrating advanced features
- High-quality job recommendations needed
- Large dataset (>10,000 users/jobs)

❌ **Keep Disabled for:**
- Development/testing
- Low traffic periods
- Budget constraints
- Learning/experimenting
- Small datasets (<1,000 jobs)

---

## After Redeployment

Once `redeploy-azure-ml.bat` completes, you'll see:

```
AZURE_ML_ENDPOINT_URL=https://kmeans-clustering-endpoint-xxx.eastasia.inference.ml.azure.com/score
AZURE_ML_ENDPOINT_KEY=xxxxxxxxxxxxx
```

**Update .env:**
1. Open `.env`
2. Find lines 83-84
3. Paste the new URL and KEY
4. Run: `php artisan config:clear`
5. Run: `php test-clustering.php` to verify

---

## Cost Summary

| Mode | Monthly Cost | Performance | Use Case |
|------|--------------|-------------|----------|
| **Disabled (Local)** | $0 | Good | Development, Testing |
| **Enabled (Azure ML)** | $102 | Excellent | Production, Demos |

---

## Important Notes

1. **No Downtime:** System automatically switches between Azure ML and local clustering
2. **Fallback Enabled:** If Azure ML fails, local clustering takes over
3. **Cache Protects You:** Even with Azure ML enabled, cache reduces costs
4. **Each Deployment May Change URL:** Always update .env after redeploying

---

## Troubleshooting

### "Endpoint not found" error
- Endpoint was deleted or never created
- Solution: Run `redeploy-azure-ml.bat`

### "Authentication failed"
- Azure credentials expired
- Solution: Run `az login` and try again

### System still using Azure ML after deletion
- Config cache not cleared
- Solution: `php artisan config:clear`

---

## Files Created

- `test-clustering.php` - Test current clustering status
- `redeploy-azure-ml.bat` - Enable Azure ML (Windows)
- `delete-azure-ml.bat` - Disable Azure ML (Windows)
- `AZURE-ML-MANAGEMENT.md` - This guide

---

## Contact Azure Support

If you have billing questions:
- Azure Portal: https://portal.azure.com
- Support: https://azure.microsoft.com/support
- Billing: Check Azure Cost Management

---

**Last Updated:** $(Get-Date -Format "yyyy-MM-dd")
**Status:** Azure ML Disabled - Using Free Local Clustering
