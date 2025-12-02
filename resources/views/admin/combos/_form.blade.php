@php
    $combo = $combo ?? null;
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Tên Combo <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $combo->name ?? '') }}">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Mô tả / Chi tiết Combo</label>
    <textarea class="form-control @error('description') is-invalid @enderror" 
              id="description" name="description" rows="4" 
              placeholder="Ví dụ: 1 Bắp (L) + 2 Nước (M)">{{ old('description', $combo->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Mô tả chi tiết các sản phẩm trong combo.</small>
</div>

@if($combo && $combo->image_path)
<div class="mb-3">
    <label class="form-label">Ảnh hiện tại:</label>
    <div>
        <img src="{{ $combo->image_url }}" alt="{{ $combo->name }}" 
             class="img-fluid rounded" style="max-height: 200px;">
    </div>
</div>
@endif

<div class="mb-3">
    <label for="image" class="form-label">Ảnh Combo @if(!$combo)<span class="text-danger">*</span>@endif</label>
    <input type="file" class="form-control @error('image') is-invalid @enderror" 
           id="image" name="image" accept="image/jpeg,image/png,image/webp">
    @error('image')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">
        @if($combo)
            Chọn ảnh mới để thay thế (tối đa 4MB). Để trống nếu giữ nguyên ảnh cũ.
        @else
            Chấp nhận ảnh JPG, PNG, WEBP tối đa 4MB.
        @endif
    </small>
    <div id="image-preview" class="mt-2" style="display: none;">
        <label class="form-label">Xem trước ảnh{{ $combo ? ' mới' : '' }}:</label>
        <div>
            <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="price" class="form-label">Giá (USD) <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('price') is-invalid @enderror" id="price" name="price"
           value="{{ old('price', $combo->price ?? '') }}" placeholder="0.00">
    @error('price')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Nhập giá bằng số thập phân (ví dụ: 5.00, 10.50 cho $5.00, $10.50).</small>
</div>

<div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
               {{ old('is_active', $combo->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Kích hoạt combo này
        </label>
    </div>
    <small class="text-muted">Combo đang hoạt động sẽ hiển thị cho khách hàng khi đặt vé.</small>
</div>


<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $combo ? 'Cập nhật Combo' : 'Tạo Combo' }}</button>
    <a href="{{ route('admin.combos.index') }}" class="btn btn-secondary">Hủy</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview ảnh
    const imageInput = document.getElementById('image');
    const previewDiv = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (imageInput && previewDiv && previewImg) {
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
    }
});
</script>
@endpush

