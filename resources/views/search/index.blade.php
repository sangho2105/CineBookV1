@extends('layouts.app')

@section('title', request('status') == 'now_showing' ? 'Phim Đang Chiếu - CineBook' : (request('status') == 'upcoming' ? 'Phim Sắp Chiếu - CineBook' : 'Danh sách phim - CineBook'))

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
                        <a href="{{ route('search') }}">Phim</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @if(request('status') == 'now_showing')
                            Phim Đang Chiếu
                        @elseif(request('status') == 'upcoming')
                            Phim Sắp Chiếu
                        @else
                            Danh sách phim
                        @endif
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        {{-- Page Title --}}
        <div class="page-header mb-4">
            <h1 class="page-title">
                @if(request('status') == 'now_showing')
                    Phim Đang Chiếu
                @elseif(request('status') == 'upcoming')
                    Phim Sắp Chiếu
                @else
                    Danh sách phim
                @endif
            </h1>
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
                            <button class="btn-like">
                                <i class="bi bi-hand-thumbs-up"></i> Like 0
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
                        Không tìm thấy phim nào.
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
@endsection
