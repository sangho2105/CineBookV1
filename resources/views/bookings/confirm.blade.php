@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Xác nhận đặt vé</h4>
                </div>
                <div class="card-body">
                    {{-- Thông tin phim và suất chiếu --}}
                    <div class="mb-4">
                        <h5 class="mb-3">Thông tin vé</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Phim:</strong> {{ $showtime->movie->title }}</p>
                                <p><strong>Rạp:</strong> CineBook Center</p>
                                <p><strong>Phòng:</strong> {{ $showtime->room->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ngày chiếu:</strong> {{ $showtime->show_date->format('d/m/Y') }}</p>
                                <p><strong>Giờ chiếu:</strong> {{ $showtime->getFormattedShowTime('H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Thông tin ghế --}}
                    <div class="mb-4">
                        <h5 class="mb-3">Ghế đã chọn</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Ghế</th>
                                        <th>Loại</th>
                                        <th class="text-end">Giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seatDetails as $detail)
                                        <tr>
                                            <td>{{ $detail['seat']->seat_number }}</td>
                                            <td>
                                                @if($detail['seat']->seat_category == 'Gold')
                                                    Ghế thường (Gold)
                                                @elseif($detail['seat']->seat_category == 'Platinum')
                                                    Ghế VIP (Platinum)
                                                @else
                                                    Ghế cặp đôi (Box)
                                                @endif
                                            </td>
                                            <td class="text-end">${{ number_format($detail['price'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Tổng tiền vé:</th>
                                        <th class="text-end">${{ number_format($ticketPrice, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Thông tin combo --}}
                    @if(!empty($comboDetails))
                    <div class="mb-4">
                        <h5 class="mb-3">Combo đã chọn</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Combo</th>
                                        <th>Số lượng</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Thành tiền</th>
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
                                        <th colspan="3" class="text-end">Tổng tiền combo:</th>
                                        <th class="text-end">${{ number_format($comboPrice, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Khuyến mãi --}}
                    @if(!empty($promotionDetails) || $hasGiftPromotion)
                    <div class="mb-4">
                        <h5 class="mb-3">Khuyến mãi</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach($promotionDetails as $promo)
                                        <tr>
                                            <td>
                                                <strong>{{ $promo['name'] }}</strong>
                                                @if($promo['type'] == 'gift')
                                                    <span class="badge bg-success ms-2">Tặng quà</span>
                                                @else
                                                    <span class="badge bg-primary ms-2">Giảm {{ $promo['percentage'] }}%</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($promo['type'] == 'discount')
                                                    -${{ number_format($promo['discount'], 2) }}
                                                @else
                                                    Miễn phí
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if($discountAmount > 0)
                                <tfoot>
                                    <tr>
                                        <th>Tổng giảm giá:</th>
                                        <th class="text-end text-danger">-${{ number_format($discountAmount, 2) }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Tổng tiền --}}
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Tổng tiền phải thanh toán:</h4>
                            <h4 class="mb-0 text-primary">${{ number_format($finalTotal, 2) }}</h4>
                        </div>
                    </div>

                    {{-- Form xác nhận --}}
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
                        
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('bookings.select-seats', $showtime) }}" class="btn btn-secondary">Quay lại</a>
                            <button type="submit" class="btn btn-primary btn-lg">Xác nhận và thanh toán</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

