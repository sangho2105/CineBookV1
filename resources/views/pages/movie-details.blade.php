@extends('layouts.app')

{{-- Lấy tên phim làm tiêu đề trang --}}
@section('title', $movie->title . ' - CineBook')

@push('css')
<link rel="stylesheet" href="{{ asset('css/search.css') }}">
<style>
    .movie-detail-tabs {
        position: relative;
        display: inline-flex;
        align-items: center;
        background-color: #dc3545;
        padding: 12px 30px;
        margin: 20px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        clip-path: polygon(
            12px 0%,
            calc(100% - 12px) 0%,
            100% 50%,
            calc(100% - 12px) 100%,
            12px 100%,
            0% 50%
        );
        -webkit-clip-path: polygon(
            12px 0%,
            calc(100% - 12px) 0%,
            100% 50%,
            calc(100% - 12px) 100%,
            12px 100%,
            0% 50%
        );
    }

    .tab-btn {
        background-color: transparent;
        color: #fff;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        position: relative;
        white-space: nowrap;
        line-height: 1.5;
    }

    .tab-btn:hover {
        opacity: 0.9;
    }

    .tab-btn.active {
        font-weight: 600;
    }

    /* Icon styling */
    .tab-btn i {
        margin-right: 6px;
        font-size: 1.1rem;
        opacity: 0.95;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
    }

    /* Separator */
    .tab-separator {
        color: rgba(255,255,255,0.7);
        padding: 0 12px;
        font-weight: 300;
        font-size: 1.1rem;
        vertical-align: middle;
    }

    .tab-content {
        min-height: 200px;
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Container lớn đóng khung nội dung phim */
    .movie-content-wrapper {
        position: relative;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        background-color: #F5F5DC;
        border: none !important;
        box-shadow: none !important;
    }
    
    /* Đảm bảo khi zoom thì layout không bị vỡ */
    .movie-content-wrapper * {
        box-sizing: border-box;
    }
    
    /* Đảm bảo 2 cột bằng nhau */
    .movie-content-wrapper .row {
        display: flex;
        align-items: stretch;
        min-height: 100%;
    }
    
    .movie-content-wrapper .col-md-4,
    .movie-content-wrapper .col-md-8 {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    /* Đảm bảo poster và thông tin có chiều cao bằng nhau */
    .movie-content-wrapper .col-md-4 img {
        height: 100%;
        object-fit: cover;
    }
    
    .movie-content-wrapper .col-md-8 {
        justify-content: flex-start;
    }
    
    /* Responsive cho mobile */
    @media (max-width: 768px) {
        .movie-content-wrapper {
            padding: 20px 15px;
        }
    }
</style>
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
                        {{ $movie->title }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="container">
    
    {{-- Container lớn đóng khung toàn bộ nội dung --}}
    <div class="movie-content-wrapper" style="border: none; border-radius: 0; padding: 30px; background-color: #F5F5DC; box-shadow: none;">
        {{-- Tiêu đề và HR --}}
        <h2 class="mb-3" style="font-size: 1.5rem; font-weight: bold; color: #333;">Movie Details</h2>
        <hr class="mb-4">

    <div class="row">
        {{-- Cột Poster --}}
        <div class="col-md-4">
            <img src="{{ $movie->poster_image_url ?? 'https://placehold.co/300x450?text=No+Poster' }}" 
                 class="img-fluid rounded shadow-sm" 
                 alt="{{ $movie->title }}"
                 style="max-height: 600px; width: 100%; object-fit: cover;">
        </div>

        {{-- Cột Thông tin --}}
        <div class="col-md-8">
            <h1 class="mb-4" style="font-size: 1.5rem;">{{ $movie->title }}</h1>
            
            {{-- Thông tin chi tiết đầy đủ --}}
            <div class="movie-info-details mb-4">
                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Director:</strong> 
                        <span>{{ $movie->director ?? 'Not updated' }}</span>
                    </p>
                </div>

                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Cast:</strong> 
                        <span>{{ $movie->cast ?? 'Not updated' }}</span>
                    </p>
                </div>

                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Genre:</strong> 
                        <span>{{ $movie->genre ?? 'Not updated' }}</span>
                    </p>
                </div>

                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Release Date:</strong> 
                        <span>{{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}</span>
                    </p>
                </div>

                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Duration:</strong> 
                        <span>{{ $movie->duration_minutes ?? 0 }} minutes</span>
                    </p>
                </div>

                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Language:</strong> 
                        <span>{{ $movie->language ?? 'Not updated' }}</span>
                    </p>
                </div>

                @if($movie->rated)
                <div class="mb-3">
                    <p class="mb-2" style="font-size: 0.95rem;">
                        <strong>Rated:</strong> 
                        <span>
                            {{ $movie->rated }} - 
                            @if($movie->rated == 'K')
                                FILM DISTRIBUTED TO VIEWERS UNDER 13 YEARS OLD WITH ACCOMPANYING GUARDIAN
                            @elseif($movie->rated == 'T13')
                                FILM FOR VIEWERS AGED 13 AND ABOVE
                            @elseif($movie->rated == 'T16')
                                FILM FOR VIEWERS AGED 16 AND ABOVE
                            @elseif($movie->rated == 'T18')
                                FILM FOR VIEWERS AGED 18 AND ABOVE
                            @elseif($movie->rated == 'P')
                                FILM FOR ALL AUDIENCES
                            @else
                                {{ $movie->rated }}
                            @endif
                        </span>
                    </p>
                </div>
                @endif
            </div>

            {{-- Nút Đặt vé --}}
            <div class="mt-2 mb-3">
                @if($movie->showtimes->isNotEmpty())
                    @auth
                        <button type="button" class="btn btn-danger btn-lg px-5" style="font-weight: bold; font-size: 1rem;" data-booking-movie-id="{{ $movie->id }}" data-bs-toggle="modal" data-bs-target="#bookingModal">BUY TICKET</button>
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-danger btn-lg px-5" style="font-weight: bold; font-size: 1rem;">BUY TICKET</a>
                    @endauth
                @else
                    <button class="btn btn-danger btn-lg px-5" style="font-weight: bold; font-size: 1rem;" disabled>BUY TICKET</button>
                @endif
            </div>

            {{-- Tab Navigation - Centered --}}
            <div class="d-flex justify-content-center my-2">
                <div class="movie-detail-tabs">
                    <button class="tab-btn active" data-tab="details">
                        <i class="bi bi-hand-index"></i>Details
                    </button>
                    <span class="tab-separator">|</span>
                    <button class="tab-btn" data-tab="trailer">
                        <i class="bi bi-play-circle"></i>Trailer
                    </button>
                </div>
            </div>

            {{-- Tab Content: Chi tiết --}}
            <div id="details-content" class="tab-content active">
                <hr class="my-4">

                <h4 class="mt-4 mb-3" style="font-size: 1.1rem;">Synopsis</h4>
                <p style="font-size: 0.95rem; line-height: 1.6; color: #333;">
                    {{ $movie->synopsis ?? 'No synopsis available.' }}
                </p>
            </div>

            {{-- Tab Content: Trailer --}}
            <div id="trailer-content" class="tab-content" style="display: none;">
                <div class="mt-4">
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
                        @else
                            <div class="text-center py-5">
                                <p class="text-muted" style="font-size: 0.95rem;">This movie has no trailer available.</p>
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>
    

    {{-- Bình luận & Đánh giá --}}
    <div class="row mt-5">
        <div class="col-md-8">
            <h4 class="mb-3" style="font-size: 1.1rem;">Comments</h4>
            @auth
                <form action="{{ route('movie.comment.store', $movie->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea name="content" id="content" rows="3" class="form-control @error('content') is-invalid @enderror" placeholder="Share your thoughts about the movie...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            @else
                <div class="alert alert-info">
                    Please <a href="{{ route('login') }}">login</a> to comment.
                </div>
            @endauth

            @forelse($comments as $comment)
                <div class="mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $comment->user->name ?? 'User' }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        @auth
                            @if(auth()->id() === ($comment->user_id ?? null))
                                <div class="ms-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-comment-{{ $comment->id }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('movie.comment.delete', [$movie->id, $comment->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this comment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                    <p class="mb-2 mt-2">{{ $comment->content }}</p>

                    @auth
                        @if(auth()->id() === ($comment->user_id ?? null))
                            <div class="collapse mt-2" id="edit-comment-{{ $comment->id }}">
                                <form action="{{ route('movie.comment.update', [$movie->id, $comment->id]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-2">
                                        <textarea name="content" rows="3" class="form-control">{{ old('content', $comment->content) }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#edit-comment-{{ $comment->id }}">Cancel</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            @empty
                <p class="text-muted">No comments yet. Be the first to comment!</p>
            @endforelse

            @if(method_exists($comments, 'links'))
                <div class="mt-3">
                    {{ $comments->links() }}
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <h4 class="mb-3" style="font-size: 1.1rem;">Ratings</h4>
            <p class="mb-2">
                <span class="h5 mb-0" style="font-size: 1rem;">⭐ {{ number_format($ratingAverage ?? 0, 1) }}/5.0</span>
                <small class="text-muted">({{ $ratingCount ?? 0 }} ratings)</small>
            </p>

            @auth
                @if($canRate)
                    <form action="{{ route('movie.rating.store', $movie->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <label class="input-group-text" for="score">Rate</label>
                            <select class="form-select @error('score') is-invalid @enderror" id="score" name="score" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} / 5</option>
                                @endfor
                            </select>
                            <button class="btn btn-success" type="submit">Submit</button>
                            @error('score')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted d-block mt-2">You can update your rating if you change your mind.</small>
                    </form>
                @else
                    @if(isset($hasCompletedBooking) && $hasCompletedBooking)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> You have purchased tickets for this movie. Please wait for the showtime to end to rate.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> You need to complete payment for tickets of this movie to rate.
                        </div>
                    @endif
                @endif
            @else
                <div class="alert alert-info">
                    Please <a href="{{ route('login') }}">login</a> to rate.
                </div>
            @endauth
        </div>
    </div>
    </div>
    {{-- Kết thúc container lớn --}}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });

            // Add active class to clicked button
            this.classList.add('active');

            // Show corresponding content
            const targetContent = document.getElementById(targetTab + '-content');
            if (targetContent) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
            }
        });
    });
});
</script>
@endpush
@endsection 