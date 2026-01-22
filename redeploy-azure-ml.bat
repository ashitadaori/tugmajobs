@echo off
echo ========================================
echo Azure ML K-Means Clustering Redeployment
echo ========================================
echo.

echo This will:
echo 1. Deploy Azure ML endpoint (~10-15 minutes)
echo 2. Display endpoint URL and API key
echo 3. You'll need to update .env manually
echo.

set /p confirm="Continue? (Y/N): "
if /i not "%confirm%"=="Y" (
    echo Deployment cancelled.
    exit /b
)

echo.
echo Starting deployment...
echo.

cd azure-ml
python deploy.py --workspace tugmajobs --resource-group AzureML-RG --subscription aa977c5d-add1-4908-9851-21c3e6aaa939

echo.
echo ========================================
echo Deployment Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Copy the AZURE_ML_ENDPOINT_URL above
echo 2. Copy the AZURE_ML_ENDPOINT_KEY above
echo 3. Update your .env file with these values
echo 4. Run: php artisan config:clear
echo 5. Run: php test-clustering.php to verify
echo.

pause
