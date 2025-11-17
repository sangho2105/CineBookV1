@extends('layouts.admin')

@section('title', 'List Showtimes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>List Showtimes</h2>
    <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary">Add Showtime</a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Movie</th>
                <th>Theater</th>
                <th>Date</th>
                <th>Show Time</th>
                <th>Gold Price</th>
                <th>Platinum Price</th>
                <th>Box Price</th>
                <th>Peak Hour</th>
                <th>Booked Seats</th>
                <th>Combos</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($showtimes as $showtime)
            <tr>
                <td>{{ $showtime->id }}</td>
                <td>{{ $showtime->movie->title }}</td>
                <td>{{ $showtime->theater->name }}</td>
                <td>{{ $showtime->show_date->format('d/m/Y') }}</td>
                <td>{{ date('H:i', strtotime($showtime->show_time)) }}</td>
                <td>{{ number_format($showtime->gold_price, 0, ',', '.') }} đ</td>
                <td>{{ number_format($showtime->platinum_price, 0, ',', '.') }} đ</td>
                <td>{{ number_format($showtime->box_price, 0, ',', '.') }} đ</td>
                <td>{{ $showtime->is_peak_hour ? 'Yes' : 'No' }}</td>
                <td>
                    {{ $showtime->stats['seat_count'] ?? 0 }}
                    @php $cat = $showtime->stats['by_category'] ?? []; @endphp
                    <div class="small text-muted">
                        Gold: {{ $cat['Gold'] ?? 0 }}, Platinum: {{ $cat['Platinum'] ?? 0 }}, Box: {{ $cat['Box'] ?? 0 }}
                    </div>
                </td>
                <td>
                    @php $combos = $showtime->stats['combos'] ?? []; @endphp
                    @if(empty($combos))
                        <span class="text-muted small">0</span>
                    @else
                        <div class="small">
                            @foreach($combos as $name => $qty)
                                <div>{{ $name }}: x{{ $qty }}</div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td>
                    <a href="{{ route('bookings.select-seats', $showtime) }}" class="btn btn-sm btn-success">Đặt vé</a>
                    <a href="{{ route('admin.showtimes.show', $showtime) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('admin.showtimes.edit', $showtime) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.showtimes.destroy', $showtime) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete?')">Delete</button>
                    </form>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No showtimes found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection