@extends('layouts.app')

@section('title', 'Vé điện tử')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-ticket-perforated"></i> Vé điện tử
                </h2>
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Header với mã đặt vé -->
                    <div class="row mb-4 pb-3 border-bottom">
                        <div class="col-md-6">
                            <h4 class="mb-2">{{ $booking->showtime->movie->title ?? '' }}</h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar3"></i> 
                                {{ optional($booking->showtime->show_date)->format('d/m/Y') }} 
                                <i class="bi bi-clock ms-2"></i> 
                                {{ $booking->showtime->getFormattedShowTime('H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="text-muted mb-1 small">Mã đặt vé</p>
                            <h5 class="mb-0">
                                <code class="fs-4">{{ $booking->booking_id_unique }}</code>
                            </h5>
                            <p class="mt-2 mb-0">
                                @if($booking->payment_status === 'completed')
                                    <span class="badge bg-success fs-6">Đã thanh toán</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-6">Chờ thanh toán</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Thông tin suất chiếu -->
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="bi bi-info-circle"></i> Thông tin suất chiếu
                            </h5>
                            <div class="mb-3">
                                <p class="mb-1 text-muted small">Rạp chiếu</p>
                                <p class="mb-0 fw-bold">{{ $booking->showtime->theater->name ?? 'N/A' }}</p>
                                @if($booking->showtime->theater)
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-geo-alt"></i> 
                                        {{ $booking->showtime->theater->address ?? '' }}
                                    </p>
                                @endif
                            </div>
                            @if($booking->showtime->room)
                                <div class="mb-3">
                                    <p class="mb-1 text-muted small">Phòng chiếu</p>
                                    <p class="mb-0 fw-bold">{{ $booking->showtime->room->name ?? 'N/A' }}</p>
                                </div>
                            @endif
                            <div class="mb-3">
                                <p class="mb-1 text-muted small">Ngày & Giờ</p>
                                <p class="mb-0 fw-bold">
                                    {{ optional($booking->showtime->show_date)->format('d/m/Y') }} 
                                    lúc {{ $booking->showtime->getFormattedShowTime('H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Thông tin ghế -->
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="bi bi-grid-3x3-gap"></i> Ghế đã đặt
                            </h5>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($booking->seats as $seat)
                                    <span class="badge bg-primary fs-6 p-2">
                                        {{ $seat->seat_number }} 
                                        <small>({{ $seat->seat_category }})</small>
                                    </span>
                                @endforeach
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted small">Tổng số ghế</p>
                                <p class="mb-0 fw-bold">{{ $booking->seats->count() }} ghế</p>
                            </div>
                        </div>
                    </div>

                    <!-- Combos (nếu có) -->
                    @if($booking->combos->isNotEmpty())
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="bi bi-bag"></i> Combo đã mua
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tên combo</th>
                                                <th class="text-center">Số lượng</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-end">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($booking->combos as $combo)
                                                <tr>
                                                    <td>{{ $combo->combo_name }}</td>
                                                    <td class="text-center">{{ $combo->quantity }}</td>
                                                    <td class="text-end">{{ format_currency($combo->unit_price) }}</td>
                                                    <td class="text-end">{{ format_currency($combo->quantity * $combo->unit_price) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tổng tiền -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-8">
                            <p class="mb-1 text-muted small">
                                <i class="bi bi-calendar-check"></i> Ngày đặt vé
                            </p>
                            <p class="mb-0 fw-bold">
                                {{ $booking->booking_date->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="mb-1 text-muted small">Tổng tiền</p>
                            <h4 class="mb-0 text-primary">
                                {{ format_currency($booking->total_amount) }}
                            </h4>
                        </div>
                    </div>

                    <!-- Lưu ý -->
                    <div class="alert alert-info mt-4 mb-0">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Lưu ý
                        </h6>
                        <ul class="mb-0 small">
                            <li>Vui lòng đến rạp trước 15 phút để làm thủ tục vào rạp.</li>
                            <li>Mang theo mã đặt vé này hoặc CMND/CCCD để đối chiếu khi vào rạp.</li>
                            <li>Vé đã thanh toán không thể hoàn tiền hoặc đổi suất chiếu.</li>
                        </ul>
                    </div>

                    <!-- Nút hành động -->
                    @if($booking->payment_status !== 'completed')
                        <div class="mt-4 text-center">
                            <a href="{{ route('bookings.payment', $booking) }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card"></i> Thanh toán ngay
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

