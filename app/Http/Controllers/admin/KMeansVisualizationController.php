<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AzureMLClusteringService;
use App\Models\Job;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KMeansVisualizationController extends Controller
{
    protected $azureMLService;

    public function __construct(AzureMLClusteringService $azureMLService)
    {
        $this->azureMLService = $azureMLService;
    }

    /**
     * Display the K-Means clustering visualization page
     */
    public function index()
    {
        $config = config('azure-ml');
        $healthCheck = $this->azureMLService->healthCheck();

        $stats = [
            'total_jobs' => Job::where('status', 1)->count(),
            'total_jobseekers' => User::where('role', 'user')->count(),
            'total_categories' => Category::where('status', 1)->count(),
        ];

        return view('admin.analytics.kmeans', [
            'stats' => $stats,
            'config' => $config,
            'healthCheck' => $healthCheck,
        ]);
    }

    /**
     * Get clustering data via AJAX
     */
    public function getData(Request $request)
    {
        $type = $request->get('type', 'job');
        $k = $request->get('k', 5);

        try {
            if ($type === 'job') {
                $result = $this->azureMLService->runJobClustering($k);
            } else {
                $result = $this->azureMLService->runUserClustering($k);
            }

            // Get cluster analysis with metrics
            $analysis = $this->azureMLService->getClusterAnalysis($type, $k);

            // Prepare visualization data
            $visualizationData = $this->prepareVisualizationData($result, $analysis, $type);

            return response()->json([
                'success' => true,
                'data' => $visualizationData,
                'source' => $result['source'] ?? 'unknown',
                'cached' => Cache::has("azure_ml_{$type}_clustering_{$k}"),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get clustering data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check endpoint
     */
    public function healthCheck()
    {
        $health = $this->azureMLService->healthCheck();

        return response()->json([
            'success' => $health['accessible'],
            'configured' => $health['configured'],
            'message' => $health['message'],
            'endpoint' => config('azure-ml.endpoint_url') ? 'Configured' : 'Not configured',
        ]);
    }

    /**
     * Refresh clusters (clear cache and recompute)
     */
    public function refreshClusters(Request $request)
    {
        $type = $request->get('type', 'job');
        $k = $request->get('k', 5);

        // Clear cache
        $this->azureMLService->clearCache();

        try {
            if ($type === 'job') {
                $result = $this->azureMLService->runJobClustering($k);
            } else {
                $result = $this->azureMLService->runUserClustering($k);
            }

            $analysis = $this->azureMLService->getClusterAnalysis($type, $k);
            $visualizationData = $this->prepareVisualizationData($result, $analysis, $type);

            return response()->json([
                'success' => true,
                'data' => $visualizationData,
                'source' => $result['source'] ?? 'unknown',
                'message' => 'Clusters refreshed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh clusters: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare data for visualization charts
     */
    protected function prepareVisualizationData($result, $analysis, $type)
    {
        $labels = $result['labels'] ?? [];
        $centroids = $result['centroids'] ?? [];
        $clusters = $result['clusters'] ?? [];

        // Calculate cluster sizes
        $clusterSizes = [];
        foreach ($clusters as $clusterId => $clusterData) {
            $clusterSizes[$clusterId] = $clusterData['size'] ?? count($clusterData['indices'] ?? []);
        }

        // Calculate metrics
        $inertia = $result['inertia'] ?? $analysis['inertia'] ?? 0;
        $silhouetteScore = $analysis['silhouette_score'] ?? null;
        $nIterations = $result['n_iterations'] ?? 0;

        // Prepare scatter plot data (2D projection of centroids)
        $scatterData = [];
        foreach ($centroids as $idx => $centroid) {
            if (is_array($centroid) && count($centroid) >= 2) {
                $scatterData[] = [
                    'x' => $centroid[0] ?? 0,
                    'y' => $centroid[1] ?? 0,
                    'cluster' => $idx,
                    'size' => $clusterSizes[$idx] ?? 0,
                ];
            }
        }

        // Get category names for job clusters
        $clusterNames = [];
        if ($type === 'job') {
            $categories = Category::where('status', 1)->pluck('name', 'id')->toArray();
            foreach ($clusters as $clusterId => $clusterData) {
                $clusterNames[$clusterId] = "Cluster " . ($clusterId + 1);
            }
        } else {
            foreach ($clusters as $clusterId => $clusterData) {
                $clusterNames[$clusterId] = "User Segment " . ($clusterId + 1);
            }
        }

        return [
            'labels' => $labels,
            'centroids' => $centroids,
            'clusters' => $clusters,
            'cluster_sizes' => array_values($clusterSizes),
            'cluster_names' => array_values($clusterNames),
            'scatter_data' => $scatterData,
            'metrics' => [
                'inertia' => round($inertia, 4),
                'silhouette_score' => $silhouetteScore ? round($silhouetteScore, 4) : null,
                'n_iterations' => $nIterations,
                'n_clusters' => count($clusters),
                'n_samples' => count($labels),
            ],
            'algorithm_info' => [
                'name' => 'K-Means Clustering',
                'source' => $result['source'] ?? 'unknown',
                'parameters' => [
                    'k' => count($clusters),
                    'max_iterations' => config('azure-ml.clustering.max_iterations', 100),
                    'algorithm' => config('azure-ml.clustering.algorithm', 'lloyd'),
                    'init_method' => config('azure-ml.clustering.init_method', 'k-means++'),
                ],
            ],
        ];
    }
}
