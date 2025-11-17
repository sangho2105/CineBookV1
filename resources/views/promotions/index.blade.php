@extends('layouts.app')

@section('title', 'Ưu đãi & Sự kiện - CineBook')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h1 class="h3 mb-3 mb-md-0">Ưu đãi &amp; Sự kiện</h1>
        <p class="text-muted mb-0">Khám phá những chương trình đang diễn ra tại CineBook.</p>
    </div>

    @if($promotions->isEmpty())
        <div class="alert alert-info">
            Hiện chưa có chương trình khuyến mãi hay sự kiện nào. Vui lòng quay lại sau nhé!
        </div>
    @else
        <div class="row g-4">
            @foreach($promotions as $promotion)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <a href="{{ route('promotion.show', $promotion) }}" class="text-decoration-none">
                            <img src="{{ $promotion->image_url }}" class="card-img-top" alt="{{ $promotion->title }}">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-primary align-self-start mb-2">{{ $promotion->category_label }}</span>
                            <h5 class="card-title">
                                <a href="{{ route('promotion.show', $promotion) }}" class="text-decoration-none text-dark">
                                    {{ $promotion->title }}
                                </a>
                            </h5>
                            @if($promotion->description)
                                <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($promotion->description, 100) }}</p>
                            @endif
                            <div class="mt-auto">
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $promotion->start_date->format('d/m/Y') }}
                                    @if($promotion->end_date)
                                        &ndash; {{ $promotion->end_date->format('d/m/Y') }}
                                    @else
                                        &bull; Không giới hạn
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $promotions->links() }}
        </div>
    @endif
</div>
@endsection

