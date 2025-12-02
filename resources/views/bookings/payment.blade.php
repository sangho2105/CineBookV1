@extends('layouts.app')

@section('title', 'Payment')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-3">Payment</h2>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ $booking->showtime->movie->title }}</h5>
                    <p class="mb-1"><strong>Room:</strong> {{ $booking->showtime->room ? $booking->showtime->room->name : 'CineBook Center' }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ $booking->showtime->show_date->format('d/m/Y') }}</p>
                    <p class="mb-1"><strong>Time:</strong> {{ $booking->showtime->getFormattedShowTime('H:i') }}</p>
                    <p class="mb-1">
                        <strong>Seats:</strong>
                        @foreach($booking->seats as $seat)
                            <span class="badge bg-secondary me-1">{{ $seat->seat_number }} ({{ $seat->seat_category }})</span>
                        @endforeach
                    </p>
                    <p class="mt-2"><strong>Total Amount:</strong> {{ format_currency($booking->total_amount) }}</p>
                    <p class="mt-2">
                        <strong>Status:</strong>
                        @if($booking->payment_status === 'completed')
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-warning text-dark">Unpaid</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($booking->payment_status !== 'completed')
                <div class="mt-3">
                    @if($errors->has('error'))
                        <div class="alert alert-warning">{{ $errors->first('error') }}</div>
                    @endif
                    {{-- PayPal Smart Buttons --}}
                    <div id="paypal-button-container"></div>
                </div>
                <div class="mt-3">
                    <form action="{{ route('bookings.pay', $booking->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Pay (Simulated)</button>
                    </form>
                </div>
            @else
                <a href="{{ route('movie.show', $booking->showtime->movie->id) }}" class="btn btn-success">Back to Movie</a>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Note</div>
                <div class="card-body">
                    <p>- This is a simulated payment page to complete the process.</p>
                    <p>- After "Pay", the ticket status will change to "Paid".</p>
                    <p>- You can then rate this movie.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($booking->payment_status !== 'completed')
<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID', '') }}&currency=USD"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.paypal) return;
    paypal.Buttons({
        createOrder: function(data, actions) {
            return fetch("{{ route('bookings.paypal.create', $booking->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({})
            }).then(res => res.json())
              .then(data => data.id);
        },
        onApprove: function(data, actions) {
            return fetch("{{ route('bookings.paypal.capture', $booking->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ orderID: data.orderID })
            }).then(res => res.json())
              .then(result => {
                if (result.status === 'COMPLETED') {
                    window.location.href = "{{ route('bookings.ticket', $booking->id) }}";
                } else {
                    alert('Payment not completed. Please try again.');
                }
            }).catch(() => alert('An error occurred while processing payment.'));
        }
    }).render('#paypal-button-container');
});
</script>
@endif
@endpush


