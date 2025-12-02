@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Layout {{ $room->name }}</h2>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Room Information</h5>
            <p class="mb-1"><strong>Total Seats:</strong> {{ $room->total_seats }} seats</p>
            <p class="mb-0"><strong>Number of Rows:</strong> {{ count($room->layout) }} rows</p>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mb-4">
        <h5>Legend:</h5>
        <div class="d-flex gap-3 flex-wrap">
            <div class="d-flex align-items-center">
                <div class="seat-preview seat-normal me-2"></div>
                <span>Regular Seat</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="seat-preview seat-vip me-2"></div>
                <span>VIP Seat</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="seat-preview seat-couple me-2"></div>
                <span>Couple Seat</span>
            </div>
        </div>
    </div>

    {{-- Room Layout --}}
    <div class="room-layout mb-4">
        {{-- Screen --}}
        <div class="screen-area text-center mb-4">
            <div class="screen-display">SCREEN</div>
        </div>

        {{-- Các hàng ghế --}}
        <div class="seating-area">
            @foreach($room->layout as $row)
            <div class="row-seat mb-3">
                <div class="row-label me-3">{{ $row['row_letter'] }}</div>
                <div class="seats-row d-flex gap-2 justify-content-center">
                    @for($i = 1; $i <= $row['seat_count']; $i++)
                        @php
                            $seatNumber = $row['row_letter'] . $i;
                            $seatClass = 'seat-' . $row['seat_type'];
                            $seatWidth = $row['seat_type'] === 'couple' ? '2' : '1';
                        @endphp
                        <div class="seat {{ $seatClass }}" 
                             style="width: {{ $seatWidth * 40 }}px; min-width: {{ $seatWidth * 40 }}px;"
                             title="{{ $seatNumber }} - {{ ucfirst($row['seat_type']) }}">
                            <span class="seat-number">{{ $i }}</span>
                        </div>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Statistics --}}
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Seat Statistics</h5>
            @php
                $normalCount = 0;
                $vipCount = 0;
                $coupleCount = 0;
                foreach($room->layout as $row) {
                    if($row['seat_type'] === 'normal') {
                        $normalCount += $row['seat_count'];
                    } elseif($row['seat_type'] === 'vip') {
                        $vipCount += $row['seat_count'];
                    } elseif($row['seat_type'] === 'couple') {
                        $coupleCount += $row['seat_count'];
                    }
                }
            @endphp
            <ul class="list-unstyled mb-0">
                <li><strong>Regular Seats:</strong> {{ $normalCount }} seats</li>
                <li><strong>VIP Seats:</strong> {{ $vipCount }} seats</li>
                <li><strong>Couple Seats:</strong> {{ $coupleCount }} seats</li>
                <li><strong>Total:</strong> {{ $normalCount + $vipCount + $coupleCount }} seats</li>
            </ul>
        </div>
    </div>
</div>

<style>
.screen-area {
    margin: 20px 0;
}

.screen-display {
    background: linear-gradient(to bottom, #333, #555);
    color: white;
    padding: 15px 40px;
    border-radius: 8px;
    display: inline-block;
    font-weight: bold;
    letter-spacing: 3px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.seating-area {
    max-width: 1000px;
    margin: 0 auto;
}

.row-seat {
    display: flex;
    align-items: center;
    justify-content: center;
}

.row-label {
    font-weight: bold;
    min-width: 30px;
    text-align: center;
    color: #666;
}

.seats-row {
    flex: 1;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

.seat {
    height: 40px;
    border: 2px solid #ddd;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    font-weight: bold;
    position: relative;
}

.seat:hover {
    transform: scale(1.1);
    z-index: 10;
}

.seat-normal {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.seat-vip {
    background-color: #fff3e0;
    border-color: #ff9800;
    color: #f57c00;
}

.seat-couple {
    background-color: #fce4ec;
    border-color: #e91e63;
    color: #c2185b;
}

.seat-number {
    font-size: 11px;
}

.seat-preview {
    width: 30px;
    height: 30px;
    border: 2px solid;
    border-radius: 4px;
    display: inline-block;
}

.seat-preview.seat-normal {
    background-color: #e3f2fd;
    border-color: #2196f3;
}

.seat-preview.seat-vip {
    background-color: #fff3e0;
    border-color: #ff9800;
}

.seat-preview.seat-couple {
    background-color: #fce4ec;
    border-color: #e91e63;
}
</style>
@endsection

