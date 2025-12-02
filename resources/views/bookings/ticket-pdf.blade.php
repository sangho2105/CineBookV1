<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Ticket - {{ $booking->booking_id_unique }}</title>
    <style>
        /* Reset và cấu hình cơ bản */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Cấu hình trang PDF - Khổ A8: 52mm x 74mm */
        @page {
            margin: 0;
            padding: 0;
            size: A8 portrait;
        }

        html {
            margin: 0;
            padding: 0;
            width: 52mm;
            height: 74mm;
        }

        body {
            margin: 0;
            padding: 0;
            width: 52mm;
            height: 74mm;
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #ffffff;
            position: relative;
        }

        /* Ngăn chặn ngắt trang */
        .ticket,
        .ticket-header,
        .ticket-body,
        .ticket-footer {
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
        }

        /* Container chính cho vé */
        .ticket {
            width: 52mm;
            height: 74mm;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Header: Tên Rạp */
        .ticket-header {
            background-color: #667eea;
            color: #ffffff;
            text-align: center;
            padding: 1.5mm 1mm;
            margin: 0;
        }

        .theater-name {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 0;
        }

        /* Body: Thông tin chi tiết */
        .ticket-body {
            padding: 1.2mm 1.5mm;
        }

        .info-section {
            margin-bottom: 1mm;
        }

        /* Layout 2 cột */
        .two-columns {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .two-columns .info-section {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 1mm;
        }

        .two-columns .info-section:last-child {
            padding-right: 0;
        }

        .section-title {
            font-size: 5.5px;
            color: #666666;
            text-transform: uppercase;
            margin-bottom: 0.4mm;
            font-weight: 600;
        }

        .movie-title {
            font-size: 7px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 0.8mm;
            word-wrap: break-word;
        }

        .info-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0.6mm;
        }

        .info-label {
            display: table-cell;
            color: #333333;
            font-size: 7px;
            font-weight: bold;
            width: 100%;
            word-wrap: break-word;
        }

        .info-value {
            display: table-cell;
            color: #333333;
            font-weight: 600;
            text-align: right;
            word-wrap: break-word;
        }

        .seat-list {
            font-size: 5.5px;
            line-height: 1.2;
            color: #333333;
        }

        .seat-item {
            display: inline-block;
            margin-right: 0.8mm;
        }

        .seat-number {
            font-weight: bold;
            color: #333333;
        }

        /* Phần tổng tiền */
        .price-section {
            background-color: #f9f9f9;
            padding: 0.8mm;
            border-radius: 2px;
            margin-top: 0.8mm;
        }

        .price-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0.4mm;
            font-size: 5.5px;
        }

        .price-row.total {
            border-top: 1px solid #cccccc;
            padding-top: 0.4mm;
            margin-top: 0.4mm;
            font-size: 6.5px;
            font-weight: bold;
            color: #667eea;
        }

        .price-label {
            display: table-cell;
            color: #666666;
            width: 60%;
        }

        .price-value {
            display: table-cell;
            color: #333333;
            font-weight: 600;
            text-align: right;
        }

        .discount-amount {
            color: #27ae60;
        }

        /* Footer: Mã đặt chỗ và mã vạch */
        .ticket-footer {
            background-color: #f9f9f9;
            text-align: center;
            padding: 1.2mm 1.5mm;
            border-top: 1px dashed #cccccc;
        }

        .booking-label {
            font-size: 5.5px;
            color: #666666;
            text-transform: uppercase;
            margin-bottom: 0.4mm;
        }

        .booking-code {
            font-size: 7px;
            font-weight: bold;
            color: #333333;
            letter-spacing: 0.5px;
            margin-bottom: 0.8mm;
        }

        /* Container mã vạch */
        .barcode-container {
            background-color: #ffffff;
            padding: 0.3mm 0.5mm;
            margin: 0 auto;
            text-align: center;
            width: 100%;
            display: block;
            box-sizing: border-box;
        }

        .barcode-container img {
            max-width: 75%;
            max-height: 9mm;
            height: auto;
            display: block;
            margin: 0 auto;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        
        .barcode-container > div {
            width: 100%;
            box-sizing: border-box;
        }
        
        /* Đảm bảo barcode không bị méo */
        .barcode-container table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .barcode-container td {
            padding: 0;
            margin: 0;
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
                    <span class="info-label">Room:{{ $booking->showtime->room->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date: {{ $booking->showtime->show_date->format('d/m/Y') }} | {{ $ticketInfo['start_time'] }}-{{ $ticketInfo['end_time'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Seats: @foreach($ticketInfo['seat_details'] as $seat)
                            <span class="seat-number">{{ $seat['number'] }}</span><span class="seat-category">({{ $seat['category'] }})</span>@if(!$loop->last), @endif
                        @endforeach</span>
                </div>
            </div>

            <!-- Total amount -->
            <div class="price-section">
                <div class="section-title">Total Amount</div>
                <div class="price-row">
                    <span class="price-label">Ticket:</span>
                    <span class="price-value">${{ number_format($ticketInfo['ticket_price'], 2, '.', ',') }}</span>
                </div>
                @if($ticketInfo['combo_price'] > 0)
                    <div class="price-row">
                        <span class="price-label">Combo:</span>
                        <span class="price-value">${{ number_format($ticketInfo['combo_price'], 2, '.', ',') }}</span>
                    </div>
                @endif
                @if($ticketInfo['discount_amount'] > 0)
                    <div class="price-row">
                        <span class="price-label">Promo:</span>
                        <span class="price-value discount-amount">-${{ number_format($ticketInfo['discount_amount'], 2, '.', ',') }}</span>
                    </div>
                @endif
                @if(isset($ticketInfo['has_gift_promotion']) && $ticketInfo['has_gift_promotion'] === true)
                    <div class="price-row" style="color: #e74c3c; font-weight: bold; font-size: 8px; margin-top: 2mm;">
                        *Gift Applied
                    </div>
                @endif
                <div class="price-row total">
                    <span class="price-label">Total:</span>
                    <span class="price-value">${{ number_format($ticketInfo['total'], 2, '.', ',') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer: Mã đặt chỗ & Mã vạch -->
        <div class="ticket-footer">
            <div class="booking-label">Booking Code</div>
            <div class="booking-code">{{ $booking->booking_id_unique }}</div>
            
            @if(isset($barcodeHtml) && !empty($barcodeHtml))
                <div class="barcode-container">
                    {!! $barcodeHtml !!}
                </div>
            @elseif(isset($barcodeBase64) && !empty($barcodeBase64))
                <div class="barcode-container" style="text-align: center;">
                    <img src="data:image/png;base64,{{ $barcodeBase64 }}" 
                         alt="Barcode {{ $booking->booking_id_unique }}" 
                         style="max-width: 70%; max-height: 10mm; height: auto; display: inline-block; vertical-align: middle;">
                </div>
            @else
                <div class="barcode-container" style="font-size: 5px; color: #333; padding: 1mm 0; text-align: center;">
                    {{ $booking->booking_id_unique }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>

