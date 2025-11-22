@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h2>Lịch chiếu - {{ $room->name }}</h2>
                <p class="text-muted mb-0">
                    <strong>Tổng số ghế:</strong> {{ $room->total_seats }} ghế | 
                    <strong>Số hàng:</strong> {{ count($room->layout) }} hàng
                </p>
            </div>
            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if($showtimes->count() > 0)
        @foreach($scheduleByDate as $date => $dayShowtimes)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event"></i> 
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} 
                        ({{ \Carbon\Carbon::parse($date)->locale('vi')->dayName }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Giờ chiếu</th>
                                    <th style="width: 40%;">Tên phim</th>
                                    <th style="width: 15%;">Thời lượng</th>
                                    <th style="width: 20%;">Khoảng thời gian</th>
                                    <th style="width: 10%;">Giờ kết thúc</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dayShowtimes as $showtime)
                                    @php
                                        // Lấy ngày dưới dạng Y-m-d
                                        $showDate = $showtime->show_date instanceof \Carbon\Carbon 
                                            ? $showtime->show_date->format('Y-m-d')
                                            : date('Y-m-d', strtotime($showtime->show_date));
                                        
                                        // Lấy giờ:phút từ show_time - lấy raw attribute để tránh lỗi
                                        // show_time trong DB là time type, nhưng model cast thành datetime
                                        $rawTimeValue = $showtime->getAttributes()['show_time'] ?? null;
                                        
                                        if ($rawTimeValue && is_string($rawTimeValue)) {
                                            // Nếu có raw value là string time (như "15:15:00"), chỉ lấy H:i
                                            $timeOnly = substr($rawTimeValue, 0, 5); // Lấy "15:15" từ "15:15:00"
                                        } elseif (is_object($showtime->show_time)) {
                                            // Nếu đã cast thành Carbon, format chỉ lấy H:i
                                            $timeOnly = $showtime->show_time->format('H:i');
                                        } else {
                                            // Fallback
                                            $timeOnly = date('H:i', strtotime($showtime->show_time));
                                        }
                                        
                                        // Tạo datetime từ ngày và giờ
                                        $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $showDate . ' ' . $timeOnly);
                                        $startTime = $startDateTime->format('H:i');
                                        $duration = $showtime->movie->duration_minutes ?? 0;
                                        $endDateTime = $startDateTime->copy()->addMinutes($duration);
                                        $endTime = $endDateTime->format('H:i');
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $startTime }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $showtime->movie->title }}</strong>
                                        </td>
                                        <td>{{ $duration }} phút</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $startTime }} - {{ $endTime }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                <strong>{{ $endTime }}</strong>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Tổng số suất chiếu:</strong> {{ $showtimes->count() }} suất
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Phòng <strong>{{ $room->name }}</strong> chưa có lịch chiếu nào.
        </div>
    @endif
</div>
@endsection

