@extends('layouts.employer')

@section('page_title', 'Edit Poster - ' . $poster->job_title)

@section('content')
    <div class="editor-page">
        <!-- Header -->
        <div class="editor-header">
            <div class="header-left">
                <a href="{{ route('employer.posters.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Posters</span>
                </a>
                <div class="poster-info">
                    <h1 class="poster-title">{{ $poster->job_title }}</h1>
                    <span class="poster-meta">
                        <i class="bi bi-brush me-1"></i>Editing in PosterMyWall
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('employer.posters.preview', $poster->id) }}" class="btn-action btn-preview">
                    <i class="bi bi-eye me-1"></i>Preview
                </a>
                <a href="{{ route('employer.posters.download', $poster->id) }}" class="btn-action btn-download">
                    <i class="bi bi-download me-1"></i>Download
                </a>
            </div>
        </div>

        <!-- Editor Container -->
        <div class="editor-container">
            <div class="editor-frame-wrapper">
                @if($editorUrl)
                    <iframe id="pmw-editor"
                            src="{{ $editorUrl }}"
                            class="editor-frame"
                            frameborder="0"
                            allowfullscreen>
                    </iframe>
                @else
                    <div class="editor-placeholder">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>Unable to load editor</h3>
                        <p>There was an issue loading the PosterMyWall editor. Please try again or contact support.</p>
                        <a href="{{ route('employer.posters.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Posters
                        </a>
                    </div>
                @endif
            </div>

            <!-- Sidebar with Poster Info -->
            <div class="editor-sidebar">
                <div class="sidebar-section">
                    <h4 class="sidebar-title">
                        <i class="bi bi-info-circle me-2"></i>Poster Details
                    </h4>
                    <div class="detail-item">
                        <span class="label">Job Title</span>
                        <span class="value">{{ $poster->job_title }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Company</span>
                        <span class="value">{{ $poster->company_name }}</span>
                    </div>
                    @if($poster->location)
                        <div class="detail-item">
                            <span class="label">Location</span>
                            <span class="value">{{ $poster->location }}</span>
                        </div>
                    @endif
                    @if($poster->job)
                        <div class="detail-item">
                            <span class="label">Linked Job</span>
                            <a href="{{ route('employer.jobs.show', $poster->job_id) }}" class="value link">
                                View Job <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="sidebar-section">
                    <h4 class="sidebar-title">
                        <i class="bi bi-lightbulb me-2"></i>Tips
                    </h4>
                    <ul class="tips-list">
                        <li>Use the PosterMyWall editor to customize colors, fonts, and images</li>
                        <li>Click "Save" in the editor to update your design</li>
                        <li>Download in PNG, JPG, or PDF format</li>
                        <li>Share directly to social media from the editor</li>
                    </ul>
                </div>

                <div class="sidebar-section">
                    <h4 class="sidebar-title">
                        <i class="bi bi-question-circle me-2"></i>Need Help?
                    </h4>
                    <p class="help-text">
                        Having trouble with the editor? Check out the
                        <a href="https://www.postermywall.com/help" target="_blank">PosterMyWall Help Center</a>
                        for tutorials and guides.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .editor-page {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 80px);
            margin: -1.5rem;
        }

        .editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: #6b7280;
            text-decoration: none;
            background: #f3f4f6;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .back-link:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .poster-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .poster-meta {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-action {
            display: flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-preview {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .btn-preview:hover {
            background: #c7d2fe;
            color: #4338ca;
        }

        .btn-download {
            background: #6366f1;
            color: white;
        }

        .btn-download:hover {
            background: #4f46e5;
            color: white;
        }

        .editor-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .editor-frame-wrapper {
            flex: 1;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .editor-frame {
            width: 100%;
            height: 100%;
            border: none;
        }

        .editor-placeholder {
            text-align: center;
            padding: 3rem;
            max-width: 400px;
        }

        .editor-placeholder i {
            font-size: 3rem;
            color: #fbbf24;
            margin-bottom: 1rem;
        }

        .editor-placeholder h3 {
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .editor-placeholder p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .editor-sidebar {
            width: 300px;
            background: white;
            border-left: 1px solid #e5e7eb;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar-section {
            padding: 1.25rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .sidebar-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            margin-bottom: 0.75rem;
        }

        .detail-item .label {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .detail-item .value {
            font-size: 0.9rem;
            color: #1f2937;
            font-weight: 500;
        }

        .detail-item .value.link {
            color: #6366f1;
            text-decoration: none;
        }

        .detail-item .value.link:hover {
            text-decoration: underline;
        }

        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tips-list li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
            font-size: 0.875rem;
            color: #6b7280;
            border-bottom: 1px solid #f9fafb;
        }

        .tips-list li:last-child {
            border-bottom: none;
        }

        .tips-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: #6366f1;
            font-weight: bold;
        }

        .help-text {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .help-text a {
            color: #6366f1;
        }

        @media (max-width: 992px) {
            .editor-sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .editor-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .header-left {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                justify-content: stretch;
            }

            .btn-action {
                flex: 1;
                justify-content: center;
            }
        }
    </style>

    @push('scripts')
        <script>
            // Listen for messages from the PosterMyWall editor iframe
            window.addEventListener('message', function(event) {
                // Verify origin for security
                if (event.origin !== 'https://www.postermywall.com' && event.origin !== 'https://api.postermywall.com') {
                    return;
                }

                const data = event.data;

                if (data.action === 'save' || data.action === 'export') {
                    // Notify our server about the save/export
                    fetch('{{ route("employer.posters.callback") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            design_id: '{{ $poster->pmw_design_id }}',
                            action: data.action
                        })
                    }).then(response => response.json())
                      .then(result => {
                          if (result.success && data.action === 'export') {
                              // Optionally redirect to preview after export
                          }
                      });
                }
            });
        </script>
    @endpush
@endsection
