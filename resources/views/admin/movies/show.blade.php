@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Chi tiết Phim</h1>
        <div>
            <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Quay lại danh sách</a>
            <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning">Sửa</a>
        </div>
    </div>

    <div class="row">
        {{-- Cột Poster --}}
        <div class="col-md-4 mb-4">
            @if($movie->poster_image_url)
                <img src="{{ $movie->poster_image_url }}" 
                     class="img-fluid rounded shadow-sm" 
                     alt="{{ $movie->title }}"
                     style="max-height: 600px; width: 100%; object-fit: cover;">
            @else
                <div class="bg-light rounded shadow-sm d-flex align-items-center justify-content-center" style="height: 600px;">
                    <p class="text-muted">Chưa có poster</p>
                </div>
            @endif
        </div>

        {{-- Cột Thông tin --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">{{ $movie->title }}</h2>
                    
                    {{-- Thông tin cơ bản --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Thể loại:</strong> 
                                <span class="badge bg-primary">{{ $movie->genre }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Ngôn ngữ:</strong> 
                                <span class="badge bg-secondary">{{ $movie->language }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Thời lượng:</strong> 
                                <span class="badge bg-info text-dark">{{ $movie->duration_minutes }} phút</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Rated:</strong> 
                                @if($movie->rated)
                                    <span class="badge bg-secondary">{{ $movie->rated }}</span>
                                @else
                                    <span class="text-muted">Chưa cập nhật</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Ngày phát hành:</strong> 
                                {{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Trạng thái:</strong> 
                                @if($movie->status === 'now_showing')
                                    <span class="badge bg-success">Đang chiếu</span>
                                @elseif($movie->status === 'upcoming')
                                    <span class="badge bg-warning text-dark">Sắp chiếu</span>
                                @else
                                    <span class="badge bg-secondary">Đã kết thúc</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    {{-- Thông tin chi tiết --}}
                    <div class="mb-3">
                        <p class="mb-2">
                            <strong>Đạo diễn:</strong> 
                            {{ $movie->director ?? 'Chưa cập nhật' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2">
                            <strong>Diễn viên:</strong> 
                            {{ $movie->cast ?? 'Chưa cập nhật' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <p class="mb-2">
                            <strong>Link Trailer:</strong> 
                            @if($movie->trailer_url)
                                <a href="{{ $movie->trailer_url }}" target="_blank" class="text-primary">{{ $movie->trailer_url }}</a>
                            @else
                                <span class="text-muted">Chưa có</span>
                            @endif
                        </p>
                    </div>

                    <hr>

                    {{-- Tóm tắt nội dung --}}
                    <div class="mb-3">
                        <h5 class="mb-2">Tóm tắt nội dung</h5>
                        <p class="text-muted">
                            {{ $movie->synopsis ?? 'Chưa có thông tin tóm tắt.' }}
                        </p>
                    </div>

                    {{-- Thông tin hệ thống --}}
                    <hr>
                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>ID:</strong> {{ $movie->id }}</p>
                            <p class="mb-1"><strong>Ngày tạo:</strong> {{ $movie->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cập nhật lần cuối:</strong> {{ $movie->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trailer Video Embed (nếu có) --}}
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
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Trailer</h5>
                    </div>
                    <div class="card-body">
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
                                Trình duyệt của bạn không hỗ trợ phát video.
                            </video>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

