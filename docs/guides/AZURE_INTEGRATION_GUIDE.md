# Azure K-Means Integration Guide

This guide details the steps to deploy the K-Means clustering model to Azure Machine Learning and integrate it with your Laravel application.

## Prerequisites

1.  **Azure Account**: You need an active Azure subscription.
2.  **Azure CLI**: [Install Azure CLI](https://docs.microsoft.com/cli/azure/install-azure-cli)
3.  **Python 3.8+**: Ensure Python is installed.

## Step 1: Deploy to Azure ML

The project includes a deployment script in the `azure-ml` directory.

1.  Open a terminal in your project root.
2.  Navigate to the directory:
    ```bash
    cd azure-ml
    ```
3.  Login to Azure:
    ```bash
    az login
    ```
4.  Run the deployment script (replace `<subscription-id>` with your Azure Subscription ID):
    ```bash
    python deploy.py --subscription <your-subscription-id> --resource-group AzureML-RG --workspace tugmajobs
    ```

    *Note: This script will verify prerequisites, create the workspace (if needed), create the endpoint, and deploy the model.*

5.  **Save the Output**: Upon success, the script will output configuration lines like this:
    ```
    ============================================================
    ADD THESE TO YOUR .env FILE:
    ============================================================
    AZURE_ML_ENDPOINT_URL=https://tugmajobs-kmeans...azure.com/score
    AZURE_ML_ENDPOINT_KEY=your_authentication_key_here
    ============================================================
    ```

## Step 2: Configure Application

1.  Open your `.env` file in the project root.
2.  Add or update the following keys with the values from Step 1:

    ```env
    # Azure Machine Learning Configuration
    AZURE_ML_ENDPOINT_URL=https://your-endpoint-url
    AZURE_ML_ENDPOINT_KEY=your-endpoint-key
    
    # Optional: Configure behavior
    AZURE_ML_FALLBACK_ENABLED=true
    AZURE_ML_CACHE_ENABLED=true
    ```

    ### Where to find these in Azure Portal?
    If you did not use the deployment script or lost the output, follow these steps:
    
    1.  Log in to [Azure Portal](https://portal.azure.com).
    2.  Search for and select **Azure Machine Learning**.
    3.  Select your workspace (e.g., `tugmajobs`).
    4.  Click **Launch Studio** to open the Azure ML Studio.
    5.  In the left sidebar of the Studio, click **Endpoints**.
    6.  Click on the endpoint named `kmeans-clustering-endpoint`.
    7.  Go to the **Consume** tab.
    8.  Copy the **REST endpoint** (this is your `AZURE_ML_ENDPOINT_URL`).
    9.  Copy the **Primary key** (this is your `AZURE_ML_ENDPOINT_KEY`).

## Step 3: Test Integration

We have created a specific test command to verify the Azure connection.

1.  Run the test command:
    ```bash
    php artisan test:azure-kmeans
    ```

2.  **Verify Output**:
    *   **Success**: You should see source indicated as `☁️ AZURE_ML`.
    *   **Fallback**: If you see `💻 LOCAL_FALLBACK`, check your `.env` configuration and ensure the Endpoint URL is reachable.

## Troubleshooting

*   **Connection Timeout**: If the test hangs or fails after 30 seconds, Azure cold starts might be the cause. Try running it again.
*   **Authentication Failed**: Double-check the `AZURE_ML_ENDPOINT_KEY`.
*   **Model Error**: Check the Azure ML Studio logs for the deployed endpoint to see if the python script is crashing.
