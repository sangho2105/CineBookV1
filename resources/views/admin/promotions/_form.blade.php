@php
    $promotion = $promotion ?? null;
    $categories = [
        'promotion' => 'Ưu đãi',
        'discount' => 'Giảm giá',
        'event' => 'Sự kiện',
        'movie' => 'Phim',
    ];

    $movies = $movies ?? collect();
@endphp

@csrf

<div class="mb-3">
    <label for="title" class="form-label">Tiêu đề</label>
    <input type="text" class="form-control" id="title" name="title"
           value="{{ old('title', $promotion->title ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="category" class="form-label">Loại</label>
    <select class="form-select" id="category" name="category" required>
        @foreach($categories as $value => $label)
            <option value="{{ $value }}"
                {{ old('category', $promotion->category ?? 'promotion') === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3 {{ old('category', $promotion->category ?? 'promotion') === 'movie' ? '' : 'd-none' }}" id="movie-select-wrapper">
    <label for="movie_id" class="form-label">Chọn phim</label>
    <select class="form-select" id="movie_id" name="movie_id">
        <option value="">-- Chọn phim --</option>
        @foreach($movies as $movie)
            <option value="{{ $movie->id }}"
                {{ (string) old('movie_id', $promotion->movie_id ?? '') === (string) $movie->id ? 'selected' : '' }}>
                {{ $movie->title }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">Bắt buộc khi loại là Phim.</small>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Mô tả</label>
    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $promotion->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="conditions" class="form-label">Điều kiện áp dụng</label>
    <textarea class="form-control" id="conditions" name="conditions" rows="3" placeholder="VD: Áp dụng từ Thứ 2 đến Thứ 6; Không áp dụng ngày lễ; Áp dụng khi mua tối thiểu 2 vé...">{{ old('conditions', $promotion->conditions ?? '') }}</textarea>
    <small class="text-muted">Nhập các điều kiện áp dụng cụ thể cho ưu đãi/sự kiện/giảm giá.</small>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label for="start_date" class="form-label">Ngày bắt đầu</label>
        <input type="date" class="form-control" id="start_date" name="start_date"
               value="{{ old('start_date', isset($promotion->start_date) ? $promotion->start_date->format('Y-m-d') : '') }}"
               required>
    </div>
    <div class="col-md-6">
        <label for="end_date" class="form-label">Ngày kết thúc</label>
        <input type="date" class="form-control" id="end_date" name="end_date"
               value="{{ old('end_date', isset($promotion->end_date) ? $promotion->end_date->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="mb-3 mt-3">
    <label for="image" class="form-label">Ảnh banner</label>
    <input class="form-control" type="file" id="image" name="image" {{ isset($promotion) ? '' : 'required' }} accept="image/jpeg,image/png,image/webp">
    <small class="text-muted">Chấp nhận ảnh JPG, PNG, WEBP tối đa 4MB.</small>
    <div id="image-preview" class="mt-2" style="display: none;">
        <label class="form-label">Xem trước ảnh mới:</label>
        <div>
            <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
        </div>
    </div>
</div>

@isset($promotion)
    @if($promotion->image_url)
        <div class="mb-3" id="current-image-wrapper">
            <label class="form-label">Ảnh hiện tại</label>
            <div>
                <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}" class="img-fluid rounded" style="max-height: 200px;">
            </div>
        </div>
    @endif
@endisset

<div class="form-check form-switch mb-4">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
           {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Kích hoạt hiển thị</label>
</div>

<button type="submit" class="btn btn-primary">Lưu</button>
<a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">Hủy</a>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category');
    const movieWrapper = document.getElementById('movie-select-wrapper');
    const movieSelect = document.getElementById('movie_id');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const currentImageWrapper = document.getElementById('current-image-wrapper');

    // Xử lý category select
    if (categorySelect && movieWrapper) {
        const toggleMovieSelect = () => {
            const isMovie = categorySelect.value === 'movie';
            movieWrapper.classList.toggle('d-none', !isMovie);

            if (!isMovie && movieSelect) {
                movieSelect.value = '';
            }
        };

        categorySelect.addEventListener('change', toggleMovieSelect);
        toggleMovieSelect();
    }

    // Xử lý preview ảnh khi upload
    if (imageInput && imagePreview && previewImg) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Kiểm tra kích thước file (4MB)
                if (file.size > 4 * 1024 * 1024) {
                    alert('Kích thước ảnh không được vượt quá 4MB.');
                    imageInput.value = '';
                    imagePreview.style.display = 'none';
                    if (currentImageWrapper) {
                        currentImageWrapper.style.display = 'block';
                    }
                    return;
                }

                // Kiểm tra loại file
                if (!file.type.match('image/(jpeg|png|webp)')) {
                    alert('Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP.');
                    imageInput.value = '';
                    imagePreview.style.display = 'none';
                    if (currentImageWrapper) {
                        currentImageWrapper.style.display = 'block';
                    }
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                    // Ẩn ảnh hiện tại nếu có
                    if (currentImageWrapper) {
                        currentImageWrapper.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                if (currentImageWrapper) {
                    currentImageWrapper.style.display = 'block';
                }
            }
        });
    }
});
</script>
@endpush
