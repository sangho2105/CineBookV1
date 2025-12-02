{{-- Giả sử bạn có layout admin --}}
@extends('layouts.admin') 

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="mb-0">Movies</h1>
            <div class="d-flex gap-2 align-items-center flex-shrink-0">
                <form method="GET" action="{{ route('admin.movies.index') }}" class="d-flex gap-2 align-items-center">
                    <div class="position-relative">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Search by movie name..." 
                               value="{{ request('search') }}"
                               style="width: 250px; padding-right: 35px;">
                        <i class="bi bi-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                    </div>
                    @if(request('search'))
                    <a href="{{ route('admin.movies.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </form>
                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">Add New Movie</a>
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
                <th style="width: 80px;">No.</th>
                <th>Movie Title</th>
                <th style="width: 150px;">Release Date</th>
                <th style="width: 180px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movies as $movie)
                <tr class="{{ $movie->is_hidden ? 'table-secondary opacity-75' : '' }}">
                    <td>{{ ($movies->currentPage() - 1) * $movies->perPage() + $loop->iteration }}</td>
                    <td>
                        {{ $movie->title }}
                        @if($movie->is_hidden)
                            <span class="badge bg-secondary ms-2">Hidden</span>
                        @endif
                    </td>
                    <td>{{ $movie->release_date }}</td>
                    <td>
                        <a href="{{ route('admin.movies.show', $movie->id) }}" class="btn btn-sm btn-info" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        
                        {{-- Hide/Show Movie Button --}}
                        <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-sm {{ $movie->is_hidden ? 'btn-success' : 'btn-warning' }}" 
                                    onclick="return confirm('{{ $movie->is_hidden ? 'Are you sure you want to show this movie?' : 'Are you sure you want to hide this movie?' }}')" 
                                    title="{{ $movie->is_hidden ? 'Show Movie' : 'Hide Movie' }}">
                                <i class="bi {{ $movie->is_hidden ? 'bi-eye' : 'bi-eye-slash' }}"></i>
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
        {{ $movies->links() }}
    </div>
</div>
@endsection