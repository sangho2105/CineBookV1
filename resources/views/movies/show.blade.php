@extends('layouts.app')

@section('title', $movie->title . ' - CineBook')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $movie->title }}</h4>
                <div>
                    <a href="{{ route('movies.edit', $movie) }}" class="btn btn-warning btn-sm">{{ __('common.edit') }}</a>
                    <a href="{{ route('movies.index') }}" class="btn btn-secondary btn-sm">{{ __('common.back_to_list') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($movie->poster_image_url)
                            <img src="{{ $movie->poster_image_url }}" alt="{{ $movie->title }}" class="img-fluid rounded mb-3">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                <span class="text-muted">{{ __('common.no_poster') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">{{ __('common.genre') }}:</th>
                                <td>{{ $movie->genre }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('common.language') }}:</th>
                                <td>{{ $movie->language }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('common.duration') }}:</th>
                                <td>{{ $movie->duration }} {{ __('common.minutes') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('common.release_date') }}:</th>
                                <td>{{ $movie->release_date->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('common.status') }}:</th>
                                <td>
                                    <span class="badge bg-{{ $movie->status == 'now_showing' ? 'success' : ($movie->status == 'upcoming' ? 'warning' : 'secondary') }}">
                                        {{ $movie->status == 'now_showing' ? __('common.now_showing') : ($movie->status == 'upcoming' ? __('common.upcoming') : ucfirst(str_replace('_', ' ', $movie->status))) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('common.rating') }}:</th>
                                <td>{{ number_format($movie->rating_average, 1) }}/5.0</td>
                            </tr>
                            @if($movie->trailer_link)
                            <tr>
                                <th>{{ __('common.trailer') }}:</th>
                                <td>
                                    <a href="{{ $movie->trailer_link }}" target="_blank" class="btn btn-sm btn-danger">
                                        {{ __('common.watch_trailer') }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </table>
                        
                        @if($movie->description)
                        <div class="mt-3">
                            <h5>{{ __('common.description') }}</h5>
                            <p class="text-muted">{{ $movie->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection