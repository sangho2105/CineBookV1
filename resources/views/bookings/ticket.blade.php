<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé Xem Phim - {{ $booking->booking_id_unique }}</title>
    <!-- JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #2c3e50;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            flex-direction: column;
        }

        .ticket {
            background-color: #fff;
            width: 400px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header: Tên Rạp */
        .ticket-header {
            color: white;
            text-align: center;
            padding: 18px 20px;
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .theater-name {
            margin: 0;
            font-weight: bold;
            font-size: 24px;
            letter-spacing: 1px;
        }

        /* Body: Thông tin chi tiết */
        .ticket-body {
            padding: 18px;
        }

        .info-section {
            margin-bottom: 12px;
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        /* Layout 2 cột */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .section-title {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: #333;
            font-size: 18px;
            font-weight: bold;
        }

        .info-value {
            color: #666;
            font-weight: 600;
            font-size: 13px;
            text-align: left;
        }

        .movie-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 12px;
        }

        .seat-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }

        .seat-item {
            background-color: #f0f0f0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #333;
        }

        .seat-item .seat-number {
            font-weight: bold;
            color: #e74c3c;
        }

        .seat-item .seat-category {
            color: #666;
            font-size: 11px;
        }

        /* Phần tổng tiền */
        .price-section {
            background-color: #f9f9f9;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .price-row.total {
            border-top: 2px solid #ddd;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
        }

        .price-label {
            color: #666;
        }

        .price-value {
            color: #333;
            font-weight: 600;
        }

        .discount-amount {
            color: #27ae60;
        }

        /* Footer: Mã đặt chỗ & Barcode */
        .ticket-footer {
            text-align: center;
            background-color: #f9f9f9;
            padding: 15px;
            border-top: 2px dashed #ddd;
        }

        .booking-code {
            font-weight: bold;
            letter-spacing: 2px;
            color: #333;
            font-size: 18px;
            margin-bottom: 12px;
        }

        .booking-label {
            color: #888;
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 6px;
        }

        /* Mã vạch container */
        .barcode-container {
            background-color: #fff;
            text-align: center;
            margin: 12px 0;
            padding: 8px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #barcode {
            max-width: 100%;
            height: auto;
        }

        .barcode-container img {
            max-width: 100%;
            height: auto;
        }
        
        .barcode-container svg {
            max-width: 100%;
            height: auto;
        }

        /* Nút tải về và điều hướng */
        .action-buttons {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
        }

        .download-btn {
            background-color: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s;
            width: 100%;
            text-align: center;
        }

        .download-btn:hover {
            background-color: #5568d3;
            color: white;
        }

        .home-link {
            background-color: #ecf0f1;
            color: #2c3e50;
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s, color 0.3s;
            width: 100%;
            text-align: center;
            border: 1px solid #d0d7de;
        }

        .home-link:hover {
            background-color: #d0d7de;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <!-- Header: Tên Rạp -->
        <div class="ticket-header">
            <h1 class="theater-name">Cinebook Center</h1>
        </div>

        <!-- Body: Thông tin chi tiết -->
        <div class="ticket-body">
            <!-- Tên phim -->
            <div class="info-section">
                <div class="movie-title">{{ $booking->showtime->movie->title ?? 'N/A' }}</div>
            </div>

            <!-- Thông tin chiếu -->
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Phòng: {{ $booking->showtime->room->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày: {{ $booking->showtime->show_date->format('d/m/Y') }} | {{ $ticketInfo['start_time'] }} - {{ $ticketInfo['end_time'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ghế: @foreach($ticketInfo['seat_details'] as $seat)
                            <span class="seat-number">{{ $seat['number'] }}</span><span class="seat-category">({{ $seat['category'] }})</span>@if(!$loop->last), @endif
                        @endforeach</span>
                </div>
            </div>

            <!-- Tổng tiền -->
            <div class="price-section">
                <div class="section-title">Tổng tiền</div>
                <div class="price-row">
                    <span class="price-label">Tiền vé:</span>
                    <span class="price-value">${{ number_format($ticketInfo['ticket_price'], 2, '.', ',') }}</span>
                </div>
                @if($ticketInfo['combo_price'] > 0)
                    <div class="price-row">
                        <span class="price-label">Tiền combo:</span>
                        <span class="price-value">${{ number_format($ticketInfo['combo_price'], 2, '.', ',') }}</span>
                    </div>
                @endif
                @if($ticketInfo['discount_amount'] > 0)
                    <div class="price-row">
                        <span class="price-label">Khuyến mãi:</span>
                        <span class="price-value discount-amount">-${{ number_format($ticketInfo['discount_amount'], 2, '.', ',') }}</span>
                    </div>
                @endif
                @if(isset($ticketInfo['has_gift_promotion']) && $ticketInfo['has_gift_promotion'] === true)
                    <div class="price-row" style="color: #e74c3c; font-weight: bold; font-size: 14px; margin-top: 8px;">
                        *Áp dụng tặng quà
                    </div>
                @endif
                <div class="price-row total">
                    <span class="price-label">Tổng cộng:</span>
                    <span class="price-value">${{ number_format($ticketInfo['total'], 2, '.', ',') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer: Mã đặt chỗ & Mã vạch -->
        <div class="ticket-footer">
            <div class="booking-label">Mã đặt chỗ / Booking Code</div>
            <div class="booking-code">{{ $booking->booking_id_unique }}</div>
            
            <!-- Container cho mã vạch -->
            <div class="barcode-container">
                <svg id="barcode"></svg>
                @if(isset($barcodeBase64))
                    <img id="barcode-fallback" src="data:image/png;base64,{{ $barcodeBase64 }}" alt="Barcode" style="max-width: 100%; height: auto; display: none;">
                @endif
            </div>

            <div class="action-buttons">
                <a href="{{ route('bookings.ticket.download', $booking->id) }}" class="download-btn">
                    Tải vé PDF / Download PDF
                </a>
                <a href="{{ route('home') }}" class="home-link">Về trang chủ / Home</a>
            </div>
        </div>
    </div>

    <script>
        // Tạo mã vạch từ mã đặt chỗ bằng JsBarcode
        document.addEventListener('DOMContentLoaded', function() {
            const bookingCode = '{{ $booking->booking_id_unique }}';
            const barcodeElement = document.getElementById('barcode');
            const fallbackElement = document.getElementById('barcode-fallback');
            
            // Kiểm tra xem JsBarcode có sẵn không
            if (typeof JsBarcode !== 'undefined' && barcodeElement) {
                try {
                    JsBarcode(barcodeElement, bookingCode, {
                        format: "CODE128",
                        width: 2,
                        height: 60,
                        displayValue: true,
                        fontSize: 14,
                        margin: 10,
                        background: "#ffffff",
                        lineColor: "#000000"
                    });
                } catch (error) {
                    console.error('Lỗi tạo mã vạch:', error);
                    // Nếu JsBarcode lỗi, hiển thị fallback
                    if (barcodeElement) barcodeElement.style.display = 'none';
                    if (fallbackElement) fallbackElement.style.display = 'block';
                }
            } else {
                // Nếu JsBarcode không có, hiển thị fallback
                if (barcodeElement) barcodeElement.style.display = 'none';
                if (fallbackElement) fallbackElement.style.display = 'block';
            }
        });
    </script>
</body>

</html>
