<!-- Applications Chart -->
<div class="card h-100">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Application Trends</h5>
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
            <canvas id="applicationsChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('applicationsChart').getContext('2d');
    
    // Sample data - replace with actual data from your controller
    const data = {
        labels: @json($chartLabels ?? array_fill(0, 7, '')),
        datasets: [{
            label: 'Applications',
            data: @json($chartData ?? array_fill(0, 7, 0)),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.4
        }]
    };

    const config = {
        type: 'line',
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