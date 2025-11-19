@extends('layouts.app')

@section('title', 'Trang chủ - CineBook')

@push('css')
    <link rel="stylesheet" href="{{ asset('public/css/home.css') }}">
@endpush

@section('content')
<div class="container">
    {{-- Hero Section --}}
    <div class="hero my-4">
        @if($promotions->isNotEmpty())
            <div id="homePromotionCarousel" class="carousel slide promotion-carousel" data-bs-ride="carousel" data-bs-interval="6000">
                <div class="carousel-indicators">
                    @foreach($promotions as $promotion)
                        <button type="button"
                                data-bs-target="#homePromotionCarousel"
                                data-bs-slide-to="{{ $loop->index }}"
                                class="{{ $loop->first ? 'active' : '' }}"
                                aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                aria-label="Slide {{ $loop->iteration }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner rounded-4">
                    @foreach($promotions as $promotion)
                        @php
                            $targetUrl = match ($promotion->category) {
                                'movie' => $promotion->movie ? route('movie.show', $promotion->movie) : null,
                                default => route('promotion.show', $promotion),
                            };
                        @endphp
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <a href="{{ $targetUrl ?? '#' }}" class="{{ $targetUrl ? '' : 'disabled-link' }}">
                                <img src="{{ $promotion->image_url }}" class="d-block w-100 promotion-slide-image" alt="{{ $promotion->title }}">
                            </a>
                        </div>
                    @endforeach
                </div>
                @if($promotions->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#homePromotionCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#homePromotionCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                @endif
            </div>
        @else
            <div class="text-center">
                <img src="https://placehold.co/1200x400?text=CineBook+Banner" class="img-fluid rounded-4" alt="CineBook Banner">
            </div>
        @endif
    </div>

    {{-- Quick Search Widget - Compact Version --}}
    <div class="d-flex justify-content-end mb-4">
        <div class="quick-search-wrapper position-relative">
            <form action="{{ route('search') }}" method="GET">
                <div class="input-group quick-search-group">
                    <input 
                        type="text" 
                        class="form-control quick-search-input" 
                        name="keyword" 
                        placeholder="Tìm kiếm phim..."
                        autocomplete="off"
                        id="quick-search-input"
                    >
                    <button type="submit" class="btn btn-primary quick-search-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div id="quick-search-results" class="list-group quick-search-results d-none"></div>
                <div class="text-end mt-2">
                    <a href="{{ route('search') }}" class="text-decoration-none small quick-search-advanced">
                        <i class="bi bi-funnel"></i> Tìm kiếm nâng cao
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Now Showing Section --}}
    <div class="now-showing my-5">
        <h2 class="text-center mb-4">Đang Chiếu</h2>
        <div class="row">
            
            {{-- Bắt đầu vòng lặp Phim Đang Chiếu --}}
            @forelse($nowShowingMovies as $movie)
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100"> {{-- Thêm h-100 để các card bằng nhau --}}
                    
                    {{-- Poster có link đến Trailer --}}
                    <a href="{{ $movie->trailer_url }}" target="_blank">
                        <img src="{{ $movie->poster_image_url ?? 'https://placehold.co/300x450?text=No+Poster' }}" class="card-img-top" alt="{{ $movie->title }}">
                    </a>
                    
                    <div class="card-body d-flex flex-column"> {{-- Thêm flex-column --}}
                                                {{-- 
                        Thay đổi ở đây: 
                        Bọc tên phim bằng thẻ <a> trỏ đến route 'movie.show'
                        --}}
                        <h5 class="card-title">
                            <a href="{{ route('movie.show', $movie->id) }}" class="text-decoration-none text-dark">
                                {{ $movie->title }}
                            </a>
                        </h5>
                        <p class="card-text">{{ Str::limit($movie->synopsis ?? 'Chưa có thông tin', 70) }}</p>
                        
                        {{-- Đẩy nút xuống dưới cùng --}}
                        <div class="mt-auto">
                            @guest
                                <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2">Đặt Vé</a>
                            @else
                                <a href="{{ route('movie.show', $movie->id) }}" class="btn btn-primary w-100 mb-2">Đặt Vé</a>
                            @endguest
                            <a href="{{ $movie->trailer_url }}" class="btn btn-outline-secondary w-100" target="_blank">Xem Trailer</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12">
                    <p class="text-center">Hiện chưa có phim nào đang chiếu.</p>
                </div>
            @endforelse
            {{-- Kết thúc vòng lặp --}}

        </div>
    </div>

    {{-- Coming Soon Section --}}
    <div class="coming-soon my-5">
        <h2 class="text-center mb-4">Sắp Chiếu</h2>
        <div class="row">
            
            {{-- Bắt đầu vòng lặp Phim Sắp Chiếu --}}
            @forelse($comingSoonMovies as $movie)
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    
                    {{-- Poster có link đến Trailer --}}
                    <a href="{{ $movie->trailer_url }}" target="_blank">
                        <img src="{{ $movie->poster_image_url ?? 'https://placehold.co/300x450?text=No+Poster' }}" class="card-img-top" alt="{{ $movie->title }}">
                    </a>
                    
                    <div class="card-body d-flex flex-column">
                        {{-- 
                        Thay đổi ở đây: 
                        Bọc tên phim bằng thẻ <a> trỏ đến route 'movie.show'
                        --}}
                        <h5 class="card-title">
                            <a href="{{ route('movie.show', $movie->id) }}" class="text-decoration-none text-dark">
                                {{ $movie->title }}
                            </a>
                        </h5>
                        {{-- Hiển thị ngày dự kiến chiếu --}}
                        <p class="card-text">Dự kiến: <strong>{{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}</strong></p>
                        
                        <div class="mt-auto">
                            <a href="#" class="btn btn-secondary w-100 disabled" tabindex="-1" aria-disabled="true">Sắp chiếu</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12">
                    <p class="text-center">Chưa có thông tin phim sắp chiếu.</p>
                </div>
            @endforelse
            {{-- Kết thúc vòng lặp --}}
            
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('quick-search-input');
    const resultsBox = document.getElementById('quick-search-results');
    let debounceTimer;

    if (!input || !resultsBox) {
        return;
    }

    const hideResults = () => {
        resultsBox.classList.add('d-none');
        resultsBox.innerHTML = '';
    };

    input.addEventListener('input', function() {
        const keyword = this.value.trim();
        clearTimeout(debounceTimer);

        if (keyword.length < 2) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/search/autocomplete?keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(movies => {
                    if (!Array.isArray(movies) || movies.length === 0) {
                        hideResults();
                        return;
                    }

                    resultsBox.innerHTML = '';
                    movies.forEach(movie => {
                        const item = document.createElement('a');
                        item.href = `/movie/${movie.id}`;
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = movie.title;
                        resultsBox.appendChild(item);
                    });
                    resultsBox.classList.remove('d-none');
                })
                .catch(() => {
                    hideResults();
                });
        }, 300);
    });

    document.addEventListener('click', function(event) {
        if (!resultsBox.contains(event.target) && event.target !== input) {
            hideResults();
        }
    });
});
</script>
@endpush

@endsection