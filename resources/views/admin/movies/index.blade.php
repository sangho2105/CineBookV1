{{-- Giả sử bạn có layout admin --}}
@extends('layouts.admin') 

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="mb-0">Quản lý Phim</h1>
            <div class="d-flex gap-2 align-items-center flex-shrink-0">
                <form method="GET" action="{{ route('admin.movies.index') }}" class="d-flex gap-2 align-items-center">
                    <div class="position-relative">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Tìm kiếm theo tên phim..." 
                               value="{{ request('search') }}"
                               style="width: 250px; padding-right: 35px;">
                        <i class="bi bi-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                    </div>
                    @if(request('search'))
                    <a href="{{ route('admin.movies.index') }}" class="btn btn-sm btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </form>
                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">Thêm Phim Mới</a>
            </div>
        </div>
    </div>

    {{-- Hiển thị thông báo thành công (nếu có) --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 80px;">STT</th>
                <th>Tên Phim</th>
                <th style="width: 150px;">Ngày phát hành</th>
                <th style="width: 180px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movies as $movie)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $movie->title }}</td>
                    <td>{{ $movie->release_date }}</td>
                    <td>
                        <a href="{{ route('admin.movies.show', $movie->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-sm btn-warning" title="Sửa">
                            <i class="bi bi-pencil"></i>
                        </a>
                        
                        {{-- Nút Xóa cần một form --}}
                        <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection