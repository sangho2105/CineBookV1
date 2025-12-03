@extends('layouts.admin')


@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="mb-0">Dashboard</h1>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Tickets</h5>
                    <h3>{{ number_format($stats['total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Paid</h5>
                    <h3>{{ number_format($stats['completed']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Payment</h5>
                    <h3>{{ number_format($stats['pending']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Revenue</h5>
                    <h3>{{ format_currency($stats['total_revenue']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Chart --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Revenue Chart</h5>
                <form method="GET" action="{{ route('admin.bookings.statistics') }}" class="d-flex align-items-center gap-2 flex-wrap" id="periodForm">
                    <select name="period" id="periodSelect" class="form-select form-select-sm" style="width: auto;" onchange="updateFilterVisibility();">
                        <option value="day" {{ $period == 'day' ? 'selected' : '' }}>By Day</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>By Month</option>
                        <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>By Quarter</option>
                    </select>
                    
                    <select name="revenue_year" id="revenueYear" class="form-select form-select-sm" style="width: auto;">
                        <option value="">All Years</option>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ ($selectedYear ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    
                    <select name="revenue_month" id="revenueMonth" class="form-select form-select-sm" style="width: auto; {{ $period == 'day' ? '' : 'display:none;' }}">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ ($selectedMonth ?? '') == $m ? 'selected' : '' }}>Month {{ $m }}</option>
                        @endfor
                    </select>
                    
                    <button type="submit" class="btn btn-sm btn-light">Apply</button>
                    <a href="{{ route('admin.bookings.statistics') }}" class="btn btn-sm btn-outline-light">Clear Filters</a>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Total Revenue: <span class="text-primary">{{ format_currency($revenueData['total'] ?? 0) }}</span></strong>
            </div>
            <div style="position: relative; height: 400px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Hiển thị/ẩn filter tháng dựa trên loại thống kê
    function updateFilterVisibility() {
        const period = document.getElementById('periodSelect').value;
        const monthSelect = document.getElementById('revenueMonth');
        if (period === 'day') {
            monthSelect.style.display = 'inline-block';
        } else {
            monthSelect.style.display = 'none';
            monthSelect.value = ''; // Xóa giá trị khi ẩn
        }
    }
    
    // Khởi tạo khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        updateFilterVisibility();
    });
    
    // Biểu đồ doanh thu
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) {
            return;
        }

        const labels = @json($revenueData['labels'] ?? []);
        const data = @json($revenueData['data'] ?? []);

        if (labels.length === 0 || data.length === 0) {
            ctx.parentElement.innerHTML = '<p class="text-muted text-center py-4">No revenue data available to display chart.</p>';
            return;
        }

        const period = @json($period ?? 'day');
        let chartLabel = 'Revenue ($)';
        if (period === 'month') {
            chartLabel = 'Revenue by Month ($)';
        } else if (period === 'quarter') {
            chartLabel = 'Revenue by Quarter ($)';
        }

        try {
            const revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartLabel,
                        data: data,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: $' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return '$' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return '$' + (value / 1000).toFixed(0) + 'K';
                                    }
                                    return '$' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(value);
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        } catch (error) {
            ctx.parentElement.innerHTML = '<p class="text-danger text-center py-4">Error creating chart: ' + error.message + '</p>';
        }
    });
</script>
@endpush


