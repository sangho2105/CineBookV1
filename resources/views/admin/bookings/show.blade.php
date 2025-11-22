@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="mb-0">Chi tiết Vé #{{ $booking->booking_id_unique }}</h1>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Thông tin chính --}}
        <div class="col-md-8">
            {{-- Thông tin vé --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-ticket-perforated"></i> Thông tin vé</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Mã vé:</strong><br>
                            <span class="text-primary fs-5">{{ $booking->booking_id_unique }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái thanh toán:</strong><br>
                            @if($booking->payment_status === 'completed')
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle"></i> Đã thanh toán
                                </span>
                            @elseif($booking->payment_status === 'pending')
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="bi bi-clock"></i> Chờ thanh toán
                                </span>
                            @elseif($booking->payment_status === 'cancelled')
                                <span class="badge bg-secondary fs-6">
                                    <i class="bi bi-x-circle"></i> Đã hủy
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="bi bi-exclamation-triangle"></i> Thất bại
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Ngày đặt vé:</strong><br>
                            {{ $booking->booking_date->format('d/m/Y H:i:s') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Tổng tiền:</strong><br>
                            <span class="text-success fs-5 fw-bold">{{ number_format($booking->total_amount, 0) }} đ</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thông tin phim và suất chiếu --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-film"></i> Thông tin phim & Suất chiếu</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tên phim:</strong><br>
                            <span class="fs-5">{{ $booking->showtime->movie->title ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Thể loại:</strong><br>
                            {{ $booking->showtime->movie->genre ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Ngày chiếu:</strong><br>
                            {{ $booking->showtime->show_date->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Giờ chiếu:</strong><br>
                            {{ $booking->showtime->show_time->format('H:i') ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Phòng chiếu:</strong><br>
                            {{ $booking->showtime->room->name ?? 'N/A' }}
                        </div>
                    </div>
                    @if($booking->showtime->theater)
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Rạp chiếu:</strong><br>
                            {{ $booking->showtime->theater->name ?? 'N/A' }} - {{ $booking->showtime->theater->city ?? 'N/A' }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Thông tin ghế --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-grid"></i> Ghế đã đặt ({{ $booking->seats->count() }} ghế)</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($booking->seats as $seat)
                            <span class="badge bg-primary fs-6 p-2">
                                {{ $seat->seat_number }} ({{ $seat->seat_category }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Thông tin combo (nếu có) --}}
            @if($booking->combos->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Combo đã mua</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tên combo</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking->combos as $combo)
                                    <tr>
                                        <td>{{ $combo->combo_name }}</td>
                                        <td>{{ $combo->quantity }}</td>
                                        <td>{{ number_format($combo->unit_price, 0) }} đ</td>
                                        <td>{{ number_format($combo->quantity * $combo->unit_price, 0) }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Thông tin khách hàng --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Họ tên:</strong><br>
                        {{ $booking->user->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong><br>
                        {{ $booking->user->email ?? 'N/A' }}
                    </div>
                    @if($booking->user)
                    <div class="mb-3">
                        <strong>Số điện thoại:</strong><br>
                        {{ $booking->user->phone ?? 'Chưa cập nhật' }}
                    </div>
                    <div>
                        <strong>Ngày tham gia:</strong><br>
                        {{ $booking->user->created_at->format('d/m/Y') ?? 'N/A' }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

