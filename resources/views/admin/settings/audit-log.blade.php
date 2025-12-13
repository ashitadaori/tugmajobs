@extends('layouts.admin')

@section('page_title', 'Audit Log')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">
                <i class="bi bi-file-text text-info me-2"></i>
                Audit Log
            </h2>
            <p class="text-muted mb-0">Track all admin actions and changes</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3>{{ $stats['total'] }}</h3>
                    <p class="mb-0">Total Actions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3>{{ $stats['today'] }}</h3>
                    <p class="mb-0">Today's Actions</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3>{{ $stats['created'] }}</h3>
                    <p class="mb-0">Created</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h3>{{ $stats['updated'] }}</h3>
                    <p class="mb-0">Updated</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h3>{{ $stats['deleted'] }}</h3>
                    <p class="mb-0">Deleted</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="model_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="Job">Job</option>
                        <option value="User">User</option>
                        <option value="Company">Company</option>
                        <option value="Category">Category</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" placeholder="From">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" placeholder="To">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>
                                    @if($log->action === 'created')
                                        <span class="badge bg-success">Created</span>
                                    @elseif($log->action === 'updated')
                                        <span class="badge bg-warning">Updated</span>
                                    @elseif($log->action === 'deleted')
                                        <span class="badge bg-danger">Deleted</span>
                                    @elseif($log->action === 'approved')
                                        <span class="badge bg-info">Approved</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($log->action) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $log->model_type }}</strong>
                                    @if($log->model_id)
                                        <small class="text-muted">#{{ $log->model_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($log->new_values)
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showChanges({{ json_encode($log->old_values) }}, {{ json_encode($log->new_values) }})">
                                            View Changes
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No audit logs yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($logs->hasPages())
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showChanges(oldValues, newValues) {
    let html = '<div class="row"><div class="col-6"><h6>Old Values</h6><pre>' + 
               JSON.stringify(oldValues, null, 2) + 
               '</pre></div><div class="col-6"><h6>New Values</h6><pre>' + 
               JSON.stringify(newValues, null, 2) + 
               '</pre></div></div>';
    
    Swal.fire({
        title: 'Changes Details',
        html: html,
        width: 800,
        confirmButtonText: 'Close'
    });
}
</script>
@endsection
