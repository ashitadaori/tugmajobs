@extends('layouts.jobseeker')

@section('page-title', 'Resume Builder')

@section('jobseeker-content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Resume Builder</h4>
                        <p class="text-muted mb-0">Create professional resumes with our easy-to-use builder</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                @if($resumes->isEmpty())
                    <!-- Template Selection -->
                    <div class="text-center mb-4">
                        <h5 class="mb-3">Choose a Template to Get Started</h5>
                        <p class="text-muted">Select a professional template and we'll auto-fill your basic information</p>
                    </div>

                    <div class="row g-4">
                        @foreach($templates as $template)
                            <div class="col-md-4">
                                <div class="card template-card h-100">
                                    <div class="card-body text-center">
                                        <div class="template-preview mb-3 p-0 overflow-hidden"
                                            style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f0f2f5;">
                                            @php
                                                $imageName = strtolower($template->name);
                                            @endphp
                                            @if(file_exists(public_path('assets/images/resume-templates/' . $imageName . '.png')))
                                                <img src="{{ asset('assets/images/resume-templates/' . $imageName . '.png') }}"
                                                    alt="{{ $template->name }}" class="img-fluid"
                                                    style="max-height: 100%; width: auto; object-fit: contain;">
                                            @else
                                                <i class="fas fa-file-alt fa-4x text-primary"></i>
                                            @endif
                                        </div>
                                        <h5 class="card-title">{{ $template->name }}</h5>
                                        <p class="card-text text-muted small">{{ $template->description }}</p>
                                        <a href="{{ route('account.resume-builder.create', ['template' => $template->id]) }}"
                                            class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-1"></i> Use This Template
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Existing Resumes -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Your Resumes</h5>
                        <a href="{{ route('account.resume-builder.index') }}?new=1" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create New Resume
                        </a>
                    </div>

                    @if(request()->get('new') == 1)
                        <!-- Show templates -->
                        <div class="row g-4 mb-4">
                            @foreach($templates as $template)
                                <div class="col-md-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body text-center">
                                            <div class="template-preview mb-3 p-0 overflow-hidden"
                                                style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f0f2f5;">
                                                @php
                                                    $imageName = strtolower($template->name);
                                                @endphp
                                                @if(file_exists(public_path('assets/images/resume-templates/' . $imageName . '.png')))
                                                    <img src="{{ asset('assets/images/resume-templates/' . $imageName . '.png') }}"
                                                        alt="{{ $template->name }}" class="img-fluid"
                                                        style="max-height: 100%; width: auto; object-fit: contain;">
                                                @else
                                                    <i class="fas fa-file-alt fa-4x text-primary"></i>
                                                @endif
                                            </div>
                                            <h5 class="card-title">{{ $template->name }}</h5>
                                            <p class="card-text text-muted small">{{ $template->description }}</p>
                                            <a href="{{ route('account.resume-builder.create', ['template' => $template->id]) }}"
                                                class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-1"></i> Use This Template
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3">
                        @foreach($resumes as $resume)
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $resume->title }}</h6>
                                                <small class="text-muted">
                                                    Template: {{ $resume->template->name }} •
                                                    Updated {{ $resume->updated_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="showResumePreview('{{ route('account.resume-builder.preview', $resume->id) }}', '{{ $resume->title }}')"
                                                    title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="{{ route('account.resume-builder.edit', $resume->id) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('account.resume-builder.download', $resume->id) }}"
                                                    class="btn btn-sm btn-outline-success" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form action="{{ route('account.resume-builder.destroy', $resume->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Delete this resume?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .template-card {
                transition: transform 0.2s;
                cursor: pointer;
            }

            .template-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .template-preview {
                padding: 2rem;
                background: #f8f9fa;
                border-radius: 8px;
            }
        </style>
    @endpush
    <!-- Resume Preview Modal -->
    <div class="modal fade" id="resumePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;">
            <div class="modal-content" style="height: 90vh;">
                <div class="modal-header">
                    <h5 class="modal-title" id="resumePreviewTitle">Resume Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: calc(90vh - 60px);">
                    <iframe id="resumePreviewFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showResumePreview(url, title) {
                // Set title
                document.getElementById('resumePreviewTitle').textContent = title;
                // Set iframe src
                document.getElementById('resumePreviewFrame').src = url;
                // Show modal
                new bootstrap.Modal(document.getElementById('resumePreviewModal')).show();
            }

            // Clear iframe when modal is closed to stop audio/video if any
            document.getElementById('resumePreviewModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('resumePreviewFrame').src = '';
            });
        </script>
    @endpush
@endsection