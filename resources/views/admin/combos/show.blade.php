@extends('layouts.admin')

@section('title', 'Chi tiết Combo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Chi tiết Combo</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.combos.edit', $combo) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Sửa
            </a>
            <a href="{{ route('admin.combos.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">ID:</dt>
                <dd class="col-sm-9">{{ $combo->id }}</dd>

                <dt class="col-sm-3">Tên Combo:</dt>
                <dd class="col-sm-9"><strong>{{ $combo->title }}</strong></dd>

                <dt class="col-sm-3">Mô tả / Chi tiết:</dt>
                <dd class="col-sm-9">{{ $combo->description ?? 'Không có mô tả' }}</dd>

                <dt class="col-sm-3">Ảnh:</dt>
                <dd class="col-sm-9">
                    @if($combo->image_path)
                        <img src="{{ $combo->image_url }}" alt="{{ $combo->title }}" 
                             class="img-fluid rounded" style="max-height: 300px;">
                    @else
                        <span class="text-muted">Chưa có ảnh</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Giá:</dt>
                <dd class="col-sm-9">
                    <strong class="text-primary fs-5">{{ format_currency($combo->price) }}</strong>
                </dd>

                <dt class="col-sm-3">Trạng thái:</dt>
                <dd class="col-sm-9">
                    @if($combo->is_active)
                        <span class="badge bg-success">Đang hoạt động</span>
                    @else
                        <span class="badge bg-secondary">Tạm ngưng</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Ngày tạo:</dt>
                <dd class="col-sm-9">{{ $combo->created_at->format('d/m/Y H:i:s') }}</dd>

                <dt class="col-sm-3">Ngày cập nhật:</dt>
                <dd class="col-sm-9">{{ $combo->updated_at->format('d/m/Y H:i:s') }}</dd>
            </dl>

            <div class="mt-4">
                <form action="{{ route('admin.combos.destroy', $combo) }}" method="POST" 
                      onsubmit="return confirm('Bạn chắc chắn muốn xóa combo này?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Xóa Combo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

