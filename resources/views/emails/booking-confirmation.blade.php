<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt vé - CineBook</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
        }
        .header h1 {
            color: #e74c3c;
            margin: 0;
            font-size: 28px;
        }
        .thank-you {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: center;
        }
        .thank-you h2 {
            color: #27ae60;
            margin: 0 0 10px 0;
        }
        .booking-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .booking-info h3 {
            color: #2c3e50;
            margin-top: 0;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
        }
        .info-row {
            margin: 15px 0;
            padding: 10px;
            background-color: #ffffff;
            border-left: 3px solid #e74c3c;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            min-width: 120px;
        }
        .info-value {
            color: #555;
        }
        .booking-code {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            letter-spacing: 2px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #e74c3c;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px;
        }
        .btn:hover {
            background-color: #c0392b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #777;
            font-size: 12px;
        }
        .seat-badge {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            margin: 2px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>CineBook Center</h1>
        </div>

        <div class="thank-you">
            <h2>Cảm ơn quý khách đã đặt vé tại CineBook!</h2>
            <p>Chúng tôi rất vui mừng được phục vụ quý khách. Dưới đây là thông tin vé của quý khách:</p>
        </div>

        <div class="booking-info">
            <h3>Thông tin vé đã đặt</h3>
            
            <div class="info-row">
                <span class="info-label">Mã đặt vé:</span>
                <span class="info-value booking-code">{{ $booking->booking_id_unique }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Phim:</span>
                <span class="info-value">{{ $booking->showtime->movie->title ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Rạp:</span>
                <span class="info-value">CineBook Center</span>
            </div>

            <div class="info-row">
                <span class="info-label">Phòng chiếu:</span>
                <span class="info-value">{{ $booking->showtime->room->name ?? 'N/A' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Ngày chiếu:</span>
                <span class="info-value">{{ $booking->showtime->show_date->format('d/m/Y') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Giờ chiếu:</span>
                <span class="info-value">{{ $ticketInfo['start_time'] }} - {{ $ticketInfo['end_time'] }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Ghế đã đặt:</span>
                <span class="info-value">
                    @foreach($ticketInfo['seat_details'] as $seat)
                        <span class="seat-badge">{{ $seat['number'] }} ({{ $seat['category'] }})</span>
                    @endforeach
                </span>
            </div>

            @if($ticketInfo['combo_price'] > 0)
            <div class="info-row">
                <span class="info-label">Combo:</span>
                <span class="info-value">
                    @foreach($ticketInfo['combo_details'] as $combo)
                        {{ $combo['name'] }} x{{ $combo['quantity'] }}<br>
                    @endforeach
                </span>
            </div>
            @endif

            <div class="info-row">
                <span class="info-label">Tổng tiền:</span>
                <span class="info-value" style="font-size: 18px; font-weight: bold; color: #e74c3c;">
                    ${{ number_format($booking->total_amount, 2, '.', ',') }}
                </span>
            </div>
            
            @if(!empty($ticketInfo['has_gift_promotion']))
            <div class="info-row" style="background-color: #fff3cd; border-left-color: #ffc107;">
                <span class="info-value" style="color: #e74c3c; font-weight: bold; font-size: 16px;">
                    ***Gift Applied
                </span>
            </div>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ route('bookings.ticket', $booking->id) }}" class="btn">
                Xem vé điện tử
            </a>
        </div>

        <div class="footer">
            <p><strong>CineBook Center</strong></p>
            <p>Email này được gửi tự động từ hệ thống đặt vé CineBook.</p>
            <p>Vui lòng không trả lời email này.</p>
            @if($ticketImagePath)
            <p style="margin-top: 15px; color: #27ae60;">
                ✓ Vé PDF đã được đính kèm trong email này.
            </p>
            @endif
        </div>
    </div>
</body>
</html>

