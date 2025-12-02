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
    $currentCategory = old('category', $promotion->category ?? 'promotion');
    $isPromotionOrDiscount = in_array($currentCategory, ['promotion', 'discount']);
    $isDiscount = $currentCategory === 'discount';
@endphp

@csrf

<div class="mb-3">
    <label for="title" class="form-label">Tiêu đề</label>
    <input type="text" class="form-control" id="title" name="title"
           value="{{ old('title', $promotion->title ?? '') }}">
</div>

<div class="mb-3">
    <label for="category" class="form-label">Loại</label>
    <select class="form-select" id="category" name="category">
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
    <textarea id="conditions" name="conditions" class="form-control" rows="10">{{ old('conditions', $promotion->conditions ?? '') }}</textarea>
    <small class="text-muted">Thông tin này sẽ được hiển thị trên trang chi tiết của ưu đãi/sự kiện.</small>
</div>

{{-- Phần quy tắc giảm giá chỉ hiển thị khi chọn "Ưu đãi" hoặc "Giảm giá" --}}
<div class="mb-4 {{ in_array(old('category', $promotion->category ?? 'promotion'), ['promotion', 'discount']) ? '' : 'd-none' }}" id="discount-rules-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="form-label mb-0">Quy tắc giảm giá 
            @if(old('category', $promotion->category ?? 'promotion') === 'discount')
                <span class="text-danger">*</span>
            @endif
        </label>
    </div>
    <small class="text-muted d-block mb-3">
        Mỗi chương trình giảm giá chỉ có một quy tắc. Để trống nếu chỉ là ưu đãi thông thường.
    </small>
    
    <div id="discount-rules-container">
        @php
            $discountRules = old('discount_rules', $promotion->discount_rules ?? []);
            // Chỉ tự động tạo rule mặc định nếu category là 'discount' và không có rule nào
            if (($currentCategory === 'discount') && (empty($discountRules) || !is_array($discountRules))) {
                $discountRules = [['discount_percentage' => null, 'applies_to' => [], 'min_tickets' => 1, 'requires_combo' => false, 'movie_id' => null]];
            }
            // Nếu là 'promotion' và không có rule, để mảng rỗng
            if (empty($discountRules) || !is_array($discountRules)) {
                $discountRules = [];
            }
            $discountPercentages = [5, 10, 15, 20, 25, 30];
            $appliesToOptions = [
                'ticket' => 'Giá vé',
                'combo' => 'Giá combo',
                'total' => 'Tổng bill'
            ];
        @endphp
        
        @php
            // Chỉ lấy quy tắc đầu tiên (mỗi promotion chỉ có một quy tắc)
            $rule = !empty($discountRules) && is_array($discountRules) ? reset($discountRules) : [];
            $index = 0;
        @endphp
        @if(empty($rule))
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Chưa có quy tắc giảm giá nào. Quy tắc sẽ được tạo tự động khi bạn điền thông tin.
            </div>
        @endif
        @if(!empty($rule) || $currentCategory === 'promotion' || $currentCategory === 'discount')
            <div class="card mb-3 discount-rule-item" data-index="{{ $index }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Quy tắc giảm giá</h6>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Áp dụng cho phim</label>
                            <select class="form-select rule-movie-id" name="discount_rules[{{ $index }}][movie_id]">
                                <option value="">-- Tất cả phim --</option>
                                @foreach($movies as $movie)
                                    <option value="{{ $movie->id }}" {{ (isset($rule['movie_id']) && $rule['movie_id'] == $movie->id) ? 'selected' : '' }}>
                                        {{ $movie->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Chọn phim cụ thể hoặc để trống để áp dụng cho tất cả phim</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Mức giảm giá</label>
                            <select class="form-select rule-discount-percentage" name="discount_rules[{{ $index }}][discount_percentage]">
                                <option value="">-- Không giảm giá --</option>
                                @foreach($discountPercentages as $percent)
                                    <option value="{{ $percent }}" {{ (isset($rule['discount_percentage']) && $rule['discount_percentage'] == $percent) ? 'selected' : '' }}>
                                        {{ $percent }}%
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Có thể để trống nếu chỉ là ưu đãi (tặng quà, v.v.)</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Số vé tối thiểu</label>
                            <input type="number" class="form-control rule-min-tickets" name="discount_rules[{{ $index }}][min_tickets]" 
                                   value="{{ $rule['min_tickets'] ?? 1 }}">
                            <small class="text-muted">Số vé tối thiểu để áp dụng quy tắc này</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Áp dụng cho</label>
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
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input rule-requires-combo" type="checkbox" 
                                       name="discount_rules[{{ $index }}][requires_combo]" 
                                       id="requires_combo_{{ $index }}" 
                                       value="1" 
                                       data-rule-index="{{ $index }}"
                                       {{ (isset($rule['requires_combo']) && $rule['requires_combo']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_combo_{{ $index }}">
                                    Yêu cầu có combo
                                </label>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input rule-gift-only" type="checkbox" 
                                       name="discount_rules[{{ $index }}][gift_only]" 
                                       id="gift_only_{{ $index }}" 
                                       value="1"
                                       {{ (isset($rule['gift_only']) && $rule['gift_only']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="gift_only_{{ $index }}">
                                    Chặn giảm giá khi tặng quà
                                </label>
                                <small class="text-muted d-block mt-1">Nếu chọn, chỉ tặng quà và không áp dụng giảm giá. Nếu không chọn, vừa giảm giá vừa tặng quà (nếu có cả hai).</small>
                            </div>
                            {{-- Hiển thị danh sách combo khi chọn "Yêu cầu có combo" --}}
                            <div class="mt-3 requires-combo-select-wrapper" id="requires-combo-select-wrapper-{{ $index }}" 
                                 data-initial-display="{{ (isset($rule['requires_combo']) && $rule['requires_combo']) ? 'block' : 'none' }}"
                                 style="{{ (isset($rule['requires_combo']) && $rule['requires_combo']) ? '' : 'display: none;' }}">
                                <label class="form-label small">Chọn combo:</label>
                                <div class="row g-2">
                                    @foreach($combos as $combo)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input requires-combo-checkbox" type="checkbox" 
                                                       name="discount_rules[{{ $index }}][requires_combo_ids][]" 
                                                       id="requires_combo_{{ $index }}_{{ $combo->id }}" 
                                                       value="{{ $combo->id }}"
                                                       data-combo-id="{{ $combo->id }}"
                                                       {{ (isset($rule['requires_combo_ids']) && is_array($rule['requires_combo_ids']) && in_array($combo->id, $rule['requires_combo_ids'])) ? 'checked' : '' }}
                                                       {{ (isset($rule['combo_ids']) && is_array($rule['combo_ids']) && in_array($combo->id, $rule['combo_ids'])) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="requires_combo_{{ $index }}_{{ $combo->id }}">
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
                                <small class="text-muted d-block mt-2">Chọn combo để yêu cầu và áp dụng giảm giá (nếu có). Nếu không chọn combo nào, sẽ yêu cầu bất kỳ combo nào</small>
                                {{-- Hidden input để lưu combo_ids (sẽ được sync từ requires_combo_ids) --}}
                                <input type="hidden" name="discount_rules[{{ $index }}][combo_ids_sync]" value="1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label for="start_date" class="form-label">Ngày bắt đầu</label>
        <input type="date" class="form-control" id="start_date" name="start_date"
               value="{{ old('start_date', isset($promotion->start_date) ? $promotion->start_date->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-6">
        <label for="end_date" class="form-label">Ngày kết thúc</label>
        <input type="date" class="form-control" id="end_date" name="end_date"
               value="{{ old('end_date', isset($promotion->end_date) ? $promotion->end_date->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="mb-3 mt-3">
    <label for="image" class="form-label">Ảnh banner</label>
    <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
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
    
    // Không cho phép thêm quy tắc mới (mỗi promotion chỉ có một quy tắc)
    if (addRuleBtn) {
        addRuleBtn.style.display = 'none'; // Ẩn nút "Thêm quy tắc"
    }
    
    // Giữ lại code cũ để tránh lỗi, nhưng không cho phép thêm quy tắc
    if (addRuleBtn && discountRulesContainer) {
        let ruleIndex = discountRulesContainer.querySelectorAll('.discount-rule-item').length;
        
        addRuleBtn.addEventListener('click', function() {
            // Không cho phép thêm quy tắc mới
            alert('Mỗi chương trình giảm giá chỉ có một quy tắc.');
            return;
            const category = categorySelect ? categorySelect.value : '';
            const isPromotionOrDiscount = category === 'promotion' || category === 'discount';
            // Validation sẽ được xử lý bởi PHP, không dùng HTML5 validation
            
            const discountPercentages = [5, 10, 15, 20, 25, 30];
            const appliesToOptions = {
                'ticket': 'Giá vé',
                'combo': 'Giá combo',
                'total': 'Tổng bill'
            };
            
            const combosData = @json($combos);
            const moviesData = @json($movies);
            const comboIndexUrl = @json(route('admin.combos.index'));
            
            // Tạo HTML cho movie select
            let movieOptionsHtml = '<option value="">-- Tất cả phim --</option>';
            if (moviesData && moviesData.length > 0) {
                moviesData.forEach(movie => {
                    movieOptionsHtml += `<option value="${movie.id}">${movie.title}</option>`;
                });
            }
            
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
            
            // Function để tạo HTML cho combo select (dùng chung cho requires_combo_ids)
            function createComboSelectHtml(ruleIndex, comboUrl) {
                let html = '';
                if (combosData && combosData.length > 0) {
                    html = `<div class="mt-3 requires-combo-select-wrapper" id="requires-combo-select-wrapper-${ruleIndex}" style="display: none;"><label class="form-label small">Chọn combo:</label><div class="row g-2">`;
                    combosData.forEach(combo => {
                        html += `
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input requires-combo-checkbox" type="checkbox" 
                                           name="discount_rules[${ruleIndex}][requires_combo_ids][]" 
                                           id="requires_combo_${ruleIndex}_${combo.id}" 
                                           value="${combo.id}"
                                           data-combo-id="${combo.id}">
                                    <label class="form-check-label small" for="requires_combo_${ruleIndex}_${combo.id}">
                                        ${combo.name}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div><small class="text-muted d-block mt-2">Chọn combo để yêu cầu và áp dụng giảm giá (nếu có). Nếu không chọn combo nào, sẽ yêu cầu bất kỳ combo nào</small><input type="hidden" name="discount_rules[' + ruleIndex + '][combo_ids_sync]" value="1"></div>';
                } else {
                    html = `<div class="mt-3 requires-combo-select-wrapper" id="requires-combo-select-wrapper-${ruleIndex}" style="display: none;"><small class="text-muted">Chưa có combo nào. Vui lòng tạo combo trong <a href="${comboUrl}" target="_blank">Quản lý Combo</a>.</small></div>`;
                }
                return html;
            }
            
            // Tạo HTML cho combo select (dùng chung)
            const requiresComboSelectHtml = createComboSelectHtml(ruleIndex, comboIndexUrl);
            
            let discountOptionsHtml = '<option value="">-- Không giảm giá --</option>';
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
                                <label class="form-label">Áp dụng cho phim</label>
                                <select class="form-select rule-movie-id" name="discount_rules[${ruleIndex}][movie_id]">
                                    ${movieOptionsHtml}
                                </select>
                                <small class="text-muted">Chọn phim cụ thể hoặc để trống để áp dụng cho tất cả phim</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mức giảm giá</label>
                                <select class="form-select rule-discount-percentage" name="discount_rules[${ruleIndex}][discount_percentage]">
                                    ${discountOptionsHtml}
                                </select>
                                <small class="text-muted">Có thể để trống nếu chỉ là ưu đãi (tặng quà, v.v.)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số vé tối thiểu</label>
                                <input type="number" class="form-control rule-min-tickets" name="discount_rules[${ruleIndex}][min_tickets]" value="1">
                                <small class="text-muted">Số vé tối thiểu để áp dụng quy tắc này</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Áp dụng cho</label>
                                <div class="row g-2">
                                    ${appliesToHtml}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input rule-requires-combo" type="checkbox" 
                                           name="discount_rules[${ruleIndex}][requires_combo]" 
                                           id="requires_combo_${ruleIndex}" 
                                           value="1"
                                           data-rule-index="${ruleIndex}">
                                    <label class="form-check-label" for="requires_combo_${ruleIndex}">
                                        Yêu cầu có combo
                                    </label>
                                </div>
                                <div class="form-check mt-3">
                                    <input class="form-check-input rule-gift-only" type="checkbox" 
                                           name="discount_rules[${ruleIndex}][gift_only]" 
                                           id="gift_only_${ruleIndex}" 
                                           value="1">
                                    <label class="form-check-label" for="gift_only_${ruleIndex}">
                                        Chặn giảm giá khi tặng quà
                                    </label>
                                    <small class="text-muted d-block mt-1">Nếu chọn, chỉ tặng quà và không áp dụng giảm giá. Nếu không chọn, vừa giảm giá vừa tặng quà (nếu có cả hai).</small>
                                </div>
                                ${requiresComboSelectHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            discountRulesContainer.insertAdjacentHTML('beforeend', ruleHtml);
            
            // Event delegation đã xử lý event listener cho rule mới, không cần thêm code ở đây
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
                const category = categorySelect ? categorySelect.value : '';
                const isDiscount = category === 'discount';
                if (isDiscount && ruleItems.length <= 1) {
                    alert('Phải có ít nhất một quy tắc giảm giá.');
                    return;
                }
                this.closest('.discount-rule-item').remove();
                renumberRules();
                // Nếu không còn rule nào và không phải discount, hiển thị thông báo
                const remainingRules = document.querySelectorAll('.discount-rule-item');
                if (remainingRules.length === 0 && !isDiscount) {
                    const container = document.getElementById('discount-rules-container');
                    if (container) {
                        container.insertAdjacentHTML('afterbegin', '<div class="alert alert-info"><i class="bi bi-info-circle"></i> Chưa có quy tắc giảm giá nào. Nhấn "Thêm quy tắc" để thêm quy tắc mới (tùy chọn).</div>');
                    }
                }
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
        // Hàm để cập nhật trạng thái enable/disable cho discount_rules fields
        function updateDiscountRulesRequired() {
            const category = categorySelect ? categorySelect.value : '';
            const discountRulesInputs = form.querySelectorAll('[name^="discount_rules"]');
            
            if (category !== 'promotion' && category !== 'discount') {
                // Nếu không phải promotion/discount, disable
                discountRulesInputs.forEach(input => {
                    input.disabled = true;
                });
            } else {
                // Nếu là promotion/discount, enable
                discountRulesInputs.forEach(input => {
                    input.disabled = false;
                });
            }
        }
        
        // Cập nhật khi category thay đổi
        if (categorySelect) {
            categorySelect.addEventListener('change', updateDiscountRulesRequired);
        }
        
        // Cập nhật ngay khi load
        updateDiscountRulesRequired();
        
        form.addEventListener('submit', function(e) {
            // Sync combo_ids từ requires_combo_ids trước khi submit
            syncComboIds();
            
            const category = categorySelect ? categorySelect.value : '';
            
            // Đảm bảo discount_rules fields được xử lý đúng trước khi submit
            updateDiscountRulesRequired();
            
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
                    const giftOnlyCheckbox = item.querySelector('.rule-gift-only');
                    const isGiftOnly = giftOnlyCheckbox && giftOnlyCheckbox.checked;
                    const requiresComboCheckbox = item.querySelector('.rule-requires-combo');
                    const requiresComboCheckboxes = item.querySelectorAll('.requires-combo-checkbox:checked');
                    
                    // Nếu có discount_percentage, thì phải có applies_to
                    // Nếu không có discount_percentage (chỉ tặng quà), thì không cần applies_to
                    if (discountSelect.value) {
                        // Có giảm giá, phải chọn applies_to
                        if (appliesToCheckboxes.length === 0) {
                            hasError = true;
                            alert('Quy tắc #' + (index + 1) + ': Vui lòng chọn ít nhất một phần để áp dụng giảm giá.');
                        }
                        discountSelect.classList.remove('is-invalid');
                    } else {
                        // Không có giảm giá (chỉ tặng quà), không cần applies_to
                        discountSelect.classList.remove('is-invalid');
                    }
                    
                    // Validate requires_combo: nếu chọn "Yêu cầu có combo" thì phải chọn ít nhất một combo
                    if (requiresComboCheckbox && requiresComboCheckbox.checked) {
                        if (requiresComboCheckboxes.length === 0) {
                            hasError = true;
                            alert('Quy tắc #' + (index + 1) + ': Vui lòng chọn ít nhất một combo khi chọn "Yêu cầu có combo".');
                            // Highlight combo select wrapper
                            const comboWrapper = item.querySelector('.requires-combo-select-wrapper');
                            if (comboWrapper) {
                                comboWrapper.style.border = '2px solid #dc3545';
                                comboWrapper.style.borderRadius = '4px';
                                comboWrapper.style.padding = '8px';
                            }
                        } else {
                            // Remove highlight nếu đã chọn combo
                            const comboWrapper = item.querySelector('.requires-combo-select-wrapper');
                            if (comboWrapper) {
                                comboWrapper.style.border = '';
                                comboWrapper.style.padding = '';
                            }
                        }
                    }
                }
                
                if (hasError) {
                    e.preventDefault();
                    return false;
                }
            }
            // Nếu category là 'event' hoặc 'movie', cho phép submit bình thường
        });
    }
    
    // Toggle hiển thị combo select khi chọn/bỏ chọn "Yêu cầu có combo"
    function toggleRequiresComboSelect(ruleIndex, checkbox) {
        // Đảm bảo ruleIndex là string
        ruleIndex = String(ruleIndex);
        console.log('toggleRequiresComboSelect called with ruleIndex:', ruleIndex, 'checked:', checkbox.checked);
        
        // Tìm wrapper bằng nhiều cách
        let requiresComboWrapper = null;
        
        // Cách 1: Tìm bằng ID
        requiresComboWrapper = document.getElementById(`requires-combo-select-wrapper-${ruleIndex}`);
        
        // Cách 2: Tìm trong cùng rule item
        if (!requiresComboWrapper) {
            const ruleItem = checkbox.closest('.discount-rule-item');
            if (ruleItem) {
                requiresComboWrapper = ruleItem.querySelector('.requires-combo-select-wrapper');
                console.log('Tìm thấy wrapper trong rule item:', !!requiresComboWrapper);
            }
        }
        
        // Cách 3: Tìm bằng class trong toàn bộ document (fallback)
        if (!requiresComboWrapper) {
            const allWrappers = document.querySelectorAll('.requires-combo-select-wrapper');
            // Tìm wrapper có ID chứa ruleIndex
            allWrappers.forEach(wrapper => {
                if (wrapper.id && wrapper.id.includes(ruleIndex)) {
                    requiresComboWrapper = wrapper;
                }
            });
        }
        
        if (requiresComboWrapper) {
            if (checkbox.checked) {
                // Xóa tất cả style inline và class ẩn để đảm bảo hiển thị
                requiresComboWrapper.removeAttribute('style');
                requiresComboWrapper.classList.remove('d-none');
                requiresComboWrapper.removeAttribute('hidden');
                // Set style mới với !important để override mọi style khác
                requiresComboWrapper.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                console.log('✓ Combo select hiển thị cho rule:', ruleIndex, 'Wrapper ID:', requiresComboWrapper.id);
            } else {
                requiresComboWrapper.setAttribute('style', 'display: none !important;');
                console.log('✗ Combo select ẩn cho rule:', ruleIndex);
            }
        } else {
            console.error('Không tìm thấy requires-combo-select-wrapper cho rule:', ruleIndex);
            // Debug: liệt kê tất cả wrappers có sẵn
            const allWrappers = document.querySelectorAll('.requires-combo-select-wrapper');
            console.log('Tất cả combo select wrappers:', allWrappers.length);
            allWrappers.forEach((wrapper, idx) => {
                console.log(`Wrapper ${idx}:`, wrapper.id, wrapper.className, wrapper.style.display);
            });
            // Debug: liệt kê tất cả rule items
            const allRuleItems = document.querySelectorAll('.discount-rule-item');
            console.log('Tất cả rule items:', allRuleItems.length);
            allRuleItems.forEach((item, idx) => {
                console.log(`Rule item ${idx}:`, item.getAttribute('data-index'), item.querySelector('.requires-combo-select-wrapper') ? 'có wrapper' : 'không có wrapper');
            });
        }
    }
    
    // Sync combo_ids từ requires_combo_ids khi submit form
    function syncComboIds() {
        document.querySelectorAll('.requires-combo-checkbox').forEach(checkbox => {
            const name = checkbox.name;
            const match = name.match(/discount_rules\[(\d+)\]/);
            if (!match) return;
            
            const ruleIndex = match[1];
            const comboId = checkbox.value;
            
            // Tìm hoặc tạo hidden input cho combo_ids
            let comboIdsInput = document.querySelector(`input[name="discount_rules[${ruleIndex}][combo_ids][]"][value="${comboId}"]`);
            if (!comboIdsInput) {
                comboIdsInput = document.createElement('input');
                comboIdsInput.type = 'hidden';
                comboIdsInput.name = `discount_rules[${ruleIndex}][combo_ids][]`;
                comboIdsInput.value = comboId;
                checkbox.closest('.requires-combo-select-wrapper').appendChild(comboIdsInput);
            }
            
            // Sync checked state - enable/disable hidden input
            if (checkbox.checked) {
                comboIdsInput.disabled = false;
            } else {
                comboIdsInput.disabled = true;
            }
        });
    }
    
    // Sử dụng event delegation cho tất cả "Yêu cầu có combo" checkboxes (bao gồm cả rule mới)
    if (discountRulesContainer) {
        discountRulesContainer.addEventListener('change', function(e) {
            // Xử lý checkbox "Yêu cầu có combo"
            if (e.target && e.target.classList.contains('rule-requires-combo')) {
                const checkbox = e.target;
                // Lấy ruleIndex từ data-rule-index hoặc từ parent element
                let ruleIndex = checkbox.getAttribute('data-rule-index');
                if (!ruleIndex && ruleIndex !== '0') {
                    const ruleItem = checkbox.closest('.discount-rule-item');
                    if (ruleItem) {
                        ruleIndex = ruleItem.getAttribute('data-index');
                    }
                }
                if (ruleIndex !== null && ruleIndex !== undefined) {
                    console.log('Event delegation: Toggle combo select for rule:', ruleIndex, 'checked:', checkbox.checked);
                    // Gọi ngay lập tức
                    toggleRequiresComboSelect(ruleIndex, checkbox);
                    // Gọi lại sau một chút để đảm bảo
                    setTimeout(() => {
                        toggleRequiresComboSelect(ruleIndex, checkbox);
                    }, 50);
                } else {
                    console.warn('Không tìm thấy ruleIndex cho checkbox requires-combo', checkbox);
                }
            }
            
            // Xử lý combo checkboxes để sync combo_ids
            if (e.target && e.target.classList.contains('requires-combo-checkbox')) {
                syncComboIds();
            }
        });
    }
    
    // Thêm event listener trực tiếp cho tất cả "Yêu cầu có combo" checkboxes hiện có
    function attachRequiresComboListeners() {
        const checkboxes = document.querySelectorAll('.rule-requires-combo');
        console.log('Tìm thấy', checkboxes.length, 'checkbox "Yêu cầu có combo"');
        
        checkboxes.forEach((checkbox, idx) => {
            // Kiểm tra xem đã có event listener chưa
            if (checkbox.hasAttribute('data-listener-attached')) {
                console.log('Checkbox', idx, 'đã có listener, bỏ qua');
                return; // Đã có listener rồi, bỏ qua
            }
            
            // Đánh dấu đã có listener
            checkbox.setAttribute('data-listener-attached', 'true');
            
            // Lấy ruleIndex
            let ruleIndex = checkbox.getAttribute('data-rule-index');
            if (!ruleIndex) {
                const ruleItem = checkbox.closest('.discount-rule-item');
                if (ruleItem) {
                    ruleIndex = ruleItem.getAttribute('data-index');
                }
            }
            console.log('Checkbox', idx, 'ruleIndex:', ruleIndex, 'checked:', checkbox.checked);
            
            // Thêm event listener trực tiếp
            checkbox.addEventListener('change', function() {
                let ruleIndex = this.getAttribute('data-rule-index');
                if (!ruleIndex && ruleIndex !== '0') {
                    const ruleItem = this.closest('.discount-rule-item');
                    if (ruleItem) {
                        ruleIndex = ruleItem.getAttribute('data-index');
                    }
                }
                console.log('Checkbox changed, ruleIndex:', ruleIndex, 'checked:', this.checked);
                if (ruleIndex !== null && ruleIndex !== undefined) {
                    // Gọi ngay lập tức
                    toggleRequiresComboSelect(ruleIndex, this);
                    // Gọi lại sau một chút để đảm bảo
                    setTimeout(() => {
                        toggleRequiresComboSelect(ruleIndex, this);
                    }, 50);
                } else {
                    console.error('Không tìm thấy ruleIndex cho checkbox', this);
                }
            });
            
            // Khởi tạo trạng thái ban đầu (cho cả checked và unchecked)
            if (ruleIndex !== null) {
                console.log('Initializing combo select for rule:', ruleIndex, 'checked:', checkbox.checked);
                // Đợi một chút để đảm bảo DOM đã render xong
                setTimeout(() => {
                    toggleRequiresComboSelect(ruleIndex, checkbox);
                }, 50);
            }
        });
    }
    
    // Hàm để khởi tạo hiển thị combo selection cho các checkbox đã checked
    function initializeComboSelections() {
        document.querySelectorAll('.rule-requires-combo').forEach(checkbox => {
            let ruleIndex = checkbox.getAttribute('data-rule-index');
            if (!ruleIndex && ruleIndex !== '0') {
                const ruleItem = checkbox.closest('.discount-rule-item');
                if (ruleItem) {
                    ruleIndex = ruleItem.getAttribute('data-index');
                }
            }
            if (ruleIndex !== null && ruleIndex !== undefined) {
                console.log('Initializing combo select for rule:', ruleIndex, 'checked:', checkbox.checked);
                toggleRequiresComboSelect(ruleIndex, checkbox);
            }
        });
    }
    
    // Gọi ngay khi DOM ready
    attachRequiresComboListeners();
    
    // Gọi lại sau 100ms để đảm bảo tất cả element đã được render
    setTimeout(() => {
        attachRequiresComboListeners();
        initializeComboSelections();
    }, 100);
    
    // Gọi lại sau 500ms để đảm bảo
    setTimeout(() => {
        attachRequiresComboListeners();
        initializeComboSelections();
    }, 500);
    
    // Gọi lại sau 1s để đảm bảo chắc chắn
    setTimeout(() => {
        initializeComboSelections();
    }, 1000);
    
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
