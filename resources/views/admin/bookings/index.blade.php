@extends('layouts.admin')

@push('styles')
<style>
    /* Đảm bảo cả 2 bảng có cùng width và padding */
    .bookings-header-wrapper table,
    .bookings-scroll-container table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
        font-size: 0.875rem; /* Giảm font size */
    }
    
    /* Đảm bảo padding và alignment giống nhau */
    .bookings-header-wrapper table thead th,
    .bookings-scroll-container table tbody td {
        padding: 0.5rem 0.75rem; /* Giảm padding dọc */
        vertical-align: middle;
    }
    
    /* Giảm font size cho các phần tử con */
    .bookings-scroll-container table tbody td strong {
        font-size: 0.875rem;
    }
    
    .bookings-scroll-container table tbody td small {
        font-size: 0.75rem;
    }
    
    .bookings-scroll-container table tbody td .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        white-space: nowrap;
    }
    
    .bookings-scroll-container table tbody td .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .bookings-scroll-container {
        max-height: 300px; /* Chiều cao để hiển thị khoảng 3 hàng */
        overflow-y: auto;
        overflow-x: hidden; /* Ẩn scrollbar ngang */
        scrollbar-width: thin;
        scrollbar-color: #6c757d #f8f9fa;
    }
    
    .bookings-scroll-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .bookings-scroll-container::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 4px;
    }
    
    .bookings-scroll-container::-webkit-scrollbar-thumb {
        background: #6c757d;
        border-radius: 4px;
    }
    
    .bookings-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #5a6268;
    }
    
    .bookings-scroll-container thead {
        display: none; /* Ẩn thead trong phần scroll vì đã có ở trên */
    }
    
    /* Bù scrollbar width cho header */
    .bookings-header-wrapper {
        padding-right: 8px;
    }
    
    /* Đảm bảo table-responsive không thêm margin và không có scrollbar ngang */
    .bookings-header-wrapper .table-responsive,
    .bookings-scroll-container .table-responsive {
        margin: 0;
        overflow-x: hidden; /* Ẩn scrollbar ngang */
    }
    
    /* Đảm bảo border và spacing giống nhau */
    .bookings-header-wrapper table,
    .bookings-scroll-container table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    /* Đảm bảo text không bị tràn */
    .bookings-scroll-container table tbody td {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* Giới hạn width của email */
    .bookings-scroll-container table tbody td small {
        display: block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="mb-0">Quản lý Vé</h1>
        </div>
    </div>

    {{-- Thống kê --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Tổng số vé</h5>
                    <h3>{{ number_format($stats['total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Đã thanh toán</h5>
                    <h3>{{ number_format($stats['completed']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Chờ thanh toán</h5>
                    <h3>{{ number_format($stats['pending']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Doanh thu</h5>
                    <h3>{{ number_format($stats['total_revenue'], 0) }} đ</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Biểu đồ doanh thu --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Biểu đồ Doanh thu 30 ngày gần nhất</h5>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 400px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           placeholder="Mã vé, tên khách hàng, email, tên phim..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="payment_status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="payment_status" name="payment_status">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                        <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thanh toán thất bại</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Từ ngày</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_from" 
                           name="date_from"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Đến ngày</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_to" 
                           name="date_to"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Danh sách vé --}}
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Danh sách vé ({{ $bookings->total() }} vé)</h5>
        </div>
                <div class="card-body p-0">
            <div class="table-responsive bookings-header-wrapper">
                <table class="table table-striped table-hover align-middle mb-0">
                    <colgroup>
                        <col style="width: 25%;">
                        <col style="width: 40%;">
                        <col style="width: 20%;">
                        <col style="width: 15%;">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th>Khách hàng</th>
                            <th>Phim</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="bookings-scroll-container">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <colgroup>
                            <col style="width: 25%;">
                            <col style="width: 40%;">
                            <col style="width: 20%;">
                            <col style="width: 15%;">
                        </colgroup>
                        <thead style="display: none;">
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $booking->user->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $booking->user->email ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $booking->showtime->movie->title ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    @if($booking->payment_status === 'completed')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Đã thanh toán
                                        </span>
                                    @elseif($booking->payment_status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Chờ thanh toán
                                        </span>
                                    @elseif($booking->payment_status === 'cancelled')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Đã hủy
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Thất bại
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $booking) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">Không tìm thấy vé nào.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Phân trang --}}
            @if($bookings->hasPages())
                <div class="card-footer bg-light">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Biểu đồ doanh thu
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) {
            console.error('Canvas element not found');
            return;
        }

        const labels = @json($revenueData['labels'] ?? []);
        const data = @json($revenueData['data'] ?? []);

        if (labels.length === 0 || data.length === 0) {
            console.warn('No data for chart');
            ctx.parentElement.innerHTML = '<p class="text-muted text-center py-4">Chưa có dữ liệu doanh thu để hiển thị biểu đồ.</p>';
            return;
        }

        try {
            const revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
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
                                    return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
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
                                        return (value / 1000000).toFixed(1) + 'M đ';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K đ';
                                    }
                                    return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
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
            console.log('Chart created successfully');
        } catch (error) {
            console.error('Error creating chart:', error);
            ctx.parentElement.innerHTML = '<p class="text-danger text-center py-4">Có lỗi xảy ra khi tạo biểu đồ: ' + error.message + '</p>';
        }
    });
</script>
@endpush


