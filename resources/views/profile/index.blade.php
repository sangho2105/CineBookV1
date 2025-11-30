@extends('layouts.app')

@section('title', 'Hồ sơ của tôi')


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
            <a class="btn btn-sm btn-outline-success mt-2" href="{{ route('profile.favorites') }}">
                <i class="bi bi-heart-fill"></i> Phim yêu thích
            </a>
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
                                <th>Phim</th>
                                <th>Ngày</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Vé điện tử</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                <tr>
                                    <td>
                                        <strong>{{ $booking->showtime->movie->title ?? '' }}</strong>
                                        @if($booking->showtime->theater)
                                            <div class="text-muted small">{{ $booking->showtime->theater->name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($booking->showtime->show_date)->format('d/m/Y') }}
                                        <div class="text-muted small">{{ $booking->showtime->getFormattedShowTime('H:i') }}</div>
                                    </td>
                                    <td>
                                        @if($booking->payment_status === 'completed')
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-ticket-perforated"></i> Xem vé
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">Bạn chưa có đặt vé nào.</div>
    @endif
</div>
@endsection

