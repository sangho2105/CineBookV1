@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Confirm Booking</h4>
                </div>
                <div class="card-body">
                    {{-- Movie and showtime information --}}
                    <div class="mb-4">
                        <h5 class="mb-3">Ticket Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Movie:</strong> {{ $showtime->movie->title }}</p>
                                <p><strong>Theater:</strong> CineBook Center</p>
                                <p><strong>Room:</strong> {{ $showtime->room->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Show Date:</strong> {{ $showtime->show_date->format('d/m/Y') }}</p>
                                <p><strong>Show Time:</strong> {{ $showtime->getFormattedShowTime('H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Seat information --}}
                    <div class="mb-4">
                        <h5 class="mb-3">Selected Seats</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Seat</th>
                                        <th>Type</th>
                                        <th class="text-end">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seatDetails as $detail)
                                        <tr>
                                            <td>{{ $detail['seat']->seat_number }}</td>
                                            <td>
                                                @if($detail['seat']->seat_category == 'Gold')
                                                    Regular Seat (Gold)
                                                @elseif($detail['seat']->seat_category == 'Platinum')
                                                    VIP Seat (Platinum)
                                                @else
                                                    Couple Seat (Box)
                                                @endif
                                            </td>
                                            <td class="text-end">${{ number_format($detail['price'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Total Ticket Price:</th>
                                        <th class="text-end">${{ number_format($ticketPrice, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Combo information --}}
                    @if(!empty($comboDetails))
                    <div class="mb-4">
                        <h5 class="mb-3">Selected Combos</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Combo</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comboDetails as $combo)
                                        <tr>
                                            <td>{{ $combo['name'] }}</td>
                                            <td>{{ $combo['quantity'] }}</td>
                                            <td class="text-end">${{ number_format($combo['unit_price'], 2) }}</td>
                                            <td class="text-end">${{ number_format($combo['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total Combo Price:</th>
                                        <th class="text-end">${{ number_format($comboPrice, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Promotions --}}
                    @if(!empty($promotionDetails) || $hasGiftPromotion)
                    <div class="mb-4">
                        <h5 class="mb-3">Promotions</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach($promotionDetails as $promo)
                                        <tr>
                                            <td>
                                                <strong>{{ $promo['name'] }}</strong>
                                                @if($promo['type'] == 'gift')
                                                    <span class="badge bg-success ms-2">Gift</span>
                                                @elseif($promo['type'] == 'discount_gift')
                                                    <span class="badge bg-primary ms-2">Discount {{ $promo['percentage'] }}%</span>
                                                    <span class="badge bg-success ms-2">Gift</span>
                                                @else
                                                    <span class="badge bg-primary ms-2">Discount {{ $promo['percentage'] }}%</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($promo['type'] == 'discount' || $promo['type'] == 'discount_gift')
                                                    -${{ number_format($promo['discount'], 2) }}
                                                @else
                                                    Free
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if($discountAmount > 0)
                                <tfoot>
                                    <tr>
                                        <th>Total Discount:</th>
                                        <th class="text-end text-danger">-${{ number_format($discountAmount, 2) }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Total amount --}}
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Total Amount to Pay:</h4>
                            <h4 class="mb-0 text-primary">${{ number_format($finalTotal, 2) }}</h4>
                        </div>
                    </div>

                    {{-- Confirmation form --}}
                    <form action="{{ route('bookings.store', $showtime) }}" method="POST">
                        @csrf
                        <input type="hidden" name="selected_seats" value="{{ json_encode($selectedSeatIds) }}">
                        @if(!empty($comboDetails))
                            <input type="hidden" name="combos" value="{{ json_encode(array_map(function($c) {
                                return [
                                    'name' => $c['name'],
                                    'quantity' => $c['quantity'],
                                    'unit_price' => $c['unit_price']
                                ];
                            }, $comboDetails)) }}">
                        @endif
                        @if(!empty($promotionInfo))
                            <input type="hidden" name="promotion_info" value="{{ json_encode([
                                'applied_promotion_id' => $promotionInfo['best_promotion']->id ?? null,
                                'has_gift_promotion' => $promotionInfo['has_gift_promotion'] ?? false,
                                'discount_amount' => $promotionInfo['discount_amount'] ?? 0,
                                'promotion_details' => $promotionInfo['promotion_details'] ?? []
                            ]) }}">
                        @endif
                        
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('bookings.select-seats', $showtime) }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary btn-lg">Confirm and Pay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

