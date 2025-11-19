@extends('layouts.app')

@section('title', 'Tìm kiếm phim - CineBook')

@section('content')
<div class="container my-5">
    <h1 class="text-center mb-4">
        <i class="bi bi-search"></i> Tìm kiếm & Bộ lọc phim
    </h1>

    {{-- Search and Filter Form --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('search') }}" method="GET" id="searchForm">
                <div class="row g-3">
                    {{-- Keyword Search --}}
                    <div class="col-md-12">
                        <label for="keyword" class="form-label">
                            <i class="bi bi-search"></i> Tìm kiếm theo tên phim
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            id="keyword" 
                            name="keyword" 
                            placeholder="Nhập tên phim hoặc từ khóa..."
                            value="{{ request('keyword') }}"
                            autocomplete="off"
                        >
                        <div id="autocomplete-results" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div>
                    </div>

                    {{-- Genre Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="genre" class="form-label">
                            <i class="bi bi-film"></i> Thể loại
                        </label>
                        <select class="form-select" id="genre" name="genre">
                            <option value="">Tất cả thể loại</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre }}" {{ request('genre') == $genre ? 'selected' : '' }}>
                                    {{ $genre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="status" class="form-label">
                            <i class="bi bi-clock"></i> Trạng thái
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tất cả</option>
                            <option value="now_showing" {{ request('status') == 'now_showing' ? 'selected' : '' }}>Đang chiếu</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                        </select>
                    </div>

                    {{-- Language Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="language" class="form-label">
                            <i class="bi bi-translate"></i> Ngôn ngữ
                        </label>
                        <select class="form-select" id="language" name="language">
                            <option value="">Tất cả ngôn ngữ</option>
                            @foreach($languageOptions as $opt)
                                <option value="{{ $opt['value'] }}" {{ request('language') == $opt['value'] ? 'selected' : '' }}>
                                    {{ $opt['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="city" class="form-label">
                            <i class="bi bi-geo-alt"></i> Thành phố
                        </label>
                        <select class="form-select" id="city" name="city">
                            <option value="">Tất cả thành phố</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Theater Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="theater_id" class="form-label">
                            <i class="bi bi-building"></i> Rạp chiếu
                        </label>
                        <select class="form-select" id="theater_id" name="theater_id">
                            <option value="">Tất cả rạp</option>
                            @foreach($filteredTheaters as $theater)
                                <option value="{{ $theater->id }}" {{ request('theater_id') == $theater->id ? 'selected' : '' }}>
                                    {{ $theater->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="date" class="form-label">
                            <i class="bi bi-calendar"></i> Ngày chiếu
                        </label>
                        <input 
                            type="date" 
                            class="form-control" 
                            id="date" 
                            name="date" 
                            value="{{ request('date') }}"
                        >
                    </div>

                    {{-- Rating Filter --}}
                    <div class="col-md-6 col-lg-3">
                        <label for="rating_min" class="form-label">
                            <i class="bi bi-star-fill text-warning"></i> Đánh giá tối thiểu
                        </label>
                        <div class="input-group">
                            <input
                                type="number"
                                step="0.1"
                                min="0"
                                max="10"
                                class="form-control"
                                id="rating_min"
                                name="rating_min"
                                placeholder="VD: 7.5"
                                value="{{ request('rating_min') }}"
                            >
                            <span class="input-group-text">/10</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                            <a href="{{ route('search') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Summary --}}
    @if(request()->hasAny(['keyword', 'genre', 'status', 'language', 'rating_min', 'city', 'theater_id', 'date']))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            Tìm thấy <strong>{{ $movies->total() }}</strong> kết quả
            @if(request('keyword'))
                cho từ khóa "<strong>{{ request('keyword') }}</strong>"
            @endif
            @if(request('genre'))
                - Thể loại: <strong>{{ request('genre') }}</strong>
            @endif
            @if(request('language'))
                - Ngôn ngữ: <strong>{{ request('language') }}</strong>
            @endif
            @if(request('rating_min'))
                - Đánh giá từ: <strong>{{ request('rating_min') }}/10</strong>
            @endif
            @if(request('city'))
                - Thành phố: <strong>{{ request('city') }}</strong>
            @endif
            @if(request('theater_id'))
                - Rạp: <strong>{{ $filteredTheaters->firstWhere('id', request('theater_id'))->name ?? '' }}</strong>
            @endif
        </div>
    @endif

    {{-- Search Results --}}
    <div class="row">
        @forelse($movies as $movie)
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm hover-shadow">
                    {{-- Movie Poster --}}
                    <a href="{{ route('movie.show', $movie->id) }}">
                        <img 
                            src="{{ $movie->poster_image_url ?? 'https://placehold.co/300x450?text=No+Poster' }}" 
                            class="card-img-top" 
                            alt="{{ $movie->title }}"
                            style="height: 400px; object-fit: cover;"
                        >
                    </a>
                    
                    {{-- Movie Info --}}
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="{{ route('movie.show', $movie->id) }}" class="text-decoration-none text-dark">
                                {{ $movie->title }}
                            </a>
                        </h5>
                        
                        <div class="mb-2">
                            <span class="badge bg-secondary">{{ $movie->genre }}</span>
                            @if($movie->status == 'now_showing')
                                <span class="badge bg-success">Đang chiếu</span>
                            @elseif($movie->status == 'upcoming')
                                <span class="badge bg-warning text-dark">Sắp chiếu</span>
                            @else
                                <span class="badge bg-dark">Đã kết thúc</span>
                            @endif
                        </div>

                        <p class="card-text small text-muted">
                            {{ Str::limit($movie->synopsis ?? 'Chưa có thông tin', 80) }}
                        </p>

                        {{-- Show theaters if available --}}
                        @if($movie->showtimes->isNotEmpty())
                            <div class="mt-auto">
                                <small class="text-muted">
                                    <i class="bi bi-building"></i> 
                                    Chiếu tại: {{ $movie->showtimes->pluck('theater.name')->unique()->take(2)->implode(', ') }}
                                    @if($movie->showtimes->pluck('theater.name')->unique()->count() > 2)
                                        và {{ $movie->showtimes->pluck('theater.name')->unique()->count() - 2 }} rạp khác
                                    @endif
                                </small>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="mt-3">
                            <a href="{{ route('movie.show', $movie->id) }}" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-info-circle"></i> Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Không tìm thấy phim nào phù hợp với tiêu chí tìm kiếm.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($movies->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $movies->links() }}
        </div>
    @endif
</div>

{{-- Custom Styles --}}
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    #autocomplete-results {
        max-height: 300px;
        overflow-y: auto;
        margin-top: 5px;
    }
    .autocomplete-item {
        cursor: pointer;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .autocomplete-item:hover {
        background-color: #f8f9fa;
    }
</style>

{{-- JavaScript for dynamic filtering --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // City change triggers theater update
    const citySelect = document.getElementById('city');
    const theaterSelect = document.getElementById('theater_id');
    
    if (citySelect && theaterSelect) {
        citySelect.addEventListener('change', function() {
            const city = this.value;
            
            if (!city) {
                // Reset theater select
                theaterSelect.innerHTML = '<option value="">Tất cả rạp</option>';
                @foreach($theaters as $theater)
                    theaterSelect.innerHTML += '<option value="{{ $theater->id }}">{{ $theater->name }}</option>';
                @endforeach
                return;
            }
            
            // Fetch theaters for selected city
            fetch(`/search/theaters-by-city?city=${encodeURIComponent(city)}`)
                .then(response => response.json())
                .then(theaters => {
                    theaterSelect.innerHTML = '<option value="">Tất cả rạp</option>';
                    theaters.forEach(theater => {
                        theaterSelect.innerHTML += `<option value="${theater.id}">${theater.name}</option>`;
                    });
                })
                .catch(error => console.error('Error:', error));
        });
    }

    // Autocomplete for keyword search
    const keywordInput = document.getElementById('keyword');
    const autocompleteResults = document.getElementById('autocomplete-results');
    let debounceTimer;

    if (keywordInput && autocompleteResults) {
        keywordInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const keyword = this.value.trim();
            
            if (keyword.length < 2) {
                autocompleteResults.style.display = 'none';
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch(`/search/autocomplete?keyword=${encodeURIComponent(keyword)}`)
                    .then(response => response.json())
                    .then(movies => {
                        if (movies.length === 0) {
                            autocompleteResults.style.display = 'none';
                            return;
                        }
                        
                        autocompleteResults.innerHTML = '';
                        movies.forEach(movie => {
                            const item = document.createElement('a');
                            item.href = `/movie/${movie.id}`;
                            item.className = 'list-group-item list-group-item-action autocomplete-item';
                            item.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${movie.poster_image_url || 'https://placehold.co/50x75?text=No+Image'}" 
                                         alt="${movie.title}" 
                                         style="width: 40px; height: 60px; object-fit: cover; margin-right: 10px;">
                                    <div>
                                        <strong>${movie.title}</strong><br>
                                        <small class="text-muted">${movie.genre}</small>
                                    </div>
                                </div>
                            `;
                            autocompleteResults.appendChild(item);
                        });
                        
                        autocompleteResults.style.display = 'block';
                    })
                    .catch(error => console.error('Error:', error));
            }, 300);
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!keywordInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
                autocompleteResults.style.display = 'none';
            }
        });
    }

    // Auto-submit on filter change (optional)
    const autoSubmitSelects = document.querySelectorAll('#genre, #status, #language, #city, #theater_id, #date, #rating_min');
    autoSubmitSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Uncomment the line below to enable auto-submit
            // document.getElementById('searchForm').submit();
        });
    });
});
</script>
@endsection
