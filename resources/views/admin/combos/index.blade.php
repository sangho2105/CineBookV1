@extends('layouts.admin')

@section('title', 'Quản lý Combo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Quản lý Combo</h1>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" action="{{ route('admin.combos.index') }}" class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Tìm kiếm theo tên combo..." 
                           value="{{ request('search') }}"
                           style="width: 250px; padding-right: 35px;">
                    <i class="bi bi-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('admin.combos.index') }}" class="btn btn-sm btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </form>
            <a href="{{ route('admin.combos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm Combo Mới
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($combos->isEmpty())
        <div class="alert alert-info">
            Chưa có combo nào. Hãy thêm combo mới để hiển thị.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">STT</th>
                        <th style="width: 150px;">Ảnh</th>
                        <th>Tên Combo</th>
                        <th>Mô tả</th>
                        <th style="width: 120px;">Giá</th>
                        <th style="width: 120px;">Trạng thái</th>
                        <th style="width: 220px;" class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody id="sortable-combos">
                    @php
                        $currentPage = $combos->currentPage();
                        $perPage = $combos->perPage();
                        $startNumber = ($currentPage - 1) * $perPage;
                    @endphp
                    @foreach($combos as $combo)
                        <tr data-id="{{ $combo->id }}" class="sortable-row" style="cursor: move;">
                            <td class="text-center">
                                <span class="text-muted fw-bold">{{ $startNumber + $loop->iteration }}</span>
                            </td>
                            <td>
                                @if($combo->image_path)
                                    <img src="{{ $combo->image_url }}" alt="{{ $combo->name }}" 
                                         class="img-fluid rounded" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                         style="width: 100px; height: 100px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $combo->name }}</strong>
                            </td>
                            <td>
                                <div class="text-muted small">
                                    {{ Str::limit($combo->description ?? 'Không có mô tả', 80) }}
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary">{{ format_currency($combo->price) }}</strong>
                            </td>
                            <td>
                                @if($combo->is_hidden)
                                    <span class="badge bg-warning">Đã ẩn</span>
                                @elseif($combo->is_active)
                                    <span class="badge bg-success">Đang hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.combos.show', $combo) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-sm btn-warning" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('{{ $combo->hasBookings() ? 'Combo này đã có khách hàng đặt. Không thể xóa, chỉ có thể ẩn. Bạn có chắc chắn muốn ẩn combo này?' : 'Combo này chưa được đặt hàng và có thể xóa. Bạn chắc chắn muốn xóa combo này?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ $combo->hasBookings() ? 'Ẩn combo' : 'Xóa combo' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Phân trang --}}
        <div class="mt-4">
            {{ $combos->links() }}
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
    const tbody = document.getElementById('sortable-combos');
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
        
        fetch('{{ route("admin.combos.update-order") }}', {
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
});
</script>
@endpush
@endsection

