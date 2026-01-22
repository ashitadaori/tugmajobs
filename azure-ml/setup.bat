@echo off
REM Azure ML K-Means Clustering Setup Script for Windows
REM =====================================================

echo.
echo ========================================
echo Azure ML K-Means Setup
echo ========================================
echo.

REM Step 1: Create virtual environment
echo [1/5] Creating Python virtual environment...
python -m venv venv
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to create virtual environment
    echo Make sure Python is installed and in PATH
    pause
    exit /b 1
)
echo ✓ Virtual environment created
echo.

REM Step 2: Activate virtual environment and install dependencies
echo [2/5] Installing Python dependencies...
call venv\Scripts\activate.bat
pip install -r requirements.txt
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to install dependencies
    pause
    exit /b 1
)
echo ✓ Dependencies installed
echo.

REM Step 3: Test local K-means
echo [3/5] Testing local K-means implementation...
python score.py
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Local K-means test failed
    pause
    exit /b 1
)
echo ✓ Local K-means working
echo.

REM Step 4: Check Azure CLI
echo [4/5] Checking Azure CLI installation...
az --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Azure CLI not found
    echo Please install Azure CLI from AzureCLI.msi
    echo Then run this script again
    pause
    exit /b 1
)
echo ✓ Azure CLI installed
echo.

REM Step 5: Instructions
echo [5/5] Setup Complete!
echo.
echo ========================================
echo Next Steps:
echo ========================================
echo.
echo 1. Login to Azure:
echo    az login
echo.
echo 2. Get your subscription ID:
echo    az account list --output table
echo.
echo 3. Deploy to Azure ML:
echo    python deploy.py --subscription YOUR_SUBSCRIPTION_ID
echo.
echo 4. Copy the endpoint URL and key to your .env file
echo.
echo For detailed instructions, see:
echo AZURE_ML_KMEANS_IMPLEMENTATION_GUIDE.md
echo.
pause
