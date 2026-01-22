@extends('layouts.employer')

@section('page_title', 'Preview - ' . $poster->job_title)

@section('content')
    <div class="preview-page">
        <!-- Header -->
        <div class="preview-header">
            <div class="header-left">
                <a href="{{ route('employer.posters.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="page-title">{{ $poster->job_title }}</h1>
                    <p class="page-meta">
                        <span class="badge bg-pink">
                            <i class="bi bi-brush me-1"></i>PosterMyWall
                        </span>
                        <span class="created-date">Created {{ $poster->created_at->diffForHumans() }}</span>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('employer.posters.editor', $poster->id) }}" class="btn-action btn-edit">
                    <i class="bi bi-pencil"></i>
                    <span>Edit Design</span>
                </a>
                <div class="dropdown">
                    <button class="btn-action btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i>
                        <span>Download</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('employer.posters.download', ['poster' => $poster->id, 'format' => 'png']) }}">
                                <i class="bi bi-file-image me-2"></i>PNG Image
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('employer.posters.download', ['poster' => $poster->id, 'format' => 'jpg']) }}">
                                <i class="bi bi-file-image me-2"></i>JPG Image
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('employer.posters.download', ['poster' => $poster->id, 'format' => 'pdf']) }}">
                                <i class="bi bi-file-pdf me-2"></i>PDF Document
                            </a>
                        </li>
                    </ul>
                </div>
                <form action="{{ route('employer.posters.duplicate', $poster->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-action btn-duplicate">
                        <i class="bi bi-copy"></i>
                        <span>Duplicate</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="preview-content">
            <div class="row g-4">
                <!-- Poster Preview -->
                <div class="col-lg-8">
                    <div class="preview-card">
                        <div class="preview-image-container">
                            @if($poster->pmw_preview_url)
                                <img src="{{ $poster->pmw_preview_url }}"
                                     alt="{{ $poster->job_title }}"
                                     class="poster-image"
                                     onerror="this.src='/images/poster-placeholder.png'">
                            @else
                                <div class="no-preview">
                                    <i class="bi bi-image"></i>
                                    <p>Preview not available</p>
                                    <a href="{{ route('employer.posters.editor', $poster->id) }}" class="btn btn-primary btn-sm">
                                        Open Editor to Generate Preview
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Poster Details -->
                <div class="col-lg-4">
                    <div class="details-card">
                        <h3 class="details-title">
                            <i class="bi bi-info-circle me-2"></i>Poster Details
                        </h3>

                        <div class="detail-section">
                            <label>Job Title</label>
                            <p>{{ $poster->job_title }}</p>
                        </div>

                        <div class="detail-section">
                            <label>Company</label>
                            <p>{{ $poster->company_name }}</p>
                        </div>

                        @if($poster->location)
                            <div class="detail-section">
                                <label>Location</label>
                                <p>{{ $poster->location }}</p>
                            </div>
                        @endif

                        @if($poster->salary_range)
                            <div class="detail-section">
                                <label>Salary Range</label>
                                <p>{{ $poster->salary_range }}</p>
                            </div>
                        @endif

                        @if($poster->employment_type)
                            <div class="detail-section">
                                <label>Employment Type</label>
                                <p>{{ $poster->employment_type }}</p>
                            </div>
                        @endif

                        @if($poster->deadline)
                            <div class="detail-section">
                                <label>Application Deadline</label>
                                <p>{{ $poster->deadline->format('F j, Y') }}</p>
                            </div>
                        @endif

                        @if($poster->job)
                            <div class="detail-section">
                                <label>Linked Job</label>
                                <a href="{{ route('employer.jobs.show', $poster->job_id) }}" class="linked-job">
                                    <i class="bi bi-briefcase me-1"></i>{{ $poster->job->title }}
                                    <i class="bi bi-arrow-right ms-auto"></i>
                                </a>
                            </div>
                        @endif

                        @if($poster->contact_email || $poster->contact_phone)
                            <div class="detail-section">
                                <label>Contact Information</label>
                                @if($poster->contact_email)
                                    <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $poster->contact_email }}</p>
                                @endif
                                @if($poster->contact_phone)
                                    <p class="mb-0"><i class="bi bi-phone me-2"></i>{{ $poster->contact_phone }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    <div class="actions-card">
                        <h3 class="actions-title">
                            <i class="bi bi-lightning me-2"></i>Quick Actions
                        </h3>

                        <a href="{{ route('employer.posters.editor', $poster->id) }}" class="action-button">
                            <i class="bi bi-pencil-square"></i>
                            <span>Edit in PosterMyWall</span>
                        </a>

                        <button type="button" class="action-button" onclick="sharePoster()">
                            <i class="bi bi-share"></i>
                            <span>Share Poster</span>
                        </button>

                        <button type="button" class="action-button danger" onclick="deletePoster()">
                            <i class="bi bi-trash"></i>
                            <span>Delete Poster</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .preview-page {
            max-width: 1200px;
            margin: 0 auto;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-link {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .back-link:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .page-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0.25rem 0 0;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .badge.bg-pink {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #fef3c7;
            color: #d97706;
        }

        .btn-edit:hover {
            background: #fde68a;
            color: #b45309;
        }

        .btn-download {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .btn-download:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
        }

        .btn-duplicate {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .btn-duplicate:hover {
            background: #c7d2fe;
            color: #4338ca;
        }

        .preview-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .preview-image-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 500px;
            background: #f8fafc;
            padding: 2rem;
        }

        .poster-image {
            max-width: 100%;
            max-height: 700px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .no-preview {
            text-align: center;
            padding: 3rem;
        }

        .no-preview i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .no-preview p {
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .details-card, .actions-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
            margin-bottom: 1.5rem;
        }

        .details-title, .actions-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 1.25rem;
            display: flex;
            align-items: center;
        }

        .detail-section {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-section label {
            display: block;
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .detail-section p {
            color: #1f2937;
            font-weight: 500;
            margin: 0;
        }

        .linked-job {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            background: #f3f4f6;
            border-radius: 8px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .linked-job:hover {
            background: #e5e7eb;
        }

        .action-button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.875rem 1rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            color: #374151;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0.75rem;
        }

        .action-button:last-child {
            margin-bottom: 0;
        }

        .action-button:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .action-button.danger {
            color: #dc2626;
        }

        .action-button.danger:hover {
            background: #fef2f2;
            border-color: #fecaca;
        }

        @media (max-width: 768px) {
            .preview-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions {
                justify-content: center;
            }
        }
    </style>

    @push('scripts')
        <script>
            function sharePoster() {
                if (navigator.share) {
                    navigator.share({
                        title: '{{ $poster->job_title }} - {{ $poster->company_name }}',
                        text: 'Check out this job opportunity!',
                        url: window.location.href
                    });
                } else {
                    // Fallback: copy URL to clipboard
                    navigator.clipboard.writeText(window.location.href).then(() => {
                        alert('Link copied to clipboard!');
                    });
                }
            }

            function deletePoster() {
                if (confirm('Are you sure you want to delete this poster? This action cannot be undone.')) {
                    fetch('{{ route("employer.posters.destroy", $poster->id) }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            window.location.href = '{{ route("employer.posters.index") }}';
                        } else {
                            alert(data.message || 'Failed to delete poster.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                }
            }
        </script>
    @endpush
@endsection
