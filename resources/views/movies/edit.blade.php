@extends('layouts.app')

@section('title', 'Edit Movie - CineBook')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Movie: {{ $movie->title }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('movies.update', $movie) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $movie->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="genre" class="form-label">Genre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('genre') is-invalid @enderror" 
                               id="genre" name="genre" value="{{ old('genre', $movie->genre) }}" required>
                        @error('genre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('language') is-invalid @enderror" 
                               id="language" name="language" value="{{ old('language', $movie->language) }}" required>
                        @error('language')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                               id="duration" name="duration" value="{{ old('duration', $movie->duration) }}" min="1" required>
                        @error('duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="release_date" class="form-label">Release Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('release_date') is-invalid @enderror" 
                               id="release_date" name="release_date" value="{{ old('release_date', $movie->release_date->format('Y-m-d')) }}" required>
                        @error('release_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="">-- Select Status --</option>
                            <option value="upcoming" {{ old('status', $movie->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="now_showing" {{ old('status', $movie->status) == 'now_showing' ? 'selected' : '' }}>Now Showing</option>
                            <option value="ended" {{ old('status', $movie->status) == 'ended' ? 'selected' : '' }}>Ended</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="poster" class="form-label">Poster URL</label>
                        <input type="text" class="form-control @error('poster') is-invalid @enderror" 
                               id="poster" name="poster" value="{{ old('poster', $movie->poster) }}" placeholder="https://example.com/poster.jpg">
                        @error('poster')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="trailer_link" class="form-label">Trailer Link</label>
                        <input type="url" class="form-control @error('trailer_link') is-invalid @enderror" 
                               id="trailer_link" name="trailer_link" value="{{ old('trailer_link', $movie->trailer_link) }}" placeholder="https://youtube.com/watch?v=...">
                        @error('trailer_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="synopsis" class="form-label">Synopsis</label>
                        <textarea class="form-control @error('synopsis') is-invalid @enderror" 
                                  id="synopsis" name="synopsis" rows="4">{{ old('synopsis', $movie->synopsis) }}</textarea>
                        @error('synopsis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="rating_average" class="form-label">Rating Average (0-5)</label>
                        <input type="number" step="0.01" class="form-control @error('rating_average') is-invalid @enderror" 
                               id="rating_average" name="rating_average" value="{{ old('rating_average', $movie->rating_average) }}" min="0" max="5">
                        @error('rating_average')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('movies.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection