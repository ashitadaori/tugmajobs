<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JobContentAnalyzerService;
use App\Models\Job;

class AnalyzeJobContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:analyze-content
                            {--mode=unanalyzed : Mode: all, unanalyzed, stale, single}
                            {--job= : Job ID for single mode}
                            {--hours=24 : Hours threshold for stale mode}
                            {--force : Force re-analysis even if already analyzed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze job content to infer categories, extract skills, and detect mismatches';

    protected JobContentAnalyzerService $analyzer;

    public function __construct()
    {
        parent::__construct();
        $this->analyzer = new JobContentAnalyzerService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->option('mode');
        $jobId = $this->option('job');
        $hours = (int) $this->option('hours');
        $force = $this->option('force');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║         JOB CONTENT ANALYSIS - K-MEANS ENHANCED          ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');

        if ($mode === 'single') {
            return $this->analyzeSingleJob($jobId);
        }

        return $this->analyzeMultipleJobs($mode, $hours, $force);
    }

    /**
     * Analyze a single job
     */
    protected function analyzeSingleJob(?string $jobId): int
    {
        if (!$jobId) {
            $this->error('Please provide a job ID with --job=ID');
            return 1;
        }

        $job = Job::with(['category', 'employer.employerProfile'])->find($jobId);

        if (!$job) {
            $this->error("Job with ID {$jobId} not found.");
            return 1;
        }

        $this->info("Analyzing job: {$job->title} (ID: {$job->id})");
        $this->info("Employer's category: {$job->category->name}");
        $this->info('');

        $analyzedJob = $this->analyzer->analyzeJob($job);

        $this->displayJobAnalysis($analyzedJob);

        return 0;
    }

    /**
     * Analyze multiple jobs
     */
    protected function analyzeMultipleJobs(string $mode, int $hours, bool $force): int
    {
        $this->info("Mode: {$mode}");

        // Get job count first
        $query = Job::where('status', 1);

        switch ($mode) {
            case 'all':
                $this->info('Analyzing ALL active jobs...');
                break;

            case 'stale':
                $query->where(function($q) use ($hours) {
                    $q->whereNull('content_analyzed_at')
                      ->orWhere('content_analyzed_at', '<', now()->subHours($hours));
                });
                $this->info("Analyzing jobs not analyzed in the last {$hours} hours...");
                break;

            case 'unanalyzed':
            default:
                $query->whereNull('content_analyzed_at');
                $this->info('Analyzing jobs that have never been analyzed...');
                break;
        }

        $totalJobs = $query->count();

        if ($totalJobs === 0) {
            $this->info('');
            $this->info('✓ No jobs to analyze. All jobs are up to date!');
            return 0;
        }

        $this->info("Found {$totalJobs} jobs to analyze.");
        $this->info('');

        if (!$this->confirm("Do you want to proceed with analyzing {$totalJobs} jobs?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Process in chunks with progress bar
        $bar = $this->output->createProgressBar($totalJobs);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $bar->start();

        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'mismatches_found' => 0
        ];

        $query->chunk(50, function($jobs) use ($bar, &$results) {
            foreach ($jobs as $job) {
                try {
                    $analyzedJob = $this->analyzer->analyzeJob($job);

                    $results['total']++;

                    if ($analyzedJob->content_analyzed_at) {
                        $results['success']++;

                        if ($analyzedJob->has_category_mismatch) {
                            $results['mismatches_found']++;
                        }
                    } else {
                        $results['failed']++;
                    }
                } catch (\Exception $e) {
                    $results['total']++;
                    $results['failed']++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info('');
        $this->info('');

        // Display results
        $this->displayResults($results);

        return 0;
    }

    /**
     * Display single job analysis
     */
    protected function displayJobAnalysis(Job $job): void
    {
        $this->info('┌────────────────────────────────────────────────────────────┐');
        $this->info('│                    ANALYSIS RESULTS                        │');
        $this->info('└────────────────────────────────────────────────────────────┘');
        $this->info('');

        // Primary inferred category
        $this->info('📊 PRIMARY INFERRED CATEGORY:');
        $this->info("   Category: {$job->primary_inferred_category}");
        $this->info("   Score: " . round($job->primary_inferred_score * 100, 1) . "%");
        $this->info('');

        // Category mismatch
        if ($job->has_category_mismatch) {
            $this->warn('⚠️  CATEGORY MISMATCH DETECTED!');
            $this->warn("   Employer selected: {$job->category->name}");
            $this->warn("   Content suggests: {$job->primary_inferred_category}");
            $this->info('');
        } else {
            $this->info('✓ No category mismatch detected.');
            $this->info('');
        }

        // Detected role type
        $this->info('👤 DETECTED ROLE TYPE:');
        $this->info("   {$job->detected_role_type}");
        $this->info('');

        // Extracted skills
        $this->info('🔧 EXTRACTED SKILLS:');
        $skills = $job->extracted_skills ?? [];
        if (!empty($skills)) {
            foreach (array_slice($skills, 0, 10) as $skill) {
                $this->info("   • {$skill}");
            }
            if (count($skills) > 10) {
                $this->info("   ... and " . (count($skills) - 10) . " more");
            }
        } else {
            $this->info('   No skills extracted');
        }
        $this->info('');

        // Inferred categories
        $this->info('📈 TOP INFERRED CATEGORIES:');
        $inferredCategories = $job->inferred_categories ?? [];
        $count = 0;
        foreach ($inferredCategories as $key => $data) {
            if ($count >= 5) break;
            $score = round($data['score'] * 100, 1);
            $confidence = $data['confidence'] ?? 'unknown';
            $this->info("   {$key}: {$score}% ({$confidence})");
            $count++;
        }
        $this->info('');

        $this->info("✓ Analysis completed at: {$job->content_analyzed_at}");
    }

    /**
     * Display batch results
     */
    protected function displayResults(array $results): void
    {
        $this->info('┌────────────────────────────────────────────────────────────┐');
        $this->info('│                    ANALYSIS COMPLETE                       │');
        $this->info('└────────────────────────────────────────────────────────────┘');
        $this->info('');

        $this->info("📊 SUMMARY:");
        $this->info("   Total jobs processed: {$results['total']}");
        $this->info("   ✓ Successfully analyzed: {$results['success']}");

        if ($results['failed'] > 0) {
            $this->error("   ✗ Failed: {$results['failed']}");
        }

        $this->info('');
        $this->info("🔍 MISMATCH DETECTION:");
        $this->info("   Category mismatches found: {$results['mismatches_found']}");

        if ($results['success'] > 0) {
            $mismatchRate = round(($results['mismatches_found'] / $results['success']) * 100, 1);
            $this->info("   Mismatch rate: {$mismatchRate}%");
        }

        $this->info('');

        if ($results['mismatches_found'] > 0) {
            $this->warn('⚠️  Some jobs have category mismatches. Review them at:');
            $this->warn('   GET /api/enhanced-recommendations/admin/category-mismatches');
        }

        $this->info('');
        $this->info('✓ Done! Jobs are now ready for enhanced K-means clustering.');
    }
}
