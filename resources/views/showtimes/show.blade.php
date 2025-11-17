@extends('layouts.admin')

@section('title', 'Showtime Details')

@section('content')
<h2>Showtime Details</h2>

<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">ID:</dt>
            <dd class="col-sm-9">{{ $showtime->id }}</dd>

            <dt class="col-sm-3">Movie:</dt>
            <dd class="col-sm-9">{{ $showtime->movie->title }}</dd>

            <dt class="col-sm-3">Theater:</dt>
            <dd class="col-sm-9">{{ $showtime->theater->name }} - {{ $showtime->theater->city }}</dd>

            <dt class="col-sm-3">Show Date:</dt>    
            <dd class="col-sm-9">{{ $showtime->show_date->format('d/m/Y') }}</dd>

            <dt class="col-sm-3">Show Time:</dt>
            <dd class="col-sm-9">{{ date('H:i', strtotime($showtime->show_time)) }}</dd>

            <dt class="col-sm-3">Gold Price:</dt>
            <dd class="col-sm-9">{{ number_format($showtime->gold_price, 0, ',', '.') }} đ</dd>

            <dt class="col-sm-3">Platinum Price:</dt>
            <dd class="col-sm-9">{{ number_format($showtime->platinum_price, 0, ',', '.') }} đ</dd>

            <dt class="col-sm-3">Box Price:</dt>
            <dd class="col-sm-9">{{ number_format($showtime->box_price, 0, ',', '.') }} đ</dd>

            <dt class="col-sm-3">Peak Hour:</dt>
            <dd class="col-sm-9">{{ $showtime->is_peak_hour ? 'Có' : 'Không' }}</dd>

            <dt class="col-sm-3">Created At:</dt>
            <dd class="col-sm-9">{{ $showtime->created_at->format('d/m/Y H:i:s') }}</dd>

            <dt class="col-sm-3">Updated At:</dt>
            <dd class="col-sm-9">{{ $showtime->updated_at->format('d/m/Y H:i:s') }}</dd>
        </dl>

        <div class="mt-3">
            <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="btn btn-warning">Sửa</a>
            <a href="{{ route('admin.showtimes.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection