@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Graphic Poster Builder</h1>
            <p class="text-muted">Create hiring posters for job positions</p>
        </div>
        <div>
            <a href="{{ route('admin.posters.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create New Poster
            </a>
        </div>
    </div>

    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ Session::get('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Posters Grid -->
    <div class="card">
        <div class="card-body">
            @if($posters->count() > 0)
                <div class="row">
                    @foreach($posters as $poster)
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card h-100 poster-card">
                                <div class="card-body">
                                    <div class="poster-preview mb-3 p-3 rounded" style="background: linear-gradient(135deg,
                                        @if($poster->template->slug == 'blue-megaphone') #3B5998, #1E3A8A
                                        @elseif($poster->template->slug == 'yellow-attention') #F59E0B, #FBBF24
                                        @else #1F2937, #374151
                                        @endif);">
                                        <div class="text-center text-white">
                                            <small class="d-block mb-1 fw-bold">WE ARE HIRING</small>
                                            <strong class="d-block" style="font-size: 0.9rem;">{{ Str::limit($poster->job_title, 20) }}</strong>
                                        </div>
                                    </div>
                                    <h6 class="card-title mb-1">{{ $poster->job_title }}</h6>
                                    <p class="text-muted small mb-2">{{ $poster->company_name }}</p>
                                    <span class="badge bg-secondary">{{ $poster->template->name }}</span>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.posters.preview', $poster->id) }}"
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.posters.edit', $poster->id) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('admin.posters.download', $poster->id) }}"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-poster"
                                                data-id="{{ $poster->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $posters->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-image fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">No Posters Created Yet</h5>
                    <p class="text-muted">Create your first hiring poster to get started</p>
                    <a href="{{ route('admin.posters.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create First Poster
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.poster-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.poster-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.poster-preview {
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete poster
    document.querySelectorAll('.delete-poster').forEach(button => {
        button.addEventListener('click', function() {
            const posterId = this.dataset.id;

            if (confirm('Are you sure you want to delete this poster?')) {
                fetch(`{{ url('admin/posters') }}/${posterId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete poster');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the poster');
                });
            }
        });
    });
});
</script>
@endpush
@endsection
