@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Add New Movie</h1>

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

    <form action="{{ route('admin.movies.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-3">
            <label for="title" class="form-label">Movie Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="form-label">Poster Image <span class="text-danger">*</span></label>
            <div class="mb-2">
                <label for="poster" class="form-label">Choose image from computer:</label>
                <input type="file" class="form-control @error('poster') is-invalid @enderror" id="poster" name="poster" accept="image/jpeg,image/png,image/webp">
                <small class="text-muted">Accepts JPG, PNG, WEBP images up to 4MB.</small>
                @error('poster')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div id="poster-preview" class="mt-2" style="display: none;">
                <label class="form-label">Preview:</label>
                <div>
                    <img id="preview-poster-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="genre" class="form-label">Genre <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('genre') is-invalid @enderror" id="genre" name="genre" value="{{ old('genre') }}" placeholder="Example: Action, Horror">
                @error('genre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="director" class="form-label">Director</label>
                <input type="text" class="form-control @error('director') is-invalid @enderror" id="director" name="director" value="{{ old('director') }}" placeholder="Example: Christopher Nolan">
                @error('director')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language') }}" placeholder="Example: English">
                @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="cast" class="form-label">Cast</label>
                <textarea class="form-control @error('cast') is-invalid @enderror" id="cast" name="cast" rows="1" placeholder="Enter cast list, separated by commas">{{ old('cast') }}</textarea>
                @error('cast')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}">
                @error('duration_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="release_date" class="form-label">Release Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date') }}">
                @error('release_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="">-- Select Status --</option>
                    <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Coming Soon</option>
                    <option value="now_showing" {{ old('status') == 'now_showing' ? 'selected' : '' }}>Now Showing</option>
                    <option value="ended" {{ old('status') == 'ended' ? 'selected' : '' }}>Ended</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="mb-3">
            <label for="rated" class="form-label">Rated</label>
            <select class="form-select @error('rated') is-invalid @enderror" id="rated" name="rated">
                <option value="">-- Select Rated --</option>
                <option value="K" {{ old('rated') == 'K' ? 'selected' : '' }}>K - FILM DISTRIBUTED TO VIEWERS UNDER 13 YEARS OLD WITH ACCOMPANYING GUARDIAN</option>
                <option value="T13" {{ old('rated') == 'T13' ? 'selected' : '' }}>T13 - FILM FOR VIEWERS AGED 13 AND ABOVE</option>
                <option value="T16" {{ old('rated') == 'T16' ? 'selected' : '' }}>T16 - FILM FOR VIEWERS AGED 16 AND ABOVE</option>
                <option value="T18" {{ old('rated') == 'T18' ? 'selected' : '' }}>T18 - FILM FOR VIEWERS AGED 18 AND ABOVE</option>
                <option value="P" {{ old('rated') == 'P' ? 'selected' : '' }}>P - FILM FOR ALL AUDIENCES</option>
            </select>
            @error('rated')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="trailer_url" class="form-label">Link Trailer</label>
            <input type="text" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url') }}">
            @error('trailer_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const posterInput = document.getElementById('poster');
    const posterPreview = document.getElementById('poster-preview');
    const previewPosterImg = document.getElementById('preview-poster-img');

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
                    return;
                }

                // Kiểm tra loại file
                if (!file.type.match('image/(jpeg|png|webp)')) {
                    alert('Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP.');
                    posterInput.value = '';
                    posterPreview.style.display = 'none';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewPosterImg.src = e.target.result;
                    posterPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                posterPreview.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
@endsection