@extends('layouts.employer')

@section('page_title', 'Browse Templates')

@section('content')
    <div class="templates-page">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <a href="{{ route('employer.posters.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Posters</span>
                </a>
                <h1 class="page-title">
                    <i class="bi bi-grid me-2"></i>Choose a Template
                </h1>
                <p class="page-description">Browse professional templates from PosterMyWall for your hiring poster</p>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter-section">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="template-search" placeholder="Search templates..." value="{{ $query }}">
            </div>
            <div class="category-pills">
                <button class="category-pill {{ $category == 'hiring' ? 'active' : '' }}" data-category="hiring">
                    <i class="bi bi-briefcase me-1"></i>Hiring
                </button>
                <button class="category-pill {{ $category == 'business' ? 'active' : '' }}" data-category="business">
                    <i class="bi bi-building me-1"></i>Business
                </button>
                <button class="category-pill {{ $category == 'corporate' ? 'active' : '' }}" data-category="corporate">
                    <i class="bi bi-diagram-3 me-1"></i>Corporate
                </button>
                <button class="category-pill {{ $category == 'professional' ? 'active' : '' }}" data-category="professional">
                    <i class="bi bi-person-badge me-1"></i>Professional
                </button>
            </div>
        </div>

        <!-- Templates Grid -->
        <div id="templates-grid" class="templates-grid">
            @if(count($templates) > 0)
                @foreach($templates as $template)
                    <div class="template-card" data-template-id="{{ $template['id'] ?? '' }}">
                        <img src="{{ $template['preview_url'] ?? $template['thumbnail_url'] ?? '/images/placeholder-template.png' }}"
                             alt="{{ $template['name'] ?? 'Template' }}"
                             class="template-thumbnail"
                             onerror="this.src='/images/placeholder-template.png'">
                        <div class="template-overlay">
                            <button class="btn-use-template">
                                <i class="bi bi-check-lg me-1"></i>Use Template
                            </button>
                        </div>
                        <div class="template-info">
                            <h5 class="template-name">{{ $template['name'] ?? 'Untitled Template' }}</h5>
                            <p class="template-category">{{ $template['category'] ?? 'General' }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-templates">
                    <i class="bi bi-images"></i>
                    <h4>No templates found</h4>
                    <p>Try adjusting your search or category filter</p>
                </div>
            @endif
        </div>

        <!-- Load More -->
        @if($pagination && ($pagination['has_more'] ?? false))
            <div id="load-more-wrapper" class="load-more-wrapper">
                <button type="button" id="load-more-btn" class="btn-load-more">
                    <i class="bi bi-arrow-down-circle me-2"></i>Load More Templates
                </button>
            </div>
        @endif

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="loading-indicator" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Template Selection Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle me-2"></i>Use This Template
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="template-preview-large">
                                <img id="modal-template-preview" src="" alt="Template Preview">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="template-details">
                                <h4 id="modal-template-name"></h4>
                                <p id="modal-template-description" class="text-muted"></p>

                                <hr>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-briefcase me-1"></i>Link to a Job (Optional)
                                    </label>
                                    <select id="modal-job-select" class="form-select">
                                        <option value="">-- No job selected --</option>
                                        @php
                                            $jobs = \App\Models\Job::where('user_id', auth()->id())
                                                ->where('status', 1)
                                                ->orderBy('created_at', 'desc')
                                                ->get();
                                        @endphp
                                        @foreach($jobs as $job)
                                            <option value="{{ $job->id }}">{{ $job->title }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Linking to a job will pre-fill poster details</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-template-btn" class="btn btn-primary">
                        <i class="bi bi-brush me-2"></i>Create Design
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .templates-page {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #4f46e5;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .page-description {
            color: #6b7280;
            margin: 0.5rem 0 0;
        }

        .search-filter-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
        }

        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-box input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .category-pill {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 50px;
            background: white;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .category-pill:hover {
            border-color: #c7d2fe;
            background: #f5f3ff;
            color: #4f46e5;
        }

        .category-pill.active {
            border-color: #6366f1;
            background: #6366f1;
            color: white;
        }

        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .template-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .template-card:hover {
            border-color: #6366f1;
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.15);
        }

        .template-card:hover .template-overlay {
            opacity: 1;
        }

        .template-thumbnail {
            width: 100%;
            height: 320px;
            object-fit: cover;
            background: #f3f4f6;
        }

        .template-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 50px;
            background: rgba(99, 102, 241, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-use-template {
            padding: 0.75rem 1.5rem;
            background: white;
            color: #6366f1;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-use-template:hover {
            transform: scale(1.05);
        }

        .template-info {
            padding: 1rem;
            background: white;
        }

        .template-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .template-category {
            font-size: 0.8rem;
            color: #6b7280;
            margin: 0.25rem 0 0;
        }

        .no-templates {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }

        .no-templates i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .no-templates h4 {
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .no-templates p {
            color: #6b7280;
        }

        .load-more-wrapper {
            text-align: center;
            padding: 2rem 0;
        }

        .btn-load-more {
            display: inline-flex;
            align-items: center;
            padding: 0.875rem 2rem;
            background: white;
            color: #4b5563;
            border: 2px solid #e5e7eb;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-load-more:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .loading-indicator {
            text-align: center;
            padding: 2rem;
        }

        /* Modal Styles */
        .template-preview-large {
            text-align: center;
        }

        .template-preview-large img {
            max-width: 100%;
            max-height: 350px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .template-details h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
        }

        .template-details .form-group {
            margin-top: 1.5rem;
        }

        .template-details .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .templates-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 1rem;
            }

            .template-thumbnail {
                height: 220px;
            }

            .category-pills {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 0.5rem;
            }

            .category-pill {
                white-space: nowrap;
            }
        }
    </style>

    @push('scripts')
        <script>
            let currentPage = 1;
            let isLoading = false;
            let selectedTemplateId = null;
            let selectedTemplateData = null;

            document.addEventListener('DOMContentLoaded', function() {
                // Search with debounce
                let searchTimeout;
                document.getElementById('template-search').addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        currentPage = 1;
                        loadTemplates(true);
                    }, 500);
                });

                // Category pills
                document.querySelectorAll('.category-pill').forEach(pill => {
                    pill.addEventListener('click', function() {
                        document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
                        this.classList.add('active');
                        currentPage = 1;
                        loadTemplates(true);
                    });
                });

                // Load more button
                const loadMoreBtn = document.getElementById('load-more-btn');
                if (loadMoreBtn) {
                    loadMoreBtn.addEventListener('click', function() {
                        currentPage++;
                        loadTemplates(false);
                    });
                }

                // Template card clicks
                document.querySelectorAll('.template-card').forEach(card => {
                    card.addEventListener('click', function() {
                        openTemplateModal({
                            id: this.dataset.templateId,
                            name: this.querySelector('.template-name').textContent,
                            preview_url: this.querySelector('.template-thumbnail').src,
                            category: this.querySelector('.template-category').textContent
                        });
                    });
                });

                // Confirm template selection
                document.getElementById('confirm-template-btn').addEventListener('click', function() {
                    if (selectedTemplateId) {
                        selectTemplate(selectedTemplateId);
                    }
                });
            });

            function loadTemplates(reset = false) {
                if (isLoading) return;
                isLoading = true;

                const grid = document.getElementById('templates-grid');
                const loadingIndicator = document.getElementById('loading-indicator');
                const loadMoreWrapper = document.getElementById('load-more-wrapper');
                const query = document.getElementById('template-search').value;
                const category = document.querySelector('.category-pill.active')?.dataset.category || 'hiring';

                loadingIndicator.style.display = 'block';

                if (reset) {
                    grid.innerHTML = '';
                }

                fetch(`{{ route('employer.posters.templates') }}?q=${encodeURIComponent(query)}&category=${category}&page=${currentPage}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    isLoading = false;
                    loadingIndicator.style.display = 'none';

                    if (data.success && data.templates && data.templates.length > 0) {
                        data.templates.forEach(template => {
                            grid.appendChild(createTemplateCard(template));
                        });

                        if (loadMoreWrapper) {
                            loadMoreWrapper.style.display = (data.pagination && data.pagination.has_more) ? 'block' : 'none';
                        }
                    } else if (reset) {
                        grid.innerHTML = `
                            <div class="no-templates">
                                <i class="bi bi-images"></i>
                                <h4>No templates found</h4>
                                <p>Try adjusting your search or category filter</p>
                            </div>
                        `;
                        if (loadMoreWrapper) loadMoreWrapper.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading templates:', error);
                    isLoading = false;
                    loadingIndicator.style.display = 'none';
                });
            }

            function createTemplateCard(template) {
                const card = document.createElement('div');
                card.className = 'template-card';
                card.dataset.templateId = template.id;
                card.innerHTML = `
                    <img src="${template.preview_url || template.thumbnail_url || '/images/placeholder-template.png'}"
                         alt="${template.name}"
                         class="template-thumbnail"
                         onerror="this.src='/images/placeholder-template.png'">
                    <div class="template-overlay">
                        <button class="btn-use-template">
                            <i class="bi bi-check-lg me-1"></i>Use Template
                        </button>
                    </div>
                    <div class="template-info">
                        <h5 class="template-name">${template.name || 'Untitled Template'}</h5>
                        <p class="template-category">${template.category || 'General'}</p>
                    </div>
                `;

                card.addEventListener('click', () => openTemplateModal(template));

                return card;
            }

            function openTemplateModal(template) {
                selectedTemplateId = template.id;
                selectedTemplateData = template;

                document.getElementById('modal-template-preview').src = template.preview_url || template.thumbnail_url || '/images/placeholder-template.png';
                document.getElementById('modal-template-name').textContent = template.name || 'Untitled Template';
                document.getElementById('modal-template-description').textContent = 'This template will be customized with your company information and job details. Click "Create Design" to start editing.';

                const modal = new bootstrap.Modal(document.getElementById('templateModal'));
                modal.show();
            }

            function selectTemplate(templateId) {
                const jobId = document.getElementById('modal-job-select').value;
                const btn = document.getElementById('confirm-template-btn');

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

                fetch(`{{ url('employer/posters/templates') }}/${templateId}/select`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        job_id: jobId || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Failed to create design. Please try again.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-brush me-2"></i>Create Design';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-brush me-2"></i>Create Design';
                });
            }
        </script>
    @endpush
@endsection
