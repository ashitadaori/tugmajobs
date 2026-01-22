"""
Azure ML K-Means Clustering Scoring Script
==========================================

This script is deployed as an Azure ML managed online endpoint for K-means clustering.
It receives job/user feature data and returns cluster assignments and centroids.

Deployment: Azure ML Managed Online Endpoint
Model: scikit-learn KMeans
"""

import json
import numpy as np
import logging
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, MinMaxScaler, RobustScaler
from sklearn.metrics import silhouette_score

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def init():
    """
    Initialize the scoring script.
    Called once when the endpoint is deployed.
    """
    global scalers
    scalers = {
        'standard': StandardScaler(),
        'minmax': MinMaxScaler(),
        'robust': RobustScaler()
    }
    logger.info("K-Means Clustering endpoint initialized")


def run(raw_data):
    """
    Run K-means clustering on the provided data.

    Expected input JSON format:
    {
        "data": [
            {"feature1": value1, "feature2": value2, ...},
            ...
        ],
        "k": 5,
        "max_iterations": 100,
        "tolerance": 0.0001,
        "algorithm": "lloyd",  # lloyd, elkan, auto
        "init_method": "k-means++",  # k-means++, random
        "scaling": {
            "enabled": true,
            "method": "standard"  # standard, minmax, robust
        },
        "include_metrics": false
    }

    Returns:
    {
        "labels": [0, 1, 2, ...],
        "centroids": [[...], [...], ...],
        "clusters": {
            "0": {"indices": [...], "size": n},
            ...
        },
        "inertia": float,
        "silhouette_score": float (if include_metrics)
    }
    """
    try:
        # Parse input
        request = json.loads(raw_data)

        # Extract parameters
        data = request.get('data', [])
        k = request.get('k', 5)
        max_iterations = request.get('max_iterations', 100)
        tolerance = request.get('tolerance', 0.0001)
        algorithm = request.get('algorithm', 'lloyd')
        init_method = request.get('init_method', 'k-means++')
        scaling_config = request.get('scaling', {'enabled': True, 'method': 'standard'})
        include_metrics = request.get('include_metrics', False)

        if not data:
            return json.dumps({
                'error': 'No data provided',
                'labels': [],
                'centroids': [],
                'clusters': {}
            })

        # Convert data to numpy array
        # Handle both list of dicts and list of lists
        if isinstance(data[0], dict):
            # Extract feature names (excluding id fields)
            feature_names = [key for key in data[0].keys()
                           if key not in ['id', 'user_id', 'job_id', 'index']]
            X = np.array([[float(item.get(f, 0)) for f in feature_names] for item in data])
        else:
            X = np.array(data, dtype=float)

        # Handle edge cases
        n_samples = len(X)
        if n_samples < k:
            k = max(1, n_samples)
            logger.warning(f"Adjusted k to {k} due to small sample size")

        # Apply scaling if enabled
        if scaling_config.get('enabled', True):
            scaler_method = scaling_config.get('method', 'standard')
            scaler = scalers.get(scaler_method, StandardScaler())
            X_scaled = scaler.fit_transform(X)
        else:
            X_scaled = X

        # Handle NaN/Inf values
        X_scaled = np.nan_to_num(X_scaled, nan=0.0, posinf=0.0, neginf=0.0)

        # Map algorithm names
        algo_map = {
            'lloyd': 'lloyd',
            'elkan': 'elkan',
            'auto': 'lloyd'  # Default to lloyd for stability
        }
        sklearn_algorithm = algo_map.get(algorithm, 'lloyd')

        # Map init method
        init_map = {
            'k-means++': 'k-means++',
            'random': 'random'
        }
        sklearn_init = init_map.get(init_method, 'k-means++')

        # Run K-means clustering
        kmeans = KMeans(
            n_clusters=k,
            max_iter=max_iterations,
            tol=tolerance,
            algorithm=sklearn_algorithm,
            init=sklearn_init,
            n_init=10,
            random_state=42
        )

        labels = kmeans.fit_predict(X_scaled)

        # Get centroids (inverse transform if scaled)
        if scaling_config.get('enabled', True):
            centroids = scaler.inverse_transform(kmeans.cluster_centers_)
        else:
            centroids = kmeans.cluster_centers_

        # Build cluster information
        clusters = {}
        for i in range(k):
            cluster_indices = np.where(labels == i)[0].tolist()
            clusters[str(i)] = {
                'indices': cluster_indices,
                'size': len(cluster_indices)
            }

        # Prepare response
        response = {
            'labels': labels.tolist(),
            'centroids': centroids.tolist(),
            'clusters': clusters,
            'inertia': float(kmeans.inertia_),
            'n_iterations': int(kmeans.n_iter_)
        }

        # Calculate additional metrics if requested
        if include_metrics and n_samples > k:
            try:
                sil_score = silhouette_score(X_scaled, labels)
                response['silhouette_score'] = float(sil_score)
            except Exception as e:
                logger.warning(f"Could not calculate silhouette score: {e}")
                response['silhouette_score'] = None

        logger.info(f"Clustering completed: k={k}, samples={n_samples}, inertia={kmeans.inertia_:.2f}")

        return json.dumps(response)

    except Exception as e:
        logger.error(f"Clustering error: {str(e)}")
        return json.dumps({
            'error': str(e),
            'labels': [],
            'centroids': [],
            'clusters': {}
        })


# For local testing
if __name__ == "__main__":
    # Initialize
    init()

    # Test data
    test_data = {
        "data": [
            {"category_id": 1.0, "job_type_id": 2.0, "salary": 50000.0, "experience": 3.0},
            {"category_id": 1.0, "job_type_id": 2.0, "salary": 55000.0, "experience": 4.0},
            {"category_id": 2.0, "job_type_id": 1.0, "salary": 40000.0, "experience": 2.0},
            {"category_id": 2.0, "job_type_id": 1.0, "salary": 42000.0, "experience": 2.5},
            {"category_id": 3.0, "job_type_id": 3.0, "salary": 80000.0, "experience": 7.0},
            {"category_id": 3.0, "job_type_id": 3.0, "salary": 85000.0, "experience": 8.0},
        ],
        "k": 3,
        "max_iterations": 100,
        "include_metrics": True,
        "scaling": {"enabled": True, "method": "standard"}
    }

    result = run(json.dumps(test_data))
    print("Test Result:")
    print(json.dumps(json.loads(result), indent=2))
