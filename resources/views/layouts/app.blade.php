<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CineBook')</title> <!-- @yied là một directive trong Laravel để hiển thị nội dung của view con -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @stack('css')
    <style>
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">CineBook</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="bi bi-house-door"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="moviesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-film"></i> Phim
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="moviesDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('search', ['status' => 'now_showing']) }}">
                                    <i class="bi bi-camera-reels"></i> Phim đang chiếu
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('search', ['status' => 'upcoming']) }}">
                                    <i class="bi bi-calendar-event"></i> Phim sắp chiếu
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('promotions.index') }}">
                            <i class="bi bi-gift"></i> Ưu đãi &amp; Sự kiện
                        </a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.index') }}">
                            <i class="bi bi-ticket-perforated"></i> Vé của tôi
                        </a>
                    </li>
                    
                    
                    {{-- Dropdown menu cho user --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="bi bi-person"></i> Hồ sơ của tôi
                                </a>
                            </li>
                            @if(Auth::user()->role === 'admin')
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.movies.index') }}">
                                    <i class="bi bi-speedometer2"></i> Trang Admin
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Đăng ký</a>
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
                    <h5 class="modal-title" id="bookingModalLabel">Chọn suất chiếu</h5>
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
        .booking-modal-content .booking-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .booking-modal-content .movie-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .booking-modal-content .movie-poster {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .booking-modal-content .movie-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }

        .booking-modal-content .date-selector {
            background: #fff;
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
            background: #fff;
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
                    .then(response => response.text())
                    .then(html => {
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
                        modalBody.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu.</div>';
                    });
            }
        });
    </script>
    @stack('scripts')

    <footer class="bg-dark text-white text-center p-4 mt-5">
        <div class="container">
            <p>&copy; 2025 CineBook. All Rights Reserved.</p>
        </div>
    </footer>
</body>

</html>