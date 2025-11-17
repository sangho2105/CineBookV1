{{-- Giả sử bạn có layout admin --}}
@extends('layouts.admin') 

@section('content')
<div class="container">
    <h1>Quản lý Phim</h1>
    <a href="{{ route('admin.movies.create') }}" class="btn btn-primary mb-3">Thêm Phim Mới</a>

    {{-- Hiển thị thông báo thành công (nếu có) --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Phim</th>
                <th>Ngày phát hành</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movies as $movie)
                <tr>
                    <td>{{ $movie->id }}</td>
                    <td>{{ $movie->title }}</td>
                    <td>{{ $movie->release_date }}</td>
                    <td>
                        <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                        
                        {{-- Nút Xóa cần một form --}}
                        <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection