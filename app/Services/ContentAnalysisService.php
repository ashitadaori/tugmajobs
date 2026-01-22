<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Content Analysis Service
 *
 * Analyzes job content (title, description, requirements) to:
 * 1. Infer actual job categories based on content (not just employer selection)
 * 2. Extract skills and role indicators
 * 3. Calculate semantic similarity between jobs and jobseekers
 *
 * This solves the problem where employers miscategorize jobs
 * (e.g., posting "Office Clerk" under "Information Technology")
 */
class ContentAnalysisService
{
    /**
     * Comprehensive category indicators covering ALL job types
     * Each category has skills, roles, and context keywords
     */
    protected $categoryIndicators = [
        'information_technology' => [
            'skills' => [
                'php', 'javascript', 'python', 'java', 'c#', 'c++', 'ruby', 'go', 'rust', 'swift', 'kotlin',
                'html', 'css', 'sql', 'nosql', 'react', 'angular', 'vue', 'laravel', 'django', 'flask',
                'nodejs', 'express', 'spring', 'dotnet', '.net', 'asp.net',
                'mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch',
                'aws', 'azure', 'gcp', 'docker', 'kubernetes', 'devops', 'ci/cd',
                'git', 'api', 'rest', 'graphql', 'microservices',
                'machine learning', 'ai', 'data science', 'tensorflow', 'pytorch',
                'cybersecurity', 'networking', 'linux', 'windows server',
                'agile', 'scrum', 'jira', 'confluence'
            ],
            'roles' => [
                'developer', 'programmer', 'software engineer', 'web developer', 'full stack',
                'frontend', 'backend', 'mobile developer', 'ios developer', 'android developer',
                'devops engineer', 'system administrator', 'network engineer', 'database administrator',
                'data scientist', 'data analyst', 'data engineer', 'ml engineer',
                'qa engineer', 'test engineer', 'automation engineer',
                'technical lead', 'tech lead', 'architect', 'cto', 'it manager',
                'cybersecurity analyst', 'security engineer', 'penetration tester'
            ],
            'context' => [
                'coding', 'programming', 'development', 'software', 'application', 'system',
                'technical', 'technology', 'digital', 'computer', 'it support', 'helpdesk'
            ],
            'category_id' => null // Will be mapped dynamically
        ],

        'administrative_clerical' => [
            'skills' => [
                'microsoft office', 'ms office', 'excel', 'word', 'powerpoint', 'outlook',
                'typing', 'data entry', 'filing', 'record keeping', 'documentation',
                'scheduling', 'calendar management', 'appointment setting',
                'correspondence', 'email management', 'letter writing',
                'office equipment', 'photocopying', 'scanning', 'faxing',
                'inventory', 'supplies management', 'office organization',
                'google workspace', 'google docs', 'google sheets'
            ],
            'roles' => [
                'clerk', 'office clerk', 'general clerk', 'file clerk',
                'secretary', 'executive secretary', 'administrative secretary',
                'administrative assistant', 'admin assistant', 'office assistant',
                'receptionist', 'front desk', 'office staff', 'office worker',
                'encoder', 'data encoder', 'typist',
                'document controller', 'records officer', 'filing clerk'
            ],
            'context' => [
                'office', 'administrative', 'clerical', 'paperwork', 'documents',
                'organize', 'maintain records', 'answer phones', 'greet visitors'
            ],
            'category_id' => null
        ],

        'customer_service' => [
            'skills' => [
                'communication', 'verbal communication', 'written communication',
                'phone etiquette', 'email support', 'live chat', 'ticketing system',
                'crm', 'salesforce', 'zendesk', 'freshdesk', 'hubspot',
                'problem solving', 'conflict resolution', 'complaint handling',
                'customer relationship', 'client management',
                'multitasking', 'patience', 'empathy'
            ],
            'roles' => [
                'customer service representative', 'csr', 'customer support',
                'call center agent', 'contact center', 'phone support',
                'technical support', 'help desk', 'service desk',
                'customer success', 'client services', 'account manager',
                'support specialist', 'service representative'
            ],
            'context' => [
                'customer', 'client', 'support', 'service', 'assist', 'help',
                'inquiries', 'complaints', 'resolve issues', 'satisfaction'
            ],
            'category_id' => null
        ],

        'sales_marketing' => [
            'skills' => [
                'sales', 'selling', 'negotiation', 'closing deals', 'lead generation',
                'cold calling', 'prospecting', 'pipeline management',
                'marketing', 'digital marketing', 'social media marketing', 'seo', 'sem',
                'content marketing', 'email marketing', 'campaign management',
                'market research', 'competitive analysis', 'brand management',
                'presentation', 'pitching', 'product demo',
                'crm', 'salesforce', 'hubspot', 'marketo',
                'google analytics', 'facebook ads', 'google ads'
            ],
            'roles' => [
                'sales representative', 'sales agent', 'sales executive', 'sales associate',
                'account executive', 'business development', 'bd manager',
                'marketing specialist', 'marketing coordinator', 'marketing manager',
                'digital marketer', 'social media manager', 'content creator',
                'brand manager', 'product manager', 'market analyst',
                'sales manager', 'regional sales', 'territory manager'
            ],
            'context' => [
                'revenue', 'target', 'quota', 'commission', 'incentive',
                'promote', 'advertise', 'campaign', 'brand', 'market'
            ],
            'category_id' => null
        ],

        'accounting_finance' => [
            'skills' => [
                'accounting', 'bookkeeping', 'financial reporting', 'financial analysis',
                'accounts payable', 'accounts receivable', 'general ledger',
                'tax', 'taxation', 'bir', 'vat', 'income tax',
                'audit', 'internal audit', 'external audit', 'compliance',
                'budgeting', 'forecasting', 'cost analysis', 'variance analysis',
                'payroll', 'payroll processing', 'timekeeping',
                'quickbooks', 'sap', 'oracle financials', 'xero', 'peachtree',
                'excel', 'pivot tables', 'vlookup', 'financial modeling'
            ],
            'roles' => [
                'accountant', 'staff accountant', 'senior accountant', 'chief accountant',
                'bookkeeper', 'accounting clerk', 'accounting assistant',
                'auditor', 'internal auditor', 'external auditor',
                'finance analyst', 'financial analyst', 'budget analyst',
                'tax accountant', 'tax specialist', 'tax preparer',
                'payroll specialist', 'payroll officer', 'compensation analyst',
                'treasurer', 'finance manager', 'cfo', 'controller',
                'billing specialist', 'collections', 'credit analyst', 'cashier'
            ],
            'context' => [
                'financial', 'fiscal', 'monetary', 'accounting', 'audit',
                'balance sheet', 'income statement', 'cash flow', 'reconciliation'
            ],
            'category_id' => null
        ],

        'human_resources' => [
            'skills' => [
                'recruitment', 'talent acquisition', 'sourcing', 'screening', 'interviewing',
                'onboarding', 'orientation', 'employee engagement',
                'compensation', 'benefits administration', 'payroll',
                'performance management', 'appraisal', 'kpi',
                'training', 'learning and development', 'l&d',
                'employee relations', 'labor relations', 'grievance handling',
                'hris', 'workday', 'bamboohr', 'sap hr',
                'labor law', 'employment law', 'dole', 'compliance'
            ],
            'roles' => [
                'hr officer', 'hr assistant', 'hr coordinator', 'hr generalist',
                'recruiter', 'talent acquisition specialist', 'sourcing specialist',
                'hr manager', 'hr director', 'hr business partner', 'hrbp',
                'compensation and benefits', 'c&b specialist',
                'training officer', 'training specialist', 'l&d manager',
                'employee relations officer', 'labor relations'
            ],
            'context' => [
                'human resources', 'hr', 'personnel', 'workforce', 'employees',
                'hiring', 'staffing', 'manpower', 'talent'
            ],
            'category_id' => null
        ],

        'healthcare_medical' => [
            'skills' => [
                'patient care', 'clinical', 'medical records', 'vital signs',
                'nursing', 'medication administration', 'wound care',
                'laboratory', 'specimen collection', 'diagnostic',
                'medical terminology', 'anatomy', 'physiology',
                'cpr', 'first aid', 'emergency response',
                'infection control', 'sterilization', 'sanitation',
                'emr', 'ehr', 'medical software'
            ],
            'roles' => [
                'nurse', 'registered nurse', 'rn', 'staff nurse', 'head nurse',
                'doctor', 'physician', 'medical officer', 'specialist',
                'medical technologist', 'medtech', 'lab technician',
                'pharmacist', 'pharmacy assistant',
                'caregiver', 'nursing aide', 'patient care assistant',
                'medical secretary', 'medical receptionist', 'medical coder',
                'radiologic technologist', 'x-ray technician',
                'physical therapist', 'pt', 'occupational therapist'
            ],
            'context' => [
                'hospital', 'clinic', 'medical', 'healthcare', 'health care',
                'patient', 'treatment', 'diagnosis', 'medicine'
            ],
            'category_id' => null
        ],

        'engineering' => [
            'skills' => [
                'autocad', 'solidworks', 'catia', 'inventor', 'revit',
                'project management', 'project planning', 'scheduling',
                'quality control', 'qc', 'quality assurance', 'qa',
                'process improvement', 'lean', 'six sigma',
                'technical drawing', 'blueprints', 'schematics',
                'maintenance', 'troubleshooting', 'repair',
                'safety', 'osha', 'hazard analysis'
            ],
            'roles' => [
                'engineer', 'mechanical engineer', 'electrical engineer', 'civil engineer',
                'industrial engineer', 'chemical engineer', 'electronics engineer',
                'project engineer', 'site engineer', 'field engineer',
                'maintenance engineer', 'facilities engineer',
                'quality engineer', 'process engineer', 'manufacturing engineer',
                'engineering manager', 'technical manager'
            ],
            'context' => [
                'engineering', 'technical', 'design', 'construction', 'manufacturing',
                'production', 'plant', 'facility', 'industrial'
            ],
            'category_id' => null
        ],

        'education_training' => [
            'skills' => [
                'teaching', 'instruction', 'curriculum development', 'lesson planning',
                'classroom management', 'student assessment', 'grading',
                'training', 'facilitation', 'presentation',
                'e-learning', 'lms', 'moodle', 'canvas',
                'tutoring', 'mentoring', 'coaching'
            ],
            'roles' => [
                'teacher', 'instructor', 'professor', 'lecturer', 'faculty',
                'tutor', 'academic coordinator', 'department head',
                'trainer', 'corporate trainer', 'training specialist',
                'training manager', 'l&d specialist',
                'guidance counselor', 'school administrator', 'principal'
            ],
            'context' => [
                'education', 'academic', 'school', 'university', 'college',
                'learning', 'teaching', 'students', 'classroom'
            ],
            'category_id' => null
        ],

        'hospitality_tourism' => [
            'skills' => [
                'guest relations', 'hospitality', 'customer service',
                'reservation', 'booking', 'check-in', 'check-out',
                'food and beverage', 'f&b', 'banquet', 'catering',
                'housekeeping', 'room service', 'concierge',
                'tour guiding', 'travel planning', 'itinerary',
                'hotel management', 'property management system', 'pms'
            ],
            'roles' => [
                'front desk agent', 'receptionist', 'guest service agent',
                'concierge', 'bellhop', 'porter',
                'housekeeper', 'room attendant', 'housekeeping supervisor',
                'waiter', 'waitress', 'server', 'barista', 'bartender',
                'chef', 'cook', 'kitchen staff', 'sous chef',
                'hotel manager', 'restaurant manager', 'f&b manager',
                'tour guide', 'travel agent', 'travel consultant'
            ],
            'context' => [
                'hotel', 'resort', 'restaurant', 'tourism', 'travel',
                'guest', 'hospitality', 'accommodation', 'dining'
            ],
            'category_id' => null
        ],

        'logistics_supply_chain' => [
            'skills' => [
                'logistics', 'supply chain', 'inventory management', 'warehouse',
                'shipping', 'receiving', 'dispatching', 'routing',
                'procurement', 'purchasing', 'vendor management',
                'import', 'export', 'customs', 'freight forwarding',
                'erp', 'sap', 'oracle', 'wms',
                'forklift', 'material handling', 'packaging'
            ],
            'roles' => [
                'logistics coordinator', 'logistics officer', 'logistics manager',
                'warehouse staff', 'warehouse supervisor', 'warehouse manager',
                'inventory clerk', 'stock controller', 'inventory analyst',
                'procurement officer', 'purchasing agent', 'buyer',
                'supply chain analyst', 'supply chain manager',
                'dispatcher', 'delivery driver', 'truck driver', 'rider',
                'customs broker', 'freight coordinator', 'shipping clerk'
            ],
            'context' => [
                'warehouse', 'distribution', 'delivery', 'shipping', 'freight',
                'inventory', 'stock', 'supply', 'procurement'
            ],
            'category_id' => null
        ],

        'construction_trades' => [
            'skills' => [
                'construction', 'carpentry', 'masonry', 'plumbing', 'electrical',
                'welding', 'painting', 'roofing', 'flooring',
                'blueprint reading', 'estimation', 'quantity surveying',
                'heavy equipment', 'crane operation', 'forklift',
                'safety', 'osha', 'ppe', 'scaffolding'
            ],
            'roles' => [
                'carpenter', 'mason', 'plumber', 'electrician', 'welder',
                'painter', 'roofer', 'tile setter', 'drywall installer',
                'foreman', 'site supervisor', 'construction manager',
                'heavy equipment operator', 'crane operator',
                'laborer', 'helper', 'skilled worker',
                'quantity surveyor', 'estimator', 'project manager'
            ],
            'context' => [
                'construction', 'building', 'site', 'project', 'contractor',
                'renovation', 'installation', 'repair', 'maintenance'
            ],
            'category_id' => null
        ],

        'manufacturing_production' => [
            'skills' => [
                'machine operation', 'assembly', 'production line',
                'quality control', 'inspection', 'testing',
                'lean manufacturing', 'kaizen', '5s', 'six sigma',
                'cnc', 'lathe', 'milling', 'grinding',
                'packaging', 'labeling', 'material handling',
                'safety', 'gmp', 'haccp', 'iso'
            ],
            'roles' => [
                'machine operator', 'production operator', 'assembly line worker',
                'production supervisor', 'line leader', 'shift supervisor',
                'quality inspector', 'qc inspector', 'qa analyst',
                'production planner', 'production scheduler',
                'maintenance technician', 'machine technician',
                'plant manager', 'production manager', 'operations manager'
            ],
            'context' => [
                'manufacturing', 'production', 'factory', 'plant', 'assembly',
                'industrial', 'operations', 'output', 'yield'
            ],
            'category_id' => null
        ],

        'creative_design' => [
            'skills' => [
                'graphic design', 'adobe photoshop', 'illustrator', 'indesign',
                'ui/ux', 'figma', 'sketch', 'adobe xd', 'prototyping',
                'video editing', 'premiere pro', 'after effects', 'final cut',
                'photography', 'videography', 'lighting',
                'animation', 'motion graphics', '3d modeling', 'blender', 'maya',
                'branding', 'logo design', 'typography', 'layout'
            ],
            'roles' => [
                'graphic designer', 'visual designer', 'creative designer',
                'ui designer', 'ux designer', 'ui/ux designer', 'product designer',
                'web designer', 'digital designer',
                'video editor', 'motion designer', 'animator',
                'photographer', 'videographer', 'multimedia artist',
                'art director', 'creative director', 'design lead'
            ],
            'context' => [
                'design', 'creative', 'visual', 'artistic', 'aesthetic',
                'brand', 'media', 'content', 'portfolio'
            ],
            'category_id' => null
        ],

        'legal' => [
            'skills' => [
                'legal research', 'contract drafting', 'legal writing',
                'litigation', 'case management', 'court filing',
                'corporate law', 'labor law', 'intellectual property',
                'compliance', 'regulatory', 'due diligence',
                'notarization', 'documentation', 'legal review'
            ],
            'roles' => [
                'lawyer', 'attorney', 'counsel', 'legal counsel',
                'paralegal', 'legal assistant', 'legal secretary',
                'compliance officer', 'compliance manager',
                'contract specialist', 'legal analyst',
                'corporate secretary', 'legal manager'
            ],
            'context' => [
                'legal', 'law', 'court', 'contract', 'compliance',
                'litigation', 'regulatory', 'attorney', 'counsel'
            ],
            'category_id' => null
        ],

        'retail' => [
            'skills' => [
                'sales', 'customer service', 'cash handling', 'pos system',
                'inventory', 'merchandising', 'visual merchandising',
                'product knowledge', 'upselling', 'cross-selling',
                'store operations', 'stock replenishment'
            ],
            'roles' => [
                'sales associate', 'sales clerk', 'retail associate',
                'cashier', 'store staff', 'shop assistant',
                'store supervisor', 'store manager', 'retail manager',
                'visual merchandiser', 'inventory clerk',
                'branch manager', 'area manager'
            ],
            'context' => [
                'retail', 'store', 'shop', 'mall', 'outlet',
                'merchandise', 'customer', 'sales floor'
            ],
            'category_id' => null
        ],

        'bpo_outsourcing' => [
            'skills' => [
                'customer service', 'technical support', 'phone support',
                'email support', 'chat support', 'ticketing',
                'data entry', 'data processing', 'back office',
                'voice', 'non-voice', 'inbound', 'outbound',
                'accent training', 'communication skills'
            ],
            'roles' => [
                'call center agent', 'csr', 'customer service representative',
                'technical support representative', 'tsr',
                'team leader', 'supervisor', 'operations manager',
                'quality analyst', 'qa', 'trainer',
                'back office associate', 'data entry specialist',
                'virtual assistant', 'va'
            ],
            'context' => [
                'bpo', 'call center', 'contact center', 'outsourcing',
                'offshore', 'shared services', 'voice account', 'non-voice'
            ],
            'category_id' => null
        ]
    ];

    /**
     * Skill synonyms and related terms for better matching
     */
    protected $skillSynonyms = [
        'ms office' => ['microsoft office', 'office suite', 'ms word', 'ms excel'],
        'customer service' => ['client service', 'customer support', 'client support'],
        'data entry' => ['encoding', 'data encoding', 'typing'],
        'communication' => ['interpersonal', 'verbal', 'written communication'],
        'leadership' => ['team lead', 'supervisory', 'management'],
        'problem solving' => ['analytical', 'critical thinking', 'troubleshooting']
    ];

    protected $categoryIdMapping = [];
    protected $cacheTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->loadCategoryMapping();
    }

    /**
     * Load category ID mapping from database
     */
    protected function loadCategoryMapping(): void
    {
        $this->categoryIdMapping = Cache::remember('category_id_mapping', $this->cacheTimeout, function() {
            $categories = Category::all();
            $mapping = [];

            foreach ($categories as $category) {
                $normalizedName = $this->normalizeString($category->name);

                // Map database categories to our indicator keys
                $indicatorKey = $this->findMatchingIndicatorKey($normalizedName);
                if ($indicatorKey) {
                    $mapping[$indicatorKey] = $category->id;
                    $this->categoryIndicators[$indicatorKey]['category_id'] = $category->id;
                }
            }

            return $mapping;
        });

        // Update category IDs in indicators
        foreach ($this->categoryIdMapping as $key => $id) {
            if (isset($this->categoryIndicators[$key])) {
                $this->categoryIndicators[$key]['category_id'] = $id;
            }
        }
    }

    /**
     * Find matching indicator key for a category name
     */
    protected function findMatchingIndicatorKey(string $categoryName): ?string
    {
        $categoryName = strtolower($categoryName);

        $mappings = [
            'information_technology' => ['information technology', 'it', 'tech', 'software', 'computer'],
            'administrative_clerical' => ['administrative', 'clerical', 'admin', 'office'],
            'customer_service' => ['customer service', 'customer support', 'client service'],
            'sales_marketing' => ['sales', 'marketing', 'business development'],
            'accounting_finance' => ['accounting', 'finance', 'financial'],
            'human_resources' => ['human resources', 'hr', 'recruitment'],
            'healthcare_medical' => ['healthcare', 'medical', 'health', 'nursing'],
            'engineering' => ['engineering', 'engineer'],
            'education_training' => ['education', 'training', 'teaching', 'academic'],
            'hospitality_tourism' => ['hospitality', 'tourism', 'hotel', 'restaurant'],
            'logistics_supply_chain' => ['logistics', 'supply chain', 'warehouse', 'shipping'],
            'construction_trades' => ['construction', 'trades', 'building'],
            'manufacturing_production' => ['manufacturing', 'production', 'factory'],
            'creative_design' => ['creative', 'design', 'graphic', 'multimedia'],
            'legal' => ['legal', 'law', 'attorney'],
            'retail' => ['retail', 'store', 'shop'],
            'bpo_outsourcing' => ['bpo', 'outsourcing', 'call center', 'contact center']
        ];

        foreach ($mappings as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($categoryName, $keyword) !== false) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Analyze job content and infer likely categories
     *
     * @param Job $job
     * @return array Category scores with confidence levels
     */
    public function inferJobCategories(Job $job): array
    {
        $cacheKey = "job_inferred_categories_{$job->id}_" . md5($job->updated_at);

        return Cache::remember($cacheKey, $this->cacheTimeout, function() use ($job) {
            $text = $this->prepareTextForAnalysis($job);
            $categoryScores = [];

            foreach ($this->categoryIndicators as $categoryKey => $indicators) {
                $analysis = $this->analyzeTextForCategory($text, $indicators);

                $categoryScores[$categoryKey] = [
                    'category_key' => $categoryKey,
                    'category_id' => $indicators['category_id'],
                    'score' => $analysis['score'],
                    'confidence' => $analysis['confidence'],
                    'matched_skills' => $analysis['matched_skills'],
                    'matched_roles' => $analysis['matched_roles'],
                    'matched_context' => $analysis['matched_context'],
                    'total_matches' => $analysis['total_matches']
                ];
            }

            // Sort by score descending
            uasort($categoryScores, fn($a, $b) => $b['score'] <=> $a['score']);

            return $categoryScores;
        });
    }

    /**
     * Prepare job text for analysis
     */
    protected function prepareTextForAnalysis(Job $job): string
    {
        $text = implode(' ', array_filter([
            $job->title ?? '',
            $job->description ?? '',
            $job->requirements ?? '',
            $job->benefits ?? ''
        ]));

        return $this->normalizeString($text);
    }

    /**
     * Normalize string for comparison
     */
    protected function normalizeString(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s\/\-\+\#]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Analyze text for a specific category
     */
    protected function analyzeTextForCategory(string $text, array $indicators): array
    {
        $matchedSkills = [];
        $matchedRoles = [];
        $matchedContext = [];

        // Check skills (weight: 2)
        foreach ($indicators['skills'] as $skill) {
            if ($this->textContainsTerm($text, $skill)) {
                $matchedSkills[] = $skill;
            }
        }

        // Check roles (weight: 3 - highest because role is most definitive)
        foreach ($indicators['roles'] as $role) {
            if ($this->textContainsTerm($text, $role)) {
                $matchedRoles[] = $role;
            }
        }

        // Check context keywords (weight: 1)
        foreach ($indicators['context'] as $context) {
            if ($this->textContainsTerm($text, $context)) {
                $matchedContext[] = $context;
            }
        }

        // Calculate weighted score
        $skillScore = count($matchedSkills) * 2;
        $roleScore = count($matchedRoles) * 3;
        $contextScore = count($matchedContext) * 1;
        $totalScore = $skillScore + $roleScore + $contextScore;

        // Calculate max possible score
        $maxSkillScore = count($indicators['skills']) * 2;
        $maxRoleScore = count($indicators['roles']) * 3;
        $maxContextScore = count($indicators['context']) * 1;
        $maxScore = $maxSkillScore + $maxRoleScore + $maxContextScore;

        // Normalize score to 0-1 range
        $normalizedScore = $maxScore > 0 ? min(1.0, $totalScore / ($maxScore * 0.3)) : 0;

        // Boost score if role matches (strong indicator)
        if (count($matchedRoles) > 0) {
            $normalizedScore = min(1.0, $normalizedScore * 1.5);
        }

        return [
            'score' => round($normalizedScore, 4),
            'confidence' => $this->getConfidenceLevel($normalizedScore, count($matchedRoles)),
            'matched_skills' => $matchedSkills,
            'matched_roles' => $matchedRoles,
            'matched_context' => $matchedContext,
            'total_matches' => count($matchedSkills) + count($matchedRoles) + count($matchedContext),
            'raw_score' => $totalScore
        ];
    }

    /**
     * Check if text contains a term (with word boundary awareness)
     */
    protected function textContainsTerm(string $text, string $term): bool
    {
        $term = strtolower($term);

        // Direct match
        if (strpos($text, $term) !== false) {
            return true;
        }

        // Check synonyms
        foreach ($this->skillSynonyms as $canonical => $synonyms) {
            if ($term === $canonical || in_array($term, $synonyms)) {
                foreach (array_merge([$canonical], $synonyms) as $variant) {
                    if (strpos($text, $variant) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get confidence level based on score and role matches
     */
    protected function getConfidenceLevel(float $score, int $roleMatches): string
    {
        if ($roleMatches >= 2 || $score >= 0.6) return 'high';
        if ($roleMatches >= 1 || $score >= 0.3) return 'medium';
        if ($score >= 0.15) return 'low';
        return 'none';
    }

    /**
     * Extract skills from job content
     *
     * @param Job $job
     * @return array
     */
    public function extractJobSkills(Job $job): array
    {
        $text = $this->prepareTextForAnalysis($job);
        $extractedSkills = [];

        foreach ($this->categoryIndicators as $categoryKey => $indicators) {
            foreach ($indicators['skills'] as $skill) {
                if ($this->textContainsTerm($text, $skill)) {
                    $extractedSkills[$skill] = [
                        'skill' => $skill,
                        'category' => $categoryKey,
                        'frequency' => substr_count($text, strtolower($skill))
                    ];
                }
            }
        }

        // Sort by frequency
        uasort($extractedSkills, fn($a, $b) => $b['frequency'] <=> $a['frequency']);

        return $extractedSkills;
    }

    /**
     * Extract roles mentioned in job content
     *
     * @param Job $job
     * @return array
     */
    public function extractJobRoles(Job $job): array
    {
        $text = $this->prepareTextForAnalysis($job);
        $extractedRoles = [];

        foreach ($this->categoryIndicators as $categoryKey => $indicators) {
            foreach ($indicators['roles'] as $role) {
                if ($this->textContainsTerm($text, $role)) {
                    $extractedRoles[] = [
                        'role' => $role,
                        'category' => $categoryKey
                    ];
                }
            }
        }

        return $extractedRoles;
    }

    /**
     * Detect category mismatch between employer selection and content
     *
     * @param Job $job
     * @return array
     */
    public function detectCategoryMismatch(Job $job): array
    {
        $employerCategoryId = $job->category_id;
        $inferredCategories = $this->inferJobCategories($job);

        // Get top inferred category
        $topInferred = array_slice($inferredCategories, 0, 1, true);
        $topKey = array_key_first($topInferred);
        $topData = $topInferred[$topKey] ?? null;

        if (!$topData) {
            return [
                'has_mismatch' => false,
                'employer_category_id' => $employerCategoryId,
                'inferred_category' => null,
                'confidence' => 'none',
                'recommendation' => null
            ];
        }

        // Check if employer's category matches top inferred
        $hasMismatch = $topData['category_id'] !== null &&
                       $topData['category_id'] !== $employerCategoryId &&
                       $topData['confidence'] !== 'none' &&
                       $topData['score'] >= 0.3;

        // Find employer's category score
        $employerCategoryKey = $this->getCategoryKeyById($employerCategoryId);
        $employerCategoryScore = $employerCategoryKey ?
            ($inferredCategories[$employerCategoryKey]['score'] ?? 0) : 0;

        return [
            'has_mismatch' => $hasMismatch,
            'employer_category_id' => $employerCategoryId,
            'employer_category_key' => $employerCategoryKey,
            'employer_category_score' => $employerCategoryScore,
            'inferred_category_key' => $topKey,
            'inferred_category_id' => $topData['category_id'],
            'inferred_score' => $topData['score'],
            'confidence' => $topData['confidence'],
            'matched_roles' => $topData['matched_roles'],
            'matched_skills' => array_slice($topData['matched_skills'], 0, 5),
            'recommendation' => $hasMismatch ?
                "This job appears to be for '{$topKey}' based on content analysis. Consider updating the category." :
                null
        ];
    }

    /**
     * Get category key by ID
     */
    public function getCategoryKeyById(?int $categoryId): ?string
    {
        if (!$categoryId) return null;

        foreach ($this->categoryIndicators as $key => $indicators) {
            if ($indicators['category_id'] === $categoryId) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get category ID by key
     */
    public function getCategoryIdByKey(string $key): ?int
    {
        return $this->categoryIndicators[$key]['category_id'] ?? null;
    }

    /**
     * Calculate skill match score between user profile and job
     *
     * @param User $user
     * @param Job $job
     * @return array
     */
    public function calculateSkillMatch(User $user, Job $job): array
    {
        $profile = $user->jobSeekerProfile;
        if (!$profile) {
            return ['score' => 0, 'matched_skills' => [], 'missing_skills' => []];
        }

        // Get user skills
        $userSkills = $profile->skills ?? [];
        if (is_string($userSkills)) {
            $userSkills = json_decode($userSkills, true) ?? [];
        }
        $userSkillsNormalized = array_map(fn($s) => $this->normalizeString($s), $userSkills);

        // Get job required skills
        $jobSkills = $this->extractJobSkills($job);
        $jobSkillNames = array_keys($jobSkills);

        // Calculate match
        $matchedSkills = [];
        $missingSkills = [];

        foreach ($jobSkillNames as $jobSkill) {
            $found = false;
            foreach ($userSkillsNormalized as $userSkill) {
                if (strpos($userSkill, $jobSkill) !== false || strpos($jobSkill, $userSkill) !== false) {
                    $matchedSkills[] = $jobSkill;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingSkills[] = $jobSkill;
            }
        }

        $totalJobSkills = count($jobSkillNames);
        $score = $totalJobSkills > 0 ? count($matchedSkills) / $totalJobSkills : 0;

        return [
            'score' => round($score, 4),
            'matched_skills' => $matchedSkills,
            'missing_skills' => array_slice($missingSkills, 0, 10),
            'user_skills_count' => count($userSkills),
            'job_skills_count' => $totalJobSkills
        ];
    }

    /**
     * Get all category indicators (for external use)
     */
    public function getCategoryIndicators(): array
    {
        return $this->categoryIndicators;
    }

    /**
     * Clear all caches
     */
    public function clearCache(): void
    {
        Cache::forget('category_id_mapping');
        // Note: Individual job caches will expire naturally
    }
}
