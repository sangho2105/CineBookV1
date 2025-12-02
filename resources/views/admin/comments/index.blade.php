@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Quản lý Bình luận</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.comments.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="movie_id" class="form-label">Lọc theo phim</label>
                    <select name="movie_id" id="movie_id" class="form-select">
                        <option value="">-- Tất cả phim --</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}" {{ request('movie_id') == $movie->id ? 'selected' : '' }}>
                                {{ $movie->title }} 
                                ({{ $movie->status == 'now_showing' ? 'Đang chiếu' : 'Sắp chiếu' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm theo tên/email user</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="Nhập tên hoặc email...">
                </div>
                <div class="col-md-4">
                    <label for="content_search" class="form-label">Tìm theo nội dung</label>
                    <input type="text" name="content_search" id="content_search" class="form-control" 
                           value="{{ request('content_search') }}" placeholder="Nhập nội dung...">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Comments Table --}}
    <div class="card">
        <div class="card-body">
            @if($comments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Phim</th>
                                <th>Nội dung</th>
                                <th>Ngày đăng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr>
                                    <td>{{ $comment->id }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $comment->user->name ?? 'N/A' }}</strong>
                                        </div>
                                        <small class="text-muted">{{ $comment->user->email ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $comment->movie->title ?? 'N/A' }}</strong>
                                        @if($comment->movie)
                                            <br>
                                            <small class="text-muted">
                                                {{ $comment->movie->status == 'now_showing' ? 'Đang chiếu' : 'Sắp chiếu' }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; word-wrap: break-word;">
                                            {{ \Illuminate\Support\Str::limit($comment->content, 100) }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.comments.destroy', $comment) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $comments->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Không tìm thấy bình luận nào.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

