@extends('layouts.admin')

@section('title', 'Sửa Combo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Sửa Combo</h1>
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
            <form action="{{ route('admin.combos.update', $combo) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Tên Combo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title', $combo->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả / Chi tiết Combo</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4" 
                              placeholder="Ví dụ: 1 Bắp (L) + 2 Nước (M)">{{ old('description', $combo->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Mô tả chi tiết các sản phẩm trong combo.</small>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Ảnh Combo</label>
                    @if($combo->image_path)
                        <div class="mb-2">
                            <label class="form-label">Ảnh hiện tại:</label>
                            <div>
                                <img src="{{ $combo->image_url }}" alt="{{ $combo->title }}" 
                                     class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Chọn ảnh mới để thay thế (tối đa 4MB). Để trống nếu giữ nguyên ảnh cũ.</small>
                    <div id="image-preview" class="mt-2" style="display: none;">
                        <label class="form-label">Xem trước ảnh mới:</label>
                        <div>
                            <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Giá (USD) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                           id="price" name="price" value="{{ old('price', $combo->price) }}" 
                           min="0" step="0.01" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Nhập giá bằng số (ví dụ: 5.00 cho $5.00).</small>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', $combo->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Kích hoạt combo này
                        </label>
                    </div>
                    <small class="text-muted">Combo đang hoạt động sẽ hiển thị cho khách hàng khi đặt vé.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Cập nhật Combo</button>
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

