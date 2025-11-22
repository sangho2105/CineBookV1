@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="mb-0">Lịch chiếu - {{ $room->name }}</h2>
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
            <p class="text-muted mb-0">
                <strong>Tổng số ghế:</strong> {{ $room->total_seats }} ghế | 
                <strong>Số hàng:</strong> {{ count($room->layout) }} hàng
            </p>
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
                                    <th style="width: 12%;">Giờ chiếu</th>
                                    <th style="width: 35%;">Tên phim</th>
                                    <th style="width: 12%;">Thời lượng</th>
                                    <th style="width: 18%;">Khoảng thời gian</th>
                                    <th style="width: 10%;">Giờ kết thúc</th>
                                    <th style="width: 13%;">Trạng thái phim</th>
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
                                        
                                        // Tính trạng thái dựa trên thời gian thực
                                        $now = \Carbon\Carbon::now();
                                        $showStatus = 'upcoming'; // Mặc định là sắp chiếu
                                        
                                        if ($now->greaterThanOrEqualTo($startDateTime) && $now->lessThanOrEqualTo($endDateTime)) {
                                            // Đang trong khoảng thời gian chiếu
                                            $showStatus = 'showing';
                                        } elseif ($now->greaterThan($endDateTime)) {
                                            // Đã qua thời gian kết thúc
                                            $showStatus = 'ended';
                                        }
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
                                        <td>
                                            @if($showStatus === 'ended')
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle"></i> Đã kết thúc
                                                </span>
                                            @elseif($showStatus === 'showing')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-play-circle"></i> Đang chiếu
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock"></i> Sắp chiếu
                                                </span>
                                            @endif
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
    
    <div class="alert alert-light border mt-3">
        <small class="text-muted">
            <i class="bi bi-arrow-clockwise"></i> 
            Trang sẽ tự động cập nhật mỗi 60 giây để hiển thị trạng thái mới nhất.
        </small>
    </div>
</div>

<script>
    // Tự động refresh trang mỗi 60 giây để cập nhật trạng thái theo thời gian thực
    setTimeout(function() {
        location.reload();
    }, 60000); // 60 giây = 60000 milliseconds
</script>
@endsection

