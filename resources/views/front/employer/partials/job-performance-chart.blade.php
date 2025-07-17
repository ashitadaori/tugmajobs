<!-- Job Performance Chart -->
<div class="card h-100">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Job Performance</h5>
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i>Export Data</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-arrow-clockwise me-2"></i>Refresh</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="jobPerformanceChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('jobPerformanceChart').getContext('2d');
    
    // Sample data - replace with actual data from your controller
    const data = {
        labels: @json($performanceLabels ?? ['Views', 'Applications', 'Shortlisted', 'Hired']),
        datasets: [{
            data: @json($performanceData ?? [0, 0, 0, 0]),
            backgroundColor: [
                'rgba(13, 110, 253, 0.2)',
                'rgba(25, 135, 84, 0.2)',
                'rgba(255, 193, 7, 0.2)',
                'rgba(13, 202, 240, 0.2)'
            ],
            borderColor: [
                'rgb(13, 110, 253)',
                'rgb(25, 135, 84)',
                'rgb(255, 193, 7)',
                'rgb(13, 202, 240)'
            ],
            borderWidth: 1
        }]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    };

    new Chart(ctx, config);
});
</script>
@endpush 