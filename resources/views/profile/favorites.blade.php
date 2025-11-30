@extends('layouts.app')

@section('title', 'Phim yêu thích - CineBook')

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
                        <a href="{{ route('profile.index') }}">Hồ sơ</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Phim yêu thích
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
                    <h1 class="page-title mb-0">Phim yêu thích của tôi</h1>
                    <p class="page-subtitle mb-0">Danh sách các phim bạn đã thích ({{ $favoriteMovies->total() }} phim)</p>
                </div>
                <div class="title-underline-full"></div>
            </div>
        </div>

        {{-- Movies List --}}
        <div class="movies-row">
            @forelse($favoriteMovies as $movie)
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
                                <strong>Thể loại:</strong> {{ $movie->genre ?? 'Chưa cập nhật' }}
                            </p>
                            <p class="detail-item">
                                <strong>Thời lượng:</strong> {{ $movie->duration_minutes ?? 0 }} phút
                            </p>
                            <p class="detail-item">
                                <strong>Khởi chiếu:</strong> {{ \Carbon\Carbon::parse($movie->release_date)->format('d-m-Y') }}
                            </p>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="movie-actions">
                            <button class="btn-like liked" 
                                    data-movie-id="{{ $movie->id }}" 
                                    data-like-url="{{ route('movie.favorite.toggle', $movie->id) }}">
                                <i class="bi bi-hand-thumbs-up-fill"></i> 
                                <span class="like-text">Đã thích</span>
                                <span class="like-count">{{ $movie->favorites_count ?? 0 }}</span>
                            </button>
                            @if($movie->showtimes->isNotEmpty())
                                <button type="button" class="btn-buy-ticket" data-booking-movie-id="{{ $movie->id }}" data-bs-toggle="modal" data-bs-target="#bookingModal">
                                    MUA VÉ
                                </button>
                            @else
                                <button class="btn-buy-ticket" disabled>MUA VÉ</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Bạn chưa có phim yêu thích nào. Hãy thích các phim bạn quan tâm!
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($favoriteMovies->hasPages())
            <div class="d-flex justify-content-center mt-5 mb-5">
                {{ $favoriteMovies->links() }}
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
                    // Nếu unlike thì xóa phim khỏi danh sách
                    if (!data.isLiked) {
                        const movieCardElement = this.closest('.movie-card');
                        if (movieCardElement) {
                            movieCardElement.style.transition = 'opacity 0.3s';
                            movieCardElement.style.opacity = '0';
                            setTimeout(() => {
                                movieCardElement.remove();
                                // Kiểm tra nếu không còn phim nào
                                if (document.querySelectorAll('.movie-card').length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    } else {
                        // Cập nhật UI nếu like lại
                        this.classList.add('liked');
                        icon.classList.remove('bi-hand-thumbs-up');
                        icon.classList.add('bi-hand-thumbs-up-fill');
                        likeText.textContent = 'Đã thích';
                        likeCount.textContent = data.likeCount;
                    }
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

