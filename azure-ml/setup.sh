#!/bin/bash
# Azure ML K-Means Clustering Setup Script for Mac/Linux
# =======================================================

echo ""
echo "========================================"
echo "Azure ML K-Means Setup"
echo "========================================"
echo ""

# Step 1: Create virtual environment
echo "[1/5] Creating Python virtual environment..."
python3 -m venv venv
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to create virtual environment"
    echo "Make sure Python 3 is installed"
    exit 1
fi
echo "✓ Virtual environment created"
echo ""

# Step 2: Activate virtual environment and install dependencies
echo "[2/5] Installing Python dependencies..."
source venv/bin/activate
pip install -r requirements.txt
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to install dependencies"
    exit 1
fi
echo "✓ Dependencies installed"
echo ""

# Step 3: Test local K-means
echo "[3/5] Testing local K-means implementation..."
python score.py
if [ $? -ne 0 ]; then
    echo "ERROR: Local K-means test failed"
    exit 1
fi
echo "✓ Local K-means working"
echo ""

# Step 4: Check Azure CLI
echo "[4/5] Checking Azure CLI installation..."
if ! command -v az &> /dev/null; then
    echo "WARNING: Azure CLI not found"
    echo "Please install Azure CLI:"
    echo "  Mac: brew install azure-cli"
    echo "  Linux: curl -sL https://aka.ms/InstallAzureCLIDeb | sudo bash"
    echo "Then run this script again"
    exit 1
fi
echo "✓ Azure CLI installed"
echo ""

# Step 5: Instructions
echo "[5/5] Setup Complete!"
echo ""
echo "========================================"
echo "Next Steps:"
echo "========================================"
echo ""
echo "1. Login to Azure:"
echo "   az login"
echo ""
echo "2. Get your subscription ID:"
echo "   az account list --output table"
echo ""
echo "3. Deploy to Azure ML:"
echo "   python deploy.py --subscription YOUR_SUBSCRIPTION_ID"
echo ""
echo "4. Copy the endpoint URL and key to your .env file"
echo ""
echo "For detailed instructions, see:"
echo "AZURE_ML_KMEANS_IMPLEMENTATION_GUIDE.md"
echo ""
