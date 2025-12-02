@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Movie Details</h1>
        <div>
            <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>

    <div class="row">
        {{-- Cột Poster --}}
        <div class="col-md-4 mb-4">
            @if($movie->poster_image_url)
                <img src="{{ $movie->poster_image_url }}" 
                     class="img-fluid rounded shadow-sm" 
                     alt="{{ $movie->title }}"
                     style="max-height: 600px; width: 100%; object-fit: cover;">
            @else
                <div class="bg-light rounded shadow-sm d-flex align-items-center justify-content-center" style="height: 600px;">
                    <p class="text-muted">No poster available</p>
                </div>
            @endif
        </div>

        {{-- Cột Thông tin --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">{{ $movie->title }}</h2>
                    
                    {{-- Thông tin cơ bản --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Genre:</strong> 
                                <span class="badge bg-primary">{{ $movie->genre }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Language:</strong> 
                                <span class="badge bg-secondary">{{ $movie->language }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Duration:</strong> 
                                <span class="badge bg-info text-dark">{{ $movie->duration_minutes }} minutes</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Rated:</strong> 
                                @if($movie->rated)
                                    <span class="badge bg-secondary">{{ $movie->rated }}</span>
                                @else
                                    <span class="text-muted">Not updated</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Release Date:</strong> 
                                {{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Status:</strong> 
                                @if($movie->status === 'now_showing')
                                    <span class="badge bg-success">Now Showing</span>
                                @elseif($movie->status === 'upcoming')
                                    <span class="badge bg-warning text-dark">Coming Soon</span>
                                @else
                                    <span class="badge bg-secondary">Ended</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    {{-- Thông tin chi tiết --}}
                    <div class="mb-3">
                        <p class="mb-2">
                            <strong>Director:</strong> 
                            {{ $movie->director ?? 'Not updated' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2">
                            <strong>Cast:</strong> 
                            {{ $movie->cast ?? 'Not updated' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2">
                            <strong>Trailer URL:</strong> 
                            @if($movie->trailer_url)
                                <a href="{{ $movie->trailer_url }}" target="_blank" class="text-primary">{{ $movie->trailer_url }}</a>
                            @else
                                <span class="text-muted">Not available</span>
                            @endif
                        </p>
                    </div>

                    <hr>

                    {{-- Synopsis --}}
                    <div class="mb-3">
                        <h5 class="mb-2">Synopsis</h5>
                        <p class="text-muted">
                            {{ $movie->synopsis ?? 'No synopsis available.' }}
                        </p>
                    </div>

                    {{-- Thông tin hệ thống --}}
                    <hr>
                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>ID:</strong> {{ $movie->id }}</p>
                            <p class="mb-1"><strong>Created At:</strong> {{ $movie->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Last Updated:</strong> {{ $movie->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trailer Video Embed (nếu có) --}}
            @if($movie->trailer_url)
                @php
                    $url = $movie->trailer_url;
                    $embedUrl = null;
                    $isMp4 = false;
                    if ($url) {
                        if (preg_match('/v=([A-Za-z0-9_\\-]+)/', $url, $matches)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                        } elseif (preg_match('#youtu\\.be/([A-Za-z0-9_\\-]+)#', $url, $matches)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                        } elseif (\Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($url), '.mp4')) {
                            $isMp4 = true;
                        }
                    }
                @endphp
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Trailer</h5>
                    </div>
                    <div class="card-body">
                        @if($embedUrl)
                            <div class="ratio ratio-16x9">
                                <iframe 
                                    src="{{ $embedUrl }}" 
                                    title="Trailer {{ $movie->title }}" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @elseif($isMp4)
                            <video class="w-100 rounded shadow-sm" controls preload="metadata">
                                <source src="{{ $url }}" type="video/mp4">
                                Your browser does not support video playback.
                            </video>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

