@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="mb-0">Chi tiết Vé #{{ $booking->booking_id_unique }}</h1>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
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
                            <strong>Mã vé:</strong> <span class="text-primary fs-5">{{ $booking->booking_id_unique }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái thanh toán:</strong> @if($booking->payment_status === 'completed')<span class="badge bg-success" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"><i class="bi bi-check-circle"></i> Đã thanh toán</span>@elseif($booking->payment_status === 'pending')<span class="badge bg-warning text-dark" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"><i class="bi bi-clock"></i> Chờ thanh toán</span>@elseif($booking->payment_status === 'cancelled')<span class="badge bg-secondary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"><i class="bi bi-x-circle"></i> Đã hủy</span>@else<span class="badge bg-danger" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"><i class="bi bi-exclamation-triangle"></i> Thất bại</span>@endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Ngày đặt vé:</strong> 
                            @if($booking->booking_date)
                                @php
                                    // Laravel lưu datetime ở UTC trong database, cần convert sang timezone VN
                                    $bookingDate = $booking->booking_date instanceof \Carbon\Carbon 
                                        ? $booking->booking_date->copy() 
                                        : \Carbon\Carbon::parse($booking->booking_date);
                                    // Convert từ UTC (hoặc timezone hiện tại) sang Asia/Ho_Chi_Minh
                                    $bookingDate = $bookingDate->setTimezone('Asia/Ho_Chi_Minh');
                                @endphp
                                {{ $bookingDate->format('d/m/Y H:i') }}
                            @else
                                Chưa thanh toán
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Tổng tiền:</strong> <span class="text-success fs-5 fw-bold">{{ format_currency($booking->total_amount) }}</span>
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
                            <strong>Tên phim:</strong> <span class="fs-5">{{ $booking->showtime->movie->title ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Thể loại:</strong> {{ $booking->showtime->movie->genre ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Ngày chiếu:</strong> {{ $booking->showtime->show_date->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Giờ chiếu:</strong> {{ $booking->showtime->getFormattedShowTime('H:i') ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Phòng chiếu:</strong> {{ $booking->showtime->room->name ?? 'N/A' }}
                        </div>
                    </div>
                    @if($booking->showtime->theater)
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Rạp chiếu:</strong> CineBook Center{{ $booking->showtime->theater->city ? ' - ' . $booking->showtime->theater->city : '' }}
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
                                        <td>{{ format_currency($combo->unit_price) }}</td>
                                        <td>{{ format_currency($combo->quantity * $combo->unit_price) }}</td>
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
                        <strong>Họ tên:</strong> {{ $booking->user->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> {{ $booking->user->email ?? 'N/A' }}
                    </div>
                    @if($booking->user)
                    <div class="mb-3">
                        <strong>Số điện thoại:</strong> {{ $booking->user->phone ?? 'Chưa cập nhật' }}
                    </div>
                    <div>
                        <strong>Ngày tham gia:</strong> {{ $booking->user->created_at->format('d/m/Y') ?? 'N/A' }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

