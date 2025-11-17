@extends('layouts.app')

@section('title', $promotion->title . ' - CineBook')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <img src="{{ $promotion->image_url }}" class="card-img-top" alt="{{ $promotion->title }}">
                <div class="card-body">
                    <span class="badge bg-primary mb-3">{{ $promotion->category_label }}</span>
                    <h1 class="card-title h3">{{ $promotion->title }}</h1>
                    @if($promotion->description)
                        <p class="card-text fs-5">{{ $promotion->description }}</p>
                    @endif
                    <div class="text-muted">
                        <i class="bi bi-calendar3"></i>
                        {{ $promotion->start_date->format('d/m/Y') }}
                        @if($promotion->end_date)
                            &ndash; {{ $promotion->end_date->format('d/m/Y') }}
                        @else
                            &bull; Không giới hạn
                        @endif
                    </div>
                </div>
            </div>

            @if($promotion->category === 'movie' && $promotion->movie)
                <div class="alert alert-info d-flex align-items-center justify-content-between">
                    <div>
                        <strong>Khuyến mãi dành cho phim:</strong> {{ $promotion->movie->title }}
                    </div>
                    <a href="{{ route('movie.show', $promotion->movie) }}" class="btn btn-outline-primary">
                        Xem chi tiết phim
                    </a>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>
    </div>
</div>
@endsection

