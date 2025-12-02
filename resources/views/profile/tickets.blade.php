@extends('layouts.app')

@section('title', 'My Tickets')

@section('content')
<div class="container">
    <h1 class="mb-4">My Tickets</h1>

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-0">
                <strong>Total Tickets:</strong> {{ $bookings->count() ?? 0 }} tickets
            </p>
        </div>
    </div>

    @if(isset($bookings) && $bookings->isNotEmpty())
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Movie</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">E-Ticket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                <tr>
                                    <td>
                                        <strong>{{ $booking->showtime->movie->title ?? '' }}</strong>
                                        <div class="text-muted small">CineBook Center</div>
                                    </td>
                                    <td>
                                        {{ optional($booking->showtime->show_date)->format('d/m/Y') }}
                                        <div class="text-muted small">{{ $booking->showtime->getFormattedShowTime('H:i') }}</div>
                                    </td>
                                    <td>
                                        @if($booking->payment_status === 'completed')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending Payment</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-ticket-perforated"></i> View Ticket
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You haven't made any bookings yet.
        </div>
    @endif
</div>
@endsection

