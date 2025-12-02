@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="mb-0">Schedule - {{ $room->name }}</h2>
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <p class="text-muted mb-0">
                <strong>Total Seats:</strong> {{ $room->total_seats }} seats | 
                <strong>Number of Rows:</strong> {{ count($room->layout) }} rows
            </p>
        </div>
    </div>

    @if($showtimes->count() > 0)
        @php
            // Lấy ngày hiện tại dưới dạng Y-m-d
            $today = \Carbon\Carbon::today()->format('Y-m-d');
            // Kiểm tra xem ngày hiện tại có trong danh sách không
            $todayInSchedule = false;
            foreach ($paginatedSchedule as $date => $dayShowtimes) {
                if ($date === $today) {
                    $todayInSchedule = true;
                    break;
                }
            }
        @endphp
        <div class="accordion" id="scheduleAccordion">
            @foreach($paginatedSchedule as $date => $dayShowtimes)
                @php
                    $dateFormatted = \Carbon\Carbon::parse($date)->format('d/m/Y');
                    $dayName = \Carbon\Carbon::parse($date)->locale('en')->dayName;
                    $collapseId = 'collapse' . str_replace(['-', '/'], '', $date);
                    $headingId = 'heading' . str_replace(['-', '/'], '', $date);
                    // Chỉ mở nếu là ngày hiện tại và ngày hiện tại có trong danh sách
                    $isToday = ($date === $today);
                    $isPast = ($date < $today);
                    $shouldOpen = $isToday && $todayInSchedule;
                @endphp
                <div class="accordion-item mb-3 {{ $isPast ? 'opacity-75' : '' }}">
                    <h2 class="accordion-header" id="{{ $headingId }}">
                        <button class="accordion-button {{ $shouldOpen ? '' : 'collapsed' }}" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" 
                                aria-expanded="{{ $shouldOpen ? 'true' : 'false' }}" 
                                aria-controls="{{ $collapseId }}">
                            <i class="bi bi-calendar-event me-2"></i>
                            <strong>{{ $dateFormatted }} ({{ $dayName }})</strong>
                            @if($isToday)
                                <span class="badge bg-success ms-2">Today</span>
                            @elseif($isPast)
                                <span class="badge bg-secondary ms-2">Past</span>
                            @endif
                            <span class="badge bg-primary ms-2">{{ count($dayShowtimes) }} showtimes</span>
                        </button>
                    </h2>
                    <div id="{{ $collapseId }}" 
                         class="accordion-collapse collapse {{ $shouldOpen ? 'show' : '' }}" 
                         aria-labelledby="{{ $headingId }}" 
                         data-bs-parent="#scheduleAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 12%;">Show Time</th>
                                            <th style="width: 35%;">Movie Title</th>
                                            <th style="width: 12%;">Duration</th>
                                            <th style="width: 18%;">Time Range</th>
                                            <th style="width: 10%;">End Time</th>
                                            <th style="width: 13%;">Status</th>
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
                                                    // Fallback - sử dụng method helper
                                                    $timeOnly = $showtime->getFormattedShowTime('H:i');
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
                                                <td>{{ $duration }} minutes</td>
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
                                                            <i class="bi bi-x-circle"></i> Ended
                                                        </span>
                                                    @elseif($showStatus === 'showing')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-play-circle"></i> Showing
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock"></i> Upcoming
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
                </div>
            @endforeach
        </div>

        {{-- Phân trang --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $paginatedSchedule->links() }}
        </div>

        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i>
            <strong>Total Showtimes:</strong> {{ $showtimes->count() }} showtimes | 
            <strong>Total Days:</strong> {{ $paginatedSchedule->total() }} days
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Room <strong>{{ $room->name }}</strong> has no schedule available.
        </div>
    @endif
    
    <div class="alert alert-light border mt-3">
        <small class="text-muted">
            <i class="bi bi-arrow-clockwise"></i> 
            Page will automatically refresh every 60 seconds to display the latest status.
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

