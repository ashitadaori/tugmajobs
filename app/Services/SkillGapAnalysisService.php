<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Skill Gap Analysis Service
 *
 * Provides detailed analysis of skill gaps between jobseekers and job requirements,
 * along with personalized recommendations for skill development.
 */
class SkillGapAnalysisService
{
    protected ContentAnalysisService $contentAnalysis;

    /**
     * Skill learning resources and difficulty levels
     */
    protected array $skillMetadata = [
        // Programming Languages
        'php' => [
            'category' => 'programming',
            'difficulty' => 'intermediate',
            'learning_time' => '3-6 months',
            'resources' => ['PHP.net', 'Laracasts', 'PHP The Right Way'],
            'prerequisites' => ['html', 'css', 'basic programming concepts'],
            'related_skills' => ['laravel', 'mysql', 'composer']
        ],
        'javascript' => [
            'category' => 'programming',
            'difficulty' => 'intermediate',
            'learning_time' => '3-6 months',
            'resources' => ['MDN Web Docs', 'JavaScript.info', 'freeCodeCamp'],
            'prerequisites' => ['html', 'css'],
            'related_skills' => ['react', 'vue', 'nodejs', 'typescript']
        ],
        'python' => [
            'category' => 'programming',
            'difficulty' => 'beginner',
            'learning_time' => '2-4 months',
            'resources' => ['Python.org', 'Codecademy', 'Real Python'],
            'prerequisites' => ['basic programming concepts'],
            'related_skills' => ['django', 'flask', 'pandas', 'numpy']
        ],
        'java' => [
            'category' => 'programming',
            'difficulty' => 'intermediate',
            'learning_time' => '4-6 months',
            'resources' => ['Oracle Java Tutorials', 'Codecademy', 'Baeldung'],
            'prerequisites' => ['basic programming concepts', 'oop'],
            'related_skills' => ['spring', 'maven', 'gradle']
        ],
        'react' => [
            'category' => 'frontend',
            'difficulty' => 'intermediate',
            'learning_time' => '2-4 months',
            'resources' => ['React.dev', 'Scrimba', 'Egghead.io'],
            'prerequisites' => ['javascript', 'html', 'css'],
            'related_skills' => ['redux', 'nextjs', 'typescript']
        ],
        'laravel' => [
            'category' => 'framework',
            'difficulty' => 'intermediate',
            'learning_time' => '2-3 months',
            'resources' => ['Laravel.com', 'Laracasts', 'Laravel Daily'],
            'prerequisites' => ['php', 'mysql', 'mvc pattern'],
            'related_skills' => ['php', 'mysql', 'composer', 'redis']
        ],

        // Databases
        'mysql' => [
            'category' => 'database',
            'difficulty' => 'beginner',
            'learning_time' => '1-2 months',
            'resources' => ['MySQL Documentation', 'W3Schools', 'SQLZoo'],
            'prerequisites' => ['basic sql concepts'],
            'related_skills' => ['sql', 'database design', 'indexing']
        ],
        'postgresql' => [
            'category' => 'database',
            'difficulty' => 'intermediate',
            'learning_time' => '2-3 months',
            'resources' => ['PostgreSQL Documentation', 'PostgreSQL Tutorial'],
            'prerequisites' => ['sql', 'mysql'],
            'related_skills' => ['sql', 'database optimization']
        ],

        // Cloud & DevOps
        'aws' => [
            'category' => 'cloud',
            'difficulty' => 'advanced',
            'learning_time' => '4-6 months',
            'resources' => ['AWS Training', 'A Cloud Guru', 'Udemy'],
            'prerequisites' => ['linux', 'networking basics', 'virtualization'],
            'related_skills' => ['docker', 'kubernetes', 'terraform']
        ],
        'docker' => [
            'category' => 'devops',
            'difficulty' => 'intermediate',
            'learning_time' => '1-2 months',
            'resources' => ['Docker Documentation', 'Docker Labs', 'Play with Docker'],
            'prerequisites' => ['linux', 'command line'],
            'related_skills' => ['kubernetes', 'docker-compose', 'ci/cd']
        ],

        // Office/Administrative Skills
        'microsoft office' => [
            'category' => 'office',
            'difficulty' => 'beginner',
            'learning_time' => '1-2 months',
            'resources' => ['Microsoft Learn', 'GCF Global', 'LinkedIn Learning'],
            'prerequisites' => ['basic computer skills'],
            'related_skills' => ['excel', 'word', 'powerpoint']
        ],
        'excel' => [
            'category' => 'office',
            'difficulty' => 'beginner',
            'learning_time' => '1-2 months',
            'resources' => ['Excel Easy', 'Chandoo', 'ExcelJet'],
            'prerequisites' => ['basic computer skills'],
            'related_skills' => ['pivot tables', 'vlookup', 'macros']
        ],
        'data entry' => [
            'category' => 'administrative',
            'difficulty' => 'beginner',
            'learning_time' => '2-4 weeks',
            'resources' => ['Typing.com', 'TypingClub', 'Practice courses'],
            'prerequisites' => ['typing skills', 'attention to detail'],
            'related_skills' => ['typing', 'excel', 'database management']
        ],

        // Customer Service
        'communication' => [
            'category' => 'soft_skill',
            'difficulty' => 'beginner',
            'learning_time' => '1-3 months',
            'resources' => ['Coursera', 'LinkedIn Learning', 'Toastmasters'],
            'prerequisites' => [],
            'related_skills' => ['presentation', 'writing', 'active listening']
        ],
        'customer service' => [
            'category' => 'soft_skill',
            'difficulty' => 'beginner',
            'learning_time' => '1-2 months',
            'resources' => ['HubSpot Academy', 'LinkedIn Learning', 'Coursera'],
            'prerequisites' => ['communication'],
            'related_skills' => ['crm', 'problem solving', 'conflict resolution']
        ],

        // Accounting/Finance
        'accounting' => [
            'category' => 'finance',
            'difficulty' => 'intermediate',
            'learning_time' => '6-12 months',
            'resources' => ['AccountingCoach', 'Coursera', 'Khan Academy'],
            'prerequisites' => ['basic math', 'excel'],
            'related_skills' => ['bookkeeping', 'financial reporting', 'tax']
        ],
        'quickbooks' => [
            'category' => 'software',
            'difficulty' => 'beginner',
            'learning_time' => '1-2 months',
            'resources' => ['QuickBooks Tutorials', 'LinkedIn Learning', 'Udemy'],
            'prerequisites' => ['basic accounting'],
            'related_skills' => ['accounting', 'bookkeeping', 'payroll']
        ]
    ];

    /**
     * Career paths and skill progressions
     */
    protected array $careerPaths = [
        'web_developer' => [
            'entry' => ['html', 'css', 'javascript', 'git'],
            'junior' => ['php', 'mysql', 'laravel', 'react'],
            'mid' => ['api design', 'testing', 'docker', 'ci/cd'],
            'senior' => ['system design', 'aws', 'team leadership', 'architecture']
        ],
        'data_analyst' => [
            'entry' => ['excel', 'sql', 'statistics'],
            'junior' => ['python', 'pandas', 'data visualization'],
            'mid' => ['machine learning', 'tableau', 'r'],
            'senior' => ['big data', 'spark', 'business intelligence']
        ],
        'administrative_assistant' => [
            'entry' => ['microsoft office', 'typing', 'communication'],
            'junior' => ['excel', 'calendar management', 'data entry'],
            'mid' => ['project coordination', 'bookkeeping', 'office management'],
            'senior' => ['executive assistance', 'team management', 'budget management']
        ],
        'customer_service' => [
            'entry' => ['communication', 'phone etiquette', 'basic computer'],
            'junior' => ['crm', 'problem solving', 'product knowledge'],
            'mid' => ['team leadership', 'quality assurance', 'training'],
            'senior' => ['operations management', 'strategy', 'process improvement']
        ]
    ];

    public function __construct()
    {
        $this->contentAnalysis = new ContentAnalysisService();
    }

    /**
     * Perform comprehensive skill gap analysis
     *
     * @param User $user
     * @param Job $job
     * @return array
     */
    public function analyzeSkillGap(User $user, Job $job): array
    {
        $profile = $user->jobSeekerProfile;

        if (!$profile) {
            return [
                'error' => 'User profile not found',
                'has_profile' => false
            ];
        }

        // Get user skills
        $userSkills = $this->normalizeSkills($this->ensureArray($profile->skills));

        // Get job required skills
        $jobSkills = $this->contentAnalysis->extractJobSkills($job);
        $jobSkillNames = array_map(fn($s) => strtolower($s), array_keys($jobSkills));

        // Calculate matches and gaps
        $matchedSkills = [];
        $missingSkills = [];
        $partialMatches = [];

        foreach ($jobSkillNames as $jobSkill) {
            $matched = false;

            foreach ($userSkills as $userSkill) {
                // Exact match
                if ($userSkill === $jobSkill) {
                    $matchedSkills[] = $jobSkill;
                    $matched = true;
                    break;
                }

                // Partial match (one contains the other)
                if (strpos($userSkill, $jobSkill) !== false || strpos($jobSkill, $userSkill) !== false) {
                    $partialMatches[] = [
                        'user_skill' => $userSkill,
                        'job_skill' => $jobSkill
                    ];
                    $matched = true;
                    break;
                }

                // Check if skills are related
                if ($this->areSkillsRelated($userSkill, $jobSkill)) {
                    $partialMatches[] = [
                        'user_skill' => $userSkill,
                        'job_skill' => $jobSkill,
                        'relation' => 'related'
                    ];
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $missingSkills[] = $jobSkill;
            }
        }

        // Calculate overall match score
        $totalJobSkills = count($jobSkillNames);
        $exactMatches = count($matchedSkills);
        $partialMatchCount = count($partialMatches);

        $matchScore = $totalJobSkills > 0
            ? ($exactMatches + ($partialMatchCount * 0.5)) / $totalJobSkills
            : 0;

        // Generate learning recommendations
        $recommendations = $this->generateLearningRecommendations($missingSkills, $userSkills);

        // Estimate readiness
        $readiness = $this->estimateJobReadiness($matchScore, $missingSkills, $user, $job);

        return [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'job_title' => $job->title,

            'skill_analysis' => [
                'user_skills_count' => count($userSkills),
                'job_skills_count' => $totalJobSkills,
                'matched_skills' => $matchedSkills,
                'partial_matches' => $partialMatches,
                'missing_skills' => $missingSkills,
                'match_score' => round($matchScore, 4),
                'match_percentage' => round($matchScore * 100, 1)
            ],

            'readiness' => $readiness,

            'learning_recommendations' => $recommendations,

            'career_path_suggestions' => $this->suggestCareerPath($userSkills, $job)
        ];
    }

    /**
     * Check if two skills are related
     */
    protected function areSkillsRelated(string $skill1, string $skill2): bool
    {
        // Check direct relations from metadata
        $metadata1 = $this->skillMetadata[$skill1] ?? null;
        $metadata2 = $this->skillMetadata[$skill2] ?? null;

        if ($metadata1 && isset($metadata1['related_skills'])) {
            if (in_array($skill2, $metadata1['related_skills'])) {
                return true;
            }
        }

        if ($metadata2 && isset($metadata2['related_skills'])) {
            if (in_array($skill1, $metadata2['related_skills'])) {
                return true;
            }
        }

        // Check if same category
        if ($metadata1 && $metadata2) {
            if ($metadata1['category'] === $metadata2['category']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate learning recommendations for missing skills
     */
    protected function generateLearningRecommendations(array $missingSkills, array $userSkills): array
    {
        $recommendations = [];

        foreach ($missingSkills as $skill) {
            $skillLower = strtolower($skill);
            $metadata = $this->skillMetadata[$skillLower] ?? null;

            $recommendation = [
                'skill' => $skill,
                'priority' => $this->calculateSkillPriority($skill, $userSkills),
                'difficulty' => $metadata['difficulty'] ?? 'unknown',
                'estimated_learning_time' => $metadata['learning_time'] ?? 'varies',
                'category' => $metadata['category'] ?? 'general'
            ];

            // Add resources if available
            if ($metadata && isset($metadata['resources'])) {
                $recommendation['resources'] = $metadata['resources'];
            }

            // Check prerequisites
            if ($metadata && isset($metadata['prerequisites'])) {
                $missingPrereqs = array_diff($metadata['prerequisites'], $userSkills);
                if (!empty($missingPrereqs)) {
                    $recommendation['prerequisites_needed'] = $missingPrereqs;
                    $recommendation['note'] = 'Consider learning prerequisites first';
                }
            }

            // Add related skills user already has
            if ($metadata && isset($metadata['related_skills'])) {
                $relatedUserHas = array_intersect($metadata['related_skills'], $userSkills);
                if (!empty($relatedUserHas)) {
                    $recommendation['leverage_skills'] = $relatedUserHas;
                    $recommendation['note'] = 'Your existing skills will help you learn this faster';
                }
            }

            $recommendations[] = $recommendation;
        }

        // Sort by priority
        usort($recommendations, fn($a, $b) => $b['priority'] <=> $a['priority']);

        return $recommendations;
    }

    /**
     * Calculate skill priority based on various factors
     */
    protected function calculateSkillPriority(string $skill, array $userSkills): int
    {
        $priority = 50; // Base priority
        $skillLower = strtolower($skill);
        $metadata = $this->skillMetadata[$skillLower] ?? null;

        if (!$metadata) {
            return $priority;
        }

        // Easier skills get higher priority (quicker wins)
        if ($metadata['difficulty'] === 'beginner') $priority += 20;
        elseif ($metadata['difficulty'] === 'intermediate') $priority += 10;

        // Skills with resources get higher priority
        if (isset($metadata['resources'])) $priority += 10;

        // Check if user has related skills (easier to learn)
        if (isset($metadata['related_skills'])) {
            $relatedUserHas = array_intersect($metadata['related_skills'], $userSkills);
            $priority += count($relatedUserHas) * 5;
        }

        // Check if user has prerequisites
        if (isset($metadata['prerequisites'])) {
            $hasPrereqs = array_intersect($metadata['prerequisites'], $userSkills);
            if (count($hasPrereqs) === count($metadata['prerequisites'])) {
                $priority += 15; // Has all prerequisites
            }
        }

        return min(100, $priority);
    }

    /**
     * Estimate job readiness based on skill match and other factors
     */
    protected function estimateJobReadiness(float $matchScore, array $missingSkills, User $user, Job $job): array
    {
        $profile = $user->jobSeekerProfile;

        // Calculate readiness score (0-100)
        $readinessScore = $matchScore * 60; // Skills account for 60%

        // Experience match (20%)
        $userExp = (int) ($profile->total_experience_years ?? 0);
        $jobExp = $this->extractExperienceFromJob($job);

        if ($jobExp === 0 || $userExp >= $jobExp) {
            $readinessScore += 20;
        } elseif ($userExp >= $jobExp - 1) {
            $readinessScore += 15;
        } elseif ($userExp >= $jobExp - 2) {
            $readinessScore += 10;
        }

        // Education/certification (10%)
        // Simplified - would need more data
        $readinessScore += 10;

        // Soft skills estimation (10%)
        $softSkills = ['communication', 'teamwork', 'problem solving', 'leadership'];
        $userSkills = $this->normalizeSkills($this->ensureArray($profile->skills ?? []));
        $softSkillMatch = count(array_intersect($softSkills, $userSkills));
        $readinessScore += min(10, $softSkillMatch * 2.5);

        // Determine readiness level
        $level = match(true) {
            $readinessScore >= 80 => 'ready',
            $readinessScore >= 60 => 'almost_ready',
            $readinessScore >= 40 => 'needs_development',
            default => 'significant_gap'
        };

        // Estimate time to readiness
        $timeToReady = $this->estimateTimeToReadiness($missingSkills, $readinessScore);

        return [
            'score' => round($readinessScore, 1),
            'level' => $level,
            'level_description' => $this->getReadinessDescription($level),
            'time_to_ready' => $timeToReady,
            'critical_gaps' => array_slice($missingSkills, 0, 3),
            'recommendation' => $this->getReadinessRecommendation($level, $missingSkills)
        ];
    }

    /**
     * Get readiness level description
     */
    protected function getReadinessDescription(string $level): string
    {
        return match($level) {
            'ready' => 'You are well-qualified for this position',
            'almost_ready' => 'You are close to meeting all requirements',
            'needs_development' => 'Some skill development needed',
            'significant_gap' => 'Significant preparation required',
            default => 'Unable to assess'
        };
    }

    /**
     * Get recommendation based on readiness level
     */
    protected function getReadinessRecommendation(string $level, array $missingSkills): string
    {
        return match($level) {
            'ready' => 'Apply now! Highlight your relevant experience in your application.',
            'almost_ready' => 'Consider applying while developing: ' . implode(', ', array_slice($missingSkills, 0, 2)),
            'needs_development' => 'Focus on learning: ' . implode(', ', array_slice($missingSkills, 0, 3)) . ' before applying.',
            'significant_gap' => 'Create a learning plan for the required skills. Consider entry-level positions in this field first.',
            default => 'Review the job requirements carefully.'
        };
    }

    /**
     * Estimate time to become ready
     */
    protected function estimateTimeToReadiness(array $missingSkills, float $readinessScore): string
    {
        if ($readinessScore >= 80) {
            return 'Ready now';
        }

        $totalWeeks = 0;

        foreach (array_slice($missingSkills, 0, 5) as $skill) {
            $metadata = $this->skillMetadata[strtolower($skill)] ?? null;

            if ($metadata && isset($metadata['learning_time'])) {
                // Parse learning time (e.g., "2-4 months" -> average weeks)
                if (preg_match('/(\d+)-(\d+)\s*(months?|weeks?)/', $metadata['learning_time'], $matches)) {
                    $min = (int)$matches[1];
                    $max = (int)$matches[2];
                    $avg = ($min + $max) / 2;

                    if (strpos($matches[3], 'month') !== false) {
                        $totalWeeks += $avg * 4;
                    } else {
                        $totalWeeks += $avg;
                    }
                }
            } else {
                $totalWeeks += 4; // Default 4 weeks per unknown skill
            }
        }

        // Assume part-time learning (reduce estimate)
        $totalWeeks = $totalWeeks * 0.7;

        if ($totalWeeks <= 4) {
            return '1 month';
        } elseif ($totalWeeks <= 12) {
            return round($totalWeeks / 4) . ' months';
        } else {
            return '3+ months';
        }
    }

    /**
     * Suggest career path based on current skills
     */
    protected function suggestCareerPath(array $userSkills, Job $job): array
    {
        $suggestions = [];

        // Identify which career path best matches user's current skills
        foreach ($this->careerPaths as $pathName => $levels) {
            $allPathSkills = array_merge(
                $levels['entry'] ?? [],
                $levels['junior'] ?? [],
                $levels['mid'] ?? [],
                $levels['senior'] ?? []
            );

            $matchCount = count(array_intersect($userSkills, $allPathSkills));
            $matchRatio = count($allPathSkills) > 0 ? $matchCount / count($allPathSkills) : 0;

            if ($matchRatio > 0.1) { // At least 10% match
                // Determine current level
                $currentLevel = $this->determineCareerLevel($userSkills, $levels);

                // Get next level skills
                $nextLevelSkills = $this->getNextLevelSkills($currentLevel, $levels, $userSkills);

                $suggestions[] = [
                    'career_path' => str_replace('_', ' ', ucwords($pathName)),
                    'current_level' => $currentLevel,
                    'match_ratio' => round($matchRatio * 100, 1),
                    'next_level_skills' => $nextLevelSkills,
                    'skills_to_advance' => array_diff($nextLevelSkills, $userSkills)
                ];
            }
        }

        // Sort by match ratio
        usort($suggestions, fn($a, $b) => $b['match_ratio'] <=> $a['match_ratio']);

        return array_slice($suggestions, 0, 3);
    }

    /**
     * Determine current career level based on skills
     */
    protected function determineCareerLevel(array $userSkills, array $levels): string
    {
        $levelOrder = ['senior', 'mid', 'junior', 'entry'];

        foreach ($levelOrder as $level) {
            if (!isset($levels[$level])) continue;

            $levelSkills = $levels[$level];
            $matchCount = count(array_intersect($userSkills, $levelSkills));

            if ($matchCount >= count($levelSkills) * 0.6) {
                return $level;
            }
        }

        return 'entry';
    }

    /**
     * Get skills needed for next level
     */
    protected function getNextLevelSkills(string $currentLevel, array $levels, array $userSkills): array
    {
        $levelOrder = ['entry', 'junior', 'mid', 'senior'];
        $currentIndex = array_search($currentLevel, $levelOrder);

        if ($currentIndex === false || $currentIndex >= count($levelOrder) - 1) {
            return []; // Already at highest level
        }

        $nextLevel = $levelOrder[$currentIndex + 1];
        return $levels[$nextLevel] ?? [];
    }

    /**
     * Extract experience requirement from job
     */
    protected function extractExperienceFromJob(Job $job): int
    {
        $requirements = strtolower($job->requirements ?? '');

        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)\s*years?/', $requirements, $matches)) {
            return (int)(($matches[1] + $matches[2]) / 2);
        }

        if (preg_match('/(\d+)\s*(?:\+|or more)\s*years?/', $requirements, $matches)) {
            return (int)$matches[1];
        }

        if (preg_match('/(\d+)\s*years?/', $requirements, $matches)) {
            return (int)$matches[1];
        }

        if (strpos($requirements, 'senior') !== false) return 5;
        if (strpos($requirements, 'mid') !== false) return 3;
        if (strpos($requirements, 'junior') !== false) return 1;

        return 0;
    }

    /**
     * Normalize skills array
     */
    protected function normalizeSkills(array $skills): array
    {
        return array_map(fn($s) => strtolower(trim($s)), $skills);
    }

    /**
     * Ensure value is array
     */
    protected function ensureArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Get skill metadata
     */
    public function getSkillMetadata(string $skill): ?array
    {
        return $this->skillMetadata[strtolower($skill)] ?? null;
    }

    /**
     * Get all career paths
     */
    public function getCareerPaths(): array
    {
        return $this->careerPaths;
    }
}
