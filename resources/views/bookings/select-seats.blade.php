@extends('layouts.app')

@section('title', 'Select Seats')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <h2 class="mb-2">Chọn ghế - {{ $showtime->movie->title }}</h2>
        <p class="mb-1"><strong>Phòng chiếu:</strong> {{ $showtime->room ? $showtime->room->name : ($showtime->theater ? $showtime->theater->name : 'N/A') }}</p>
        <p class="mb-1"><strong>Ngày:</strong> {{ $showtime->show_date->format('d/m/Y') }}</p>
        <p class="mb-3"><strong>Giờ:</strong> {{ date('H:i', strtotime($showtime->show_time)) }}</p>

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
                    @php 
                        $count = $rowSeats->count();
                        $isCoupleRow = in_array($rowLabel, $coupleRows ?? []);
                        // Nếu là hàng couple, mỗi ghế chiếm 2 cột
                        $gridCols = $isCoupleRow ? $count * 2 : $count;
                    @endphp
                    <div class="d-flex align-items-center mb-1 seat-row">
                        <div class="row-label">{{ $rowLabel }}</div>
                        <div class="row-seats" style="grid-template-columns: repeat({{ $gridCols }}, 1fr)">
                            @foreach($rowSeats as $seat)
                                @php
                                    $isBooked = in_array($seat->id, $bookedSeatIds);
                                    $categoryClass = $seat->seat_category === 'Platinum' ? 'seat-vip' : ($seat->seat_category === 'Box' ? 'seat-sweet' : 'seat-regular');
                                @endphp
                                <button type="button"
                                        class="seat {{ $categoryClass }} {{ $isBooked ? 'seat-booked' : 'seat-available' }} {{ $isCoupleRow ? 'seat-couple' : '' }}"
                                        data-seat-id="{{ $seat->id }}"
                                        data-seat-number="{{ $seat->seat_number }}"
                                        data-row="{{ $rowLabel }}"
                                        data-seat-index="{{ $loop->index }}"
                                        data-category="{{ $seat->seat_category }}"
                                        data-is-couple-row="{{ $isCoupleRow ? '1' : '0' }}"
                                        style="{{ $isCoupleRow ? 'grid-column: span 2;' : '' }}"
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
                <div class="legend-item"><span class="legend-box seat-regular"></span> Ghế thường (Gold)</div>
                <div class="legend-item"><span class="legend-box seat-vip"></span> Ghế VIP (Platinum)</div>
                <div class="legend-item"><span class="legend-box seat-sweet"></span> Ghế cặp đôi (Box)</div>
                <div class="legend-item"><span class="legend-box center-zone-box"></span> Vùng trung tâm</div>
            </div>

            <div class="selected-seats mb-3">
                <h5>Ghế đã chọn:</h5>
                <div id="selectedSeatsList" class="mb-2 text-muted">Chưa chọn ghế nào</div>
                <div id="seatError" class="alert alert-danger d-none mb-2"></div>
                <div class="mb-2">
                    <strong>Tổng tiền: <span id="totalAmount">0</span> đ</strong>
                </div>
            </div>

            <input type="hidden" name="selected_seats" id="selectedSeatsInput">
            <input type="hidden" name="combos" id="combosInput">

            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Tiếp tục thanh toán</button>
            <a href="{{ route('movie.show', $showtime->movie->id) }}" class="btn btn-secondary">Hủy</a>
        </form>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Giá vé</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-2"><span class="legend-box seat-regular me-2"></span> Ghế thường (Gold): {{ number_format($showtime->gold_price, 0, ',', '.') }} đ</div>
                <div class="d-flex align-items-center mb-2"><span class="legend-box seat-vip me-2"></span> Ghế VIP (Platinum): {{ number_format($showtime->platinum_price, 0, ',', '.') }} đ</div>
                <div class="d-flex align-items-center"><span class="legend-box seat-sweet me-2"></span> Ghế cặp đôi (Box): {{ number_format($showtime->box_price, 0, ',', '.') }} đ</div>
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
.seat-couple{ 
    grid-column: span 2 !important;
    min-width: 68px;
}
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
    const seatErrorDiv = document.getElementById('seatError');
    
    // Lấy tất cả ghế theo hàng để kiểm tra
    const seatsByRow = {};
    seatButtons.forEach(button => {
        const row = button.dataset.row;
        if (!seatsByRow[row]) {
            seatsByRow[row] = [];
        }
        seatsByRow[row].push({
            button: button,
            id: parseInt(button.dataset.seatId),
            number: button.dataset.seatNumber,
            index: parseInt(button.dataset.seatIndex),
            row: row,
            category: button.dataset.category,
            isBooked: button.classList.contains('seat-booked'),
            isCoupleRow: button.dataset.isCoupleRow === '1'
        });
    });
    
    // Sắp xếp ghế trong mỗi hàng theo index
    Object.keys(seatsByRow).forEach(row => {
        seatsByRow[row].sort((a, b) => a.index - b.index);
    });
    
    // Hàm kiểm tra ghế lẻ - kiểm tra tất cả ghế đã chọn
    function checkLonelySeats() {
        const lonelySeats = {
            left: [],    // Ghế lẻ bên trái
            right: [],   // Ghế lẻ bên phải
            middle: []   // Ghế lẻ ở giữa
        };
        
        // Kiểm tra từng hàng
        Object.keys(seatsByRow).forEach(row => {
            const seatsInRow = seatsByRow[row];
            
            // Kiểm tra xem hàng này có phải là hàng ghế đôi không
            const firstSeatInRow = seatsInRow[0];
            if (firstSeatInRow && firstSeatInRow.isCoupleRow) {
                // Bỏ qua logic kiểm tra ghế lẻ cho hàng ghế đôi
                return;
            }
            
            const selectedSeatsInRow = selectedSeats.filter(s => {
                const seatBtn = Array.from(seatButtons).find(btn => parseInt(btn.dataset.seatId) === s.id);
                return seatBtn && seatBtn.dataset.row === row;
            }).map(s => {
                const seatBtn = Array.from(seatButtons).find(btn => parseInt(btn.dataset.seatId) === s.id);
                return {
                    id: s.id,
                    number: s.number,
                    index: parseInt(seatBtn.dataset.seatIndex)
                };
            });
            
            if (selectedSeatsInRow.length === 0) {
                return; // Không có ghế nào được chọn trong hàng này
            }
            
            // Lấy danh sách index của ghế đã chọn và sắp xếp
            const selectedIndices = selectedSeatsInRow.map(s => s.index).sort((a, b) => a - b);
            
            if (selectedIndices.length === 0) {
                return;
            }
            
            // Tìm min và max index của ghế đã chọn
            const minSelectedIndex = selectedIndices[0];
            const maxSelectedIndex = selectedIndices[selectedIndices.length - 1];
            
            // Lấy số ghế đầu tiên và cuối cùng đã chọn để hiển thị trong thông báo
            const firstSelectedSeat = selectedSeatsInRow.find(s => s.index === minSelectedIndex);
            const lastSelectedSeat = selectedSeatsInRow.find(s => s.index === maxSelectedIndex);
            
            // Tìm index của ghế đầu tiên và cuối cùng trong hàng (không tính ghế đã đặt)
            const availableSeatsInRow = seatsInRow.filter(s => !s.isBooked);
            if (availableSeatsInRow.length === 0) {
                return;
            }
            const firstSeatIndex = Math.min(...availableSeatsInRow.map(s => s.index));
            const lastSeatIndex = Math.max(...availableSeatsInRow.map(s => s.index));
            
            // Kiểm tra từng ghế trống trong hàng
            for (let i = 0; i < seatsInRow.length; i++) {
                const seat = seatsInRow[i];
                // Bỏ qua ghế đã được chọn hoặc đã bị đặt
                if (selectedIndices.includes(seat.index) || seat.isBooked) {
                    continue;
                }
                
                // Kiểm tra 3 trường hợp ghế lẻ:
                
                // 1. Ghế bị kẹp giữa 2 ghế đã chọn
                const leftSelected = selectedIndices.filter(idx => idx < seat.index);
                const rightSelected = selectedIndices.filter(idx => idx > seat.index);
                if (leftSelected.length > 0 && rightSelected.length > 0) {
                    // Có ghế đã chọn ở cả 2 bên -> đây là ghế lẻ ở giữa
                    lonelySeats.middle.push({
                        seat: seat.number,
                        firstSelected: firstSelectedSeat ? firstSelectedSeat.number : '',
                        lastSelected: lastSelectedSeat ? lastSelectedSeat.number : ''
                    });
                    continue;
                }
                
                // 2. Ghế trống ngay bên trái của ghế đã chọn đầu tiên
                // CHỈ áp dụng nếu ghế đó là ghế đầu tiên của hàng
                // Ví dụ: A1 trống, A2 đã chọn -> A1 là ghế lẻ (vì A1 là ghế đầu)
                if (seat.index === minSelectedIndex - 1 && seat.index === firstSeatIndex) {
                    lonelySeats.left.push({
                        seat: seat.number,
                        selected: firstSelectedSeat ? firstSelectedSeat.number : ''
                    });
                    continue;
                }
                
                // 3. Ghế trống ngay bên phải của ghế đã chọn cuối cùng
                // CHỈ áp dụng nếu ghế đó là ghế cuối cùng của hàng
                // Ví dụ: A5 đã chọn, A6 trống -> A6 là ghế lẻ (vì A6 là ghế cuối)
                if (seat.index === maxSelectedIndex + 1 && seat.index === lastSeatIndex) {
                    lonelySeats.right.push({
                        seat: seat.number,
                        selected: lastSelectedSeat ? lastSelectedSeat.number : ''
                    });
                    continue;
                }
            }
        });
        
        return lonelySeats;
    }
    
    seatButtons.forEach(button => {
        button.addEventListener('click', function() {
            const seatId = parseInt(this.dataset.seatId);
            const seatNumber = this.dataset.seatNumber;
            const category = this.dataset.category;
            const row = this.dataset.row;
            const seatIndex = parseInt(this.dataset.seatIndex);
            
            const seatIndexInArray = selectedSeats.findIndex(s => s.id === seatId);
            
            if (seatIndexInArray > -1) {
                // Deselect
                selectedSeats.splice(seatIndexInArray, 1);
                this.classList.remove('seat-selected');
                this.classList.add('seat-available');
            } else {
                // Select - Cho phép chọn tự do
                selectedSeats.push({
                    id: seatId,
                    number: seatNumber,
                    category: category,
                    row: row,
                    index: seatIndex
                });
                this.classList.remove('seat-available');
                this.classList.add('seat-selected');
            }
            
            // Ẩn lỗi khi chọn/bỏ chọn ghế
            seatErrorDiv.classList.add('d-none');
            updateSelectedSeats();
        });
    });
    
    function updateSelectedSeats() {
        // Update selected seats list
        if (selectedSeats.length === 0) {
            selectedSeatsList.innerHTML = 'Chưa chọn ghế nào';
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
        totalAmountElement.textContent = total.toLocaleString('vi-VN');
        
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
        e.preventDefault();
        
        // Kiểm tra ghế lẻ trước
        const lonelySeats = checkLonelySeats();
        let errorMessages = [];
        
        // Kiểm tra ghế lẻ bên trái
        if (lonelySeats.left.length > 0) {
            lonelySeats.left.forEach(item => {
                errorMessages.push(`Không thể để trống ghế "${item.seat}" bên trái ghế "${item.selected}" bạn đã chọn.`);
            });
        }
        
        // Kiểm tra ghế lẻ bên phải
        if (lonelySeats.right.length > 0) {
            lonelySeats.right.forEach(item => {
                errorMessages.push(`Không thể để trống ghế "${item.seat}" bên phải ghế "${item.selected}" bạn đã chọn.`);
            });
        }
        
        // Kiểm tra ghế lẻ ở giữa
        if (lonelySeats.middle.length > 0) {
            lonelySeats.middle.forEach(item => {
                errorMessages.push(`Bạn không thể để trống ghế "${item.seat}" ở giữa các ghế bạn đã chọn.`);
            });
        }
        
        if (errorMessages.length > 0) {
            // Có ghế lẻ, hiển thị lỗi
            seatErrorDiv.innerHTML = '<strong>Lỗi chọn ghế:</strong><br>' + errorMessages.join('<br>');
            seatErrorDiv.classList.remove('d-none');
            // Scroll đến phần lỗi
            seatErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        
        // Không có ghế lẻ, ẩn lỗi
        seatErrorDiv.classList.add('d-none');
        
        // Nếu chưa hiển thị modal thì chặn submit để chọn combo
        if (!seatForm.dataset.comboShown) {
            comboModal.show();
        } else {
            // Đã chọn combo rồi, submit form
            seatForm.submit();
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