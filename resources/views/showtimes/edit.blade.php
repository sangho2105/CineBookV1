@extends('layouts.admin')

@section('title', 'Edit Showtime')

@section('content')
<h2>Edit Showtime</h2>

<form action="{{ route('admin.showtimes.update', $showtime) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="movie_id" class="form-label">Movie <span class="text-danger">*</span></label>
        <select name="movie_id" id="movie_id" class="form-select @error('movie_id') is-invalid @enderror">
            <option value="">-- Select Movie --</option>
            @foreach($movies as $movie)
            <option value="{{ $movie->id }}" {{ (old('movie_id', $showtime->movie_id) == $movie->id) ? 'selected' : '' }}>
                {{ $movie->title }}
            </option>
            @endforeach
        </select>
        @error('movie_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="room_id" class="form-label">Room <span class="text-danger">*</span></label>
        <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror">
            <option value="">-- Select Room --</option>
            @foreach($rooms as $room)
            <option value="{{ $room->id }}" {{ (old('room_id', $showtime->room_id) == $room->id) ? 'selected' : '' }}>
                {{ $room->name }} ({{ $room->total_seats }} seats)
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
                   value="{{ old('show_date', $showtime->show_date->format('Y-m-d')) }}">
            @error('show_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Show Time <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 align-items-center">
                @php
                    $formattedTime = $showtime->getFormattedShowTime('H:i:s');
                    $oldTime = old('show_time', $formattedTime);
                    $oldHour = old('show_time_hour', $showtime->getFormattedShowTime('H'));
                    $oldMinute = old('show_time_minute', $showtime->getFormattedShowTime('i'));
                    // Chuyển đổi 00 thành 24 để hiển thị
                    if ($oldHour === '00') {
                        $oldHour = '24';
                    }
                @endphp
                <select name="show_time_hour" id="show_time_hour" class="form-select @error('show_time') is-invalid @enderror" style="flex: 0 0 auto; width: 80px;">
                    <option value="">Hour</option>
                    @for($i = 1; $i <= 24; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                {{ $oldHour == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
                <span class="fw-bold" style="flex: 0 0 auto;">:</span>
                <select name="show_time_minute" id="show_time_minute" class="form-select @error('show_time') is-invalid @enderror" style="flex: 0 0 auto; width: 80px;">
                    <option value="">Minute</option>
                    @for($i = 0; $i < 60; $i += 5)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                {{ $oldMinute == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </option>
                    @endfor
                </select>
                <input type="hidden" name="show_time" id="show_time" value="{{ $oldTime }}">
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
                   value="{{ old('gold_price', $showtime->gold_price) }}" readonly>
            @error('gold_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="platinum_price" class="form-label">Platinum Price (USD) <span class="text-danger">*</span></label>
            <input type="number" name="platinum_price" id="platinum_price" class="form-control @error('platinum_price') is-invalid @enderror" 
                   value="{{ old('platinum_price', $showtime->platinum_price) }}" readonly>
            @error('platinum_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="box_price" class="form-label">Box Price (USD) <span class="text-danger">*</span></label>
            <input type="number" name="box_price" id="box_price" class="form-control @error('box_price') is-invalid @enderror" 
                   value="{{ old('box_price', $showtime->box_price) }}" readonly>
            @error('box_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" name="is_peak_hour" id="is_peak_hour" value="1" 
                   class="form-check-input" {{ (old('is_peak_hour', $showtime->is_peak_hour)) ? 'checked' : '' }}>
            <label for="is_peak_hour" class="form-check-label">Peak Hour</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Cập nhật</button>
    <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">Hủy</a>
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
    
    // Xóa class is-invalid khi user thay đổi
    function clearValidationErrors() {
        showTimeHour.classList.remove('is-invalid');
        showTimeMinute.classList.remove('is-invalid');
        // Xóa thông báo lỗi nếu có
        const errorDiv = showTimeHour.closest('.col-md-6').querySelector('.invalid-feedback.d-block');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    showTimeHour.addEventListener('change', function() {
        updateTimeInput();
        clearValidationErrors();
    });
    showTimeMinute.addEventListener('change', function() {
        updateTimeInput();
        clearValidationErrors();
    });
    
    // Parse old value nếu có - chuyển đổi 00 thành 24 để hiển thị
    @if(old('show_time') || isset($showtime))
        const oldTime = '{{ old("show_time", $showtime->getFormattedShowTime("H:i:s")) }}';
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
    
    // Cập nhật giá ban đầu dựa trên trạng thái hiện tại
    updatePrices();
});
</script>
@endpush
@endsection