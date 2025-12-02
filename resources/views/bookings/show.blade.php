@extends('layouts.app')

@section('title', 'E-Ticket')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-ticket-perforated"></i> E-Ticket
                </h2>
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Header với mã đặt vé -->
                    <div class="row mb-4 pb-3 border-bottom">
                        <div class="col-md-6">
                            <h4 class="mb-2">{{ $booking->showtime->movie->title ?? '' }}</h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar3"></i> 
                                {{ optional($booking->showtime->show_date)->format('d/m/Y') }} 
                                <i class="bi bi-clock ms-2"></i> 
                                {{ $booking->showtime->getFormattedShowTime('H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="text-muted mb-1 small">Booking ID</p>
                            <h5 class="mb-0">
                                <code class="fs-4">{{ $booking->booking_id_unique }}</code>
                            </h5>
                            <p class="mt-2 mb-0">
                                @if($booking->payment_status === 'completed')
                                    <span class="badge bg-success fs-6">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-6">Pending Payment</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Showtime information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="bi bi-info-circle"></i> Showtime Information
                            </h5>
                            <div class="mb-3">
                                <p class="mb-1 text-muted small">Theater</p>
                                <p class="mb-0 fw-bold">CineBook Center</p>
                                @if($booking->showtime->theater)
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-geo-alt"></i> 
                                        {{ $booking->showtime->theater->address ?? '' }}
                                    </p>
                                @endif
                            </div>
                            @if($booking->showtime->room)
                                <div class="mb-3">
                                    <p class="mb-1 text-muted small">Room</p>
                                    <p class="mb-0 fw-bold">{{ $booking->showtime->room->name ?? 'N/A' }}</p>
                                </div>
                            @endif
                            <div class="mb-3">
                                <p class="mb-1 text-muted small">Date & Time</p>
                                <p class="mb-0 fw-bold">
                                    {{ optional($booking->showtime->show_date)->format('d/m/Y') }} 
                                    @php
                                        $showTimeStr = $booking->showtime->show_time;
                                        if ($showTimeStr instanceof \DateTime) {
                                            $showTimeStr = $showTimeStr->format('H:i:s');
                                        } elseif (is_string($showTimeStr)) {
                                            $showTimeStr = date('H:i:s', strtotime($showTimeStr));
                                        }
                                        $startTime = \Carbon\Carbon::parse($booking->showtime->show_date->format('Y-m-d') . ' ' . $showTimeStr);
                                        $endTime = $startTime->copy()->addMinutes($booking->showtime->movie->duration_minutes ?? 0);
                                    @endphp
                                    {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Seat information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="bi bi-grid-3x3-gap"></i> Booked Seats
                            </h5>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($booking->seats as $seat)
                                    <span class="badge bg-primary fs-6 p-2">
                                        {{ $seat->seat_number }} 
                                        <small>({{ $seat->seat_category }})</small>
                                    </span>
                                @endforeach
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted small">Total Seats</p>
                                <p class="mb-0 fw-bold">{{ $booking->seats->count() }} seats</p>
                            </div>
                        </div>
                    </div>

                    <!-- Combos (if any) -->
                    @if($booking->combos->isNotEmpty())
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="bi bi-bag"></i> Purchased Combos
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Combo Name</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($booking->combos as $combo)
                                                <tr>
                                                    <td>{{ $combo->combo_name }}</td>
                                                    <td class="text-center">{{ $combo->quantity }}</td>
                                                    <td class="text-end">{{ format_currency($combo->unit_price) }}</td>
                                                    <td class="text-end">{{ format_currency($combo->quantity * $combo->unit_price) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Total amount -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-8">
                            <p class="mb-1 text-muted small">
                                <i class="bi bi-calendar-check"></i> Booking Date
                            </p>
                            <p class="mb-0 fw-bold">
                                @if($booking->booking_date)
                                    @php
                                        // Laravel lưu datetime ở UTC trong database, cần convert sang timezone VN
                                        $bookingDate = $booking->booking_date instanceof \Carbon\Carbon 
                                            ? $booking->booking_date->copy() 
                                            : \Carbon\Carbon::parse($booking->booking_date);
                                        // Convert từ UTC (hoặc timezone hiện tại) sang Asia/Ho_Chi_Minh
                                        $bookingDate = $bookingDate->setTimezone('Asia/Ho_Chi_Minh');
                                    @endphp
                                    {{ $bookingDate->format('d/m/Y H:i') }}
                                @else
                                    Unpaid
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="mb-1 text-muted small">Total Amount</p>
                            <h4 class="mb-0 text-primary">
                                {{ format_currency($booking->total_amount) }}
                            </h4>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="alert alert-info mt-4 mb-0">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Notes
                        </h6>
                        <ul class="mb-0 small">
                            <li>Please arrive at the theater 15 minutes early to complete check-in procedures.</li>
                            <li>Bring this booking ID or your ID card for verification when entering the theater.</li>
                            <li>Paid tickets cannot be refunded or exchanged for another showtime.</li>
                        </ul>
                    </div>

                    <!-- Action buttons -->
                    @if($booking->payment_status !== 'completed')
                        <div class="mt-4 text-center">
                            <a href="{{ route('bookings.payment', $booking) }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card"></i> Pay Now
                            </a>
                        </div>
                    @else
                        <div class="mt-4 text-center">
                            <a href="{{ route('bookings.ticket', $booking) }}" class="btn btn-success btn-lg">
                                <i class="bi bi-ticket-perforated"></i> View E-Ticket
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

