@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Quản lý User</h1>
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
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="Tên, email hoặc số điện thoại...">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Vai trò</label>
                    <select name="role" id="role" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="city" class="form-label">Thành phố</label>
                    <select name="city" id="city" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort_by" class="form-label">Sắp xếp</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Tên</option>
                        <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Tuổi</th>
                                <th>Thành phố</th>
                                <th>Ngôn ngữ</th>
                                <th>Vai trò</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td>{{ $user->age ?? '-' }}</td>
                                    <td>{{ $user->preferred_city ?? '-' }}</td>
                                    <td>{{ $user->preferred_language ?? '-' }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-primary">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $user->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Không tìm thấy user nào.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

