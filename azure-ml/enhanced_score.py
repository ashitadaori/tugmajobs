"""
Enhanced Azure ML K-Means Clustering Scoring Script
====================================================

This enhanced version includes:
1. Content-based category inference (not just employer's selection)
2. Role type detection (technical, administrative, customer-facing, etc.)
3. Weighted feature distance calculation
4. Improved skill extraction with comprehensive dictionary
5. Better cluster quality metrics

Deployment: Azure ML Managed Online Endpoint
Model: Enhanced K-Means with content analysis
"""

import json
import numpy as np
import logging
import re
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, MinMaxScaler, RobustScaler
from sklearn.metrics import silhouette_score, calinski_harabasz_score, davies_bouldin_score

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


# ==========================================
# CATEGORY INDICATORS DICTIONARY
# ==========================================

CATEGORY_INDICATORS = {
    'information_technology': {
        'skills': [
            'php', 'javascript', 'python', 'java', 'c#', 'c++', 'ruby', 'go', 'rust', 'swift', 'kotlin',
            'html', 'css', 'sql', 'nosql', 'react', 'angular', 'vue', 'laravel', 'django', 'flask',
            'nodejs', 'express', 'spring', 'dotnet', '.net', 'asp.net',
            'mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch',
            'aws', 'azure', 'gcp', 'docker', 'kubernetes', 'devops', 'ci/cd',
            'git', 'api', 'rest', 'graphql', 'microservices',
            'machine learning', 'ai', 'data science', 'tensorflow', 'pytorch'
        ],
        'roles': [
            'developer', 'programmer', 'software engineer', 'web developer', 'full stack',
            'frontend', 'backend', 'mobile developer', 'devops engineer', 'system administrator',
            'data scientist', 'data analyst', 'qa engineer', 'technical lead', 'architect'
        ]
    },
    'administrative_clerical': {
        'skills': [
            'microsoft office', 'ms office', 'excel', 'word', 'powerpoint', 'outlook',
            'typing', 'data entry', 'filing', 'record keeping', 'documentation',
            'scheduling', 'calendar management', 'appointment setting',
            'correspondence', 'email management', 'office equipment'
        ],
        'roles': [
            'clerk', 'office clerk', 'general clerk', 'file clerk',
            'secretary', 'executive secretary', 'administrative assistant',
            'receptionist', 'front desk', 'office staff', 'encoder', 'typist'
        ]
    },
    'customer_service': {
        'skills': [
            'communication', 'phone etiquette', 'email support', 'live chat',
            'crm', 'salesforce', 'zendesk', 'freshdesk',
            'problem solving', 'conflict resolution', 'complaint handling',
            'customer relationship', 'client management'
        ],
        'roles': [
            'customer service representative', 'csr', 'customer support',
            'call center agent', 'contact center', 'technical support',
            'help desk', 'service desk', 'customer success'
        ]
    },
    'sales_marketing': {
        'skills': [
            'sales', 'selling', 'negotiation', 'closing deals', 'lead generation',
            'cold calling', 'prospecting', 'marketing', 'digital marketing',
            'social media marketing', 'seo', 'sem', 'content marketing',
            'google analytics', 'facebook ads', 'google ads'
        ],
        'roles': [
            'sales representative', 'sales agent', 'sales executive',
            'account executive', 'business development', 'marketing specialist',
            'digital marketer', 'social media manager', 'brand manager'
        ]
    },
    'accounting_finance': {
        'skills': [
            'accounting', 'bookkeeping', 'financial reporting', 'financial analysis',
            'accounts payable', 'accounts receivable', 'general ledger',
            'tax', 'taxation', 'audit', 'budgeting', 'payroll',
            'quickbooks', 'sap', 'oracle financials', 'excel'
        ],
        'roles': [
            'accountant', 'staff accountant', 'senior accountant',
            'bookkeeper', 'accounting clerk', 'auditor', 'finance analyst',
            'tax accountant', 'payroll specialist', 'cashier', 'billing specialist'
        ]
    },
    'human_resources': {
        'skills': [
            'recruitment', 'talent acquisition', 'sourcing', 'screening', 'interviewing',
            'onboarding', 'compensation', 'benefits administration',
            'performance management', 'training', 'employee relations',
            'hris', 'workday', 'bamboohr', 'labor law'
        ],
        'roles': [
            'hr officer', 'hr assistant', 'hr coordinator', 'hr generalist',
            'recruiter', 'talent acquisition specialist', 'hr manager',
            'compensation and benefits', 'training officer'
        ]
    },
    'healthcare_medical': {
        'skills': [
            'patient care', 'clinical', 'medical records', 'vital signs',
            'nursing', 'medication administration', 'laboratory',
            'medical terminology', 'cpr', 'first aid', 'infection control'
        ],
        'roles': [
            'nurse', 'registered nurse', 'staff nurse', 'doctor', 'physician',
            'medical technologist', 'pharmacist', 'caregiver',
            'medical secretary', 'physical therapist'
        ]
    },
    'engineering': {
        'skills': [
            'autocad', 'solidworks', 'catia', 'revit', 'project management',
            'quality control', 'process improvement', 'lean', 'six sigma',
            'technical drawing', 'blueprints', 'maintenance', 'troubleshooting'
        ],
        'roles': [
            'engineer', 'mechanical engineer', 'electrical engineer', 'civil engineer',
            'industrial engineer', 'project engineer', 'site engineer',
            'maintenance engineer', 'quality engineer'
        ]
    },
    'hospitality_tourism': {
        'skills': [
            'guest relations', 'hospitality', 'reservation', 'booking',
            'food and beverage', 'f&b', 'housekeeping', 'concierge',
            'tour guiding', 'travel planning', 'hotel management'
        ],
        'roles': [
            'front desk agent', 'receptionist', 'concierge', 'housekeeper',
            'waiter', 'waitress', 'chef', 'cook', 'hotel manager',
            'restaurant manager', 'tour guide', 'travel agent'
        ]
    },
    'logistics_supply_chain': {
        'skills': [
            'logistics', 'supply chain', 'inventory management', 'warehouse',
            'shipping', 'receiving', 'dispatching', 'procurement',
            'import', 'export', 'customs', 'freight forwarding', 'erp', 'sap'
        ],
        'roles': [
            'logistics coordinator', 'logistics officer', 'warehouse staff',
            'inventory clerk', 'procurement officer', 'purchasing agent',
            'supply chain analyst', 'dispatcher', 'delivery driver'
        ]
    },
    'manufacturing_production': {
        'skills': [
            'machine operation', 'assembly', 'production line',
            'quality control', 'inspection', 'lean manufacturing',
            'cnc', 'packaging', 'material handling', 'safety', 'gmp'
        ],
        'roles': [
            'machine operator', 'production operator', 'assembly line worker',
            'production supervisor', 'quality inspector', 'qc inspector',
            'maintenance technician', 'plant manager', 'production manager'
        ]
    },
    'creative_design': {
        'skills': [
            'graphic design', 'photoshop', 'illustrator', 'indesign',
            'ui/ux', 'figma', 'sketch', 'video editing', 'premiere pro',
            'photography', 'animation', 'motion graphics', '3d modeling'
        ],
        'roles': [
            'graphic designer', 'visual designer', 'ui designer', 'ux designer',
            'web designer', 'video editor', 'animator', 'photographer',
            'art director', 'creative director'
        ]
    },
    'bpo_outsourcing': {
        'skills': [
            'customer service', 'technical support', 'phone support',
            'email support', 'chat support', 'ticketing',
            'data entry', 'data processing', 'back office',
            'voice', 'non-voice', 'inbound', 'outbound'
        ],
        'roles': [
            'call center agent', 'csr', 'customer service representative',
            'technical support representative', 'team leader', 'supervisor',
            'quality analyst', 'trainer', 'virtual assistant'
        ]
    }
}

# Role type indicators
ROLE_TYPES = {
    'technical': {
        'keywords': ['developer', 'engineer', 'programmer', 'analyst', 'architect', 'devops', 'admin'],
        'skills': ['php', 'javascript', 'python', 'java', 'sql', 'aws', 'docker', 'linux']
    },
    'administrative': {
        'keywords': ['clerk', 'secretary', 'assistant', 'receptionist', 'encoder', 'staff'],
        'skills': ['filing', 'data entry', 'typing', 'ms office', 'excel', 'scheduling']
    },
    'customer_facing': {
        'keywords': ['customer', 'sales', 'support', 'representative', 'agent', 'service'],
        'skills': ['communication', 'phone', 'crm', 'negotiation', 'presentation']
    },
    'creative': {
        'keywords': ['designer', 'artist', 'creative', 'editor', 'photographer'],
        'skills': ['photoshop', 'illustrator', 'figma', 'video editing', 'animation']
    },
    'management': {
        'keywords': ['manager', 'supervisor', 'lead', 'head', 'director', 'chief'],
        'skills': ['leadership', 'team management', 'strategic', 'budget', 'planning']
    },
    'manual_labor': {
        'keywords': ['worker', 'operator', 'driver', 'helper', 'laborer', 'technician'],
        'skills': ['physical', 'equipment', 'maintenance', 'repair', 'assembly']
    }
}

# Feature weights for distance calculation
FEATURE_WEIGHTS = {
    'inferred_cat_1_score': 4.0,
    'inferred_cat_2_score': 2.0,
    'inferred_cat_3_score': 1.0,
    'role_technical': 3.0,
    'role_administrative': 3.0,
    'role_customer_facing': 3.0,
    'role_creative': 3.0,
    'role_management': 2.5,
    'role_manual_labor': 3.0,
    'experience_level': 2.0,
    'salary_normalized': 1.5,
    'is_remote': 0.5,
    'job_type_id': 1.0,
    'freshness': 0.5
}


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
    logger.info("Enhanced K-Means Clustering endpoint initialized")


def normalize_text(text):
    """Normalize text for comparison"""
    if not text:
        return ""
    text = text.lower()
    text = re.sub(r'[^a-z0-9\s/\-+#]', ' ', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()


def text_contains_term(text, term):
    """Check if text contains a term"""
    return term.lower() in text.lower()


def infer_job_categories(job_data):
    """
    Analyze job content and infer likely categories.
    Returns scores for each category based on content matching.
    """
    title = job_data.get('title', '')
    description = job_data.get('description', '')
    requirements = job_data.get('requirements', '')

    text = normalize_text(f"{title} {description} {requirements}")

    category_scores = {}

    for category_key, indicators in CATEGORY_INDICATORS.items():
        matched_skills = []
        matched_roles = []

        # Check skills (weight: 2)
        for skill in indicators['skills']:
            if text_contains_term(text, skill):
                matched_skills.append(skill)

        # Check roles (weight: 3)
        for role in indicators['roles']:
            if text_contains_term(text, role):
                matched_roles.append(role)

        # Calculate weighted score
        skill_score = len(matched_skills) * 2
        role_score = len(matched_roles) * 3
        total_score = skill_score + role_score

        # Calculate max possible score
        max_skill_score = len(indicators['skills']) * 2
        max_role_score = len(indicators['roles']) * 3
        max_score = max_skill_score + max_role_score

        # Normalize score
        normalized_score = min(1.0, total_score / (max_score * 0.3)) if max_score > 0 else 0

        # Boost score if role matches
        if len(matched_roles) > 0:
            normalized_score = min(1.0, normalized_score * 1.5)

        category_scores[category_key] = {
            'score': round(normalized_score, 4),
            'matched_skills': matched_skills[:5],
            'matched_roles': matched_roles[:3]
        }

    # Sort by score descending
    sorted_categories = dict(sorted(category_scores.items(), key=lambda x: x[1]['score'], reverse=True))

    return sorted_categories


def calculate_role_type_scores(job_data):
    """Calculate role type scores for a job"""
    title = job_data.get('title', '')
    description = job_data.get('description', '')
    requirements = job_data.get('requirements', '')

    text = normalize_text(f"{title} {description} {requirements}")

    scores = {}

    for role_type, indicators in ROLE_TYPES.items():
        score = 0
        max_score = len(indicators['keywords']) + len(indicators['skills'])

        for keyword in indicators['keywords']:
            if text_contains_term(text, keyword):
                score += 1

        for skill in indicators['skills']:
            if text_contains_term(text, skill):
                score += 1

        scores[role_type] = score / max_score if max_score > 0 else 0

    return scores


def extract_experience_level(requirements):
    """Extract experience level from requirements text"""
    if not requirements:
        return 0

    requirements = requirements.lower()

    # Pattern: "3-5 years"
    match = re.search(r'(\d+)\s*(?:to|-)\s*(\d+)\s*years?', requirements)
    if match:
        return (int(match.group(1)) + int(match.group(2))) / 2

    # Pattern: "5+ years"
    match = re.search(r'(\d+)\s*(?:\+|or more)\s*years?', requirements)
    if match:
        return int(match.group(1))

    # Pattern: "3 years"
    match = re.search(r'(\d+)\s*years?', requirements)
    if match:
        return int(match.group(1))

    # Keywords
    if 'senior' in requirements or 'lead' in requirements:
        return 5
    if 'mid' in requirements or 'intermediate' in requirements:
        return 3
    if 'junior' in requirements:
        return 2
    if 'entry' in requirements or 'fresh' in requirements:
        return 0

    return 2  # Default to junior/entry


def parse_salary_range(salary_string):
    """Parse salary range from string"""
    if not salary_string:
        return {'min': 0, 'max': 0}

    numbers = re.findall(r'[\d,]+', str(salary_string))
    numbers = [float(n.replace(',', '')) for n in numbers]

    if len(numbers) >= 2:
        return {'min': min(numbers), 'max': max(numbers)}
    elif len(numbers) == 1:
        return {'min': numbers[0], 'max': numbers[0]}

    return {'min': 0, 'max': 0}


def extract_enhanced_features(job_data):
    """Extract enhanced features for a job"""
    # Get inferred categories
    inferred_categories = infer_job_categories(job_data)
    top_categories = list(inferred_categories.items())[:3]

    # Get role type scores
    role_type_scores = calculate_role_type_scores(job_data)

    # Parse salary
    salary = parse_salary_range(job_data.get('salary_range', ''))
    avg_salary = (salary['min'] + salary['max']) / 2
    salary_normalized = min(1.0, avg_salary / 200000) if avg_salary > 0 else 0

    # Build feature vector
    features = {
        # Content-based category scores
        'inferred_cat_1_score': top_categories[0][1]['score'] if len(top_categories) > 0 else 0,
        'inferred_cat_2_score': top_categories[1][1]['score'] if len(top_categories) > 1 else 0,
        'inferred_cat_3_score': top_categories[2][1]['score'] if len(top_categories) > 2 else 0,

        # Role type scores
        'role_technical': role_type_scores.get('technical', 0),
        'role_administrative': role_type_scores.get('administrative', 0),
        'role_customer_facing': role_type_scores.get('customer_facing', 0),
        'role_creative': role_type_scores.get('creative', 0),
        'role_management': role_type_scores.get('management', 0),
        'role_manual_labor': role_type_scores.get('manual_labor', 0),

        # Traditional features
        'experience_level': extract_experience_level(job_data.get('requirements', '')) / 10,
        'salary_normalized': salary_normalized,
        'is_remote': float(job_data.get('is_remote', 0)),
        'job_type_id': float(job_data.get('job_type_id', 0)) / 10,

        # Freshness (passed from PHP)
        'freshness': float(job_data.get('freshness', 0.5)),
    }

    return features, inferred_categories


def weighted_euclidean_distance(point1, point2, weights=None):
    """Calculate weighted Euclidean distance"""
    if weights is None:
        weights = FEATURE_WEIGHTS

    distance = 0
    for key in point1:
        if key in point2:
            weight = weights.get(key, 1.0)
            diff = point1[key] - point2[key]
            distance += weight * diff * diff

    return np.sqrt(distance)


def run(raw_data):
    """
    Run enhanced K-means clustering on the provided data.

    Expected input JSON format:
    {
        "data": [
            {
                "id": job_id,
                "title": "Job Title",
                "description": "Job description...",
                "requirements": "Job requirements...",
                "salary_range": "50000-80000",
                "job_type_id": 1,
                "is_remote": 0,
                "freshness": 0.8
            },
            ...
        ],
        "k": 5,
        "max_iterations": 100,
        "use_content_analysis": true,
        "include_metrics": true
    }

    Returns:
    {
        "labels": [0, 1, 2, ...],
        "centroids": [[...], [...], ...],
        "clusters": {...},
        "inferred_categories": {...},
        "metrics": {...}
    }
    """
    try:
        # Parse input
        request = json.loads(raw_data)

        data = request.get('data', [])
        k = request.get('k', 5)
        max_iterations = request.get('max_iterations', 100)
        use_content_analysis = request.get('use_content_analysis', True)
        include_metrics = request.get('include_metrics', True)
        scaling_config = request.get('scaling', {'enabled': True, 'method': 'standard'})

        if not data:
            return json.dumps({
                'error': 'No data provided',
                'labels': [],
                'centroids': [],
                'clusters': {}
            })

        # Extract features for each job
        feature_vectors = []
        job_inferred_categories = {}
        feature_names = None

        for item in data:
            if use_content_analysis:
                features, inferred = extract_enhanced_features(item)
                job_id = item.get('id', len(feature_vectors))
                job_inferred_categories[str(job_id)] = {
                    k: v for k, v in list(inferred.items())[:3]
                }
            else:
                # Use traditional features only
                features = {
                    'job_type_id': float(item.get('job_type_id', 0)) / 10,
                    'salary_normalized': min(1.0, float(item.get('salary_normalized', 0)) / 200000),
                    'experience_level': float(item.get('experience_level', 0)) / 10,
                    'is_remote': float(item.get('is_remote', 0)),
                    'freshness': float(item.get('freshness', 0.5))
                }

            if feature_names is None:
                feature_names = list(features.keys())

            feature_vectors.append([features[k] for k in feature_names])

        X = np.array(feature_vectors, dtype=float)

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

        # Apply feature weights
        weights = np.array([FEATURE_WEIGHTS.get(name, 1.0) for name in feature_names])
        X_weighted = X_scaled * np.sqrt(weights)

        # Run K-means clustering
        kmeans = KMeans(
            n_clusters=k,
            max_iter=max_iterations,
            tol=0.0001,
            algorithm='lloyd',
            init='k-means++',
            n_init=10,
            random_state=42
        )

        labels = kmeans.fit_predict(X_weighted)

        # Get centroids (inverse transform if scaled)
        centroids_weighted = kmeans.cluster_centers_
        if scaling_config.get('enabled', True):
            centroids_scaled = centroids_weighted / np.sqrt(weights)
            centroids = scaler.inverse_transform(centroids_scaled)
        else:
            centroids = centroids_weighted / np.sqrt(weights)

        # Build cluster information
        clusters = {}
        for i in range(k):
            cluster_indices = np.where(labels == i)[0].tolist()
            cluster_job_ids = []
            for idx in cluster_indices:
                if idx < len(data):
                    cluster_job_ids.append(data[idx].get('id', idx))

            clusters[str(i)] = {
                'indices': cluster_indices,
                'job_ids': cluster_job_ids,
                'size': len(cluster_indices)
            }

        # Prepare response
        response = {
            'labels': labels.tolist(),
            'centroids': centroids.tolist(),
            'centroids_scaled': centroids_weighted.tolist(),
            'clusters': clusters,
            'feature_names': feature_names,
            'inertia': float(kmeans.inertia_),
            'n_iterations': int(kmeans.n_iter_),
            'k': k
        }

        # Add inferred categories if content analysis was used
        if use_content_analysis:
            response['job_inferred_categories'] = job_inferred_categories

        # Calculate additional metrics if requested
        if include_metrics and n_samples > k:
            try:
                sil_score = silhouette_score(X_weighted, labels)
                ch_score = calinski_harabasz_score(X_weighted, labels)
                db_score = davies_bouldin_score(X_weighted, labels)

                response['metrics'] = {
                    'silhouette_score': float(sil_score),
                    'calinski_harabasz_score': float(ch_score),
                    'davies_bouldin_score': float(db_score),
                    'inertia': float(kmeans.inertia_),
                    'cluster_sizes': [clusters[str(i)]['size'] for i in range(k)]
                }
            except Exception as e:
                logger.warning(f"Could not calculate metrics: {e}")
                response['metrics'] = {
                    'inertia': float(kmeans.inertia_),
                    'error': str(e)
                }

        logger.info(f"Enhanced clustering completed: k={k}, samples={n_samples}, "
                   f"content_analysis={use_content_analysis}, inertia={kmeans.inertia_:.2f}")

        return json.dumps(response)

    except Exception as e:
        logger.error(f"Enhanced clustering error: {str(e)}")
        return json.dumps({
            'error': str(e),
            'labels': [],
            'centroids': [],
            'clusters': {}
        })


def detect_category_mismatch(job_data, employer_category_id):
    """
    Detect if there's a mismatch between employer's category selection
    and the actual job content.
    """
    inferred = infer_job_categories(job_data)
    top_category = list(inferred.items())[0] if inferred else None

    if not top_category:
        return {
            'has_mismatch': False,
            'confidence': 'none'
        }

    top_key, top_data = top_category

    # Map category keys to potential IDs (this should match your database)
    # In production, this mapping should come from the request
    has_mismatch = top_data['score'] >= 0.3 and employer_category_id != top_key

    return {
        'has_mismatch': has_mismatch,
        'employer_category': employer_category_id,
        'inferred_category': top_key,
        'inferred_score': top_data['score'],
        'matched_roles': top_data['matched_roles'],
        'matched_skills': top_data['matched_skills']
    }


# For local testing
if __name__ == "__main__":
    # Initialize
    init()

    # Test data with intentional category mismatch
    # Employer selected "Information Technology" but job is actually "Administrative"
    test_data = {
        "data": [
            {
                "id": 1,
                "title": "Office Clerk",
                "description": "Looking for an Office Clerk to handle filing, data entry, scheduling appointments, and answering phone calls. Must be proficient in Microsoft Office.",
                "requirements": "1-2 years experience in clerical work. Good typing skills. Knowledge of MS Office.",
                "salary_range": "15000-20000",
                "job_type_id": 1,
                "is_remote": 0,
                "freshness": 0.9
            },
            {
                "id": 2,
                "title": "Full Stack Developer",
                "description": "We are looking for a Full Stack Developer proficient in PHP, Laravel, JavaScript, and React. Experience with REST APIs and MySQL required.",
                "requirements": "3-5 years experience in web development. Strong knowledge of PHP, JavaScript, SQL.",
                "salary_range": "60000-80000",
                "job_type_id": 1,
                "is_remote": 1,
                "freshness": 0.8
            },
            {
                "id": 3,
                "title": "Customer Service Representative",
                "description": "Handle customer inquiries via phone and email. Resolve complaints and provide excellent customer support.",
                "requirements": "1 year experience in customer service. Good communication skills.",
                "salary_range": "18000-25000",
                "job_type_id": 1,
                "is_remote": 0,
                "freshness": 0.7
            },
            {
                "id": 4,
                "title": "Accountant",
                "description": "Prepare financial statements, handle accounts payable and receivable, assist with tax filing and audit preparation.",
                "requirements": "CPA preferred. 2-3 years accounting experience. Proficient in QuickBooks.",
                "salary_range": "35000-50000",
                "job_type_id": 1,
                "is_remote": 0,
                "freshness": 0.6
            },
            {
                "id": 5,
                "title": "Sales Representative",
                "description": "Generate leads, close deals, and meet sales targets. Build relationships with clients.",
                "requirements": "1-2 years sales experience. Strong negotiation and presentation skills.",
                "salary_range": "20000-30000",
                "job_type_id": 1,
                "is_remote": 0,
                "freshness": 0.5
            }
        ],
        "k": 3,
        "max_iterations": 100,
        "use_content_analysis": True,
        "include_metrics": True,
        "scaling": {"enabled": True, "method": "standard"}
    }

    result = run(json.dumps(test_data))
    parsed_result = json.loads(result)

    print("=" * 60)
    print("ENHANCED K-MEANS CLUSTERING TEST RESULT")
    print("=" * 60)

    print("\n1. CLUSTER ASSIGNMENTS:")
    for i, label in enumerate(parsed_result['labels']):
        job = test_data['data'][i]
        print(f"   Job {job['id']} ({job['title']}): Cluster {label}")

    print("\n2. CLUSTER SIZES:")
    for cluster_id, cluster_data in parsed_result['clusters'].items():
        print(f"   Cluster {cluster_id}: {cluster_data['size']} jobs")

    print("\n3. INFERRED CATEGORIES (Content Analysis):")
    for job_id, categories in parsed_result.get('job_inferred_categories', {}).items():
        job = next((j for j in test_data['data'] if str(j['id']) == job_id), None)
        if job:
            print(f"\n   Job {job_id} ({job['title']}):")
            for cat_key, cat_data in categories.items():
                print(f"      - {cat_key}: {cat_data['score']:.3f}")
                if cat_data.get('matched_roles'):
                    print(f"        Matched roles: {', '.join(cat_data['matched_roles'])}")

    print("\n4. METRICS:")
    metrics = parsed_result.get('metrics', {})
    print(f"   Silhouette Score: {metrics.get('silhouette_score', 'N/A'):.4f}")
    print(f"   Calinski-Harabasz Score: {metrics.get('calinski_harabasz_score', 'N/A'):.2f}")
    print(f"   Davies-Bouldin Score: {metrics.get('davies_bouldin_score', 'N/A'):.4f}")
    print(f"   Inertia: {metrics.get('inertia', 'N/A'):.2f}")

    print("\n5. CATEGORY MISMATCH DETECTION TEST:")
    # Test mismatch detection for the "Office Clerk" job
    mismatch = detect_category_mismatch(test_data['data'][0], 'information_technology')
    print(f"   Job: Office Clerk")
    print(f"   Employer Category: information_technology")
    print(f"   Has Mismatch: {mismatch['has_mismatch']}")
    print(f"   Inferred Category: {mismatch['inferred_category']}")
    print(f"   Confidence Score: {mismatch['inferred_score']:.3f}")
    print(f"   Matched Roles: {mismatch['matched_roles']}")

    print("\n" + "=" * 60)
