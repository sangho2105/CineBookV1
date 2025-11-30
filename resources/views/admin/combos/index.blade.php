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
        <div class="alert alert-success">{{ session('success') }}</div>
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
                        <th style="width: 100px;">Trạng thái</th>
                        <th style="width: 180px;" class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($combos as $combo)
                        <tr>
                            <td>{{ ($combos->currentPage() - 1) * $combos->perPage() + $loop->iteration }}</td>
                            <td>
                                @if($combo->image_path)
                                    <img src="{{ $combo->image_url }}" alt="{{ $combo->title }}" 
                                         class="img-fluid rounded" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                         style="width: 100px; height: 100px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $combo->title }}</strong>
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
                                @if($combo->is_active)
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
                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa combo này?');">
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
        
        {{-- Phân trang --}}
        <div class="mt-4">
            {{ $combos->links() }}
        </div>
    @endif
</div>
@endsection

