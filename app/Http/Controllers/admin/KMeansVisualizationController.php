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
            'total_jobseekers' => User::where('role', 'jobseeker')->count(),
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

            // Prepare visualization data with improved naming
            $visualizationData = $this->prepareVisualizationData($result, $analysis, $type, $k);

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
            $visualizationData = $this->prepareVisualizationData($result, $analysis, $type, $k);

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
     * Prepare data for visualization charts with user-friendly names
     */
    protected function prepareVisualizationData($result, $analysis, $type, $k)
    {
        $labels = $result['labels'] ?? [];
        $centroids = $result['centroids'] ?? [];
        $clusters = $result['clusters'] ?? [];

        // Calculate cluster sizes
        $clusterSizes = [];
        $totalSamples = 0;

        foreach ($clusters as $clusterId => $clusterData) {
            if (is_array($clusterData)) {
                $size = count($clusterData);
                $clusterSizes[$clusterId] = $size;
                $totalSamples += $size;
            } else {
                $clusterSizes[$clusterId] = $clusterData['size'] ?? count($clusterData['indices'] ?? []);
                $totalSamples += $clusterSizes[$clusterId];
            }
        }

        // If labels is empty but we have clusters, generate labels from cluster data
        if (empty($labels) && !empty($clusters)) {
            $labels = [];
            foreach ($clusters as $clusterId => $clusterData) {
                if (is_array($clusterData)) {
                    foreach ($clusterData as $point) {
                        $labels[] = $clusterId;
                    }
                }
            }
        }

        // Calculate metrics
        $inertia = $result['inertia'] ?? $analysis['inertia'] ?? $this->calculateInertia($clusters, $centroids);
        $silhouetteScore = $analysis['silhouette_score'] ?? null;
        $nIterations = $result['n_iterations'] ?? config('azure-ml.clustering.max_iterations', 20);

        // Prepare scatter plot data
        $scatterData = [];
        foreach ($centroids as $idx => $centroid) {
            if (is_array($centroid) && !empty($centroid)) {
                $keys = array_keys($centroid);
                $x = $centroid[$keys[0]] ?? 0;
                $y = isset($keys[1]) ? ($centroid[$keys[1]] ?? 0) : 0;

                $scatterData[] = [
                    'x' => (float) $x,
                    'y' => (float) $y,
                    'cluster' => $idx,
                    'size' => $clusterSizes[$idx] ?? 0,
                ];
            }
        }

        // Generate user-friendly cluster names
        $clusterNames = $this->generateUserFriendlyClusterNames($clusters, $centroids, $labels, $type, $k);

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
                'n_samples' => $totalSamples > 0 ? $totalSamples : count($labels),
            ],
            'algorithm_info' => [
                'name' => 'K-Means Clustering',
                'source' => $result['source'] ?? 'local_fallback',
                'parameters' => [
                    'k' => count($clusters),
                    'max_iterations' => config('azure-ml.clustering.max_iterations', 100),
                    'algorithm' => config('azure-ml.clustering.algorithm', 'lloyd'),
                    'init_method' => config('azure-ml.clustering.init_method', 'k-means++'),
                ],
            ],
        ];
    }

    /**
     * Generate user-friendly cluster names that non-technical people can understand
     */
    protected function generateUserFriendlyClusterNames($clusters, $centroids, $labels, $type, $k)
    {
        $clusterNames = [];

        if ($type === 'job') {
            $clusterNames = $this->generateJobClusterNames($clusters, $centroids, $labels, $k);
        } else {
            $clusterNames = $this->generateUserClusterNames($clusters, $centroids, $labels, $k);
        }

        return $clusterNames;
    }

    /**
     * Generate friendly names for job clusters by analyzing actual job data
     */
    protected function generateJobClusterNames($clusters, $centroids, $labels, $k)
    {
        // Get all active jobs with their categories and job types
        $jobs = Job::where('status', 1)
            ->with(['category', 'jobType'])
            ->get();

        // If no jobs or no clustering data, return generic names
        if ($jobs->isEmpty() || empty($clusters)) {
            return $this->getGenericJobClusterNames($k);
        }

        // Category friendly names
        $categoryNames = Category::where('status', 1)->pluck('name', 'id')->toArray();

        $clusterNames = [];

        foreach ($clusters as $clusterId => $clusterData) {
            // Collect job indices in this cluster
            $clusterJobIndices = [];
            if (is_array($clusterData)) {
                foreach ($clusterData as $point) {
                    if (isset($point['index'])) {
                        $clusterJobIndices[] = $point['index'];
                    }
                }
            }

            // Also check labels array
            if (!empty($labels)) {
                foreach ($labels as $index => $label) {
                    if ($label == $clusterId && !in_array($index, $clusterJobIndices)) {
                        $clusterJobIndices[] = $index;
                    }
                }
            }

            // Get centroid characteristics
            $centroid = $centroids[$clusterId] ?? [];

            // Analyze this cluster
            $analysis = $this->analyzeJobCluster($jobs, $clusterJobIndices, $centroid, $categoryNames);

            $clusterNames[$clusterId] = $analysis['name'];
        }

        // Ensure unique names
        return $this->ensureUniqueNames($clusterNames);
    }

    /**
     * Analyze a job cluster and generate a descriptive name
     */
    protected function analyzeJobCluster($jobs, $clusterJobIndices, $centroid, $categoryNames)
    {
        $jobsArray = $jobs->values();

        // Collect stats about jobs in this cluster
        $categoryCounts = [];
        $jobTypeCounts = [];
        $salaries = [];
        $remoteCounts = ['remote' => 0, 'onsite' => 0];
        $experienceLevels = [];

        // If we have specific job indices, analyze them
        if (!empty($clusterJobIndices)) {
            foreach ($clusterJobIndices as $index) {
                if (isset($jobsArray[$index])) {
                    $job = $jobsArray[$index];

                    // Category
                    $catId = $job->category_id;
                    $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;

                    // Job type
                    $typeId = $job->job_type_id;
                    $jobTypeCounts[$typeId] = ($jobTypeCounts[$typeId] ?? 0) + 1;

                    // Salary
                    $salary = $this->extractSalaryValue($job->salary_range);
                    if ($salary > 0) {
                        $salaries[] = $salary;
                    }

                    // Remote
                    if ($job->is_remote) {
                        $remoteCounts['remote']++;
                    } else {
                        $remoteCounts['onsite']++;
                    }

                    // Experience
                    $exp = $this->extractExperienceFromText($job->requirements);
                    if ($exp !== null) {
                        $experienceLevels[] = $exp;
                    }
                }
            }
        }

        // If no specific indices, use centroid to estimate
        if (empty($clusterJobIndices) || empty($categoryCounts)) {
            return $this->generateNameFromCentroid($centroid, $categoryNames);
        }

        // Build the name based on analysis
        $nameParts = [];

        // Determine dominant category
        arsort($categoryCounts);
        $dominantCatId = array_key_first($categoryCounts);
        $dominantCatName = $categoryNames[$dominantCatId] ?? 'General';

        // Determine experience level
        $avgSalary = !empty($salaries) ? array_sum($salaries) / count($salaries) : 0;
        $avgExperience = !empty($experienceLevels) ? array_sum($experienceLevels) / count($experienceLevels) : null;

        $levelLabel = $this->getSalaryLevelLabel($avgSalary, $avgExperience);

        // Determine work arrangement
        $workArrangement = '';
        if ($remoteCounts['remote'] > $remoteCounts['onsite'] * 2) {
            $workArrangement = 'Remote ';
        } elseif ($remoteCounts['onsite'] > $remoteCounts['remote'] * 2) {
            $workArrangement = 'On-site ';
        }

        // Build final name
        $name = trim($workArrangement . $levelLabel . ' ' . $this->shortenCategoryName($dominantCatName));

        return [
            'name' => $name,
            'category' => $dominantCatName,
            'level' => $levelLabel,
            'avgSalary' => $avgSalary,
        ];
    }

    /**
     * Generate name from centroid when we don't have specific job data
     */
    protected function generateNameFromCentroid($centroid, $categoryNames)
    {
        $catId = round($centroid['category_id'] ?? 0);
        $salary = $centroid['salary_normalized'] ?? $centroid['salary_range_normalized'] ?? 0;
        $isRemote = ($centroid['is_remote'] ?? 0) > 0.5;
        $experience = $centroid['experience_level'] ?? 0;

        $catName = $categoryNames[$catId] ?? 'Various';
        $levelLabel = $this->getSalaryLevelLabel($salary, $experience);
        $workArrangement = $isRemote ? 'Remote ' : '';

        $name = trim($workArrangement . $levelLabel . ' ' . $this->shortenCategoryName($catName));

        return [
            'name' => $name,
            'category' => $catName,
            'level' => $levelLabel,
            'avgSalary' => $salary,
        ];
    }

    /**
     * Get salary/experience level label
     */
    protected function getSalaryLevelLabel($salary, $experience = null)
    {
        // Based on Philippine salary standards
        if ($salary > 0) {
            if ($salary >= 60000) {
                return 'Senior';
            } elseif ($salary >= 35000) {
                return 'Mid-Level';
            } elseif ($salary >= 20000) {
                return 'Junior';
            } else {
                return 'Entry-Level';
            }
        }

        // Fallback to experience
        if ($experience !== null) {
            if ($experience >= 5) {
                return 'Senior';
            } elseif ($experience >= 3) {
                return 'Mid-Level';
            } elseif ($experience >= 1) {
                return 'Junior';
            } else {
                return 'Entry-Level';
            }
        }

        return 'Various';
    }

    /**
     * Shorten category names for cleaner display
     */
    protected function shortenCategoryName($name)
    {
        $shortcuts = [
            'Information Technology' => 'IT & Tech',
            'Business Process Outsourcing' => 'BPO',
            'Sales and Marketing' => 'Sales & Marketing',
            'Finance and Accounting' => 'Finance',
            'Administrative and Clerical' => 'Admin & Office',
            'Engineering and Architecture' => 'Engineering',
            'Healthcare and Medical' => 'Healthcare',
            'Education and Training' => 'Education',
            'Manufacturing and Production' => 'Manufacturing',
            'Retail and Consumer' => 'Retail',
            'Media and Communications' => 'Media & Comms',
            'Construction and Real Estate' => 'Construction',
            'Transportation and Logistics' => 'Logistics',
            'Legal and Compliance' => 'Legal',
            'Agriculture and Environment' => 'Agriculture',
            'Government and Public Service' => 'Government',
            'Real Estate' => 'Real Estate',
            'Tourism and Hospitality' => 'Hospitality',
            'Research and Development' => 'R&D',
            'Human Resources' => 'HR',
            'Customer Service' => 'Customer Service',
        ];

        foreach ($shortcuts as $long => $short) {
            if (stripos($name, $long) !== false || stripos($name, $short) !== false) {
                return $short . ' Jobs';
            }
        }

        // If name is already short enough
        if (strlen($name) <= 15) {
            return $name . ' Jobs';
        }

        // Shorten to first two words
        $words = explode(' ', $name);
        if (count($words) > 2) {
            return implode(' ', array_slice($words, 0, 2)) . ' Jobs';
        }

        return $name . ' Jobs';
    }

    /**
     * Extract salary value from salary range string
     */
    protected function extractSalaryValue($salaryRange)
    {
        if (empty($salaryRange)) return 0;

        preg_match_all('/[\d,]+/', str_replace(',', '', $salaryRange), $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        if (empty($numbers)) return 0;

        // Return average of range
        return count($numbers) >= 2 ? (($numbers[0] + $numbers[1]) / 2) : $numbers[0];
    }

    /**
     * Extract experience years from text
     */
    protected function extractExperienceFromText($text)
    {
        if (empty($text)) return null;

        $text = strtolower($text);

        // Try to match experience patterns
        if (preg_match('/(\d+)\s*[-to]+\s*(\d+)\s*years?/i', $text, $matches)) {
            return ($matches[1] + $matches[2]) / 2;
        }

        if (preg_match('/(\d+)\s*\+?\s*years?/i', $text, $matches)) {
            return (float) $matches[1];
        }

        // Check for keywords
        if (strpos($text, 'senior') !== false || strpos($text, 'lead') !== false) {
            return 5;
        }
        if (strpos($text, 'junior') !== false) {
            return 1;
        }
        if (strpos($text, 'entry') !== false || strpos($text, 'fresh') !== false) {
            return 0;
        }

        return null;
    }

    /**
     * Generate friendly names for user clusters
     */
    protected function generateUserClusterNames($clusters, $centroids, $labels, $k)
    {
        $users = User::where('role', 'jobseeker')
            ->with('jobSeekerProfile')
            ->get();

        if ($users->isEmpty() || empty($clusters)) {
            return $this->getGenericUserClusterNames($k);
        }

        $categoryNames = Category::where('status', 1)->pluck('name', 'id')->toArray();
        $clusterNames = [];

        foreach ($clusters as $clusterId => $clusterData) {
            $centroid = $centroids[$clusterId] ?? [];
            $name = $this->generateUserClusterName($centroid, $categoryNames);
            $clusterNames[$clusterId] = $name;
        }

        return $this->ensureUniqueNames($clusterNames);
    }

    /**
     * Generate a single user cluster name
     */
    protected function generateUserClusterName($centroid, $categoryNames)
    {
        $catId = round($centroid['category_preference'] ?? 0);
        $experience = $centroid['experience_years'] ?? 0;
        $expectedSalary = $centroid['expected_salary'] ?? 0;
        $openToRemote = ($centroid['open_to_remote'] ?? 0) > 0.5;

        // Get category name
        $catName = $categoryNames[$catId] ?? 'General';

        // Determine seeker type based on experience
        if ($experience >= 5) {
            $typeLabel = 'Experienced';
        } elseif ($experience >= 2) {
            $typeLabel = 'Mid-Career';
        } elseif ($experience >= 1) {
            $typeLabel = 'Early-Career';
        } else {
            $typeLabel = 'Fresh Graduate';
        }

        // Build name
        $catShort = $this->shortenCategoryName($catName);
        $catShort = str_replace(' Jobs', '', $catShort);

        $remotePref = $openToRemote ? 'Remote-Ready ' : '';

        return trim($remotePref . $typeLabel . ' ' . $catShort . ' Seekers');
    }

    /**
     * Get generic job cluster names when data is insufficient
     */
    protected function getGenericJobClusterNames($k)
    {
        $genericNames = [
            'Entry-Level Opportunities',
            'Mid-Career Positions',
            'Senior Roles',
            'Technical Jobs',
            'Administrative Positions',
            'Customer-Facing Roles',
            'Management Positions',
            'Creative & Design Jobs',
            'Healthcare Positions',
            'Sales Opportunities',
        ];

        $names = [];
        for ($i = 0; $i < $k; $i++) {
            $names[$i] = $genericNames[$i % count($genericNames)] ?? "Job Group " . ($i + 1);
        }

        return $names;
    }

    /**
     * Get generic user cluster names when data is insufficient
     */
    protected function getGenericUserClusterNames($k)
    {
        $genericNames = [
            'Fresh Graduates',
            'Early-Career Professionals',
            'Mid-Level Candidates',
            'Senior Professionals',
            'Career Changers',
            'Remote Workers',
            'Technical Specialists',
            'Business Professionals',
            'Creative Talents',
            'Service Industry Workers',
        ];

        $names = [];
        for ($i = 0; $i < $k; $i++) {
            $names[$i] = $genericNames[$i % count($genericNames)] ?? "Seeker Group " . ($i + 1);
        }

        return $names;
    }

    /**
     * Ensure cluster names are unique
     */
    protected function ensureUniqueNames($names)
    {
        $counts = [];
        $result = [];

        foreach ($names as $clusterId => $name) {
            // Extract name string if it's an array
            $nameStr = is_array($name) ? $name['name'] : $name;

            if (isset($counts[$nameStr])) {
                $counts[$nameStr]++;
                $result[$clusterId] = $nameStr . ' (' . $counts[$nameStr] . ')';
            } else {
                $counts[$nameStr] = 1;
                $result[$clusterId] = $nameStr;
            }
        }

        // Add (1) to first occurrence if there are duplicates
        foreach ($names as $clusterId => $name) {
            $nameStr = is_array($name) ? $name['name'] : $name;
            if ($counts[$nameStr] > 1 && $result[$clusterId] === $nameStr) {
                $result[$clusterId] = $nameStr . ' (1)';
            }
        }

        return $result;
    }

    /**
     * Calculate inertia (WCSS) from clusters and centroids
     */
    protected function calculateInertia($clusters, $centroids)
    {
        $inertia = 0;

        foreach ($clusters as $clusterId => $clusterData) {
            if (!isset($centroids[$clusterId]) || !is_array($clusterData)) {
                continue;
            }

            $centroid = $centroids[$clusterId];

            foreach ($clusterData as $point) {
                $pointData = $point['point'] ?? $point;
                if (is_array($pointData) && is_array($centroid)) {
                    $distance = 0;
                    foreach ($centroid as $key => $value) {
                        if (isset($pointData[$key])) {
                            $distance += pow($pointData[$key] - $value, 2);
                        }
                    }
                    $inertia += $distance;
                }
            }
        }

        return $inertia;
    }
}
