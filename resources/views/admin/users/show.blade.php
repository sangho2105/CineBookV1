@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Chi tiết User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Quay lại danh sách</a>
    </div>

    <div class="row">
        {{-- Thông tin User --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin cá nhân</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ID:</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Tên:</th>
                            <td><strong>{{ $user->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Số điện thoại:</th>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tuổi:</th>
                            <td>{{ $user->age ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Thành phố ưa thích:</th>
                            <td>{{ $user->preferred_city ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Ngôn ngữ ưa thích:</th>
                            <td>{{ $user->preferred_language ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Vai trò:</th>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @else
                                    <span class="badge bg-primary">User</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày đăng ký:</th>
                            <td>
                                {{ $user->created_at->format('d/m/Y H:i:s') }}
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Thống kê --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="60%">Tổng số đặt vé:</th>
                            <td><strong>{{ $stats['total_bookings'] }}</strong></td>
                        </tr>
                        <tr>
                            <th>Đã thanh toán:</th>
                            <td><span class="badge bg-success">{{ $stats['completed_bookings'] }}</span></td>
                        </tr>
                        <tr>
                            <th>Đang chờ thanh toán:</th>
                            <td><span class="badge bg-warning">{{ $stats['pending_bookings'] }}</span></td>
                        </tr>
                        <tr>
                            <th>Tổng chi tiêu:</th>
                            <td><strong class="text-success">${{ number_format($stats['total_spent'], 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách đặt vé --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Lịch sử đặt vé</h5>
        </div>
        <div class="card-body">
            @if($user->bookings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã đặt vé</th>
                                <th>Phim</th>
                                <th>Ngày chiếu</th>
                                <th>Phòng</th>
                                <th>Số ghế</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->bookings->sortByDesc('created_at') as $booking)
                                <tr>
                                    <td>
                                        <strong>{{ $booking->booking_id_unique }}</strong>
                                    </td>
                                    <td>
                                        {{ $booking->showtime->movie->title ?? 'N/A' }}
                                    </td>
                                    <td>
                                        @if($booking->showtime && $booking->showtime->show_date)
                                            {{ $booking->showtime->show_date->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $booking->showtime->getFormattedShowTime('H:i') }}
                                            </small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        {{ $booking->showtime->room->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $booking->seats->count() }} ghế
                                    </td>
                                    <td>
                                        <strong>${{ number_format($booking->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($booking->payment_status === 'completed')
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        @else
                                            <span class="badge bg-warning">Chờ thanh toán</span>
                                        @endif
                                    </td>
                                    <td>
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
                                            @php
                                                // Laravel lưu datetime ở UTC trong database, cần convert sang timezone VN
                                                $createdAt = $booking->created_at instanceof \Carbon\Carbon 
                                                    ? $booking->created_at->copy() 
                                                    : \Carbon\Carbon::parse($booking->created_at);
                                                // Convert từ UTC (hoặc timezone hiện tại) sang Asia/Ho_Chi_Minh
                                                $createdAt = $createdAt->setTimezone('Asia/Ho_Chi_Minh');
                                            @endphp
                                            {{ $createdAt->format('d/m/Y H:i') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> User này chưa có đặt vé nào.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

