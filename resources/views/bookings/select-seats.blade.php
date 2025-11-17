@extends('layouts.app')

@section('title', 'Select Seats')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <h2 class="mb-2">Select Seats - {{ $showtime->movie->title }}</h2>
        <p class="mb-1"><strong>Theater:</strong> {{ $showtime->theater->name }}</p>
        <p class="mb-1"><strong>Date:</strong> {{ $showtime->show_date->format('d/m/Y') }}</p>
        <p class="mb-3"><strong>Time:</strong> {{ date('H:i', strtotime($showtime->show_time)) }}</p>

        <div class="screen text-center mb-3">
            <div class="screen-bar">MÀN HÌNH</div>
        </div>

        <form id="seatForm" action="{{ route('bookings.store', $showtime) }}" method="POST"
              data-gold-price="{{ $showtime->gold_price }}"
              data-platinum-price="{{ $showtime->platinum_price }}"
              data-box-price="{{ $showtime->box_price }}">
            @csrf

            <div class="seatmap-wrapper position-relative mb-3">
                <div class="center-zone"></div>
                @php
                    $seatsByRow = $seats->groupBy('row_number')->sortKeys();
                @endphp
                @foreach($seatsByRow as $rowLabel => $rowSeats)
                    @php $count = $rowSeats->count(); @endphp
                    <div class="d-flex align-items-center mb-1 seat-row">
                        <div class="row-label">{{ $rowLabel }}</div>
                        <div class="row-seats" style="grid-template-columns: repeat({{ $count }}, 1fr)">
                            @foreach($rowSeats as $seat)
                                @php
                                    $isBooked = in_array($seat->id, $bookedSeatIds);
                                    $categoryClass = $seat->seat_category === 'Platinum' ? 'seat-vip' : ($seat->seat_category === 'Box' ? 'seat-sweet' : 'seat-regular');
                                @endphp
                                <button type="button"
                                        class="seat {{ $categoryClass }} {{ $isBooked ? 'seat-booked' : 'seat-available' }}"
                                        data-seat-id="{{ $seat->id }}"
                                        data-seat-number="{{ $seat->seat_number }}"
                                        data-category="{{ $seat->seat_category }}"
                                        {{ $isBooked ? 'disabled' : '' }}>
                                    {{ str_replace($rowLabel, '', $seat->seat_number) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex align-items-center gap-4 flex-wrap mb-3 legend">
                <div class="legend-item"><span class="legend-box seat-booked"></span> Đã đặt</div>
                <div class="legend-item"><span class="legend-box seat-selected"></span> Ghế bạn chọn</div>
                <div class="legend-item"><span class="legend-box seat-regular"></span> Ghế thường</div>
                <div class="legend-item"><span class="legend-box seat-sweet"></span> Ghế Sweetbox</div>
                <div class="legend-item"><span class="legend-box center-zone-box"></span> Vùng trung tâm</div>
            </div>

            <div class="selected-seats mb-3">
                <h5>Selected Seats:</h5>
                <div id="selectedSeatsList" class="mb-2 text-muted">No seats selected</div>
                <div class="mb-2">
                    <strong>Total Amount: $<span id="totalAmount">0</span></strong>
                </div>
            </div>

            <input type="hidden" name="selected_seats" id="selectedSeatsInput">
            <input type="hidden" name="combos" id="combosInput">

            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Continue to Payment</button>
            <a href="{{ route('movie.show', $showtime->movie->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ticket Price</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-2"><span class="legend-box seat-regular me-2"></span> Gold (Thường): ${{ number_format($showtime->gold_price, 0, ',', '.') }}</div>
                <div class="d-flex align-items-center mb-2"><span class="legend-box seat-vip me-2"></span> Platinum (VIP): ${{ number_format($showtime->platinum_price, 0, ',', '.') }}</div>
                <div class="d-flex align-items-center"><span class="legend-box seat-sweet me-2"></span> Sweetbox: ${{ number_format($showtime->box_price, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<style>
.screen .screen-bar{
    width: 60%;
    margin: 0 auto;
    border-top: 4px solid #cfcfcf;
    color: #6c757d;
    padding-top: 8px;
    font-weight: 600;
    letter-spacing: 2px;
}
.seatmap-wrapper{
    position: relative;
    background: #111;
    border-radius: 12px;
    padding: 16px 12px 20px 12px;
    color: #fff;
    overflow: hidden;
}
.seat-row .row-label{
    width: 36px;
    text-align: center;
    font-weight: 600;
    color: #adb5bd;
}
.seat-row .row-seats{
    display: grid;
    grid-template-columns: repeat(20, 1fr);
    gap: 6px;
    width: 100%;
}
.seat{
    border: none;
    color: #fff;
    font-weight: 600;
    height: 34px;
    border-radius: 6px;
    transition: transform .15s ease, filter .15s ease;
}
.seat-regular{ background:#5b5bd6; }
.seat-vip{ background:#e55353; }
.seat-sweet{ background:#bf3fb9; border-radius:18px; }
.seat-available:hover{ transform: scale(1.06); filter: brightness(1.05); }
.seat-selected{ background:#0d6efd !important; }
.seat-booked{ background:#6c757d !important; cursor:not-allowed; opacity:.7; }

.legend{ color:#333; }
.legend .legend-box{
    display:inline-block; width:24px; height:16px; border-radius:4px; margin-right:6px; vertical-align:middle;
}
.legend .seat-booked{ background:#6c757d; }
.legend .seat-selected{ background:#0d6efd; }
.legend .seat-regular{ background:#5b5bd6; }
.legend .seat-sweet{ background:#bf3fb9; border-radius:12px; }
.center-zone-box{ background:rgba(255,255,255,0.25); }

/* Vùng trung tâm (highlight) */
.seatmap-wrapper .center-zone{
    position:absolute;
    top:10%;
    left:22%;
    width:56%;
    height:78%;
    background: rgba(255,255,255,0.08);
    border-radius:12px;
    pointer-events:none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatForm = document.getElementById('seatForm');
    const seatButtons = document.querySelectorAll('.seat-available.seat');
    const selectedSeatsInput = document.getElementById('selectedSeatsInput');
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    const totalAmountElement = document.getElementById('totalAmount');
    const submitBtn = document.getElementById('submitBtn');
    
    // Get prices from form data attributes
    const prices = {
        'Gold': parseFloat(seatForm.dataset.goldPrice),
        'Platinum': parseFloat(seatForm.dataset.platinumPrice),
        'Box': parseFloat(seatForm.dataset.boxPrice)
    };
    
    let selectedSeats = [];
    
    seatButtons.forEach(button => {
        button.addEventListener('click', function() {
            const seatId = parseInt(this.dataset.seatId);
            const seatNumber = this.dataset.seatNumber;
            const category = this.dataset.category;
            
            const seatIndex = selectedSeats.findIndex(s => s.id === seatId);
            
            if (seatIndex > -1) {
                // Deselect
                selectedSeats.splice(seatIndex, 1);
                this.classList.remove('seat-selected');
                this.classList.add('seat-available');
            } else {
                // Select
                selectedSeats.push({
                    id: seatId,
                    number: seatNumber,
                    category: category
                });
                this.classList.remove('seat-available');
                this.classList.add('seat-selected');
            }
            
            updateSelectedSeats();
        });
    });
    
    function updateSelectedSeats() {
        // Update selected seats list
        if (selectedSeats.length === 0) {
            selectedSeatsList.innerHTML = 'No seats selected';
            submitBtn.disabled = true;
        } else {
            const seatsHtml = selectedSeats.map(seat => 
                `<span class="badge bg-primary me-2">${seat.number} (${seat.category})</span>`
            ).join('');
            selectedSeatsList.innerHTML = seatsHtml;
            submitBtn.disabled = false;
        }
        
        // Calculate total amount
        let total = 0;
        selectedSeats.forEach(seat => {
            total += prices[seat.category];
        });
        totalAmountElement.textContent = total.toLocaleString('en-US');
        
        // Update hidden input
        selectedSeatsInput.value = JSON.stringify(selectedSeats.map(s => s.id));
    }
    
    // Modal chọn combo
    const combosInput = document.getElementById('combosInput');
    const comboModalHtml = `
    <div class="modal fade" id="comboModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Bạn có muốn chọn thêm combo bắp nước?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2 d-flex align-items-center justify-content-between">
              <div>
                <strong>Combo 1:</strong> Bắp + Nước (M) - $5
              </div>
              <input type="number" min="0" value="0" class="form-control form-control-sm" style="width:80px" id="combo1Qty">
            </div>
            <div class="mb-2 d-flex align-items-center justify-content-between">
              <div>
                <strong>Combo 2:</strong> Bắp (L) + 2 Nước (M) - $8
              </div>
              <input type="number" min="0" value="0" class="form-control form-control-sm" style="width:80px" id="combo2Qty">
            </div>
            <small class="text-muted">Bạn có thể bỏ qua bước này và thanh toán ngay.</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bỏ qua</button>
            <button type="button" class="btn btn-primary" id="confirmCombosBtn">Xác nhận</button>
          </div>
        </div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', comboModalHtml);

    const comboModal = new bootstrap.Modal(document.getElementById('comboModal'));
    const confirmCombosBtn = document.getElementById('confirmCombosBtn');

    seatForm.addEventListener('submit', function(e) {
        // Nếu chưa hiển thị modal thì chặn submit để chọn combo
        if (!seatForm.dataset.comboShown) {
            e.preventDefault();
            comboModal.show();
        }
    });

    confirmCombosBtn.addEventListener('click', function() {
        const combo1 = parseInt(document.getElementById('combo1Qty').value || '0', 10);
        const combo2 = parseInt(document.getElementById('combo2Qty').value || '0', 10);
        const combos = [];
        if (combo1 > 0) combos.push({ name: 'Combo 1: Popcorn + Drink (M)', quantity: combo1, unit_price: 5 });
        if (combo2 > 0) combos.push({ name: 'Combo 2: Popcorn (L) + 2 Drinks (M)', quantity: combo2, unit_price: 8 });
        combosInput.value = JSON.stringify(combos);
        // Gắn cờ đã hiển thị modal rồi để submit thực sự
        seatForm.dataset.comboShown = '1';
        seatForm.submit();
    });

    // Initialize
    updateSelectedSeats();
});
</script>
@endsection