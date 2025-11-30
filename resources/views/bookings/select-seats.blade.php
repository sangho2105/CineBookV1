@extends('layouts.app')

@section('title', 'Select Seats')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <h2 class="mb-2">Chọn ghế - {{ $showtime->movie->title }}</h2>
        <p class="mb-1"><strong>Phòng chiếu:</strong> {{ $showtime->room ? $showtime->room->name : ($showtime->theater ? $showtime->theater->name : 'N/A') }}</p>
        <p class="mb-1"><strong>Ngày:</strong> {{ $showtime->show_date->format('d/m/Y') }}</p>
        <p class="mb-3"><strong>Giờ:</strong> {{ $showtime->getFormattedShowTime('H:i') }}</p>

        <div class="screen text-center mb-3">
            <div class="screen-bar">MÀN HÌNH</div>
        </div>

        <form id="seatForm" action="{{ route('bookings.store', $showtime) }}" method="POST"
              data-gold-price="{{ $showtime->gold_price }}"
              data-platinum-price="{{ $showtime->platinum_price }}"
              data-box-price="{{ $showtime->box_price }}">
            @csrf

            <div class="seatmap-wrapper position-relative mb-3">
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
            </div>

            <div class="selected-seats mb-3">
                <h5>Ghế đã chọn:</h5>
                <div id="selectedSeatsList" class="mb-2 text-muted">Chưa chọn ghế nào</div>
                <div id="seatError" class="alert alert-danger d-none mb-2"></div>
                <div class="mb-2">
                    <strong>Tổng tiền: <span id="totalAmount">$0.00</span></strong>
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
                <div class="d-flex align-items-center mb-2"><span class="legend-box seat-regular me-2"></span> Ghế thường (Gold): {{ format_currency($showtime->gold_price) }}</div>
                <div class="d-flex align-items-center mb-2"><span class="legend-box seat-vip me-2"></span> Ghế VIP (Platinum): {{ format_currency($showtime->platinum_price) }}</div>
                <div class="d-flex align-items-center"><span class="legend-box seat-sweet me-2"></span> Ghế cặp đôi (Box): {{ format_currency($showtime->box_price) }}</div>
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

/* Đồng bộ nền cho modal combo */
#comboModal .modal-content {
    background-color: #F5F5DC;
}

#comboModal .modal-header {
    background-color: #F5F5DC;
    border-bottom: 1px solid #e0e0e0;
}

#comboModal .modal-body {
    background-color: #F5F5DC;
}

#comboModal .modal-footer {
    background-color: #F5F5DC;
    border-top: 1px solid #e0e0e0;
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
        totalAmountElement.textContent = '$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        // Update hidden input
        selectedSeatsInput.value = JSON.stringify(selectedSeats.map(s => s.id));
    }
    
    // Modal chọn combo
    const combosInput = document.getElementById('combosInput');
    @php
        // Chuẩn bị dữ liệu combo với URL ảnh đầy đủ
        $combosForJs = collect($combos ?? [])->map(function($combo) {
            return [
                'id' => $combo->id,
                'title' => $combo->title,
                'description' => $combo->description ?? '',
                'price' => (float)$combo->price,
                'image_url' => $combo->image_path ? asset('storage/' . $combo->image_path) : null,
            ];
        })->toArray();
    @endphp
    const combosData = @json($combosForJs ?? []);
    
    console.log('Combos data:', combosData); // Debug
    
    // Xóa modal cũ nếu có (để tránh duplicate)
    const oldModal = document.getElementById('comboModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Tạo HTML cho modal combo động từ database với phân trang
    let comboModalBody = '';
    const combosPerPage = 3;
    let currentComboPage = 1;
    const totalComboPages = combosData && combosData.length > 0 ? Math.ceil(combosData.length / combosPerPage) : 0;
    
    if (combosData && combosData.length > 0) {
        // Tạo container cho combo items
        comboModalBody += '<div id="comboItemsContainer">';
        combosData.forEach((combo, index) => {
            const price = parseFloat(combo.price) || 0;
            const title = (combo.title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            const description = combo.description ? (combo.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') : '';
            const imageHtml = combo.image_url ? `<img src="${combo.image_url}" alt="${title}" class="img-fluid rounded" style="max-width: 100px; max-height: 100px; object-fit: cover;">` : '';
            const pageNumber = Math.floor(index / combosPerPage) + 1;
            const displayStyle = pageNumber === 1 ? '' : 'display: none;';
            
            comboModalBody += `
            <div class="combo-item mb-3 p-3 border rounded" data-combo-page="${pageNumber}" style="${displayStyle}">
                <div class="d-flex align-items-start gap-3">
                    ${imageHtml}
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><strong>${title}</strong></h6>
                        ${description ? `<p class="text-muted small mb-2">${description}</p>` : ''}
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-primary fw-bold">$${price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="small text-muted mb-0">Số lượng:</label>
                                <input type="number" min="0" value="0" 
                                       class="form-control form-control-sm" 
                                       style="width:80px" 
                                       id="combo${combo.id}Qty"
                                       data-combo-id="${combo.id}"
                                       data-combo-price="${combo.price}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        comboModalBody += '</div>';
        
        // Thêm phân trang nếu có nhiều hơn 3 combo
        if (totalComboPages > 1) {
            comboModalBody += `
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-primary" id="prevComboPage" style="display: none;">
                    <i class="bi bi-chevron-left"></i> Trước
                </button>
                <div class="text-muted small">
                    Trang <span id="currentComboPage">1</span> / <span id="totalComboPages">${totalComboPages}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="nextComboPage">
                    Sau <i class="bi bi-chevron-right"></i>
                </button>
            </div>`;
        }
    } else {
        comboModalBody = '<p class="text-muted">Hiện tại chưa có combo nào. Vui lòng liên hệ admin để thêm combo.</p>';
    }
    
    const comboModalHtml = `
    <div class="modal fade" id="comboModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Bạn có muốn chọn thêm combo bắp nước?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            ${comboModalBody}
            <small class="text-muted d-block mt-3">Bạn có thể bỏ qua bước này và thanh toán ngay.</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bỏ qua</button>
            <button type="button" class="btn btn-primary" id="confirmCombosBtn">Xác nhận</button>
          </div>
        </div>
      </div>
    </div>`;
    
    // Tạo modal
    document.body.insertAdjacentHTML('beforeend', comboModalHtml);
    
    // Xử lý phân trang combo
    if (totalComboPages > 1) {
        const prevBtn = document.getElementById('prevComboPage');
        const nextBtn = document.getElementById('nextComboPage');
        const currentPageSpan = document.getElementById('currentComboPage');
        const totalPagesSpan = document.getElementById('totalComboPages');
        let currentPage = 1;
        
        function showComboPage(page) {
            // Ẩn tất cả combo items
            document.querySelectorAll('.combo-item').forEach(item => {
                item.style.display = 'none';
            });
            
            // Hiển thị combo items của trang hiện tại
            document.querySelectorAll(`.combo-item[data-combo-page="${page}"]`).forEach(item => {
                item.style.display = 'block';
            });
            
            // Cập nhật nút điều hướng
            if (prevBtn) prevBtn.style.display = page > 1 ? 'inline-block' : 'none';
            if (nextBtn) nextBtn.style.display = page < totalComboPages ? 'inline-block' : 'none';
            if (currentPageSpan) currentPageSpan.textContent = page;
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    showComboPage(currentPage);
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (currentPage < totalComboPages) {
                    currentPage++;
                    showComboPage(currentPage);
                }
            });
        }
    }

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
        const combos = [];
        
        // Lấy tất cả các input combo từ database
        combosData.forEach(combo => {
            const qtyInput = document.getElementById(`combo${combo.id}Qty`);
            if (qtyInput) {
                const quantity = parseInt(qtyInput.value || '0', 10);
                if (quantity > 0) {
                    combos.push({
                        name: combo.title,
                        quantity: quantity,
                        unit_price: parseFloat(combo.price)
                    });
                }
            }
        });
        
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