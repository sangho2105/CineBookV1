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
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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
                                <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa khuyến mãi này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                        <i class="bi bi-trash"></i>
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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Có thể hiển thị thông báo thành công nếu muốn
                console.log('Thứ tự đã được cập nhật');
            }
        })
        .catch(error => {
            console.error('Lỗi cập nhật thứ tự:', error);
            alert('Có lỗi xảy ra khi cập nhật thứ tự. Vui lòng thử lại.');
        });
    }
});
</script>
@endpush
@endsection

