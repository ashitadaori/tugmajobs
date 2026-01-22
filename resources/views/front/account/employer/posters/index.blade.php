@extends('layouts.employer')

@section('page_title', 'Poster Builder')

@section('content')
    <div class="posters-page">
        <!-- Header Section -->
        <div class="posters-header">
            <div class="header-left">
                <h1 class="page-title">
                    <i class="bi bi-images me-2"></i>Poster Builder
                </h1>
                <p class="page-description">Create eye-catching hiring posters for your job openings</p>
            </div>
            <div class="header-right">
                <a href="{{ route('employer.posters.create') }}" class="btn-create-poster">
                    <i class="bi bi-plus-lg"></i>
                    <span>Create New Poster</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="bi bi-images"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $posters->total() }}</span>
                    <span class="stat-label">Total Posters</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $posters->where('created_at', '>=', now()->subMonth())->count() }}</span>
                    <span class="stat-label">This Month</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon linked">
                    <i class="bi bi-link-45deg"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $posters->whereNotNull('job_id')->count() }}</span>
                    <span class="stat-label">Linked to Jobs</span>
                </div>
            </div>
        </div>

        <!-- Posters Grid -->
        @if($posters->count() > 0)
            <div class="posters-grid">
                @foreach($posters as $poster)
                    <div class="poster-card" data-poster-id="{{ $poster->id }}">
                        @if($poster->isPosterMyWall() && $poster->pmw_preview_url)
                            <div class="poster-preview poster-preview-pmw">
                                <img src="{{ $poster->pmw_preview_url }}" alt="{{ $poster->job_title }}" class="pmw-preview-img">
                                <div class="pmw-badge">
                                    <i class="bi bi-brush"></i> PosterMyWall
                                </div>
                                @if($poster->job)
                                    <div class="linked-badge">
                                        <i class="bi bi-link-45deg"></i> Linked
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="poster-preview poster-preview-{{ $poster->template->slug ?? 'default' }}">
                                <div class="preview-content">
                                    <div class="preview-badge">{{ strtoupper(substr($poster->template->name ?? 'Template', 0, 1)) }}
                                    </div>
                                    <div class="preview-title">{{ Str::limit($poster->job_title, 25) }}</div>
                                    <div class="preview-company">{{ Str::limit($poster->company_name, 20) }}</div>
                                </div>
                                @if($poster->job)
                                    <div class="linked-badge">
                                        <i class="bi bi-link-45deg"></i> Linked
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="poster-info">
                            <h4 class="poster-title">{{ Str::limit($poster->job_title, 30) }}</h4>
                            <p class="poster-meta">
                                <span class="template-name">
                                    @if($poster->isPosterMyWall())
                                        <i class="bi bi-brush"></i> PosterMyWall
                                    @else
                                        <i class="bi bi-palette"></i> {{ $poster->template->name ?? 'Unknown Template' }}
                                    @endif
                                </span>
                                <span class="poster-date">
                                    <i class="bi bi-clock"></i> {{ $poster->created_at->diffForHumans() }}
                                </span>
                            </p>
                        </div>
                        <div class="poster-actions">
                            <a href="{{ route('employer.posters.preview', $poster->id) }}" class="btn-action btn-preview"
                                title="Preview">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($poster->isPosterMyWall())
                                <a href="{{ route('employer.posters.editor', $poster->id) }}" class="btn-action btn-edit" title="Edit in PosterMyWall">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @else
                                <a href="{{ route('employer.posters.edit', $poster->id) }}" class="btn-action btn-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                            <a href="{{ route('employer.posters.download', $poster->id) }}" class="btn-action btn-download"
                                title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            <form action="{{ route('employer.posters.duplicate', $poster->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-action btn-duplicate" title="Duplicate">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </form>
                            <button type="button" class="btn-action btn-delete" title="Delete"
                                onclick="deletePoster({{ $poster->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $posters->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-images"></i>
                </div>
                <h3>No Posters Yet</h3>
                <p>Create your first hiring poster to attract more candidates!</p>
                <a href="{{ route('employer.posters.create') }}" class="btn-create-first">
                    <i class="bi bi-plus-lg me-2"></i>Create Your First Poster
                </a>
            </div>
        @endif
    </div>

    <style>
        .posters-page {
            padding: 0;
        }

        .posters-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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

        .btn-create-poster {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-create-poster:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-icon.linked {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* Posters Grid */
        .posters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .poster-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
            transition: all 0.3s ease;
        }

        .poster-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .poster-preview {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .poster-preview-blue-megaphone {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .poster-preview-yellow-attention {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }

        .poster-preview-modern-corporate {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        }

        .poster-preview-minimalist-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .poster-preview-default {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .poster-preview-pmw {
            background: #f8fafc;
            position: relative;
        }

        .pmw-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pmw-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .preview-content {
            text-align: center;
            color: white;
            padding: 1rem;
        }

        .preview-badge {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .preview-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .preview-company {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .linked-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.95);
            color: #059669;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .poster-info {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .poster-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 0.5rem;
        }

        .poster-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 0;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .poster-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .poster-actions {
            display: flex;
            justify-content: center;
            padding: 0.75rem;
            gap: 0.5rem;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-preview {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .btn-preview:hover {
            background: #4f46e5;
            color: white;
        }

        .btn-edit {
            background: #fef3c7;
            color: #d97706;
        }

        .btn-edit:hover {
            background: #d97706;
            color: white;
        }

        .btn-download {
            background: #d1fae5;
            color: #059669;
        }

        .btn-download:hover {
            background: #059669;
            color: white;
        }

        .btn-duplicate {
            background: #e0e7ff;
            color: #6366f1;
        }

        .btn-duplicate:hover {
            background: #6366f1;
            color: white;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #dc2626;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .empty-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .btn-create-first {
            display: inline-flex;
            align-items: center;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-create-first:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Pagination */
        .pagination-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .posters-header {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-create-poster {
                justify-content: center;
            }

            .posters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @push('scripts')
        <script>
            function deletePoster(posterId) {
                if (confirm('Are you sure you want to delete this poster?')) {
                    fetch(`{{ url('employer/posters') }}/${posterId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                // Remove the poster card from DOM
                                document.querySelector(`[data-poster-id="${posterId}"]`).remove();
                                // Show success message
                                alert('Poster deleted successfully!');
                                location.reload();
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