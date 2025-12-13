@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Saved Jobs</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('components.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">Saved Jobs</h3>
                        <p class="mb-4 text-muted">Jobs you've saved for later</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script type="text/javascript">
function removeSavedJob(jobId){
    if(confirm("Are you sure you want to remove this job?")){
        $.ajax({
            url: '{{ route("account.removeSavedJob") }}',
            type: 'POST',
            data: { job_id: jobId, _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(response) {
                if(response.status == true) {
                    window.location.href="{{ route('account.saved-jobs.index') }}";
                }
            }
        });
    }
}
</script>
@endsection


