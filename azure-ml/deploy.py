"""
Azure ML Deployment Script
==========================

This script deploys the K-Means clustering model to Azure ML as a managed online endpoint.

Prerequisites:
1. Azure CLI installed and logged in
2. Azure ML CLI extension installed: az extension add -n ml
3. Azure ML workspace created

Usage:
    python deploy.py --workspace tugmajobs --resource-group AzureML-RG --subscription <sub-id>
"""

import argparse
import subprocess
import json
import os
import sys


def run_command(cmd, capture_output=True):
    """Run a shell command and return output."""
    print(f"Running: {cmd}")
    result = subprocess.run(cmd, shell=True, capture_output=capture_output, text=True)
    if result.returncode != 0:
        print(f"Error: {result.stderr}")
        return None
    return result.stdout.strip() if capture_output else True


def check_prerequisites():
    """Check if Azure CLI and ML extension are installed."""
    print("Checking prerequisites...")

    # Check Azure CLI
    if not run_command("az --version"):
        print("ERROR: Azure CLI not installed. Install from: https://docs.microsoft.com/cli/azure/install-azure-cli")
        return False

    # Check ML extension
    extensions = run_command("az extension list --query \"[].name\" -o tsv")
    if extensions and "ml" not in extensions:
        print("Installing Azure ML extension...")
        run_command("az extension add -n ml -y")

    # Check login status
    account = run_command("az account show")
    if not account:
        print("ERROR: Not logged in to Azure. Run: az login")
        return False

    print("Prerequisites check passed!")
    return True


def set_workspace(subscription_id, resource_group, workspace_name):
    """Set the default Azure ML workspace."""
    print(f"\nConfiguring workspace: {workspace_name}")

    # Set subscription
    run_command(f"az account set --subscription {subscription_id}")

    # Set defaults for ML commands
    run_command(f"az configure --defaults group={resource_group} workspace={workspace_name}")

    return True


def create_endpoint(workspace_name, resource_group):
    """Create the managed online endpoint."""
    print("\nCreating managed online endpoint...")

    # Check if endpoint already exists
    existing = run_command(f"az ml online-endpoint show --name kmeans-clustering-endpoint -g {resource_group} -w {workspace_name} 2>/dev/null")

    if existing:
        print("Endpoint already exists, skipping creation.")
        return True

    # Create endpoint
    result = run_command(f"az ml online-endpoint create --file endpoint.yml -g {resource_group} -w {workspace_name}")
    if result is None:
        print("Failed to create endpoint")
        return False

    print("Endpoint created successfully!")
    return True


def create_deployment(workspace_name, resource_group):
    """Create the deployment on the endpoint."""
    print("\nCreating deployment...")

    result = run_command(
        f"az ml online-deployment create --file deployment.yml -g {resource_group} -w {workspace_name} --all-traffic"
    )

    if result is None:
        print("Failed to create deployment")
        return False

    print("Deployment created successfully!")
    return True


def get_endpoint_info(workspace_name, resource_group):
    """Get endpoint URL and key."""
    print("\nRetrieving endpoint information...")

    # Get endpoint details
    endpoint_json = run_command(
        f"az ml online-endpoint show --name kmeans-clustering-endpoint -g {resource_group} -w {workspace_name} -o json"
    )

    if not endpoint_json:
        return None, None

    endpoint = json.loads(endpoint_json)
    scoring_uri = endpoint.get('scoring_uri', '')

    # Get endpoint key
    keys_json = run_command(
        f"az ml online-endpoint get-credentials --name kmeans-clustering-endpoint -g {resource_group} -w {workspace_name} -o json"
    )

    if not keys_json:
        return scoring_uri, None

    keys = json.loads(keys_json)
    primary_key = keys.get('primaryKey', '')

    return scoring_uri, primary_key


def test_endpoint(scoring_uri, api_key):
    """Test the deployed endpoint."""
    print("\nTesting endpoint...")

    test_data = {
        "data": [
            {"category_id": 1.0, "job_type_id": 2.0, "salary": 50000.0, "experience": 3.0},
            {"category_id": 1.0, "job_type_id": 2.0, "salary": 55000.0, "experience": 4.0},
            {"category_id": 2.0, "job_type_id": 1.0, "salary": 40000.0, "experience": 2.0},
        ],
        "k": 2,
        "include_metrics": True
    }

    import urllib.request
    import ssl

    body = json.dumps(test_data).encode('utf-8')
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {api_key}'
    }

    req = urllib.request.Request(scoring_uri, body, headers)

    try:
        context = ssl.create_default_context()
        response = urllib.request.urlopen(req, context=context)
        result = json.loads(response.read().decode('utf-8'))
        print("Test successful!")
        print(f"Result: {json.dumps(result, indent=2)}")
        return True
    except Exception as e:
        print(f"Test failed: {e}")
        return False


def generate_env_config(scoring_uri, api_key):
    """Generate environment configuration for Laravel."""
    print("\n" + "=" * 60)
    print("ADD THESE TO YOUR .env FILE:")
    print("=" * 60)
    print(f"AZURE_ML_ENDPOINT_URL={scoring_uri}")
    print(f"AZURE_ML_ENDPOINT_KEY={api_key}")
    print("=" * 60)


def main():
    parser = argparse.ArgumentParser(description='Deploy K-Means Clustering to Azure ML')
    parser.add_argument('--workspace', '-w', default='tugmajobs', help='Azure ML workspace name')
    parser.add_argument('--resource-group', '-g', default='AzureML-RG', help='Azure resource group')
    parser.add_argument('--subscription', '-s', required=True, help='Azure subscription ID')
    parser.add_argument('--test-only', action='store_true', help='Only test existing endpoint')

    args = parser.parse_args()

    # Change to script directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    os.chdir(script_dir)

    if not check_prerequisites():
        sys.exit(1)

    if not set_workspace(args.subscription, args.resource_group, args.workspace):
        sys.exit(1)

    if not args.test_only:
        if not create_endpoint(args.workspace, args.resource_group):
            sys.exit(1)

        if not create_deployment(args.workspace, args.resource_group):
            sys.exit(1)

    scoring_uri, api_key = get_endpoint_info(args.workspace, args.resource_group)

    if not scoring_uri:
        print("ERROR: Could not retrieve endpoint information")
        sys.exit(1)

    print(f"\nEndpoint URL: {scoring_uri}")
    print(f"API Key: {api_key[:20]}..." if api_key else "API Key: Not available")

    if scoring_uri and api_key:
        test_endpoint(scoring_uri, api_key)
        generate_env_config(scoring_uri, api_key)

    print("\nDeployment complete!")


if __name__ == "__main__":
    main()
