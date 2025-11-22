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
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Loại</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promotions as $promotion)
                        <tr>
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
                                @if($promotion->is_active)
                                    <span class="badge bg-success">Đang kích hoạt</span>
                                @else
                                    <span class="badge bg-secondary">Đã tắt</span>
                                @endif
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
@endsection

