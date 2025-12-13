@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create Category</h1>
            <p class="text-muted">Add a new job category</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Categories
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

    <!-- Create Form -->
    <div class="card">
        <div class="card-body">
            <form action="" method="post" id="createCategory" name="createCategory">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" placeholder="e.g., Software Development" class="form-control">
                            <p class="text-danger"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Create Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$("#createCategory").submit(function (e) {
    e.preventDefault();

    $.ajax({
        type: "post",
        url: "{{ route('admin.categories.store') }}",
        data: $("#createCategory").serializeArray(),
        dataType: "json",
        success: function (response) {
            if(response.status == true){
                $("#name").removeClass('is-invalid').siblings('p').removeClass('text-danger').html("");
                window.location.href="{{ route('admin.categories.index') }}";
            }else{
                var errors = response.errors;
                if(errors.name){
                    $("#name").addClass('is-invalid').siblings('p').addClass('text-danger').html(errors.name);
                }else{
                    $("#name").removeClass('is-invalid').siblings('p').removeClass('text-danger').html("");
                }
            }
        }
    });
});
</script>
@endpush
@endsection
