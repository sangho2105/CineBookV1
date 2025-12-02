@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Khuyến mãi &amp; Sự kiện</h1>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" action="{{ route('admin.promotions.index') }}" class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Tìm kiếm theo tên khuyến mãi..." 
                           value="{{ request('search') }}"
                           style="width: 250px; padding-right: 35px;">
                    <i class="bi bi-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </form>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#manageRulesModal">
                <i class="bi bi-gear"></i> Quản lý quy tắc
            </button>
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($promotions->isEmpty())
        <div class="alert alert-info">
            Chưa có khuyến mãi nào. Hãy thêm mới để hiển thị trên trang chủ.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Loại</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody id="sortable-promotions">
                    @php
                        $currentPage = $promotions->currentPage();
                        $perPage = $promotions->perPage();
                        $startNumber = ($currentPage - 1) * $perPage;
                    @endphp
                    @foreach($promotions as $index => $promotion)
                        <tr data-id="{{ $promotion->id }}" class="sortable-row" style="cursor: move;">
                            <td class="text-center">
                                <span class="text-muted fw-bold">{{ $startNumber + $loop->iteration }}</span>
                            </td>
                            <td style="width: 120px">
                                <img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}" class="img-fluid rounded">
                            </td>
                            <td>
                                <strong>{{ $promotion->title }}</strong>
                                <div class="text-muted small">{{ Str::limit($promotion->description, 80) }}</div>
                                @if($promotion->category === 'movie' && $promotion->movie)
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-film"></i> Liên kết: {{ $promotion->movie->title }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $promotion->category_label }}</td>
                            <td>
                                {{ $promotion->start_date->format('d/m/Y') }}
                                @if($promotion->end_date)
                                    &ndash; {{ $promotion->end_date->format('d/m/Y') }}
                                @else
                                    <span class="badge bg-secondary">Không giới hạn</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $promotion->status_badge_class }}">{{ $promotion->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-warning" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @php
                                    $isCurrentlyActive = $promotion->isCurrentlyActive();
                                @endphp
                                <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('{{ $isCurrentlyActive ? 'Sự kiện đang trong thời gian hoạt động. Bạn có chắc chắn muốn ẩn sự kiện này?' : 'Bạn chắc chắn muốn xóa khuyến mãi này?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm {{ $isCurrentlyActive ? 'btn-secondary' : 'btn-danger' }}" 
                                            title="{{ $isCurrentlyActive ? 'Ẩn sự kiện' : 'Xóa khuyến mãi' }}">
                                        <i class="bi {{ $isCurrentlyActive ? 'bi-eye-slash' : 'bi-trash' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $promotions->links() }}
        </div>
    @endif
</div>

<!-- Modal Quản lý quy tắc -->
<div class="modal fade" id="manageRulesModal" tabindex="-1" aria-labelledby="manageRulesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageRulesModalLabel">Quản lý quy tắc áp dụng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle"></i> Chọn các khuyến mãi/ưu đãi được áp dụng <strong>chung</strong> (có thể kết hợp với nhau) hoặc <strong>riêng</strong> (chỉ áp dụng một quy tắc).
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th>Tiêu đề</th>
                                <th>Loại</th>
                                <th class="text-center" style="width: 150px;">Áp dụng</th>
                            </tr>
                        </thead>
                        <tbody id="rulesTableBody">
                            @php
                                $allPromotions = \App\Models\Promotion::whereIn('category', ['promotion', 'discount'])
                                    ->orderBy('sort_order')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            @endphp
                            @foreach($allPromotions as $idx => $promo)
                                <tr data-promotion-id="{{ $promo->id }}">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $promo->title }}</strong>
                                        @if($promo->discount_rules)
                                            @php
                                                $discountRules = $promo->discount_rules;
                                                $rule = is_array($discountRules) && !empty($discountRules) ? reset($discountRules) : [];
                                                $hasDiscount = !empty($rule['discount_percentage'] ?? null);
                                                $isGiftOnly = !empty($rule['gift_only'] ?? false);
                                            @endphp
                                            <div class="text-muted small mt-1">
                                                @if($hasDiscount)
                                                    <span class="badge bg-primary">Giảm {{ $rule['discount_percentage'] }}%</span>
                                                @endif
                                                @if($isGiftOnly)
                                                    <span class="badge bg-success">Tặng quà</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $promo->category_label }}</td>
                                    <td class="text-center">
                                        <select class="form-select form-select-sm apply-type-select" 
                                                data-promotion-id="{{ $promo->id }}"
                                                name="apply_type[{{ $promo->id }}]">
                                            <option value="shared" {{ ($promo->apply_type ?? 'shared') === 'shared' ? 'selected' : '' }}>
                                                Chung
                                            </option>
                                            <option value="exclusive" {{ ($promo->apply_type ?? 'shared') === 'exclusive' ? 'selected' : '' }}>
                                                Riêng
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="saveRulesBtn">
                    <i class="bi bi-save"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .sortable-row {
        transition: background-color 0.2s;
    }
    .sortable-row:hover {
        background-color: #f8f9fa;
    }
    .sortable-row.dragging {
        opacity: 0.5;
        background-color: #e9ecef;
    }
    .sortable-row.drag-over {
        border-top: 2px solid #007bff;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('sortable-promotions');
    if (!tbody) return;
    
    let draggedElement = null;
    let draggedOverElement = null;
    
    // Làm cho các hàng có thể kéo được
    const rows = tbody.querySelectorAll('.sortable-row');
    rows.forEach(row => {
        row.draggable = true;
        
        row.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        });
        
        row.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            rows.forEach(r => r.classList.remove('drag-over'));
        });
        
        row.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            if (this !== draggedElement && this !== draggedOverElement) {
                rows.forEach(r => r.classList.remove('drag-over'));
                this.classList.add('drag-over');
                draggedOverElement = this;
            }
            return false;
        });
        
        row.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        row.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            
            if (draggedElement !== this) {
                const allRows = Array.from(tbody.querySelectorAll('.sortable-row'));
                const draggedIndex = allRows.indexOf(draggedElement);
                const targetIndex = allRows.indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    tbody.insertBefore(draggedElement, this.nextSibling);
                } else {
                    tbody.insertBefore(draggedElement, this);
                }
                
                // Cập nhật thứ tự trên server
                updateOrder();
            }
            
            this.classList.remove('drag-over');
            return false;
        });
    });
    
    function updateOrder() {
        const rows = tbody.querySelectorAll('.sortable-row');
        const order = Array.from(rows).map(row => row.getAttribute('data-id'));
        
        fetch('{{ route("admin.promotions.update-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Thứ tự đã được cập nhật thành công, không cần thông báo
                return;
            } else {
                throw new Error(data.message || 'Cập nhật thất bại');
            }
        })
        .catch(error => {
            console.error('Lỗi cập nhật thứ tự:', error);
            // Chỉ hiển thị alert nếu có lỗi thực sự
            if (error.message) {
                alert('Có lỗi xảy ra khi cập nhật thứ tự: ' + error.message);
            } else {
                alert('Có lỗi xảy ra khi cập nhật thứ tự. Vui lòng thử lại.');
            }
        });
    }
    
    // Xử lý lưu quy tắc áp dụng
    const saveRulesBtn = document.getElementById('saveRulesBtn');
    if (saveRulesBtn) {
        saveRulesBtn.addEventListener('click', function() {
            const selects = document.querySelectorAll('.apply-type-select');
            const data = {};
            
            selects.forEach(select => {
                const promotionId = select.getAttribute('data-promotion-id');
                const applyType = select.value;
                data[promotionId] = applyType;
            });
            
            // Gửi request lưu
            fetch('{{ route("admin.promotions.save-rules") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ rules: data })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Đã lưu quy tắc áp dụng thành công!');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('manageRulesModal'));
                    modal.hide();
                } else {
                    alert('Có lỗi xảy ra: ' + (result.message || 'Vui lòng thử lại.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi lưu quy tắc. Vui lòng thử lại.');
            });
        });
    }
});
</script>
@endpush
@endsection

