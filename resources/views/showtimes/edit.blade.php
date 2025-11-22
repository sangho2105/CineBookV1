@extends('layouts.admin')

@section('title', 'Edit Showtime')

@section('content')
<h2>Edit Showtime</h2>

<form action="{{ route('admin.showtimes.update', $showtime) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="movie_id" class="form-label">Movie <span class="text-danger">*</span></label>
        <select name="movie_id" id="movie_id" class="form-select @error('movie_id') is-invalid @enderror" required>
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
        <label for="room_id" class="form-label">Phòng chiếu <span class="text-danger">*</span></label>
        <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
            <option value="">-- Chọn Phòng chiếu --</option>
            @foreach($rooms as $room)
            <option value="{{ $room->id }}" {{ (old('room_id', $showtime->room_id) == $room->id) ? 'selected' : '' }}>
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
                   value="{{ old('show_date', $showtime->show_date->format('Y-m-d')) }}" required>
            @error('show_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="show_time" class="form-label">Show Time <span class="text-danger">*</span></label>
            <input type="time" name="show_time" id="show_time" class="form-control @error('show_time') is-invalid @enderror" 
                   value="{{ old('show_time', date('H:i', strtotime($showtime->show_time))) }}" required>
            @error('show_time')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="gold_price" class="form-label">Gold Price (USD) <span class="text-danger">*</span></label>
        <input type="number" name="gold_price" id="gold_price" class="form-control @error('gold_price') is-invalid @enderror" 
               value="{{ old('gold_price', $showtime->gold_price) }}" min="1" max="1000" step="1" required>
        @error('gold_price')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="platinum_price" class="form-label">Platinum Price (USD) <span class="text-danger">*</span></label>
        <input type="number" name="platinum_price" id="platinum_price" class="form-control @error('platinum_price') is-invalid @enderror" 
               value="{{ old('platinum_price', $showtime->platinum_price) }}" min="1" max="1000" step="1" required>
        @error('platinum_price')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="box_price" class="form-label">Box Price (USD) <span class="text-danger">*</span></label>
        <input type="number" name="box_price" id="box_price" class="form-control @error('box_price') is-invalid @enderror" 
               value="{{ old('box_price', $showtime->box_price) }}" min="1" max="1000" step="1" required>
        @error('box_price')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" name="is_peak_hour" id="is_peak_hour" value="1" 
                   class="form-check-input" {{ (old('is_peak_hour', $showtime->is_peak_hour)) ? 'checked' : '' }}>
            <label for="is_peak_hour" class="form-check-label">Giờ cao điểm</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Cập nhật</button>
    <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">Hủy</a>
</form>
@endsection