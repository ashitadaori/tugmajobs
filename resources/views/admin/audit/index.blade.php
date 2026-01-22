@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary font-weight-bold">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> Audit Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select the report type and date range to generate an audit report. You can export the data as
                            PDF or Excel (CSV).
                        </div>

                        <form action="{{ route('admin.audit-reports.export') }}" method="GET" target="_blank">
                            <div class="row">
                                <!-- Report Type -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="type" class="font-weight-bold">Report Type</label>
                                        <select name="type" id="type" class="form-control" required>
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="jobseekers">Jobseekers</option>
                                            <option value="employers">Employers</option>
                                            <option value="job_hirings">Job Hirings</option>
                                            <option value="hired_jobseekers">Hired Jobseekers</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Start Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="start_date" class="font-weight-bold">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="end_date" class="font-weight-bold">End Date</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Format -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="format" class="font-weight-bold">Export Format</label>
                                        <select name="format" id="format" class="form-control" required>
                                            <option value="pdf">PDF Document</option>
                                            <option value="csv">Excel (CSV)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-download mr-1"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection