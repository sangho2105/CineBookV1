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
            background-color: #F5F5DC;
        }
        
        html {
            background-color: #F5F5DC;
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
            overflow-x: auto;
        }
        
        /* Container wrapper để tránh biến dạng khi zoom */
        .admin-content-wrapper {
            max-width: 100%;
            width: 100%;
            overflow-x: auto;
            box-sizing: border-box;
        }
        
        .admin-content-wrapper .container-fluid {
            max-width: 100%;
            width: 100%;
            padding-left: 15px;
            padding-right: 15px;
            box-sizing: border-box;
        }
        
        /* Đảm bảo table responsive khi zoom */
        .admin-content-wrapper .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }
        
        .admin-content-wrapper .table {
            min-width: 600px;
            width: 100%;
            table-layout: auto;
        }
        
        /* Đảm bảo form không bị vỡ khi zoom */
        .admin-content-wrapper .row {
            margin-left: -15px;
            margin-right: -15px;
        }
        
        .admin-content-wrapper .row > * {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        /* Đảm bảo các element không bị overflow */
        .admin-content-wrapper img {
            max-width: 100%;
            height: auto;
        }
        
        .admin-content-wrapper .card {
            max-width: 100%;
            box-sizing: border-box;
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
        
        /* Style cho bảng - màu beige phù hợp với nền */
        .content .table {
            background-color: #faf9f5;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .content .table thead,
        .content .table thead.table-dark,
        .content .table thead.table-light {
            background-color: #d4c5a9 !important;
            color: #333 !important;
        }
        
        .content .table thead th,
        .content .table thead.table-dark th,
        .content .table thead.table-light th {
            border-bottom: 2px solid #b8a082 !important;
            padding: 12px 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #333 !important;
            background-color: #d4c5a9 !important;
            white-space: nowrap; /* Ngăn text xuống dòng */
        }
        
        .content .table tbody tr {
            background-color: #faf9f5;
            transition: background-color 0.2s ease;
        }
        
        .content .table.table-striped tbody tr:nth-of-type(odd) {
            background-color: #f5f0e8;
        }
        
        .content .table tbody tr:hover {
            background-color: #ebe5d8;
        }
        
        .content .table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #e8e0d4;
            vertical-align: middle;
        }
        
        .content .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .content .table-responsive {
            border-radius: 8px;
            overflow: hidden;
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
        
        /* CSS cho phân trang nhỏ gọn - đặt sau Bootstrap để override */
        .content .pagination,
        .pagination {
            margin-bottom: 0 !important;
            font-size: 0.8rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        
        .content .pagination .page-link,
        .content .pagination a.page-link,
        .pagination .page-link,
        .pagination a.page-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
            line-height: 1.4 !important;
            min-width: 32px !important;
            text-align: center !important;
            border-radius: 0.25rem !important;
        }
        
        .content .pagination .page-item,
        .pagination .page-item {
            margin: 0 !important;
        }
        
        .content .pagination .page-item.disabled .page-link,
        .content .pagination .page-item.disabled a.page-link,
        .pagination .page-item.disabled .page-link,
        .pagination .page-item.disabled a.page-link {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }
        
        .content .pagination .page-item.active .page-link,
        .content .pagination .page-item.active a.page-link,
        .pagination .page-item.active .page-link,
        .pagination .page-item.active a.page-link {
            z-index: 3 !important;
            color: #fff !important;
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
        }
        
        .content .pagination .page-link:hover:not(.disabled),
        .content .pagination a.page-link:hover:not(.disabled),
        .pagination .page-link:hover:not(.disabled),
        .pagination a.page-link:hover:not(.disabled) {
            background-color: #e9ecef !important;
            border-color: #dee2e6 !important;
        }
        
        /* Đảm bảo các nút Previous/Next cũng được style */
        .content .pagination .page-item:first-child .page-link,
        .content .pagination .page-item:first-child a.page-link,
        .content .pagination .page-item:last-child .page-link,
        .content .pagination .page-item:last-child a.page-link,
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:first-child a.page-link,
        .pagination .page-item:last-child .page-link,
        .pagination .page-item:last-child a.page-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
        }
        
        /* Giới hạn kích thước SVG/icon trong pagination - override tất cả class */
        .content .pagination svg,
        .content .pagination .page-link svg,
        .content .pagination a.page-link svg,
        .content .pagination .page-item svg,
        .content .pagination .page-item:first-child svg,
        .content .pagination .page-item:last-child svg,
        .pagination svg,
        .pagination .page-link svg,
        .pagination a.page-link svg,
        .pagination .page-item svg,
        .pagination .page-item:first-child svg,
        .pagination .page-item:last-child svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
            min-width: 1rem !important;
            min-height: 1rem !important;
            display: inline-block !important;
            vertical-align: middle !important;
            flex-shrink: 0 !important;
        }
        
        /* Override các class Tailwind như w-5, h-5 trong pagination */
        .content .pagination svg.w-5,
        .content .pagination svg.h-5,
        .content .pagination .page-link svg.w-5,
        .content .pagination .page-link svg.h-5,
        .content .pagination a.page-link svg.w-5,
        .content .pagination a.page-link svg.h-5,
        .pagination svg.w-5,
        .pagination svg.h-5,
        .pagination .page-link svg.w-5,
        .pagination .page-link svg.h-5,
        .pagination a.page-link svg.w-5,
        .pagination a.page-link svg.h-5,
        /* Override tất cả SVG trong pagination bất kể class nào */
        .content .pagination svg[class*="w-"],
        .content .pagination svg[class*="h-"],
        .pagination svg[class*="w-"],
        .pagination svg[class*="h-"] {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
        }
        
        /* Đảm bảo các nút Previous/Next có kích thước hợp lý */
        .content .pagination .page-item[rel="prev"] svg,
        .content .pagination .page-item[rel="next"] svg,
        .content .pagination a[rel="prev"] svg,
        .content .pagination a[rel="next"] svg,
        .pagination .page-item[rel="prev"] svg,
        .pagination .page-item[rel="next"] svg,
        .pagination a[rel="prev"] svg,
        .pagination a[rel="next"] svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
        }
        
        /* Responsive cho mobile và tablet */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }
        
        /* Đảm bảo khi zoom không bị vỡ layout */
        @media (max-width: 1200px) {
            .admin-content-wrapper .table {
                font-size: 0.85rem;
            }
            
            .admin-content-wrapper .table th,
            .admin-content-wrapper .table td {
                padding: 8px 10px;
            }
        }
        
        /* Đảm bảo các button và form control responsive */
        .admin-content-wrapper .btn {
            white-space: nowrap;
        }
        
        .admin-content-wrapper .form-control,
        .admin-content-wrapper .form-select {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Đảm bảo flex containers không bị vỡ */
        .admin-content-wrapper .d-flex {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        /* Đảm bảo khi zoom in/out không bị overflow */
        .admin-content-wrapper * {
            max-width: 100%;
            box-sizing: border-box;
        }
    </style>
    @stack('styles')
</head>
<body>

    <div class="sidebar">
        <h3>Admin Panel</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                    🎫 Thống kê
                </a>
            </li>
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
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.combos.*') ? 'active' : '' }}" href="{{ route('admin.combos.index') }}">
                    🍿 Quản lý Combo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}">
                    💬 Quản lý Bình luận
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    👥 Quản lý User
                </a>
            </li>
        </ul>
    </div>

    <div class="content">
        <div class="admin-content-wrapper">
        {{-- 
            Đây là nơi nội dung từ các file con
            (index.blade.php, create.blade.php...) 
            sẽ được chèn vào
        --}}
        @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>