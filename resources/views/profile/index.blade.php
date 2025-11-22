@extends('layouts.app')

@section('title', 'Hồ sơ của tôi')

@push('css')
<style>
    .bookings-scroll-container {
        max-height: 300px; /* Chiều cao để hiển thị khoảng 3 hàng */
        overflow-y: auto;
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
    
    .bookings-scroll-container .table {
        margin-bottom: 0;
    }
    
    .bookings-scroll-container thead {
        display: none; /* Ẩn thead trong phần scroll vì đã có ở trên */
    }
    
    /* Đảm bảo các cột có cùng width */
    .bookings-scroll-container table {
        table-layout: fixed;
        width: 100%;
    }
</style>
@endpush

@section('content')
<div class="container">
    <h1 class="mb-4">Hồ sơ của tôi</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Thông tin cá nhân</h5>
            <p class="mb-1"><strong>Tên:</strong> {{ $user->name }}</p>
            <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
            @if($user->phone)
                <p class="mb-1"><strong>Điện thoại:</strong> {{ $user->phone }}</p>
            @endif
            <a class="btn btn-sm btn-outline-primary mt-2" href="{{ route('profile.edit') }}">Chỉnh sửa</a>
        </div>
    </div>

    <h4 class="mb-3">Lịch sử đặt vé ({{ $bookings->count() ?? 0 }} vé)</h4>
    @if(isset($bookings) && $bookings->isNotEmpty())
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đặt vé</th>
                                <th>Phim</th>
                                <th>Rạp</th>
                                <th>Ngày</th>
                                <th>Giờ</th>
                                <th>Ghế</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Vé điện tử</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="bookings-scroll-container">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead style="display: none;">
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td><code>{{ $booking->booking_id_unique }}</code></td>
                            <td>{{ $booking->showtime->movie->title ?? '' }}</td>
                            <td>{{ $booking->showtime->theater->name ?? '' }}</td>
                            <td>{{ optional($booking->showtime->show_date)->format('d/m/Y') }}</td>
                            <td>{{ date('H:i', strtotime($booking->showtime->show_time)) }}</td>
                            <td>
                                @php
                                    $seatLabels = $booking->seats->map(function($s){
                                        return $s->seat_number . ' (' . $s->seat_category . ')';
                                    })->implode(', ');
                                @endphp
                                {{ $seatLabels }}
                            </td>
                            <td>${{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($booking->payment_status === 'completed')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @else
                                    <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#ticket-{{ $booking->id }}">
                                    Xem vé
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="ticket-{{ $booking->id }}">
                            <td colspan="9">
                                <div class="p-3 border rounded bg-light">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="mb-2">{{ $booking->showtime->movie->title ?? '' }}</h5>
                                            <p class="mb-1"><strong>Rạp:</strong> {{ $booking->showtime->theater->name ?? '' }}</p>
                                            <p class="mb-1">
                                                <strong>Suất:</strong>
                                                {{ optional($booking->showtime->show_date)->format('d/m/Y') }}
                                                - {{ date('H:i', strtotime($booking->showtime->show_time)) }}
                                            </p>
                                            <p class="mb-1"><strong>Ghế:</strong> {{ $seatLabels }}</p>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-1"><strong>Mã đặt vé</strong></p>
                                            <h5><code>{{ $booking->booking_id_unique }}</code></h5>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">Bạn chưa có đặt vé nào.</div>
    @endif
</div>
@endsection

