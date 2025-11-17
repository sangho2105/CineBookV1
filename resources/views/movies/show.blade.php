@extends('layouts.app')

@section('title', $movie->title . ' - CineBook')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $movie->title }}</h4>
                <div>
                    <a href="{{ route('movies.edit', $movie) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('movies.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($movie->poster)
                            <img src="{{ $movie->poster }}" alt="{{ $movie->title }}" class="img-fluid rounded mb-3">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                <span class="text-muted">No poster available</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Genre:</th>
                                <td>{{ $movie->genre }}</td>
                            </tr>
                            <tr>
                                <th>Language:</th>
                                <td>{{ $movie->language }}</td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td>{{ $movie->duration }} minutes</td>
                            </tr>
                            <tr>
                                <th>Release Date:</th>
                                <td>{{ $movie->release_date->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $movie->status == 'now_showing' ? 'success' : ($movie->status == 'upcoming' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $movie->status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Rating:</th>
                                <td>{{ number_format($movie->rating_average, 1) }}/5.0</td>
                            </tr>
                            @if($movie->trailer_link)
                            <tr>
                                <th>Trailer:</th>
                                <td>
                                    <a href="{{ $movie->trailer_link }}" target="_blank" class="btn btn-sm btn-danger">
                                        Watch Trailer
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </table>
                        
                        @if($movie->synopsis)
                        <div class="mt-3">
                            <h5>Synopsis</h5>
                            <p class="text-muted">{{ $movie->synopsis }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection