@php
    $combo = $combo ?? null;
    $items = old('items', $combo ? $combo->items->toArray() : []);
    if (empty($items)) {
        $items = [['item_type' => 'popcorn', 'item_name' => '', 'size' => '', 'quantity' => 1]];
    }
@endphp

@csrf

<div class="mb-3">
    <label for="name" class="form-label">Tên combo <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name"
           value="{{ old('name', $combo->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Mô tả</label>
    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $combo->description ?? '') }}</textarea>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="price" class="form-label">Giá (USD) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="price" name="price"
               value="{{ old('price', $combo->price ?? '') }}" min="0.01" step="0.01" required>
    </div>
    <div class="col-md-6">
        <label for="sort_order" class="form-label">Thứ tự hiển thị</label>
        <input type="number" class="form-control" id="sort_order" name="sort_order"
               value="{{ old('sort_order', $combo->sort_order ?? 0) }}" min="0">
        <small class="text-muted">Số nhỏ hơn sẽ hiển thị trước</small>
    </div>
</div>

<div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
               {{ old('is_active', $combo->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Hiển thị ở trang user
        </label>
    </div>
</div>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="form-label mb-0">Thành phần combo <span class="text-danger">*</span></label>
        <button type="button" class="btn btn-sm btn-success" id="add-item">
            <i class="bi bi-plus-circle"></i> Thêm thành phần
        </button>
    </div>
    <small class="text-muted d-block mb-3">Thêm các thành phần như bắp, nước, thức ăn vào combo.</small>
    
    <div id="items-container">
        @foreach($items as $index => $item)
            <div class="card mb-3 item-row" data-index="{{ $index }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Thành phần #{{ $index + 1 }}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-item" {{ count($items) <= 1 ? 'disabled' : '' }}>
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Loại <span class="text-danger">*</span></label>
                            <select class="form-select item-type" name="items[{{ $index }}][item_type]" required>
                                <option value="popcorn" {{ ($item['item_type'] ?? 'popcorn') === 'popcorn' ? 'selected' : '' }}>Bắp</option>
                                <option value="drink" {{ ($item['item_type'] ?? '') === 'drink' ? 'selected' : '' }}>Nước</option>
                                <option value="food" {{ ($item['item_type'] ?? '') === 'food' ? 'selected' : '' }}>Thức ăn</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control item-name" name="items[{{ $index }}][item_name]"
                                   value="{{ $item['item_name'] ?? '' }}" placeholder="VD: Bắp rang bơ, Coca Cola, Hotdog" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Size</label>
                            <input type="text" class="form-control item-size" name="items[{{ $index }}][size]"
                                   value="{{ $item['size'] ?? '' }}" placeholder="VD: S, M, L">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control item-quantity" name="items[{{ $index }}][quantity]"
                                   value="{{ $item['quantity'] ?? 1 }}" min="1" required>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Lưu</button>
    <a href="{{ route('admin.combos.index') }}" class="btn btn-secondary">Hủy</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('items-container');
    let itemIndex = {{ count($items) }};
    
    // Thêm item mới
    document.getElementById('add-item').addEventListener('click', function() {
        const newItem = `
            <div class="card mb-3 item-row" data-index="${itemIndex}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Thành phần #${itemIndex + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Loại <span class="text-danger">*</span></label>
                            <select class="form-select item-type" name="items[${itemIndex}][item_type]" required>
                                <option value="popcorn">Bắp</option>
                                <option value="drink">Nước</option>
                                <option value="food">Thức ăn</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control item-name" name="items[${itemIndex}][item_name]"
                                   placeholder="VD: Bắp rang bơ, Coca Cola, Hotdog" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Size</label>
                            <input type="text" class="form-control item-size" name="items[${itemIndex}][size]"
                                   placeholder="VD: S, M, L">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]"
                                   value="1" min="1" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newItem);
        itemIndex++;
        updateRemoveButtons();
    });
    
    // Xóa item
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const itemRow = e.target.closest('.item-row');
            const items = container.querySelectorAll('.item-row');
            if (items.length > 1) {
                itemRow.remove();
                updateItemNumbers();
                updateRemoveButtons();
            }
        }
    });
    
    function updateItemNumbers() {
        const items = container.querySelectorAll('.item-row');
        items.forEach((item, index) => {
            item.querySelector('h6').textContent = `Thành phần #${index + 1}`;
            const inputs = item.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.name;
                if (name) {
                    const match = name.match(/items\[(\d+)\]/);
                    if (match) {
                        input.name = name.replace(/items\[\d+\]/, `items[${index}]`);
                    }
                }
            });
        });
    }
    
    function updateRemoveButtons() {
        const items = container.querySelectorAll('.item-row');
        const removeButtons = container.querySelectorAll('.remove-item');
        removeButtons.forEach(btn => {
            btn.disabled = items.length <= 1;
        });
    }
    
    updateRemoveButtons();
});
</script>
@endpush

