<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CineBook')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    @stack('css')
    <style>
        /* Màu nền beige/ivory nhạt cho toàn bộ trang web */
        body {
            background-color: #F5F5DC !important;
            min-height: 100vh;
        }
        
        html {
            background-color: #F5F5DC;
        }
        
        .dropdown-item form {
            margin: 0;
        }
        .dropdown-item form button {
            background: none;
            border: none;
            padding: 0;
            width: 100%;
            text-align: left;
            color: inherit;
            cursor: pointer;
        }
        .dropdown-item form button:hover {
            background: none;
        }
        .dropdown-item i {
            margin-right: 8px;
        }
        
        /* Navbar sticky/fixed */
        .navbar.sticky-top {
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            background-color: rgba(33, 37, 41, 0.98) !important;
            backdrop-filter: blur(10px);
        }
        
        /* Dropdown hiển thị khi hover */
        .navbar-nav .nav-item.dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
        
        .navbar-nav .nav-item.dropdown .dropdown-menu {
            margin-top: 0;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Modal backdrop tối hơn */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.85) !important;
            opacity: 1 !important;
        }
        
        .modal-backdrop.show {
            opacity: 1 !important;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">CineBook</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="moviesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-film"></i> Movies
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="moviesDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('search', ['status' => 'now_showing']) }}">
                                    <i class="bi bi-camera-reels"></i> Now Showing
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('search', ['status' => 'upcoming']) }}">
                                    <i class="bi bi-calendar-event"></i> Coming Soon
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('promotions.index') }}">
                            <i class="bi bi-gift"></i> Promotions &amp; Events
                        </a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.tickets') }}">
                            <i class="bi bi-ticket-perforated"></i> My Tickets
                        </a>
                    </li>
                    
                    
                    {{-- User dropdown menu --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="bi bi-person"></i> My Profile
                                </a>
                            </li>
                            @if(Auth::user()->role === 'admin')
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.movies.index') }}">
                                    <i class="bi bi-speedometer2"></i> Admin Panel
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Select Showtime</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookingModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #bookingModal .modal-content {
            background-color: #F5F5DC;
        }
        
        #bookingModal .modal-header {
            background-color: #F5F5DC;
            border-bottom: 1px solid #e0e0e0;
        }
        
        #bookingModal .modal-body {
            background-color: #F5F5DC;
        }
        
        .booking-modal-content .booking-header {
            background: #F5F5DC;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .booking-modal-content .movie-info {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            justify-content: flex-start;
            width: 100%;
        }

        .booking-modal-content .movie-poster {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }

        .booking-modal-content .movie-info > div {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 8px;
        }

        .booking-modal-content .movie-title {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
            color: #333;
            line-height: 1.3;
            text-align: left !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            width: 100%;
        }

        .booking-modal-content .movie-info .text-muted {
            margin: 0;
            text-align: left !important;
        }

        .booking-modal-content .date-selector {
            background: #F5F5DC;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .booking-modal-content .date-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 8px;
            margin-top: 15px;
        }

        .booking-modal-content .date-item {
            text-align: center;
            padding: 10px 6px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: #fff;
            font-size: 14px;
        }

        .booking-modal-content .date-item:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .booking-modal-content .date-item.active {
            border-color: #000;
            background: #000;
            color: #fff;
        }

        .booking-modal-content .date-day {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }

        .booking-modal-content .date-item.active .date-day {
            color: #fff;
        }

        .booking-modal-content .date-number {
            font-size: 16px;
            font-weight: bold;
        }

        .booking-modal-content .showtime-section {
            background: #F5F5DC;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .booking-modal-content .room-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .booking-modal-content .showtime-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .booking-modal-content .showtime-btn {
            padding: 10px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            color: #333;
            display: inline-block;
        }

        .booking-modal-content .showtime-btn:hover {
            border-color: #007bff;
            background: #f0f8ff;
            color: #007bff;
            text-decoration: none;
        }

        .booking-modal-content .no-showtimes {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý navbar sticky khi scroll
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('mainNavbar');
            
            if (navbar) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                });
            }
        });
        
        // Xử lý mở modal đặt vé
        document.addEventListener('DOMContentLoaded', function() {
            const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
            const modalBody = document.getElementById('bookingModalBody');
            
            // Lắng nghe sự kiện click trên các nút "Đặt Vé"
            document.addEventListener('click', function(e) {
                const bookBtn = e.target.closest('[data-booking-movie-id]');
                if (bookBtn) {
                    e.preventDefault();
                    const movieId = bookBtn.getAttribute('data-booking-movie-id');
                    loadBookingModal(movieId);
                }
            });
            
            // Load nội dung modal
            function loadBookingModal(movieId, selectedDate = null) {
                modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                bookingModal.show();
                
                let url = `/movie/${movieId}/book/modal`;
                if (selectedDate) {
                    url += `?date=${selectedDate}`;
                }
                
                fetch(url)
                    .then(response => {
                        // Kiểm tra nếu response redirect đến trang login (status 302 hoặc response là HTML của trang login)
                        if (response.redirected || response.status === 401 || response.status === 403) {
                            // Chuyển hướng đến trang login và lưu lại URL hiện tại
                            window.location.href = '{{ route("login") }}';
                            return;
                        }
                        return response.text();
                    })
                    .then(html => {
                        if (!html) return; // Nếu đã redirect thì không xử lý
                        
                        // Kiểm tra xem response có phải là trang login không (kiểm tra nội dung HTML)
                        if (html.includes('login') && (html.includes('Login') || html.includes('Đăng nhập'))) {
                            window.location.href = '{{ route("login") }}';
                            return;
                        }
                        
                        modalBody.innerHTML = html;
                        
                        // Xử lý click vào ngày
                        modalBody.querySelectorAll('.date-item').forEach(item => {
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                const date = this.getAttribute('data-date');
                                const movieId = this.getAttribute('data-movie-id');
                                loadBookingModal(movieId, date);
                            });
                        });
                    })
                    .catch(error => {
                        modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading data. Please try again.</div>';
                    });
            }
        });
    </script>
    @stack('scripts')

    <footer class="bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <!-- About CineBook -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-film"></i> CineBook
                    </h5>
                    <p class="text-light">
                        Vietnam's leading online movie ticket booking system. 
                        Experience great cinema with modern technology and professional service.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white" style="font-size: 1.5rem;" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="text-white" style="font-size: 1.5rem;" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="text-white" style="font-size: 1.5rem;" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="text-white" style="font-size: 1.5rem;" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                    <p class="text-light mt-3 mb-0">
                        &copy; {{ date('Y') }} CineBook. All Rights Reserved.
                    </p>
                </div>

                <!-- Our Team -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-people"></i> Our Team
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="text-light">
                                <i class="bi bi-person"></i> Nguyen Cao Tien
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="text-light">
                                <i class="bi bi-person"></i> Nguyen Hoang Sang
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-headset"></i> Support
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-telephone"></i> 
                            <span class="text-light">Hotline: 1900 1234</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope"></i> 
                            <span class="text-light">Email: support@cinebook.vn</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock"></i> 
                            <span class="text-light">Working Hours: 8:00 - 22:00</span>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none">
                                <i class="bi bi-question-circle"></i> FAQ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none">
                                <i class="bi bi-file-text"></i> Terms of Service
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-bell"></i> Newsletter
                    </h5>
                    <p class="text-light small">
                        Receive information about new movies, special offers and exclusive events.
                    </p>
                    <form class="mt-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <h6 class="mb-2">
                            <i class="bi bi-download"></i> Download App
                        </h6>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-apple"></i> App Store
                            </a>
                            <a href="#" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-google-play"></i> Google Play
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 bg-secondary">
        </div>
    </footer>

    <style>
        footer a {
            transition: color 0.3s ease;
        }
        footer a:hover {
            color: #0d6efd !important;
        }
        footer .list-unstyled li {
            transition: transform 0.2s ease;
        }
        footer .list-unstyled li:hover {
            transform: translateX(5px);
        }
        footer .input-group input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
</body>

</html>