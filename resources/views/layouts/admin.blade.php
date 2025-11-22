<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* Thêm một số style đơn giản cho admin */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #343a40;
            color: white;
            padding: 15px;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar h3 {
            color: white;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #495057;
        }
        
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar a.active, .sidebar a:hover {
            color: white;
            background: #495057;
        }
        
        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            width: calc(100% - 250px);
        }
        
        /* Chuẩn hóa font chữ và kích cỡ cho admin */
        .content h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .content h2 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .content h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .content .table {
            font-size: 0.95rem;
        }
        
        .content .table th {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .content .btn {
            font-size: 0.9rem;
        }
        
        .content .form-control {
            font-size: 0.9rem;
        }
        
        .content .alert {
            font-size: 0.9rem;
        }
        
        /* Scrollbar cho sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #343a40;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3>Admin Panel</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.movies.*') ? 'active' : '' }}" href="{{ route('admin.movies.index') }}">
                    📽️ Quản lý Phim
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}">
                    🎭 Quản lý Phòng chiếu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.showtimes.*') ? 'active' : '' }}" href="{{ route('admin.showtimes.index') }}">
                    🕒 Quản lý Suất chiếu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}" href="{{ route('admin.promotions.index') }}">
                    🎉 Khuyến mãi &amp; Sự kiện
                </a>
            </li>
        </ul>
    </div>

    <div class="content">
        {{-- 
            Đây là nơi nội dung từ các file con
            (index.blade.php, create.blade.php...) 
            sẽ được chèn vào
        --}}
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>