@extends('layouts.app')

@section('title', 'Chọn suất chiếu - ' . $movie->title)

@push('css')
<style>
.booking-container {
    background: #f5f5f5;
    min-height: 100vh;
    padding: 20px 0;
}

.booking-header {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.movie-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.movie-poster {
    width: 80px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
}

.movie-title {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
    color: #333;
}

.date-selector {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.date-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.date-item {
    text-align: center;
    padding: 12px 8px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    background: #fff;
}

.date-item:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.date-item.active {
    border-color: #000;
    background: #000;
    color: #fff;
}

.date-day {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.date-item.active .date-day {
    color: #fff;
}

.date-number {
    font-size: 18px;
    font-weight: bold;
}

.showtime-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.room-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
}

.showtime-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.showtime-btn {
    padding: 12px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    color: #333;
    display: inline-block;
}

.showtime-btn:hover {
    border-color: #007bff;
    background: #f0f8ff;
    color: #007bff;
    text-decoration: none;
}

.showtime-btn:active {
    transform: scale(0.98);
}

.no-showtimes {
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 16px;
}

.close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    color: #000;
}
</style>
@endpush

@section('content')
<div class="booking-container">
    <div class="container">
        <button class="close-btn" onclick="window.history.back()">×</button>
        
        <div class="booking-header">
            <div class="movie-info">
                @if($movie->poster_image_url)
                    <img src="{{ $movie->poster_image_url }}" alt="{{ $movie->title }}" class="movie-poster">
                @endif
                <div>
                    <h1 class="movie-title">{{ $movie->title }}</h1>
                    <p class="text-muted mb-0">{{ $movie->duration_minutes ?? 0 }} phút</p>
                </div>
            </div>
        </div>

        <div class="date-selector">
            <h4 class="mb-3">Chọn ngày</h4>
            <div class="date-grid">
                @foreach($availableDates as $date)
                    @php
                        $isActive = $date->format('Y-m-d') === $selectedDate;
                        $dayName = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
                    @endphp
                    <a href="{{ route('bookings.select-showtime', ['movie' => $movie->id, 'date' => $date->format('Y-m-d')]) }}" 
                       class="date-item {{ $isActive ? 'active' : '' }}">
                        <div class="date-day">{{ $dayName }}</div>
                        <div class="date-number">{{ $date->format('d') }}</div>
                        <div class="date-day" style="font-size: 10px;">{{ $date->format('m') }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="showtime-section">
            <h4 class="mb-3">Chọn suất chiếu - {{ $selectedDateCarbon->format('d/m/Y') }}</h4>
            
            @if($showtimesByRoom->isEmpty())
                <div class="no-showtimes">
                    Không có suất chiếu nào cho ngày này.
                </div>
            @else
                @foreach($showtimesByRoom as $roomId => $roomShowtimes)
                    @php
                        $room = $roomShowtimes->first()->room;
                    @endphp
                    <div class="room-name">
                        {{ $room ? $room->name : 'Phòng chiếu' }}
                    </div>
                    <div class="showtime-grid">
                        @foreach($roomShowtimes as $showtime)
                            <a href="{{ route('bookings.select-seats', $showtime->id) }}" 
                               class="showtime-btn">
                                {{ $showtime->getFormattedShowTime('H:i') }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection

