@extends('layouts.app')

@section('title', request('status') == 'now_showing' ? 'Now Showing Movies - CineBook' : (request('status') == 'upcoming' ? 'Coming Soon Movies - CineBook' : 'Movie List - CineBook'))

@push('css')
<link rel="stylesheet" href="{{ asset('css/search.css') }}">
@endpush

@section('content')
<div class="movies-listing-page">
    {{-- Top Border --}}
    <div class="top-dotted-border"></div>
    
    {{-- Breadcrumb --}}
    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">
                            <i class="bi bi-house-door"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('search') }}">Movies</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @if(request('status') == 'now_showing')
                            Now Showing
                        @elseif(request('status') == 'upcoming')
                            Coming Soon
                        @else
                            Movie List
                        @endif
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        {{-- Page Title --}}
        <div class="page-header mb-4">
            <div class="d-flex align-items-start justify-content-between position-relative">
                <div class="flex-grow-1">
                    <h1 class="page-title mb-0">
                        @if(request('status') == 'now_showing')
                            Now Showing
                        @elseif(request('status') == 'upcoming')
                            Coming Soon
                        @else
                            Movie List
                        @endif
                    </h1>
                </div>
                <div class="ms-4">
                    @if(request('status') == 'now_showing')
                        <a href="{{ route('search', ['status' => 'upcoming']) }}" class="text-decoration-none text-dark">
                            <h2 class="page-title-secondary mb-0">COMING SOON</h2>
                        </a>
                    @elseif(request('status') == 'upcoming')
                        <a href="{{ route('search', ['status' => 'now_showing']) }}" class="text-decoration-none text-dark">
                            <h2 class="page-title-secondary mb-0">NOW SHOWING</h2>
                        </a>
                    @endif
                </div>
                <div class="title-underline-full"></div>
            </div>
        </div>

        {{-- Movies List --}}
        <div class="movies-row">
            @forelse($movies as $movie)
                @php
                    // Xác định rating badge
                    $rating = $movie->rating_average ?? 0;
                    $ratingClass = '';
                    $ratingText = '';
                    if ($rating >= 18) {
                        $ratingClass = 'rating-t18';
                        $ratingText = 'T18';
                    } elseif ($rating >= 16) {
                        $ratingClass = 'rating-t16';
                        $ratingText = 'T16';
                    } elseif ($rating >= 13) {
                        $ratingClass = 'rating-t13';
                        $ratingText = 'T13';
                    } elseif ($rating > 0) {
                        $ratingClass = 'rating-k';
                        $ratingText = 'K';
                    }
                @endphp
                <div class="movie-card">
                    <div class="movie-poster-wrapper">
                        <a href="{{ route('movie.show', $movie->id) }}">
                            <img 
                                src="{{ $movie->poster_image_url ?? 'https://placehold.co/240x360?text=No+Poster' }}" 
                                class="movie-poster" 
                                alt="{{ $movie->title }}"
                            >
                        </a>
                        
                        {{-- Rating Badge --}}
                        @if($rating > 0)
                            <span class="rating-badge {{ $ratingClass }}">{{ $ratingText }}</span>
                        @endif
                        
                        {{-- Format Tags --}}
                        <div class="format-tags">
                            @if($loop->iteration == 1 || $loop->iteration == 4)
                                <span class="format-tag">4DX</span>
                                <span class="format-tag">IMAX</span>
                                <span class="format-tag">SCREENX</span>
                            @elseif($loop->iteration == 2)
                                <span class="format-tag">STARIUM</span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Movie Info --}}
                    <div class="movie-info">
                        <h3 class="movie-title">
                            <a href="{{ route('movie.show', $movie->id) }}">{{ $movie->title }}</a>
                        </h3>
                        
                        <div class="movie-details">
                            <p class="detail-item">
                                <strong>Genre:</strong> {{ $movie->genre ?? 'Not updated' }}
                            </p>
                            <p class="detail-item">
                                <strong>Duration:</strong> {{ $movie->duration_minutes ?? 0 }} minutes
                            </p>
                            <p class="detail-item">
                                <strong>Release Date:</strong> {{ \Carbon\Carbon::parse($movie->release_date)->format('d-m-Y') }}
                            </p>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="movie-actions">
                            @auth
                                <button class="btn-like {{ isset($movie->is_favorited) && $movie->is_favorited ? 'liked' : '' }}" 
                                        data-movie-id="{{ $movie->id }}" 
                                        data-like-url="{{ route('movie.favorite.toggle', $movie->id) }}">
                                    <i class="bi bi-hand-thumbs-up{{ isset($movie->is_favorited) && $movie->is_favorited ? '-fill' : '' }}"></i> 
                                    <span class="like-text">{{ isset($movie->is_favorited) && $movie->is_favorited ? 'Liked' : 'Like' }}</span>
                                    <span class="like-count">{{ $movie->favorites_count ?? 0 }}</span>
                                </button>
                            @else
                                <a href="{{ route('login', ['redirect' => route('movie.show', $movie->id)]) }}" class="btn-like">
                                    <i class="bi bi-hand-thumbs-up"></i> 
                                    <span class="like-text">Like</span>
                                    <span class="like-count">{{ $movie->favorites_count ?? 0 }}</span>
                                </a>
                            @endauth
                            @if($movie->showtimes->isNotEmpty())
                                @auth
                                    <button type="button" class="btn-buy-ticket" data-booking-movie-id="{{ $movie->id }}" data-bs-toggle="modal" data-bs-target="#bookingModal">
                                        BUY TICKET
                                    </button>
                                @else
                                    <a href="{{ route('login', ['redirect' => route('movie.show', $movie->id)]) }}" class="btn-buy-ticket">
                                        BUY TICKET
                                    </a>
                                @endauth
                            @else
                                <button class="btn-buy-ticket" disabled>BUY TICKET</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        No movies found.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($movies->hasPages())
            <div class="d-flex justify-content-center mt-5 mb-5">
                {{ $movies->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý click nút like
    document.querySelectorAll('.btn-like[data-like-url]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const movieId = this.getAttribute('data-movie-id');
            const url = this.getAttribute('data-like-url');
            const icon = this.querySelector('i');
            const likeText = this.querySelector('.like-text');
            const likeCount = this.querySelector('.like-count');
            
            // Disable button trong lúc xử lý
            this.disabled = true;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật UI
                    if (data.isLiked) {
                        this.classList.add('liked');
                        icon.classList.remove('bi-hand-thumbs-up');
                        icon.classList.add('bi-hand-thumbs-up-fill');
                        likeText.textContent = 'Đã thích';
                    } else {
                        this.classList.remove('liked');
                        icon.classList.remove('bi-hand-thumbs-up-fill');
                        icon.classList.add('bi-hand-thumbs-up');
                        likeText.textContent = 'Thích';
                    }
                    likeCount.textContent = data.likeCount;
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xử lý yêu cầu');
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
@endsection
