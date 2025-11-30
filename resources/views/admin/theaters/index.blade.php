@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h2>Quản lý Rạp chiếu</h2>
            <a href="{{ route('admin.theaters.create') }}" class="btn btn-primary">Thêm Rạp mới</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($theaters->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Rạp</th>
                        <th>Thành phố</th>
                        <th>Địa chỉ</th>
                        <th>Sức chứa</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($theaters as $theater)
                    <tr>
                        <td>{{ $theater->id }}</td>
                        <td><strong>{{ $theater->name }}</strong></td>
                        <td>{{ $theater->city }}</td>
                        <td>{{ $theater->address }}</td>
                        <td>{{ number_format($theater->seating_capacity) }} ghế</td>
                        <td>
                            <a href="{{ route('admin.theaters.show', $theater) }}" class="btn btn-sm btn-info">Xem</a>
                            <a href="{{ route('admin.theaters.edit', $theater) }}" class="btn btn-sm btn-warning">Sửa</a>
                            <form action="{{ route('admin.theaters.destroy', $theater) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa rạp này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Phân trang --}}
        <div class="mt-4">
            {{ $theaters->links() }}
        </div>
    @else
        <div class="alert alert-info">
            Chưa có rạp chiếu nào. <a href="{{ route('admin.theaters.create') }}">Tạo rạp đầu tiên!</a>
        </div>
    @endif
</div>
@endsection
