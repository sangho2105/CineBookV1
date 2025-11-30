@extends('layouts.admin')

@section('title', 'Thêm Combo Mới')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Thêm Combo Mới</h1>
        <a href="{{ route('admin.combos.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Đã có lỗi xảy ra!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.combos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Tên Combo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả / Chi tiết Combo</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4" 
                              placeholder="Ví dụ: 1 Bắp (L) + 2 Nước (M)">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Mô tả chi tiết các sản phẩm trong combo.</small>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Ảnh Combo <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/jpeg,image/png,image/webp" required>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Chấp nhận ảnh JPG, PNG, WEBP tối đa 4MB.</small>
                    <div id="image-preview" class="mt-2" style="display: none;">
                        <label class="form-label">Xem trước ảnh:</label>
                        <div>
                            <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Giá (USD) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                           id="price" name="price" value="{{ old('price') }}" 
                           min="0" step="0.01" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Nhập giá bằng số (ví dụ: 5.00 cho $5.00).</small>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Kích hoạt combo này
                        </label>
                    </div>
                    <small class="text-muted">Combo đang hoạt động sẽ hiển thị cho khách hàng khi đặt vé.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Tạo Combo</button>
                    <a href="{{ route('admin.combos.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const previewDiv = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewDiv.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewDiv.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection

