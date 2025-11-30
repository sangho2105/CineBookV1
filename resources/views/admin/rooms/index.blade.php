@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Quản lý Phòng chiếu</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($rooms->count() > 0)
        <div class="row">
            @foreach($rooms as $room)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $room->name }}</h5>
                        <p class="card-text">
                            <strong>Tổng số ghế:</strong> {{ $room->total_seats }} ghế<br>
                            <strong>Số hàng:</strong> {{ count($room->layout) }} hàng
                        </p>
                        <div class="d-flex gap-2" style="flex-wrap: nowrap;">
                            <a href="{{ route('admin.rooms.show', $room) }}" class="btn btn-primary btn-sm" style="white-space: nowrap; flex: 0 0 auto;">Xem sơ đồ phòng</a>
                            <a href="{{ route('admin.rooms.schedule', $room) }}" class="btn btn-info btn-sm" style="white-space: nowrap; flex: 0 0 auto;">
                                <i class="bi bi-calendar-event"></i> Xem lịch chiếu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            Chưa có phòng chiếu nào. Vui lòng chạy seeder để tạo 6 phòng.
        </div>
    @endif
</div>
@endsection

