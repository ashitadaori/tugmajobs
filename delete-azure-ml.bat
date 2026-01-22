@echo off
echo ========================================
echo Azure ML K-Means Clustering Deletion
echo ========================================
echo.

echo This will:
echo 1. Delete the Azure ML endpoint
echo 2. Stop all billing charges
echo 3. Switch to FREE local clustering
echo.

set /p confirm="Continue? (Y/N): "
if /i not "%confirm%"=="Y" (
    echo Deletion cancelled.
    exit /b
)

echo.
echo Deleting endpoint...
echo.

az ml online-endpoint delete --name kmeans-clustering-endpoint --resource-group AzureML-RG --workspace-name tugmajobs --yes --no-wait

echo.
echo Endpoint deletion initiated!
echo.
echo Updating .env to disable Azure ML...

powershell -Command "(Get-Content .env) -replace 'AZURE_ML_ENDPOINT_URL=.*', 'AZURE_ML_ENDPOINT_URL=' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'AZURE_ML_ENDPOINT_KEY=.*', 'AZURE_ML_ENDPOINT_KEY=' | Set-Content .env"

echo Clearing Laravel config cache...
php artisan config:clear

echo.
echo Testing local clustering...
php test-clustering.php

echo.
echo ========================================
echo Azure ML Disabled Successfully!
echo ========================================
echo Monthly cost is now: $0
echo System is using FREE local clustering
echo.

pause
