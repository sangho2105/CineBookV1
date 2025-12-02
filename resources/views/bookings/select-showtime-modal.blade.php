<div class="booking-modal-content">
    <div class="date-selector">
        <h4 class="mb-3">Select Date</h4>
        <div class="date-grid">
            @foreach($availableDates as $date)
                @php
                    $isActive = $date->format('Y-m-d') === $selectedDate;
                    $dayName = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
                @endphp
                <button type="button" 
                        class="date-item {{ $isActive ? 'active' : '' }}"
                        data-date="{{ $date->format('Y-m-d') }}"
                        data-movie-id="{{ $movie->id }}">
                    <div class="date-day">{{ $dayName }}</div>
                    <div class="date-number">{{ $date->format('d') }}</div>
                    <div class="date-day" style="font-size: 10px;">{{ $date->format('m') }}</div>
                </button>
            @endforeach
        </div>
    </div>

    <div class="showtime-section">
        <h4 class="mb-3">Select Showtime - {{ $selectedDateCarbon->format('d/m/Y') }}</h4>
        
        @if($showtimesByRoom->isEmpty())
            <div class="no-showtimes">
                No showtimes available for this date.
            </div>
        @else
            @foreach($showtimesByRoom as $roomId => $roomShowtimes)
                @php
                    $room = $roomShowtimes->first()->room;
                @endphp
                <div class="room-name">
                    {{ $room ? $room->name : 'Room' }}
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

