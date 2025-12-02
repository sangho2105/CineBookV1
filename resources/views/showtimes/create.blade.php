@extends('layouts.admin')

@section('title', 'Add Showtime')

@section('content')
<h2>Thêm suất chiếu</h2>

<form action="{{ route('admin.showtimes.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="movie_id" class="form-label">Movie <span class="text-danger">*</span></label>
        <select name="movie_id" id="movie_id" class="form-select @error('movie_id') is-invalid @enderror">
            <option value="">-- Select Movie --</option>
            @foreach($movies as $movie)
            <option value="{{ $movie->id }}" {{ old('movie_id') == $movie->id ? 'selected' : '' }}>
                {{ $movie->title }}
            </option>
            @endforeach
        </select>
        @error('movie_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="room_id" class="form-label">Phòng chiếu <span class="text-danger">*</span></label>
        <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror">
            <option value="">-- Chọn Phòng chiếu --</option>
            @foreach($rooms as $room)
            <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                {{ $room->name }} ({{ $room->total_seats }} ghế)
            </option>
            @endforeach
        </select>
        @error('room_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="show_date" class="form-label">Show Date <span class="text-danger">*</span></label>
            <input type="date" name="show_date" id="show_date" class="form-control @error('show_date') is-invalid @enderror" 
                   value="{{ old('show_date') }}">
            @error('show_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Show Time <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 align-items-center">
                @php
                    $defaultHour = old('show_time_hour');
                    if (!$defaultHour && old('show_time')) {
                        // Parse thủ công từ chuỗi H:i hoặc H:i:s
                        $timeStr = old('show_time');
                        if (strlen($timeStr) > 5) {
                            $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
                        }
                        $timeParts = explode(':', $timeStr);
                        $defaultHour = str_pad((int)($timeParts[0] ?? 0), 2, '0', STR_PAD_LEFT);
                    }
                    // Chuyển đổi 00 thành 24 để hiển thị
                    if ($defaultHour === '00') {
                        $defaultHour = '24';
                    }
                @endphp
                <select name="show_time_hour" id="show_time_hour" class="form-select @error('show_time') is-invalid @enderror" style="flex: 0 0 auto; width: 80px;">
                    <option value="">Giờ</option>
                    @for($i = 1; $i <= 24; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                {{ $defaultHour == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
                <span class="fw-bold" style="flex: 0 0 auto;">:</span>
                <select name="show_time_minute" id="show_time_minute" class="form-select @error('show_time') is-invalid @enderror" style="flex: 0 0 auto; width: 80px;">
                    <option value="">Phút</option>
                    @for($i = 0; $i < 60; $i += 5)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                {{ old('show_time_minute', '00') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </option>
                    @endfor
                </select>
                <input type="hidden" name="show_time" id="show_time" value="{{ old('show_time') }}">
            </div>
            @error('show_time')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="gold_price" class="form-label">Gold Price (USD) <span class="text-danger">*</span></label>
            <input type="number" name="gold_price" id="gold_price" class="form-control @error('gold_price') is-invalid @enderror" 
                   value="{{ old('gold_price', 17) }}" readonly>
            @error('gold_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="platinum_price" class="form-label">Platinum Price (USD) <span class="text-danger">*</span></label>
            <input type="number" name="platinum_price" id="platinum_price" class="form-control @error('platinum_price') is-invalid @enderror" 
                   value="{{ old('platinum_price', 20) }}" readonly>
            @error('platinum_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="box_price" class="form-label">Box Price (USD) <span class="text-danger">*</span></label>
            <input type="number" name="box_price" id="box_price" class="form-control @error('box_price') is-invalid @enderror" 
                   value="{{ old('box_price', 40) }}" readonly>
            @error('box_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" name="is_peak_hour" id="is_peak_hour" value="1" 
                   class="form-check-input" {{ old('is_peak_hour') ? 'checked' : '' }}>
                <label for="is_peak_hour" class="form-check-label">Giờ cao điểm</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">Cancel</a>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const showDateInput = document.getElementById('show_date');
    const showTimeHour = document.getElementById('show_time_hour');
    const showTimeMinute = document.getElementById('show_time_minute');
    const showTimeInput = document.getElementById('show_time');
    
    // Cập nhật hidden input khi chọn giờ/phút
    function updateTimeInput() {
        let hour = showTimeHour.value;
        const minute = showTimeMinute.value;
        if (hour && minute) {
            // Chuyển đổi giờ 24 thành 00 (vì 24:00 = 00:00)
            if (hour === '24') {
                hour = '00';
            }
            showTimeInput.value = hour + ':' + minute + ':00';
        } else {
            showTimeInput.value = '';
        }
    }
    
    showTimeHour.addEventListener('change', updateTimeInput);
    showTimeMinute.addEventListener('change', updateTimeInput);
    
    // Parse old value nếu có - chuyển đổi 00 thành 24 để hiển thị
    @if(old('show_time'))
        const oldTime = '{{ old("show_time") }}';
        if (oldTime) {
            let [hour, minute] = oldTime.split(':');
            // Chuyển đổi 00 thành 24 để hiển thị
            if (hour === '00') {
                hour = '24';
            }
            if (hour) showTimeHour.value = hour;
            if (minute) showTimeMinute.value = minute;
            updateTimeInput();
        }
    @endif
    
    // Tính giá tự động khi chọn Peak hour
    const goldPriceInput = document.getElementById('gold_price');
    const platinumPriceInput = document.getElementById('platinum_price');
    const boxPriceInput = document.getElementById('box_price');
    const peakHourCheckbox = document.getElementById('is_peak_hour');
    
    // Giá cố định
    const BASE_GOLD_PRICE = 17;
    const BASE_PLATINUM_PRICE = 20;
    const BASE_BOX_PRICE = 40;
    const PEAK_HOUR_MULTIPLIER = 1.2; // Tăng 20%
    
    function updatePrices() {
        const isPeakHour = peakHourCheckbox.checked;
        const multiplier = isPeakHour ? PEAK_HOUR_MULTIPLIER : 1;
        
        goldPriceInput.value = (BASE_GOLD_PRICE * multiplier).toFixed(2);
        platinumPriceInput.value = (BASE_PLATINUM_PRICE * multiplier).toFixed(2);
        boxPriceInput.value = (BASE_BOX_PRICE * multiplier).toFixed(2);
    }
    
    // Cập nhật giá khi checkbox thay đổi
    peakHourCheckbox.addEventListener('change', updatePrices);
    
    // Cập nhật giá ban đầu nếu checkbox đã được chọn
    updatePrices();
});
</script>
@endpush
@endsection