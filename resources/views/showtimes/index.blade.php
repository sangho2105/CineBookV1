@extends('layouts.admin')

@section('title', 'List Showtimes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Quản lý Suất chiếu</h1>
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" action="{{ route('admin.showtimes.index') }}" class="d-flex gap-2 align-items-center">
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
            <a href="{{ route('admin.showtimes.index') }}" class="btn btn-sm btn-outline-secondary" title="Xóa bộ lọc">
                <i class="bi bi-x-circle"></i>
            </a>
            @endif
        </form>
        <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary">Thêm Suất chiếu</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 80px;">STT</th>
                <th>Movie</th>
                <th>Phòng chiếu</th>
                <th>Date</th>
                <th>Show Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($showtimes as $showtime)
            <tr>
                <td>{{ ($showtimes->currentPage() - 1) * $showtimes->perPage() + $loop->iteration }}</td>
                <td>{{ $showtime->movie->title }}</td>
                <td>{{ $showtime->room ? $showtime->room->name . ' (' . $showtime->room->total_seats . ' ghế)' : ($showtime->theater ? $showtime->theater->name : 'N/A') }}</td>
                <td>{{ $showtime->show_date->format('d/m/Y') }}</td>
                <td>{{ $showtime->getFormattedShowTime('H:i') }}</td>
                <td>
                    <a href="{{ route('admin.showtimes.show', $showtime) }}" class="btn btn-sm btn-info" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('admin.showtimes.destroy', $showtime) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete?')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No showtimes found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Phân trang --}}
<div class="mt-4">
    {{ $showtimes->links() }}
</div>
</div>
@endsection