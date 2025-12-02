@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Sửa Phim</h1>

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

    <form action="{{ route('admin.movies.update', $movie->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="title" class="form-label">Tên Phim <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $movie->title) }}">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Ảnh Poster <span class="text-danger">*</span></label>
            
            @if($movie->poster_image_url)
                <div class="mb-3" id="current-poster-wrapper">
                    <label class="form-label">Ảnh hiện tại:</label>
                    <div>
                        <img src="{{ $movie->poster_image_url }}" alt="{{ $movie->title }}" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                </div>
            @endif

            <div class="mb-2">
                <label for="poster" class="form-label">Chọn ảnh mới từ máy tính:</label>
                <input type="file" class="form-control @error('poster') is-invalid @enderror" id="poster" name="poster" accept="image/jpeg,image/png,image/webp">
                <small class="text-muted">Chấp nhận ảnh JPG, PNG, WEBP tối đa 4MB.</small>
                @error('poster')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="text-center my-2">
                <span class="text-muted">hoặc</span>
            </div>
            <div>
                <label for="poster_url" class="form-label">Nhập URL ảnh mới:</label>
                <input type="text" class="form-control @error('poster_url') is-invalid @enderror" id="poster_url" name="poster_url" value="{{ old('poster_url', filter_var($movie->poster_url, FILTER_VALIDATE_URL) ? $movie->poster_url : '') }}" placeholder="https://example.com/poster.jpg">
                @error('poster_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div id="poster-preview" class="mt-2" style="display: none;">
                <label class="form-label">Xem trước ảnh mới:</label>
                <div>
                    <img id="preview-poster-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="genre" class="form-label">Thể loại <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('genre') is-invalid @enderror" id="genre" name="genre" value="{{ old('genre', $movie->genre) }}">
                @error('genre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="director" class="form-label">Đạo diễn</label>
                <input type="text" class="form-control @error('director') is-invalid @enderror" id="director" name="director" value="{{ old('director', $movie->director) }}">
                @error('director')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="language" class="form-label">Ngôn ngữ <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language', $movie->language) }}">
                @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="cast" class="form-label">Diễn viên</label>
                <textarea class="form-control @error('cast') is-invalid @enderror" id="cast" name="cast" rows="1" placeholder="Nhập danh sách diễn viên, cách nhau bằng dấu phẩy">{{ old('cast', $movie->cast) }}</textarea>
                @error('cast')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="duration_minutes" class="form-label">Thời lượng (phút) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $movie->duration_minutes) }}">
                @error('duration_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="release_date" class="form-label">Ngày phát hành <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date', $movie->release_date ? \Carbon\Carbon::parse($movie->release_date)->format('Y-m-d') : '') }}">
                @error('release_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="">-- Chọn trạng thái --</option>
                    <option value="upcoming" {{ old('status', $movie->status) == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="now_showing" {{ old('status', $movie->status) == 'now_showing' ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="ended" {{ old('status', $movie->status) == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="mb-3">
            <label for="rated" class="form-label">Rated</label>
            <select class="form-select @error('rated') is-invalid @enderror" id="rated" name="rated">
                <option value="">-- Chọn Rated --</option>
                <option value="K" {{ old('rated', $movie->rated) == 'K' ? 'selected' : '' }}>K - PHIM ĐƯỢC PHỔ BIẾN ĐẾN NGƯỜI XEM DƯỚI 13 TUỔI VÀ CÓ NGƯỜI BẢO HỘ ĐI KÈM</option>
                <option value="T13" {{ old('rated', $movie->rated) == 'T13' ? 'selected' : '' }}>T13 - PHIM DÀNH CHO NGƯỜI XEM TỪ 13 TUỔI TRỞ LÊN</option>
                <option value="T16" {{ old('rated', $movie->rated) == 'T16' ? 'selected' : '' }}>T16 - PHIM DÀNH CHO NGƯỜI XEM TỪ 16 TUỔI TRỞ LÊN</option>
                <option value="T18" {{ old('rated', $movie->rated) == 'T18' ? 'selected' : '' }}>T18 - PHIM DÀNH CHO NGƯỜI XEM TỪ 18 TUỔI TRỞ LÊN</option>
                <option value="P" {{ old('rated', $movie->rated) == 'P' ? 'selected' : '' }}>P - PHIM DÀNH CHO MỌI ĐỐI TƯỢNG</option>
            </select>
            @error('rated')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="trailer_url" class="form-label">Link Trailer</label>
            <input type="text" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}">
            @error('trailer_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="synopsis" class="form-label">Tóm tắt nội dung</label>
            <textarea class="form-control @error('synopsis') is-invalid @enderror" id="synopsis" name="synopsis" rows="4">{{ old('synopsis', $movie->synopsis) }}</textarea>
            @error('synopsis')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const posterInput = document.getElementById('poster');
    const posterUrlInput = document.getElementById('poster_url');
    const posterPreview = document.getElementById('poster-preview');
    const previewPosterImg = document.getElementById('preview-poster-img');
    const currentPosterWrapper = document.getElementById('current-poster-wrapper');

    if (posterInput && posterPreview && previewPosterImg) {
        // Xử lý preview khi chọn file
        posterInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Kiểm tra kích thước file (4MB)
                if (file.size > 4 * 1024 * 1024) {
                    alert('Kích thước ảnh không được vượt quá 4MB.');
                    posterInput.value = '';
                    posterPreview.style.display = 'none';
                    if (currentPosterWrapper) {
                        currentPosterWrapper.style.display = 'block';
                    }
                    return;
                }

                // Kiểm tra loại file
                if (!file.type.match('image/(jpeg|png|webp)')) {
                    alert('Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP.');
                    posterInput.value = '';
                    posterPreview.style.display = 'none';
                    if (currentPosterWrapper) {
                        currentPosterWrapper.style.display = 'block';
                    }
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewPosterImg.src = e.target.result;
                    posterPreview.style.display = 'block';
                    // Ẩn ảnh hiện tại
                    if (currentPosterWrapper) {
                        currentPosterWrapper.style.display = 'none';
                    }
                    // Xóa URL input khi có file
                    if (posterUrlInput) {
                        posterUrlInput.value = '';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                posterPreview.style.display = 'none';
                if (currentPosterWrapper) {
                    currentPosterWrapper.style.display = 'block';
                }
            }
        });

        // Ẩn preview khi nhập URL
        if (posterUrlInput) {
            posterUrlInput.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    posterPreview.style.display = 'none';
                    if (currentPosterWrapper) {
                        currentPosterWrapper.style.display = 'none';
                    }
                    if (posterInput) {
                        posterInput.value = '';
                    }
                } else {
                    if (currentPosterWrapper) {
                        currentPosterWrapper.style.display = 'block';
                    }
                }
            });
        }
    }
});
</script>
@endpush
@endsection