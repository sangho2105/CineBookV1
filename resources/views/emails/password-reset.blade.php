<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - CineBook</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .password-box {
            background-color: #f0f0f0;
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .password-box .password {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CineBook</h1>
        </div>

        <div class="content">
            <p>Xin chào <strong>{{ $user->name }}</strong>,</p>

            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>

            <p>Mật khẩu mới của bạn là:</p>

            <div class="password-box">
                <div class="password">{{ $newPassword }}</div>
            </div>

            <div class="warning">
                <strong>⚠️ Lưu ý:</strong> Vui lòng đăng nhập và đổi mật khẩu ngay sau khi nhận được email này để đảm bảo an toàn cho tài khoản của bạn.
            </div>

            <p style="text-align: center;">
                <a href="{{ route('login') }}" class="button">Đăng nhập ngay</a>
            </p>

            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi nếu bạn có bất kỳ thắc mắc nào.</p>

            <p>Trân trọng,<br><strong>Đội ngũ CineBook</strong></p>
        </div>

        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời email này.</p>
            <p>&copy; {{ date('Y') }} CineBook. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

