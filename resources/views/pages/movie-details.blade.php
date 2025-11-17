@extends('layouts.app')

{{-- Lấy tên phim làm tiêu đề trang --}}
@section('title', $movie->title . ' - CineBook')

@section('content')
<div class="container my-5">
    <div class="row">
        {{-- Cột Poster --}}
        <div class="col-md-4">
            <img src="{{ $movie->poster_url ?? 'https://placehold.co/300x450?text=No+Poster' }}" 
                 class="img-fluid rounded shadow-sm" 
                 alt="{{ $movie->title }}">
        </div>

        {{-- Cột Thông tin --}}
        <div class="col-md-8">
            <h1>{{ $movie->title }}</h1>
            
            {{-- Thông tin cơ bản --}}
            <div class="mb-3">
                <span class="badge bg-primary">{{ $movie->genre }}</span>
                <span class="badge bg-secondary">{{ $movie->language }}</span>
                <span class="badge bg-info text-dark">{{ $movie->duration_minutes }} phút</span>
                <span class="badge bg-warning text-dark">⭐ {{ number_format($ratingAverage ?? $movie->rating_average, 1) }}/5.0 ({{ $ratingCount ?? 0 }})</span>
            </div>
            
            <p class="text-muted" style="font-size: 1.1rem;">
                <strong>Ngày phát hành:</strong> {{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}
            </p>
            
            <p class="text-muted" style="font-size: 1.1rem;">
                <strong>Trạng thái:</strong> 
                @if($movie->status === 'now_showing')
                    <span class="badge bg-success">Đang chiếu</span>
                @elseif($movie->status === 'upcoming')
                    <span class="badge bg-warning text-dark">Sắp chiếu</span>
                @else
                    <span class="badge bg-secondary">Đã kết thúc</span>
                @endif
            </p>

            {{-- Thông tin chi tiết --}}
            <div class="mt-3">
                <p class="text-muted" style="font-size: 1.05rem;">
                    <strong>Đạo diễn:</strong> {{ $movie->director ?? 'Chưa cập nhật' }}
                </p>
                <p class="text-muted" style="font-size: 1.05rem;">
                    <strong>Diễn viên:</strong> {{ $movie->cast ?? 'Chưa cập nhật' }}
                </p>
            </div>

            <h4 class="mt-4">Tóm tắt nội dung</h4>
            <p style="font-size: 1.1rem;">
                {{ $movie->synopsis ?? 'Chưa có thông tin tóm tắt.' }}
            </p>

            {{-- Nút Đặt vé và Trailer --}}
            <div class="mt-4">
                @if($movie->showtimes->isNotEmpty())
                    <a href="{{ route('bookings.select-seats', $movie->showtimes->first()->id) }}" class="btn btn-primary btn-lg">Đặt Vé</a>
                @else
                    <button class="btn btn-primary btn-lg" disabled>Đặt Vé</button>
                @endif
                @if($movie->trailer_url)
                    <a href="{{ $movie->trailer_url }}" class="btn btn-outline-secondary btn-lg" target="_blank">Xem Trailer</a>
                @endif
            </div>

            {{-- Trailer Video Embed --}}
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
                <div class="mt-4">
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
            @endif
        </div>
    </div>
    

    {{-- Bình luận & Đánh giá --}}
    <div class="row mt-5">
        <div class="col-md-8">
            <h4 class="mb-3">Bình luận</h4>
            @auth
                <form action="{{ route('movie.comment.store', $movie->id) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea name="content" id="content" rows="3" class="form-control @error('content') is-invalid @enderror" placeholder="Chia sẻ cảm nhận của bạn về phim...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                </form>
            @else
                <div class="alert alert-info">
                    Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để bình luận.
                </div>
            @endauth

            @forelse($comments as $comment)
                <div class="mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $comment->user->name ?? 'Người dùng' }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        @auth
                            @if(auth()->id() === ($comment->user_id ?? null))
                                <div class="ms-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-comment-{{ $comment->id }}">
                                        Sửa
                                    </button>
                                    <form action="{{ route('movie.comment.delete', [$movie->id, $comment->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa bình luận này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
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
                                    <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#edit-comment-{{ $comment->id }}">Hủy</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            @empty
                <p class="text-muted">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
            @endforelse

            @if(method_exists($comments, 'links'))
                <div class="mt-3">
                    {{ $comments->links() }}
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <h4 class="mb-3">Đánh giá</h4>
            <p class="mb-2">
                <span class="h5 mb-0">⭐ {{ number_format($ratingAverage ?? 0, 1) }}/5.0</span>
                <small class="text-muted">({{ $ratingCount ?? 0 }} lượt)</small>
            </p>

            @auth
                @if($canRate)
                    <form action="{{ route('movie.rating.store', $movie->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <label class="input-group-text" for="score">Chấm điểm</label>
                            <select class="form-select @error('score') is-invalid @enderror" id="score" name="score" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} / 5</option>
                                @endfor
                            </select>
                            <button class="btn btn-success" type="submit">Gửi</button>
                            @error('score')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted d-block mt-2">Bạn có thể cập nhật điểm nếu đổi ý.</small>
                    </form>
                @else
                    <div class="alert alert-warning">
                        Bạn cần hoàn tất thanh toán vé của phim này để có thể chấm điểm.
                    </div>
                @endif
            @else
                <div class="alert alert-info">
                    Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để chấm điểm.
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection 