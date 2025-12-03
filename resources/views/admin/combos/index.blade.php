@extends('layouts.admin')

@section('title', 'Combos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Combos</h1>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" action="{{ route('admin.combos.index') }}" class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Search by combo name..." 
                           value="{{ request('search') }}"
                           style="width: 250px; padding-right: 35px;">
                    <i class="bi bi-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                </div>
                @if(request('search'))
                <a href="{{ route('admin.combos.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </form>
            <a href="{{ route('admin.combos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Combo
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
            No combos available. Please add new combos to display.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">No.</th>
                        <th style="width: 150px;">Image</th>
                        <th>Combo Name</th>
                        <th>Description</th>
                        <th style="width: 120px;">Price</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 220px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="sortable-combos">
                    @php
                        $currentPage = $combos->currentPage();
                        $perPage = $combos->perPage();
                        $startNumber = ($currentPage - 1) * $perPage;
                    @endphp
                    @foreach($combos as $combo)
                        <tr data-id="{{ $combo->id }}" class="sortable-row {{ $combo->is_hidden ? 'text-muted' : '' }}" style="cursor: move;">
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
                                @if($combo->is_hidden)
                                    <span class="badge bg-secondary ms-2">Hidden</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-muted small">
                                    {{ Str::limit($combo->description ?? 'No description', 80) }}
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary">{{ format_currency($combo->price) }}</strong>
                            </td>
                            <td>
                                @if($combo->is_hidden)
                                    <span class="badge bg-warning">Hidden</span>
                                @elseif($combo->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.combos.show', $combo) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($combo->hasBookings())
                                    {{-- Nếu có bookings, chỉ cho phép ẩn --}}
                                    <form action="{{ route('admin.combos.toggleHidden', $combo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $combo->is_hidden ? 'btn-success' : 'btn-warning' }}" 
                                                onclick="return confirm('Are you sure you want to {{ $combo->is_hidden ? 'show' : 'hide' }} this combo?')" 
                                                title="{{ $combo->is_hidden ? 'Show Combo' : 'Hide Combo' }}">
                                            <i class="bi {{ $combo->is_hidden ? 'bi-eye' : 'bi-eye-slash' }}"></i>
                                        </button>
                                    </form>
                                @else
                                    {{-- Nếu chưa có bookings, cho phép xóa --}}
                                    <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this combo? This action cannot be undone.')" 
                                                title="Delete Combo">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
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
            // Only show alert if there's a real error
            if (error.message) {
                alert('An error occurred while updating order: ' + error.message);
            } else {
                alert('An error occurred while updating order. Please try again.');
            }
        });
    }
});
</script>
@endpush
@endsection

