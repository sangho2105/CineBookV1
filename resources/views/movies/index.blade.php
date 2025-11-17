@extends('layouts.app')

@section('title', 'Movies - CineBook')

@section('content')
<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <h2>Movies Management</h2>
        <a href="{{ route('movies.create') }}" class="btn btn-primary">Add New Movie</a>
    </div>
</div>

@if($movies->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Genre</th>
                    <th>Language</th>
                    <th>Duration</th>
                    <th>Release Date</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movies as $movie)
                <tr>
                    <td>{{ $movie->id }}</td>
                    <td><strong>{{ $movie->title }}</strong></td>
                    <td>{{ $movie->genre }}</td>
                    <td>{{ $movie->language }}</td>
                    <td>{{ $movie->duration }} min</td>
                    <td>{{ $movie->release_date->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge bg-{{ $movie->status == 'now_showing' ? 'success' : ($movie->status == 'upcoming' ? 'warning' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $movie->status)) }}
                        </span>
                    </td>
                    <td>{{ number_format($movie->rating_average, 1) }}/5.0</td>
                    <td>
                        <a href="{{ route('movies.show', $movie) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('movies.edit', $movie) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('movies.destroy', $movie) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this movie?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info">
        No movies found. <a href="{{ route('movies.create') }}">Create your first movie!</a>
    </div>
@endif
@endsection