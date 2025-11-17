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
        </div>
    </div>

    <h4 class="mb-3">Lịch sử đặt vé</h4>
    @if(isset($bookings) && $bookings->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
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
    @else
        <div class="alert alert-info">Bạn chưa có đặt vé nào.</div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Profile Information')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Profile Information</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Full Name:</strong></div>
                    <div class="col-md-8">{{ $user->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Email:</strong></div>
                    <div class="col-md-8">{{ $user->email }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Phone Number:</strong></div>
                    <div class="col-md-8">{{ $user->phone ?? 'Not updated yet' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Age:</strong></div>
                    <div class="col-md-8">{{ $user->age ?? 'Not updated yet' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Preferred Language:</strong></div>
                    <div class="col-md-8">{{ $user->preferred_language ?? 'Not updated yet' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Preferred City:</strong></div>
                    <div class="col-md-8">{{ $user->preferred_city ?? 'Not updated yet' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Role:</strong></div>
                    <div class="col-md-8">
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                            {{ $user->role === 'admin' ? 'Administrator' : 'User' }}
                        </span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
