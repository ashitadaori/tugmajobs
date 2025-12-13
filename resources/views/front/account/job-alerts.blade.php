@extends('layouts.jobseeker')

@section('page-title', 'Job Alerts')

@section('jobseeker-content')
                <!-- Job Alerts Card -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">Job Alerts</h3>
                        <p class="mb-4 text-muted">Get notified when new jobs match your preferences</p>

                        <!-- Alert Preferences Form -->
                        <form action="{{ route('account.updateJobAlerts') }}" method="POST" id="jobAlertsForm">
                            @csrf
                            <div class="row">
                                <!-- Job Categories -->
                                <div class="col-md-6 mb-4">
                                    <label for="categories" class="form-label">Job Categories</label>
                                    <select class="form-select" id="categories" name="categories[]" multiple>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories ?? []) ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Job Types -->
                                <div class="col-md-6 mb-4">
                                    <label for="job_types" class="form-label">Job Types</label>
                                    <select class="form-select" id="job_types" name="job_types[]" multiple>
                                        @foreach($jobTypes ?? [] as $type)
                                            <option value="{{ $type->id }}" {{ in_array($type->id, $selectedJobTypes ?? []) ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location -->
                                <div class="col-md-6 mb-4">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="{{ $location ?? '' }}" placeholder="Enter location">
                                </div>

                                <!-- Salary Range -->
                                <div class="col-md-6 mb-4">
                                    <label for="salary_range" class="form-label">Minimum Salary</label>
                                    <select class="form-select" id="salary_range" name="salary_range">
                                        <option value="">Any Salary</option>
                                        <option value="20000" {{ ($salaryRange ?? '') == '20000' ? 'selected' : '' }}>₱20,000+</option>
                                        <option value="30000" {{ ($salaryRange ?? '') == '30000' ? 'selected' : '' }}>₱30,000+</option>
                                        <option value="50000" {{ ($salaryRange ?? '') == '50000' ? 'selected' : '' }}>₱50,000+</option>
                                        <option value="75000" {{ ($salaryRange ?? '') == '75000' ? 'selected' : '' }}>₱75,000+</option>
                                        <option value="100000" {{ ($salaryRange ?? '') == '100000' ? 'selected' : '' }}>₱100,000+</option>
                                    </select>
                                </div>

                                <!-- Alert Frequency -->
                                <div class="col-md-6 mb-4">
                                    <label for="frequency" class="form-label">Alert Frequency</label>
                                    <select class="form-select" id="frequency" name="frequency">
                                        <option value="daily" {{ ($frequency ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ ($frequency ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="instant" {{ ($frequency ?? '') == 'instant' ? 'selected' : '' }}>Instant</option>
                                    </select>
                                </div>

                                <!-- Email Notifications -->
                                <div class="col-md-6 mb-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" {{ ($emailNotifications ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_notifications">
                                            Receive email notifications
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Active Alerts Card -->
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h4 class="fs-5 mb-4">Active Job Alerts</h4>
                        
                        @if(empty($activeAlerts ?? []))
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                You don't have any active job alerts. Set up your preferences above to start receiving alerts.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Categories</th>
                                            <th>Job Types</th>
                                            <th>Location</th>
                                            <th>Frequency</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeAlerts as $alert)
                                            <tr>
                                                <td>{{ $alert->categories }}</td>
                                                <td>{{ $alert->job_types }}</td>
                                                <td>{{ $alert->location ?: 'Any' }}</td>
                                                <td>{{ ucfirst($alert->frequency) }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger delete-alert" data-id="{{ $alert->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize multiple select
    $('#categories, #job_types').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select options'
    });

    // Handle alert deletion
    $('.delete-alert').click(function() {
        if (confirm('Are you sure you want to delete this job alert?')) {
            const alertId = $(this).data('id');
            $.ajax({
                url: `/account/job-alerts/${alertId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error deleting job alert');
                    }
                },
                error: function() {
                    alert('Error deleting job alert');
                }
            });
        }
    });

    // Form validation
    $('#jobAlertsForm').submit(function(e) {
        e.preventDefault();
        
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.message || 'Error saving preferences');
                }
                $submitBtn.prop('disabled', false);
            },
            error: function() {
                alert('Error saving preferences');
                $submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush 