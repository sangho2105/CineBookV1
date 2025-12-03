@extends('layouts.app')

@section('title', 'Home - CineBook')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
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

    {{-- Now Showing Section - CGV Style --}}
</div>
<div class="home-movie-selection my-5">
        <div class="movie-selection-header">
            <div class="header-dots"></div>
            <h2 class="movie-selection-title">NOW SHOWING</h2>
            <div class="header-dots"></div>
        </div>
        @if($nowShowingMovies->isNotEmpty())
            <div class="slideshow-containe-movier feature_slide_show_480_767">
                <ul class="feature_slide_show">
                    <div class="cycle-carousel-wrap">
                        @foreach($nowShowingMovies as $movie)
                            @php
                                // Xác định rating badge từ field 'rated' (K, T13, T16, T18, P)
                                $rated = $movie->rated ?? null;
                                $ratingClass = '';
                                $ratingText = '';
                                if ($rated) {
                                    switch (strtoupper($rated)) {
                                        case 'T18':
                                            $ratingClass = 'nmovie-rating-t18';
                                            $ratingText = 'T18';
                                            break;
                                        case 'T16':
                                            $ratingClass = 'nmovie-rating-t16';
                                            $ratingText = 'T16';
                                            break;
                                        case 'T13':
                                            $ratingClass = 'nmovie-rating-t13';
                                            $ratingText = 'T13';
                                            break;
                                        case 'K':
                                            $ratingClass = 'nmovie-rating-k';
                                            $ratingText = 'K';
                                            break;
                                        case 'P':
                                            $ratingClass = 'nmovie-rating-p';
                                            $ratingText = 'P';
                                            break;
                                        default:
                                            $ratingClass = '';
                                            $ratingText = '';
                                    }
                                }
                            @endphp
                            <li class="poster-film film-lists item cycle-slide">
                                {{-- Ribbon ranking chỉ hiển thị ở trang search, không hiển thị ở home --}}
                                <img class="product-img" src="{{ $movie->poster_image_url ?? 'https://placehold.co/240x388?text=No+Poster' }}" alt="{{ $movie->title }}">
                                
                                {{-- Rating Badge --}}
                                @if($rated && !empty($ratingText))
                                    <div class="nmovie-rating {{ $ratingClass }}">{{ $ratingText }}</div>
                                @endif
                                <input type="hidden" value="{{ $movie->trailer_url ?? '' }}">
                                
                                <div class="feature_film_content">
                                    <h3>{{ $movie->title }}</h3>
                                    <a title="View Details" class="button" href="{{ route('movie.show', $movie->id) }}">View Details</a>
                                    @guest
                                        <a title="Book Ticket" class="button btn-booking" href="{{ route('login') }}">Book Ticket</a>
                                    @else
                                        @if($movie->showtimes->isNotEmpty())
                                            <button type="button" title="Book Ticket" class="button btn-booking" data-booking-movie-id="{{ $movie->id }}" data-bs-toggle="modal" data-bs-target="#bookingModal">Book Ticket</button>
                                        @else
                                            <button type="button" title="Book Ticket" class="button btn-booking" disabled>Book Ticket</button>
                                        @endif
                                    @endguest
                                </div>
                                
                                @if($movie->trailer_url)
                                    <div class="play-button">
                                        <span><span>Play</span></span>
                                        <div style="display:none">
                                            <span class="movie-trailer">{{ $movie->trailer_url }}</span>
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </div>
                </ul>
                <span class="feature_slide_show_prev disabled">&lt;&lt;</span>
                <span class="feature_slide_show_next">&gt;&gt;</span>
            </div>
        @else
            <div class="col-12">
                <p class="text-center">No movies are currently showing.</p>
            </div>
        @endif
    </div>

    {{-- Coming Soon Section - CGV Style --}}
    <div class="home-movie-selection my-5">
        <div class="movie-selection-header">
            <div class="header-dots"></div>
            <h2 class="movie-selection-title">COMING SOON</h2>
            <div class="header-dots"></div>
        </div>
        @if($comingSoonMovies->isNotEmpty())
            <div class="slideshow-containe-movier feature_slide_show_480_767">
                <ul class="feature_slide_show">
                    <div class="cycle-carousel-wrap">
                        @foreach($comingSoonMovies as $movie)
                            @php
                                // Xác định rating badge từ field 'rated' (K, T13, T16, T18, P)
                                $rated = $movie->rated ?? null;
                                $ratingClass = '';
                                $ratingText = '';
                                if ($rated) {
                                    switch (strtoupper($rated)) {
                                        case 'T18':
                                            $ratingClass = 'nmovie-rating-t18';
                                            $ratingText = 'T18';
                                            break;
                                        case 'T16':
                                            $ratingClass = 'nmovie-rating-t16';
                                            $ratingText = 'T16';
                                            break;
                                        case 'T13':
                                            $ratingClass = 'nmovie-rating-t13';
                                            $ratingText = 'T13';
                                            break;
                                        case 'K':
                                            $ratingClass = 'nmovie-rating-k';
                                            $ratingText = 'K';
                                            break;
                                        case 'P':
                                            $ratingClass = 'nmovie-rating-p';
                                            $ratingText = 'P';
                                            break;
                                        default:
                                            $ratingClass = '';
                                            $ratingText = '';
                                    }
                                }
                            @endphp
                            <li class="poster-film film-lists item cycle-slide">
                                <img class="product-img" src="{{ $movie->poster_image_url ?? 'https://placehold.co/240x388?text=No+Poster' }}" alt="{{ $movie->title }}">
                                
                                {{-- Rating Badge --}}
                                @if($rated && !empty($ratingText))
                                    <div class="nmovie-rating {{ $ratingClass }}">{{ $ratingText }}</div>
                                @endif
                                <input type="hidden" value="{{ $movie->trailer_url ?? '' }}">
                                
                                <div class="feature_film_content">
                                    <h3>{{ $movie->title }}</h3>
                                    <a title="View Details" class="button" href="{{ route('movie.show', $movie->id) }}">View Details</a>
                                    <button type="button" title="Coming Soon" class="button btn-booking" disabled>Coming Soon</button>
                                </div>
                                
                                @if($movie->trailer_url)
                                    <div class="play-button">
                                        <span><span>Play</span></span>
                                        <div style="display:none">
                                            <span class="movie-trailer">{{ $movie->trailer_url }}</span>
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </div>
                </ul>
                <span class="feature_slide_show_prev disabled">&lt;&lt;</span>
                <span class="feature_slide_show_next">&gt;&gt;</span>
            </div>
        @else
            <div class="col-12">
                <p class="text-center">No upcoming movies information available.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick Search
    const input = document.getElementById('quick-search-input');
    const resultsBox = document.getElementById('quick-search-results');
    let debounceTimer;

    if (input && resultsBox) {
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
    }

    // Movie Carousel - CGV Style
    const carouselContainers = document.querySelectorAll('.slideshow-containe-movier');
    
    carouselContainers.forEach(container => {
        const carouselWrap = container.querySelector('.cycle-carousel-wrap');
        const prevBtn = container.querySelector('.feature_slide_show_prev');
        const nextBtn = container.querySelector('.feature_slide_show_next');
        const posters = container.querySelectorAll('.poster-film');
        
        if (!carouselWrap || !prevBtn || !nextBtn || posters.length === 0) {
            return;
        }

        let currentPosition = 0;
        const posterWidth = 240; // Width of each poster
        const gap = 15; // Gap between posters
        const visibleCount = 4; // Number of posters visible at once
        const scrollAmount = (posterWidth + gap) * visibleCount;
        const maxPosition = Math.max(0, (posters.length - visibleCount) * (posterWidth + gap));

        const updateButtons = () => {
            if (currentPosition <= 0) {
                prevBtn.classList.add('disabled');
            } else {
                prevBtn.classList.remove('disabled');
            }
            
            if (currentPosition >= maxPosition) {
                nextBtn.classList.add('disabled');
            } else {
                nextBtn.classList.remove('disabled');
            }
        };

        const moveCarousel = () => {
            carouselWrap.style.transform = `translateX(-${currentPosition}px)`;
            updateButtons();
        };

        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!prevBtn.classList.contains('disabled')) {
                currentPosition = Math.max(0, currentPosition - scrollAmount);
                moveCarousel();
            }
        });

        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!nextBtn.classList.contains('disabled')) {
                currentPosition = Math.min(maxPosition, currentPosition + scrollAmount);
                moveCarousel();
            }
        });

        // Play button click handler
        container.querySelectorAll('.play-button').forEach(playBtn => {
            playBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const trailerUrl = this.querySelector('.movie-trailer')?.textContent;
                if (trailerUrl) {
                    window.open(trailerUrl, '_blank');
                }
            });
        });

        updateButtons();
    });
});
</script>
@endpush

@endsection