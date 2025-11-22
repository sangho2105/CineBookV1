@extends('layouts.admin')

@section('title', 'Showtime Details')

@section('content')
<h2>Showtime Details</h2>

<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">ID:</dt>
            <dd class="col-sm-9">{{ $showtime->id }}</dd>

            <dt class="col-sm-3">Movie:</dt>
            <dd class="col-sm-9">{{ $showtime->movie->title }}</dd>

            <dt class="col-sm-3">Phòng chiếu:</dt>
            <dd class="col-sm-9">{{ $showtime->room ? $showtime->room->name . ' (' . $showtime->room->total_seats . ' ghế)' : ($showtime->theater ? $showtime->theater->name . ' - ' . $showtime->theater->city : 'N/A') }}</dd>

            <dt class="col-sm-3">Show Date:</dt>    
            <dd class="col-sm-9">{{ $showtime->show_date->format('d/m/Y') }}</dd>

            <dt class="col-sm-3">Show Time:</dt>
            <dd class="col-sm-9">{{ date('H:i', strtotime($showtime->show_time)) }}</dd>

            <dt class="col-sm-3">Gold Price:</dt>
            <dd class="col-sm-9">{{ number_format($showtime->gold_price, 0, ',', '.') }} đ</dd>

            <dt class="col-sm-3">Platinum Price:</dt>
            <dd class="col-sm-9">{{ number_format($showtime->platinum_price, 0, ',', '.') }} đ</dd>

            <dt class="col-sm-3">Box Price:</dt>
            <dd class="col-sm-9">{{ number_format($showtime->box_price, 0, ',', '.') }} đ</dd>

            <dt class="col-sm-3">Peak Hour:</dt>
            <dd class="col-sm-9">{{ $showtime->is_peak_hour ? 'Có' : 'Không' }}</dd>

            <dt class="col-sm-3">Created At:</dt>
            <dd class="col-sm-9">{{ $showtime->created_at->format('d/m/Y H:i:s') }}</dd>

            <dt class="col-sm-3">Updated At:</dt>
            <dd class="col-sm-9">{{ $showtime->updated_at->format('d/m/Y H:i:s') }}</dd>
        </dl>

        <div class="mt-3">
            <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>

@if($showtime->room && $seats->isNotEmpty())
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Sơ đồ ghế - {{ $showtime->room->name }}</h5>
    </div>
    <div class="card-body">
        <div class="screen text-center mb-3">
            <div class="screen-bar">MÀN HÌNH</div>
        </div>

        <div class="seatmap-wrapper position-relative mb-3">
            <div class="center-zone"></div>
            @php
                $seatsByRow = $seats->groupBy('row_number')->sortKeys();
            @endphp
            @foreach($seatsByRow as $rowLabel => $rowSeats)
                @php 
                    $count = $rowSeats->count();
                    $isCoupleRow = in_array($rowLabel, $coupleRows ?? []);
                    $gridCols = $isCoupleRow ? $count * 2 : $count;
                @endphp
                <div class="d-flex align-items-center mb-1 seat-row">
                    <div class="row-label">{{ $rowLabel }}</div>
                    <div class="row-seats" style="grid-template-columns: repeat({{ $gridCols }}, 1fr)">
                        @foreach($rowSeats as $seat)
                            @php
                                $isBooked = in_array($seat->id, $bookedSeatIds);
                                $categoryClass = $seat->seat_category === 'Platinum' ? 'seat-vip' : ($seat->seat_category === 'Box' ? 'seat-sweet' : 'seat-regular');
                            @endphp
                            <div class="seat {{ $categoryClass }} {{ $isBooked ? 'seat-booked' : 'seat-available' }} {{ $isCoupleRow ? 'seat-couple' : '' }}"
                                 style="{{ $isCoupleRow ? 'grid-column: span 2;' : '' }}"
                                 title="{{ $seat->seat_number }} - {{ $isBooked ? 'Đã đặt' : 'Trống' }}">
                                {{ str_replace($rowLabel, '', $seat->seat_number) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="legend mt-3">
            <div class="d-flex align-items-center mb-2">
                <span class="legend-box seat-regular me-2"></span>
                <span>Ghế thường (Gold) - Trống</span>
            </div>
            <div class="d-flex align-items-center mb-2">
                <span class="legend-box seat-vip me-2"></span>
                <span>Ghế VIP (Platinum) - Trống</span>
            </div>
            <div class="d-flex align-items-center mb-2">
                <span class="legend-box seat-sweet me-2"></span>
                <span>Ghế cặp đôi (Box) - Trống</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="legend-box seat-booked me-2"></span>
                <span>Ghế đã đặt</span>
            </div>
        </div>
    </div>
</div>

<style>
.screen .screen-bar{
    width: 60%;
    margin: 0 auto;
    border-top: 4px solid #cfcfcf;
    color: #6c757d;
    padding-top: 8px;
    font-weight: 600;
    letter-spacing: 2px;
}
.seatmap-wrapper{
    position: relative;
    background: #111;
    border-radius: 12px;
    padding: 16px 12px 20px 12px;
    color: #fff;
    overflow: hidden;
}
.seat-row .row-label{
    width: 36px;
    text-align: center;
    font-weight: 600;
    color: #adb5bd;
}
.seat-row .row-seats{
    display: grid;
    grid-template-columns: repeat(20, 1fr);
    gap: 6px;
    width: 100%;
}
.seat{
    border: none;
    color: #fff;
    font-weight: 600;
    height: 34px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: default;
}
.seat-regular{ background:#5b5bd6; }
.seat-vip{ background:#e55353; }
.seat-sweet{ background:#bf3fb9; border-radius:18px; }
.seat-couple{ 
    grid-column: span 2 !important;
    min-width: 68px;
}
.seat-booked{ background:#6c757d !important; opacity:.7; }

.legend{ color:#333; }
.legend .legend-box{
    display:inline-block; width:24px; height:16px; border-radius:4px; margin-right:6px; vertical-align:middle;
}
.legend .seat-booked{ background:#6c757d; }
.legend .seat-regular{ background:#5b5bd6; }
.legend .seat-vip{ background:#e55353; }
.legend .seat-sweet{ background:#bf3fb9; border-radius:12px; }
.center-zone-box{ background:rgba(255,255,255,0.25); }

.seatmap-wrapper .center-zone{
    position:absolute;
    top:10%;
    left:22%;
    width:56%;
    height:78%;
    background: rgba(255,255,255,0.08);
    border-radius:12px;
    pointer-events:none;
}
</style>
@endif
@endsection