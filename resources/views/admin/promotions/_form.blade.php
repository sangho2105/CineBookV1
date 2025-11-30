@php
    $promotion = $promotion ?? null;
    $categories = [
        'promotion' => 'Ưu đãi',
        'discount' => 'Giảm giá',
        'event' => 'Sự kiện',
        'movie' => 'Phim',
    ];

    $movies = $movies ?? collect();
    $combos = $combos ?? collect();
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
    <label for="conditions" class="form-label">Thông tin chung</label>
    <div id="conditions-editor" style="height: 300px; background: white; border: 1px solid #ced4da; border-radius: 0.375rem;">
        {!! old('conditions', $promotion->conditions ?? '') !!}
    </div>
    <textarea id="conditions" name="conditions" style="display: none;">{{ old('conditions', $promotion->conditions ?? '') }}</textarea>
    <small class="text-muted">Thông tin này sẽ được hiển thị trên trang chi tiết của ưu đãi/sự kiện. Bạn có thể định dạng văn bản bằng thanh công cụ phía trên.</small>
</div>

{{-- Phần quy tắc giảm giá chỉ hiển thị khi chọn "Ưu đãi" hoặc "Giảm giá" --}}
<div class="mb-4 {{ in_array(old('category', $promotion->category ?? 'promotion'), ['promotion', 'discount']) ? '' : 'd-none' }}" id="discount-rules-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="form-label mb-0">Quy tắc giảm giá <span class="text-danger">*</span></label>
        <button type="button" class="btn btn-sm btn-success" id="add-discount-rule">
            <i class="bi bi-plus-circle"></i> Thêm quy tắc
        </button>
    </div>
    <small class="text-muted d-block mb-3">Bạn có thể thêm nhiều quy tắc giảm giá khác nhau cho cùng một chương trình.</small>
    
    <div id="discount-rules-container">
        @php
            $discountRules = old('discount_rules', $promotion->discount_rules ?? []);
            if (empty($discountRules) || !is_array($discountRules)) {
                $discountRules = [['discount_percentage' => null, 'applies_to' => [], 'min_tickets' => 1, 'requires_combo' => false]];
            }
            $discountPercentages = [5, 10, 15, 20, 25, 30];
            $appliesToOptions = [
                'ticket' => 'Giá vé',
                'combo' => 'Giá combo',
                'total' => 'Tổng bill'
            ];
        @endphp
        
        @foreach($discountRules as $index => $rule)
            <div class="card mb-3 discount-rule-item" data-index="{{ $index }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Quy tắc #{{ $index + 1 }}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-rule" {{ count($discountRules) <= 1 ? 'disabled' : '' }}>
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mức giảm giá <span class="text-danger">*</span></label>
                            <select class="form-select rule-discount-percentage" name="discount_rules[{{ $index }}][discount_percentage]" required>
                                <option value="">-- Chọn mức giảm --</option>
                                @foreach($discountPercentages as $percent)
                                    <option value="{{ $percent }}" {{ (isset($rule['discount_percentage']) && $rule['discount_percentage'] == $percent) ? 'selected' : '' }}>
                                        {{ $percent }}%
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Số vé tối thiểu <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rule-min-tickets" name="discount_rules[{{ $index }}][min_tickets]" 
                                   value="{{ $rule['min_tickets'] ?? 1 }}" min="1" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach($appliesToOptions as $key => $label)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input rule-applies-to" type="checkbox" 
                                                   name="discount_rules[{{ $index }}][applies_to][]" 
                                                   id="applies_to_{{ $index }}_{{ $key }}" 
                                                   value="{{ $key }}" 
                                                   data-applies-to="{{ $key }}"
                                                   {{ (isset($rule['applies_to']) && is_array($rule['applies_to']) && in_array($key, $rule['applies_to'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="applies_to_{{ $index }}_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{-- Hiển thị danh sách combo khi chọn "Giá combo" --}}
                            <div class="mt-3 combo-select-wrapper" id="combo-select-wrapper-{{ $index }}" 
                                 style="{{ (isset($rule['applies_to']) && is_array($rule['applies_to']) && in_array('combo', $rule['applies_to'])) ? '' : 'display: none;' }}">
                                <label class="form-label small">Chọn combo áp dụng:</label>
                                <div class="row g-2">
                                    @foreach($combos as $combo)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input combo-checkbox" type="checkbox" 
                                                       name="discount_rules[{{ $index }}][combo_ids][]" 
                                                       id="combo_{{ $index }}_{{ $combo->id }}" 
                                                       value="{{ $combo->id }}"
                                                       {{ (isset($rule['combo_ids']) && is_array($rule['combo_ids']) && in_array($combo->id, $rule['combo_ids'])) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="combo_{{ $index }}_{{ $combo->id }}">
                                                    {{ $combo->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($combos->isEmpty())
                                        <div class="col-12">
                                            <small class="text-muted">Chưa có combo nào. Vui lòng tạo combo trong <a href="{{ route('admin.combos.index') }}" target="_blank">Quản lý Combo</a>.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input rule-requires-combo" type="checkbox" 
                                       name="discount_rules[{{ $index }}][requires_combo]" 
                                       id="requires_combo_{{ $index }}" 
                                       value="1" 
                                       {{ (isset($rule['requires_combo']) && $rule['requires_combo']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_combo_{{ $index }}">
                                    Yêu cầu có combo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
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

@push('styles')
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    #conditions-editor .ql-editor {
        min-height: 250px;
        font-size: 14px;
    }
</style>
@endpush

@push('scripts')
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Khởi tạo Quill Editor cho "Thông tin chung"
    const editorContainer = document.getElementById('conditions-editor');
    const hiddenTextarea = document.getElementById('conditions');
    
    if (editorContainer && hiddenTextarea) {
        const quill = new Quill('#conditions-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'font': [] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            },
            placeholder: 'Nhập thông tin chung về ưu đãi/sự kiện...'
        });
        
        // Đồng bộ nội dung từ textarea ẩn vào editor khi load
        if (hiddenTextarea.value) {
            quill.root.innerHTML = hiddenTextarea.value;
        }
        
        // Cập nhật textarea ẩn mỗi khi editor thay đổi
        quill.on('text-change', function() {
            hiddenTextarea.value = quill.root.innerHTML;
        });
        
        // Đồng bộ trước khi submit form
        const form = editorContainer.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                hiddenTextarea.value = quill.root.innerHTML;
            });
        }
    }
    const categorySelect = document.getElementById('category');
    const movieWrapper = document.getElementById('movie-select-wrapper');
    const movieSelect = document.getElementById('movie_id');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const currentImageWrapper = document.getElementById('current-image-wrapper');

    // Xử lý category select và quy tắc giảm giá
    const discountRulesWrapper = document.getElementById('discount-rules-wrapper');
    const discountRulesContainer = document.getElementById('discount-rules-container');
    const addRuleBtn = document.getElementById('add-discount-rule');
    
    if (categorySelect && movieWrapper) {
        const toggleFields = () => {
            const category = categorySelect.value;
            const isMovie = category === 'movie';
            const isPromotionOrDiscount = category === 'promotion' || category === 'discount';
            
            // Toggle movie select
            movieWrapper.classList.toggle('d-none', !isMovie);
            if (!isMovie && movieSelect) {
                movieSelect.value = '';
            }
            
            // Toggle discount rules wrapper
            if (discountRulesWrapper) {
                discountRulesWrapper.classList.toggle('d-none', !isPromotionOrDiscount);
            }
        };

        categorySelect.addEventListener('change', toggleFields);
        toggleFields();
    }
    
    // Thêm quy tắc mới
    if (addRuleBtn && discountRulesContainer) {
        let ruleIndex = discountRulesContainer.querySelectorAll('.discount-rule-item').length;
        
        addRuleBtn.addEventListener('click', function() {
            const discountPercentages = [5, 10, 15, 20, 25, 30];
            const appliesToOptions = {
                'ticket': 'Giá vé',
                'combo': 'Giá combo',
                'total': 'Tổng bill'
            };
            
            const combosData = @json($combos);
            let appliesToHtml = '';
            Object.keys(appliesToOptions).forEach(key => {
                appliesToHtml += `
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input rule-applies-to" type="checkbox" 
                                   name="discount_rules[${ruleIndex}][applies_to][]" 
                                   id="applies_to_${ruleIndex}_${key}" 
                                   value="${key}"
                                   data-applies-to="${key}">
                            <label class="form-check-label" for="applies_to_${ruleIndex}_${key}">
                                ${appliesToOptions[key]}
                            </label>
                        </div>
                    </div>
                `;
            });
            
            // Tạo HTML cho combo select
            let comboSelectHtml = '';
            if (combosData && combosData.length > 0) {
                comboSelectHtml = '<div class="mt-3 combo-select-wrapper" id="combo-select-wrapper-' + ruleIndex + '" style="display: none;"><label class="form-label small">Chọn combo áp dụng:</label><div class="row g-2">';
                combosData.forEach(combo => {
                    comboSelectHtml += `
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input combo-checkbox" type="checkbox" 
                                       name="discount_rules[${ruleIndex}][combo_ids][]" 
                                       id="combo_${ruleIndex}_${combo.id}" 
                                       value="${combo.id}">
                                <label class="form-check-label small" for="combo_${ruleIndex}_${combo.id}">
                                    ${combo.name}
                                </label>
                            </div>
                        </div>
                    `;
                });
                comboSelectHtml += '</div></div>';
            } else {
                comboSelectHtml = '<div class="mt-3 combo-select-wrapper" id="combo-select-wrapper-' + ruleIndex + '" style="display: none;"><small class="text-muted">Chưa có combo nào. Vui lòng tạo combo trong <a href="{{ route("admin.combos.index") }}" target="_blank">Quản lý Combo</a>.</small></div>';
            }
            
            let discountOptionsHtml = '<option value="">-- Chọn mức giảm --</option>';
            discountPercentages.forEach(percent => {
                discountOptionsHtml += `<option value="${percent}">${percent}%</option>`;
            });
            
            const ruleHtml = `
                <div class="card mb-3 discount-rule-item" data-index="${ruleIndex}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Quy tắc #${ruleIndex + 1}</h6>
                            <button type="button" class="btn btn-sm btn-danger remove-rule">
                                <i class="bi bi-trash"></i> Xóa
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mức giảm giá <span class="text-danger">*</span></label>
                                <select class="form-select rule-discount-percentage" name="discount_rules[${ruleIndex}][discount_percentage]" required>
                                    ${discountOptionsHtml}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số vé tối thiểu <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rule-min-tickets" name="discount_rules[${ruleIndex}][min_tickets]" value="1" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    ${appliesToHtml}
                                </div>
                                ${comboSelectHtml}
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input rule-requires-combo" type="checkbox" 
                                           name="discount_rules[${ruleIndex}][requires_combo]" 
                                           id="requires_combo_${ruleIndex}" 
                                           value="1">
                                    <label class="form-check-label" for="requires_combo_${ruleIndex}">
                                        Yêu cầu có combo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            discountRulesContainer.insertAdjacentHTML('beforeend', ruleHtml);
            
            // Thêm event listener cho applies-to checkbox của rule mới
            const newRuleItem = discountRulesContainer.querySelector(`[data-index="${ruleIndex}"]`);
            if (newRuleItem) {
                const appliesToCheckboxes = newRuleItem.querySelectorAll('.rule-applies-to');
                appliesToCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        toggleComboSelect(ruleIndex, this);
                    });
                });
            }
            
            ruleIndex++;
            updateRemoveButtons();
        });
    }
    
    // Xóa quy tắc
    function updateRemoveButtons() {
        const removeButtons = document.querySelectorAll('.remove-rule');
        const ruleItems = document.querySelectorAll('.discount-rule-item');
        
        removeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                if (ruleItems.length <= 1) {
                    alert('Phải có ít nhất một quy tắc giảm giá.');
                    return;
                }
                this.closest('.discount-rule-item').remove();
                renumberRules();
            });
        });
    }
    
    // Đánh số lại quy tắc sau khi xóa
    function renumberRules() {
        const ruleItems = document.querySelectorAll('.discount-rule-item');
        ruleItems.forEach((item, index) => {
            item.querySelector('h6').textContent = `Quy tắc #${index + 1}`;
            const inputs = item.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/discount_rules\[\d+\]/, `discount_rules[${index}]`);
                }
                if (input.id) {
                    input.id = input.id.replace(/\d+/, index);
                }
            });
            const labels = item.querySelectorAll('label');
            labels.forEach(label => {
                if (label.htmlFor) {
                    label.htmlFor = label.htmlFor.replace(/\d+/, index);
                }
            });
        });
        updateRemoveButtons();
    }
    
    // Validate form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const category = categorySelect ? categorySelect.value : '';
            if (category === 'promotion' || category === 'discount') {
                const ruleItems = document.querySelectorAll('.discount-rule-item');
                if (ruleItems.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một quy tắc giảm giá.');
                    return false;
                }
                
                let hasError = false;
                ruleItems.forEach((item, index) => {
                    const discountSelect = item.querySelector('.rule-discount-percentage');
                    const appliesToCheckboxes = item.querySelectorAll('.rule-applies-to:checked');
                    
                    if (!discountSelect.value) {
                        hasError = true;
                        discountSelect.classList.add('is-invalid');
                    } else {
                        discountSelect.classList.remove('is-invalid');
                    }
                    
                    if (appliesToCheckboxes.length === 0) {
                        hasError = true;
                        alert(`Quy tắc #${index + 1}: Vui lòng chọn ít nhất một phần để áp dụng giảm giá.`);
                    }
                });
                
                if (hasError) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
    
    // Toggle hiển thị combo select khi chọn/bỏ chọn "Giá combo"
    function toggleComboSelect(ruleIndex, checkbox) {
        const comboWrapper = document.getElementById(`combo-select-wrapper-${ruleIndex}`);
        if (comboWrapper) {
            if (checkbox.checked && checkbox.value === 'combo') {
                comboWrapper.style.display = 'block';
            } else if (checkbox.value === 'combo') {
                // Nếu bỏ chọn combo, ẩn combo select và uncheck tất cả combo
                comboWrapper.style.display = 'none';
                const comboCheckboxes = comboWrapper.querySelectorAll('.combo-checkbox');
                comboCheckboxes.forEach(cb => cb.checked = false);
            }
        }
    }
    
    // Thêm event listener cho tất cả applies-to checkboxes hiện có
    document.querySelectorAll('.rule-applies-to').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const ruleItem = this.closest('.discount-rule-item');
            const ruleIndex = ruleItem ? ruleItem.getAttribute('data-index') : null;
            if (ruleIndex !== null) {
                toggleComboSelect(ruleIndex, this);
            }
        });
        
        // Kiểm tra trạng thái ban đầu
        const ruleItem = checkbox.closest('.discount-rule-item');
        const ruleIndex = ruleItem ? ruleItem.getAttribute('data-index') : null;
        if (ruleIndex !== null && checkbox.value === 'combo' && checkbox.checked) {
            toggleComboSelect(ruleIndex, checkbox);
        }
    });
    
    // Khởi tạo
    updateRemoveButtons();

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
