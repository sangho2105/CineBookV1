@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Rooms</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($rooms->count() > 0)
        <div class="row">
            @foreach($rooms as $room)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $room->name }}</h5>
                        <p class="card-text">
                            <strong>Total Seats:</strong> {{ $room->total_seats }} seats<br>
                            <strong>Number of Rows:</strong> {{ count($room->layout) }} rows
                        </p>
                        <div class="d-flex gap-2" style="flex-wrap: nowrap;">
                            <a href="{{ route('admin.rooms.show', $room) }}" class="btn btn-primary btn-sm" style="white-space: nowrap; flex: 0 0 auto;">View Room Layout</a>
                            <a href="{{ route('admin.rooms.schedule', $room) }}" class="btn btn-info btn-sm" style="white-space: nowrap; flex: 0 0 auto;">
                                <i class="bi bi-calendar-event"></i> View Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            No rooms available. Please run seeder to create 6 rooms.
        </div>
    @endif
</div>
@endsection

