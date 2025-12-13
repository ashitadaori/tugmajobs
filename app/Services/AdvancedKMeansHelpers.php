<?php

namespace App\Services;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\SavedJob;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Helper methods for Advanced K-Means Clustering Service
 * This class contains all the advanced feature extraction and analysis methods
 */
trait AdvancedKMeansHelpers
{
    /**
     * Determine user's career level based on experience and other factors
     */
    protected function determineCareerLevel($user)
    {
        $experience = $user->experience_years ?? 0;
        $education = $this->analyzeEducationLevel($user);
        $skillCount = $this->countUserSkills($user);
        
        $score = 0;
        
        // Experience scoring
        if ($experience <= 1) $score += 1; // Entry level
        elseif ($experience <= 3) $score += 2; // Junior
        elseif ($experience <= 6) $score += 3; // Mid-level
        elseif ($experience <= 10) $score += 4; // Senior
        else $score += 5; // Expert/Lead
        
        // Education boost
        if ($education >= 3) $score += 1; // Graduate degree
        
        // Skills boost
        if ($skillCount > 10) $score += 1;
        
        return min(5, $score);
    }

    /**
     * Calculate industry experience across different sectors
     */
    protected function calculateIndustryExperience($user)
    {
        // Get user's job application history to analyze industry exposure
        $applications = JobApplication::where('user_id', $user->id)
            ->join('jobs', 'job_applications.job_id', '=', 'jobs.id')
            ->join('categories', 'jobs.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.name')
            ->get();
        
        $industryExp = [];
        foreach ($applications as $app) {
            $industryExp[$app->name] = $app->count;
        }
        
        return $industryExp;
    }

    /**
     * Calculate job seeking urgency based on various factors
     */
    protected function calculateJobSeekingUrgency($user)
    {
        $urgency = 1; // Base urgency
        
        // Check recent activity
        $recentApplications = JobApplication::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        if ($recentApplications > 10) $urgency += 2; // High activity
        elseif ($recentApplications > 5) $urgency += 1; // Moderate activity
        
        // Check employment status
        if (isset($user->currently_employed) && !$user->currently_employed) {
            $urgency += 2; // Unemployed users are more urgent
        }
        
        // Check profile completion (more complete = more serious)
        $profileCompletion = $this->calculateProfileCompletion($user);
        if ($profileCompletion > 0.8) $urgency += 1;
        
        return min(5, $urgency);
    }

    /**
     * Calculate user's flexibility score
     */
    protected function calculateFlexibilityScore($user)
    {
        $flexibility = 0;
        
        // Check willingness to relocate
        if (isset($user->open_to_relocation) && $user->open_to_relocation) {
            $flexibility += 2;
        }
        
        // Check openness to remote work
        if (isset($user->open_to_remote) && $user->open_to_remote) {
            $flexibility += 2;
        }
        
        // Check number of preferred categories (more = more flexible)
        $categories = $this->ensureArray($user->preferred_categories);
        if (count($categories) > 3) $flexibility += 1;
        
        // Check job types flexibility
        $jobTypes = $this->ensureArray($user->preferred_job_types);
        if (count($jobTypes) > 2) $flexibility += 1;
        
        return min(5, $flexibility);
    }

    /**
     * Calculate growth orientation score
     */
    protected function calculateGrowthOrientation($user)
    {
        $growth = 1; // Base score
        
        // Analyze career progression from applications
        $applications = $this->analyzeCareerProgression($user);
        if ($applications['upward_trend']) $growth += 2;
        
        // Check for continuous learning indicators
        $education = $this->analyzeEducationLevel($user);
        if ($education > 2) $growth += 1; // Advanced education
        
        // Check skill diversity
        $skillCount = $this->countUserSkills($user);
        if ($skillCount > 15) $growth += 1; // Diverse skill set
        
        return min(5, $growth);
    }

    /**
     * Analyze user's application patterns
     */
    protected function analyzeApplicationPatterns($user)
    {
        $applications = JobApplication::where('user_id', $user->id)
            ->with('job')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        $patterns = [
            'frequency' => $this->calculateApplicationFrequency($applications),
            'category_consistency' => $this->analyzeCategoryConsistency($applications),
            'salary_progression' => $this->analyzeSalaryProgression($applications),
            'success_rate' => $this->calculateApplicationSuccessRate($user->id)
        ];
        
        return $patterns;
    }

    /**
     * Analyze user's browsing behavior
     */
    protected function analyzeBrowsingBehavior($user)
    {
        // This would require user activity tracking
        // For now, return basic metrics based on saved jobs
        $savedJobs = SavedJob::where('user_id', $user->id)->count();
        $applications = JobApplication::where('user_id', $user->id)->count();
        
        return [
            'engagement_score' => min(5, ($savedJobs + $applications) / 10),
            'browse_to_apply_ratio' => $applications > 0 ? $savedJobs / $applications : 0,
            'activity_level' => $this->calculateActivityLevel($user)
        ];
    }

    /**
     * Calculate preference stability
     */
    protected function calculatePreferenceStability($user)
    {
        // Analyze how consistent user preferences have been over time
        // This would require tracking preference changes
        // For now, return a default stability score
        return 3; // Medium stability
    }

    /**
     * Calculate company reputation score
     */
    protected function calculateCompanyReputation($job)
    {
        $score = 3; // Default score
        
        if ($job->employer && $job->employer->employerProfile) {
            $profile = $job->employer->employerProfile;
            
            // Factor in company size
            if ($profile->company_size) {
                $size = strtolower($profile->company_size);
                if (strpos($size, 'large') !== false || strpos($size, '1000+') !== false) {
                    $score += 1;
                }
            }
            
            // Factor in years in business
            if ($profile->years_in_business) {
                if ($profile->years_in_business > 10) $score += 1;
                if ($profile->years_in_business > 20) $score += 1;
            }
            
            // Factor in industry reputation keywords
            $description = strtolower($profile->company_description ?? '');
            $reputationKeywords = ['leader', 'award', 'certified', 'recognized', 'established'];
            foreach ($reputationKeywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $score += 0.5;
                }
            }
        }
        
        return min(5, $score);
    }

    /**
     * Assess career growth potential of a job
     */
    protected function assessCareerGrowthPotential($job)
    {
        $score = 2; // Base score
        
        $text = strtolower($job->description . ' ' . $job->title . ' ' . $job->benefits);
        
        // Look for growth indicators
        $growthKeywords = [
            'career advancement' => 2,
            'promotion' => 2,
            'growth opportunity' => 2,
            'training' => 1,
            'learning' => 1,
            'development' => 1,
            'mentorship' => 1,
            'leadership' => 1,
            'senior' => 1,
            'lead' => 1
        ];
        
        foreach ($growthKeywords as $keyword => $points) {
            if (strpos($text, $keyword) !== false) {
                $score += $points;
            }
        }
        
        // Company size factor (larger companies often have more growth paths)
        if ($job->employer && $job->employer->employerProfile) {
            $companySize = strtolower($job->employer->employerProfile->company_size ?? '');
            if (strpos($companySize, 'large') !== false) {
                $score += 1;
            }
        }
        
        return min(5, $score);
    }

    /**
     * Calculate market competitiveness of a job
     */
    protected function calculateMarketCompetitiveness($job)
    {
        // Compare with similar jobs in the market
        $similarJobs = Job::where('category_id', $job->category_id)
            ->where('id', '!=', $job->id)
            ->where('status', 1)
            ->get();
        
        $score = 3; // Base competitiveness
        
        // Salary competitiveness
        $jobSalary = $this->extractSalaryRange($job->salary_range);
        $avgSalary = $this->calculateAverageSalary($similarJobs);
        
        if ($jobSalary['min'] > $avgSalary * 1.1) $score += 1; // Above market
        elseif ($jobSalary['min'] < $avgSalary * 0.9) $score -= 1; // Below market
        
        // Benefits competitiveness
        if ($job->benefits && strlen($job->benefits) > 100) {
            $score += 0.5; // Good benefits description
        }
        
        return max(1, min(5, $score));
    }

    /**
     * Assess job urgency indicators
     */
    protected function assessJobUrgency($job)
    {
        $urgency = 1; // Base urgency
        
        $text = strtolower($job->title . ' ' . $job->description);
        
        // Urgency keywords
        $urgencyKeywords = ['urgent', 'immediate', 'asap', 'quickly', 'fast track'];
        foreach ($urgencyKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $urgency += 1;
                break;
            }
        }
        
        // Deadline urgency
        if ($job->deadline && Carbon::parse($job->deadline)->diffInDays(Carbon::now()) <= 7) {
            $urgency += 2;
        }
        
        // Recent posting
        if ($job->created_at && $job->created_at->diffInDays(Carbon::now()) <= 3) {
            $urgency += 1;
        }
        
        return min(5, $urgency);
    }

    /**
     * Calculate job complexity score
     */
    protected function calculateJobComplexity($job)
    {
        $complexity = 1; // Base complexity
        
        // Analyze requirements text
        $requirements = strtolower($job->requirements ?? '');
        
        // Experience requirements
        if (preg_match('/(\d+)\s*years?/i', $requirements, $matches)) {
            $years = (int)$matches[1];
            $complexity += min(3, floor($years / 2));
        }
        
        // Skill count
        $skillCount = $this->countSkillsInText($requirements);
        $complexity += min(2, floor($skillCount / 5));
        
        // Education requirements
        if (strpos($requirements, 'degree') !== false) $complexity += 1;
        if (strpos($requirements, 'master') !== false || strpos($requirements, 'mba') !== false) {
            $complexity += 1;
        }
        
        return min(5, $complexity);
    }

    /**
     * Get job demand trend
     */
    protected function getJobDemandTrend($job)
    {
        // Analyze job posting trends in this category over time
        $currentMonth = Job::where('category_id', $job->category_id)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();
        
        $previousMonth = Job::where('category_id', $job->category_id)
            ->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth()
            ])
            ->count();
        
        if ($previousMonth == 0) return 3; // No trend data
        
        $trend = $currentMonth / $previousMonth;
        
        if ($trend > 1.2) return 5; // High demand
        elseif ($trend > 1.0) return 4; // Growing
        elseif ($trend > 0.8) return 3; // Stable
        elseif ($trend > 0.6) return 2; // Declining
        else return 1; // Low demand
    }

    /**
     * Assess salary competitiveness
     */
    protected function assessSalaryCompetitiveness($job)
    {
        $jobSalary = $this->extractSalaryRange($job->salary_range);
        if ($jobSalary['min'] == 0) return 3; // No salary info
        
        // Get market average for similar jobs
        $marketAvg = $this->getMarketAverageSalary($job->category_id, $job->experience);
        
        if ($marketAvg == 0) return 3; // No market data
        
        $ratio = $jobSalary['min'] / $marketAvg;
        
        if ($ratio >= 1.2) return 5; // Very competitive
        elseif ($ratio >= 1.1) return 4; // Competitive
        elseif ($ratio >= 0.9) return 3; // Market rate
        elseif ($ratio >= 0.8) return 2; // Below market
        else return 1; // Poor
    }

    /**
     * Calculate application competition
     */
    protected function calculateApplicationCompetition($job)
    {
        $applicationCount = JobApplication::where('job_id', $job->id)->count();
        
        // Normalize based on job age
        $daysOld = max(1, $job->created_at->diffInDays(Carbon::now()));
        $applicationsPerDay = $applicationCount / $daysOld;
        
        if ($applicationsPerDay > 10) return 5; // Very high competition
        elseif ($applicationsPerDay > 5) return 4; // High competition
        elseif ($applicationsPerDay > 2) return 3; // Moderate competition
        elseif ($applicationsPerDay > 1) return 2; // Low competition
        else return 1; // Very low competition
    }

    /**
     * Extract experience level from job requirements using NLP
     */
    protected function extractExperienceLevel($requirements)
    {
        if (empty($requirements)) return 0;
        
        $text = strtolower($requirements);
        
        // Look for explicit year requirements
        if (preg_match('/(\d+)\s*(?:to|-)?\s*(\d+)?\s*(?:\+)?\s*years?\s*(?:of\s*)?(?:experience|exp)/i', $requirements, $matches)) {
            if (isset($matches[2]) && $matches[2]) {
                return (int)(($matches[1] + $matches[2]) / 2);
            } else {
                return (int)$matches[1];
            }
        }
        
        // Look for experience level keywords
        if (strpos($text, 'entry level') !== false || strpos($text, 'fresh grad') !== false) {
            return 0;
        } elseif (strpos($text, 'junior') !== false) {
            return 2;
        } elseif (strpos($text, 'senior') !== false) {
            return 5;
        } elseif (strpos($text, 'lead') !== false || strpos($text, 'principal') !== false) {
            return 7;
        } elseif (strpos($text, 'manager') !== false || strpos($text, 'head of') !== false) {
            return 8;
        }
        
        return 3; // Default mid-level
    }

    /**
     * Find similar users based on preferences and behavior
     */
    protected function findSimilarUsers($targetUser, $limit = 10)
    {
        $users = User::where('role', 'jobseeker')
            ->where('id', '!=', $targetUser->id)
            ->get();
        
        $similarities = [];
        
        foreach ($users as $user) {
            $similarity = $this->calculateUserSimilarity($targetUser, $user);
            if ($similarity > 0.3) { // Minimum similarity threshold
                $similarities[] = ['user' => $user, 'similarity' => $similarity];
            }
        }
        
        // Sort by similarity and return top N
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        return collect(array_slice($similarities, 0, $limit));
    }

    /**
     * Calculate similarity between two users
     */
    protected function calculateUserSimilarity($user1, $user2)
    {
        $similarity = 0;
        $factors = 0;
        
        // Category preferences similarity
        $cats1 = $this->ensureArray($user1->preferred_categories);
        $cats2 = $this->ensureArray($user2->preferred_categories);
        if (!empty($cats1) && !empty($cats2)) {
            $intersection = count(array_intersect($cats1, $cats2));
            $union = count(array_unique(array_merge($cats1, $cats2)));
            $similarity += $union > 0 ? $intersection / $union : 0;
            $factors++;
        }
        
        // Experience similarity
        $exp1 = $user1->experience_years ?? 0;
        $exp2 = $user2->experience_years ?? 0;
        if ($exp1 > 0 && $exp2 > 0) {
            $expSim = 1 - (abs($exp1 - $exp2) / max($exp1, $exp2));
            $similarity += $expSim;
            $factors++;
        }
        
        // Location similarity
        if ($user1->preferred_location && $user2->preferred_location) {
            $locSim = strtolower($user1->preferred_location) === strtolower($user2->preferred_location) ? 1 : 0;
            $similarity += $locSim;
            $factors++;
        }
        
        return $factors > 0 ? $similarity / $factors : 0;
    }

    /**
     * Get user job interactions (applications and saves)
     */
    protected function getUserJobInteractions($user)
    {
        $applications = JobApplication::where('user_id', $user->id)
            ->join('jobs', 'job_applications.job_id', '=', 'jobs.id')
            ->select('jobs.*', DB::raw('2 as interaction_weight')) // Applications are stronger signal
            ->get();
        
        $savedJobs = SavedJob::where('user_id', $user->id)
            ->join('jobs', 'saved_jobs.job_id', '=', 'jobs.id')
            ->select('jobs.*', DB::raw('1 as interaction_weight')) // Saves are weaker signal
            ->get();
        
        return $applications->merge($savedJobs);
    }

    // Additional helper methods...
    
    protected function analyzeEducationLevel($user)
    {
        // Analyze education level from user profile
        // This would need to be implemented based on your user profile structure
        return 2; // Default: Bachelor's degree
    }
    
    protected function countUserSkills($user)
    {
        if (!$user->skills) return 0;
        $skills = $this->ensureArray($user->skills);
        return count($skills);
    }
    
    protected function calculateProfileCompletion($user)
    {
        $fields = ['name', 'email', 'preferred_categories', 'experience_years', 'skills'];
        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($user->$field)) $completed++;
        }
        return $completed / count($fields);
    }
    
    protected function analyzeCareerProgression($user)
    {
        // Analyze if user is applying for progressively better positions
        return ['upward_trend' => true]; // Placeholder
    }
    
    protected function calculateApplicationFrequency($applications)
    {
        if ($applications->count() < 2) return 0;
        
        $timeSpan = $applications->first()->created_at->diffInDays($applications->last()->created_at);
        return $timeSpan > 0 ? $applications->count() / $timeSpan : 0;
    }
    
    protected function analyzeCategoryConsistency($applications)
    {
        $categories = $applications->pluck('job.category_id')->unique();
        return $applications->count() > 0 ? 1 - ($categories->count() / $applications->count()) : 0;
    }
    
    protected function analyzeSalaryProgression($applications)
    {
        // Analyze if user is applying for jobs with increasing salaries
        return 0.5; // Placeholder
    }
    
    protected function calculateApplicationSuccessRate($userId)
    {
        $total = JobApplication::where('user_id', $userId)->count();
        $successful = JobApplication::where('user_id', $userId)
            ->where('status', 'accepted')->count();
        
        return $total > 0 ? $successful / $total : 0;
    }
    
    protected function calculateActivityLevel($user)
    {
        $recentActivity = JobApplication::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        if ($recentActivity > 10) return 5;
        elseif ($recentActivity > 5) return 4;
        elseif ($recentActivity > 2) return 3;
        elseif ($recentActivity > 0) return 2;
        else return 1;
    }
    
    protected function calculateAverageSalary($jobs)
    {
        $salaries = [];
        foreach ($jobs as $job) {
            $salary = $this->extractSalaryRange($job->salary_range);
            if ($salary['min'] > 0) {
                $salaries[] = ($salary['min'] + $salary['max']) / 2;
            }
        }
        
        return count($salaries) > 0 ? array_sum($salaries) / count($salaries) : 0;
    }
    
    protected function countSkillsInText($text)
    {
        $skillCount = 0;
        foreach ($this->skillsDictionary as $category => $skillGroups) {
            foreach ($skillGroups as $primary => $related) {
                if (strpos($text, $primary) !== false) $skillCount++;
                foreach ($related as $skill) {
                    if (strpos($text, $skill) !== false) $skillCount++;
                }
            }
        }
        return $skillCount;
    }
    
    protected function getMarketAverageSalary($categoryId, $experience)
    {
        $jobs = Job::where('category_id', $categoryId)
            ->where('status', 1)
            ->get();
        
        return $this->calculateAverageSalary($jobs);
    }
}
