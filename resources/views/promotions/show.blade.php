@extends('layouts.app')

@section('title', $promotion->title . ' - CineBook')

@push('css')
<style>
    .card-img-top {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        display: block;
    }
    
    @media (max-width: 768px) {
        .card-img-top {
            max-height: 300px;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <img src="{{ $promotion->image_url }}" class="card-img-top" alt="{{ $promotion->title }}">
                <div class="card-body">
                    <h1 class="card-title h3 mb-4">{{ $promotion->title }}</h1>
                    @if($promotion->conditions)
                        <div style="font-size: 1.1rem; line-height: 1.8;">{!! $promotion->conditions !!}</div>
                    @endif
                </div>
            </div>

            <div class="text-center">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

