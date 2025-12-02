@extends('layouts.admin')

@section('title', 'List Showtimes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Showtimes</h1>
        <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary">Add Showtime</a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.showtimes.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search by Movie Name</label>
                    <div class="position-relative">
                        <input type="text" 
                               class="form-control" 
                               id="search"
                               name="search" 
                               placeholder="Enter movie name..."
                               value="{{ request('search') }}">
                        <i class="bi bi-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="today" {{ request('status') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Past</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="room_id" class="form-label">Room</label>
                    <select class="form-select" id="room_id" name="room_id">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
                @if(request()->anyFilled(['search', 'status', 'room_id']))
                <div class="col-12">
                    <a href="{{ route('admin.showtimes.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 80px;">STT</th>
                <th>Movie</th>
                <th>Room</th>
                <th>Date</th>
                <th>Show Time</th>
                <th style="width: 120px;">Status</th>
                <th style="width: 150px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($showtimes as $showtime)
            @php
                // Lấy giờ bắt đầu
                $startTime = $showtime->getFormattedShowTime('H:i');
                
                // Tính giờ kết thúc = giờ bắt đầu + duration_minutes
                $duration = $showtime->movie->duration_minutes ?? 0;
                $startTimeParts = explode(':', $startTime);
                $startHour = (int)($startTimeParts[0] ?? 0);
                $startMinute = (int)($startTimeParts[1] ?? 0);
                
                // Tạo Carbon instance từ show_date và show_time
                $startDateTime = \Carbon\Carbon::create(
                    $showtime->show_date->year,
                    $showtime->show_date->month,
                    $showtime->show_date->day,
                    $startHour,
                    $startMinute,
                    0
                );
                
                // Cộng thêm duration để có giờ kết thúc
                $endDateTime = $startDateTime->copy()->addMinutes($duration);
                $endTime = $endDateTime->format('H:i');
                
                // Xác định trạng thái
                $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');
                $isPast = $startDateTime->lt($now);
                $isToday = $showtime->show_date->isToday();
                $isUpcoming = $startDateTime->gt($now);
                
                if ($isPast) {
                    $status = 'past';
                    $statusText = 'Past';
                    $statusClass = 'bg-secondary';
                } elseif ($isToday) {
                    $status = 'today';
                    $statusText = 'Today';
                    $statusClass = 'bg-info';
                } else {
                    $status = 'upcoming';
                    $statusText = 'Upcoming';
                    $statusClass = 'bg-success';
                }
            @endphp
            <tr class="{{ $isPast ? 'table-secondary opacity-75' : '' }}">
                <td>{{ ($showtimes->currentPage() - 1) * $showtimes->perPage() + $loop->iteration }}</td>
                <td>{{ $showtime->movie->title }}</td>
                <td>{{ $showtime->room ? $showtime->room->name . ' (' . $showtime->room->total_seats . ' ghế)' : 'CineBook Center' }}</td>
                <td>{{ $showtime->show_date->format('d/m/Y') }}</td>
                <td>{{ $startTime }} - {{ $endTime }}</td>
                <td>
                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                </td>
                <td>
                    <a href="{{ route('admin.showtimes.show', $showtime) }}" class="btn btn-sm btn-info" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No showtimes found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Phân trang --}}
<div class="mt-4">
    {{ $showtimes->links() }}
</div>
</div>

@endsection