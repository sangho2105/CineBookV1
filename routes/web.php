<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\TheaterController as AdminTheaterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromotionDisplayController;
use App\Http\Controllers\SearchController;


// Authentication Routes
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');

Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetPassword'])->name('password.forgot.post');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Search Routes (Public)
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/theaters-by-city', [SearchController::class, 'getTheatersByCity'])->name('search.theaters-by-city');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/tickets', [App\Http\Controllers\ProfileController::class, 'tickets'])->name('profile.tickets');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Movies Routes (only for authenticated users)
    Route::resource('movies', App\Http\Controllers\MovieController::class);

    // Booking Routes
    Route::get('/movie/{movie}/book', [App\Http\Controllers\BookingController::class, 'selectShowtime'])->name('bookings.select-showtime');
    Route::get('/movie/{movie}/book/modal', [App\Http\Controllers\BookingController::class, 'selectShowtimeModal'])->name('bookings.select-showtime-modal');
    Route::get('/showtimes/{showtime}/select-seats', [App\Http\Controllers\BookingController::class, 'selectSeats'])->name('bookings.select-seats');
    Route::post('/showtimes/{showtime}/confirm', [App\Http\Controllers\BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/showtimes/{showtime}/bookings', [App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/payment', [App\Http\Controllers\BookingController::class, 'payment'])->name('bookings.payment');
    Route::get('/bookings/{booking}', [App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/pay', [App\Http\Controllers\BookingController::class, 'pay'])->name('bookings.pay');
    Route::get('/bookings/{booking}/ticket', [App\Http\Controllers\BookingController::class, 'showTicket'])->name('bookings.ticket');
    Route::get('/bookings/{booking}/ticket/download', [App\Http\Controllers\BookingController::class, 'downloadTicket'])->name('bookings.ticket.download');
    // PayPal endpoints
    Route::post('/bookings/{booking}/paypal/create-order', [App\Http\Controllers\BookingController::class, 'paypalCreateOrder'])->name('bookings.paypal.create');
    Route::post('/bookings/{booking}/paypal/capture', [App\Http\Controllers\BookingController::class, 'paypalCapture'])->name('bookings.paypal.capture');
    Route::get('/bookings/{booking}/paypal/return', [App\Http\Controllers\BookingController::class, 'paypalReturn'])->name('bookings.paypal.return');
});
// Route này sẽ xử lý các URL như /movie/1, /movie/2, ...
Route::get('/movie/{movie}', [HomeController::class, 'show'])->name('movie.show');
Route::get('/promotions', [PromotionDisplayController::class, 'index'])->name('promotions.index');
Route::get('/promotions/{promotion}', [PromotionDisplayController::class, 'show'])->name('promotion.show');

// Movie feedback (comments & ratings)
Route::middleware('auth')->group(function () {
    Route::post('/movie/{movie}/comments', [\App\Http\Controllers\MovieFeedbackController::class, 'storeComment'])->name('movie.comment.store');
    Route::post('/movie/{movie}/rating', [\App\Http\Controllers\MovieFeedbackController::class, 'storeRating'])->name('movie.rating.store');
    Route::put('/movie/{movie}/comments/{comment}', [\App\Http\Controllers\MovieFeedbackController::class, 'updateComment'])->name('movie.comment.update');
    Route::delete('/movie/{movie}/comments/{comment}', [\App\Http\Controllers\MovieFeedbackController::class, 'deleteComment'])->name('movie.comment.delete');
    
    // Favorites Routes
    Route::post('/movie/{movie}/favorite/toggle', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('movie.favorite.toggle');
    Route::get('/profile/favorites', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('profile.favorites');
});

// Nhóm các route cho admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Route cho quản lý Phim (CRUD)
    Route::resource('movies', MovieController::class);

    // Route cho quản lý Phòng chiếu (chỉ xem)
    Route::get('rooms', [\App\Http\Controllers\Admin\RoomController::class, 'index'])->name('rooms.index');
    Route::get('rooms/{room}', [\App\Http\Controllers\Admin\RoomController::class, 'show'])->name('rooms.show');
    Route::get('rooms/{room}/schedule', [\App\Http\Controllers\Admin\RoomController::class, 'schedule'])->name('rooms.schedule');

    // Route cho quản lý Rạp chiếu (CRUD) - giữ lại để tương thích
    Route::resource('theaters', AdminTheaterController::class);

    // Route cho quản lý Khuyến mãi & Sự kiện
    Route::resource('promotions', PromotionController::class)->except(['show']);
    Route::post('promotions/update-order', [PromotionController::class, 'updateOrder'])->name('promotions.update-order');
    Route::post('promotions/save-rules', [PromotionController::class, 'saveRules'])->name('promotions.save-rules');

    // Route cho quản lý Suất chiếu (Showtimes)
    Route::resource('showtimes', \App\Http\Controllers\ShowtimeController::class);

    // Route cho quản lý Vé (Bookings)
    Route::get('bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/statistics', [\App\Http\Controllers\Admin\BookingController::class, 'statistics'])->name('bookings.statistics');
    Route::get('bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');

    // Route cho quản lý Combo
    Route::resource('combos', \App\Http\Controllers\Admin\ComboController::class);
    Route::post('combos/{combo}/toggle-hidden', [\App\Http\Controllers\Admin\ComboController::class, 'toggleHidden'])->name('combos.toggleHidden');
    Route::post('combos/update-order', [\App\Http\Controllers\Admin\ComboController::class, 'updateOrder'])->name('combos.update-order');

    // Route cho quản lý Bình luận
    Route::get('comments', [\App\Http\Controllers\Admin\CommentController::class, 'index'])->name('comments.index');
    Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');

    // Route cho quản lý User
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
});