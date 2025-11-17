@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Thêm Phim Mới</h1>

    {{-- Hiển thị lỗi validation --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.movies.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="title" class="form-label">Tên Phim <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="poster_url" class="form-label">Link ảnh Poster <span class="text-danger">*</span></label>
            <input type="url" class="form-control @error('poster_url') is-invalid @enderror" id="poster_url" name="poster_url" value="{{ old('poster_url') }}" required>
            @error('poster_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="genre" class="form-label">Thể loại <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('genre') is-invalid @enderror" id="genre" name="genre" value="{{ old('genre') }}" placeholder="Ví dụ: Hành động, Kinh dị" required>
                @error('genre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="director" class="form-label">Đạo diễn</label>
                <input type="text" class="form-control @error('director') is-invalid @enderror" id="director" name="director" value="{{ old('director') }}" placeholder="Ví dụ: Christopher Nolan">
                @error('director')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="language" class="form-label">Ngôn ngữ <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language') }}" placeholder="Ví dụ: Tiếng Việt" required>
                @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="cast" class="form-label">Diễn viên</label>
                <textarea class="form-control @error('cast') is-invalid @enderror" id="cast" name="cast" rows="1" placeholder="Nhập danh sách diễn viên, cách nhau bằng dấu phẩy">{{ old('cast') }}</textarea>
                @error('cast')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="duration_minutes" class="form-label">Thời lượng (phút) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" required>
                @error('duration_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="rating_average" class="form-label">Đánh giá (0-5) <span class="text-danger">*</span></label>
                <input type="number" step="0.1" class="form-control @error('rating_average') is-invalid @enderror" id="rating_average" name="rating_average" value="{{ old('rating_average', 0) }}" min="0" max="5" required>
                @error('rating_average')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="">-- Chọn trạng thái --</option>
                    <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="now_showing" {{ old('status') == 'now_showing' ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="ended" {{ old('status') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="mb-3">
            <label for="release_date" class="form-label">Ngày phát hành <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date') }}" required>
            @error('release_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="trailer_url" class="form-label">Link Trailer</label>
            <input type="url" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url') }}">
            @error('trailer_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="synopsis" class="form-label">Tóm tắt nội dung</label>
            <textarea class="form-control @error('synopsis') is-invalid @enderror" id="synopsis" name="synopsis" rows="4">{{ old('synopsis') }}</textarea>
            @error('synopsis')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection