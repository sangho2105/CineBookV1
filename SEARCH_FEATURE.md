# Tính năng Tìm kiếm Thông minh & Bộ lọc

## Tổng quan
Hệ thống tìm kiếm thông minh cho phép người dùng tìm kiếm và lọc phim theo nhiều tiêu chí khác nhau.

## Các tính năng chính

### 1. Tìm kiếm theo từ khóa
- Tìm kiếm theo tên phim
- Tìm kiếm trong nội dung mô tả phim
- Hỗ trợ autocomplete với gợi ý phim khi gõ

### 2. Bộ lọc theo thể loại (Genre)
- Lọc phim theo thể loại: Hành động, Tình cảm, Kinh dị, v.v.
- Tự động lấy danh sách thể loại từ database

### 3. Bộ lọc theo thành phố (City)
- Lọc phim đang chiếu tại thành phố cụ thể
- Danh sách thành phố được lấy từ các rạp chiếu

### 4. Bộ lọc theo rạp chiếu (Theater)
- Lọc phim theo rạp chiếu cụ thể
- Danh sách rạp tự động cập nhật khi chọn thành phố
- Hỗ trợ AJAX để cập nhật động

### 5. Bộ lọc theo trạng thái
- Đang chiếu (now_showing)
- Sắp chiếu (upcoming)
- Đã kết thúc (ended)

### 6. Bộ lọc theo ngày chiếu
- Chọn ngày cụ thể để xem phim chiếu trong ngày đó

## Cấu trúc Files

### Controller
```
app/Http/Controllers/SearchController.php
```
- `index()`: Hiển thị trang tìm kiếm và xử lý các bộ lọc
- `getTheatersByCity()`: API endpoint để lấy danh sách rạp theo thành phố
- `autocomplete()`: API endpoint cho tính năng gợi ý tự động

### View
```
resources/views/search/index.blade.php
```
- Giao diện tìm kiếm với form bộ lọc
- Hiển thị kết quả tìm kiếm dạng grid
- Hỗ trợ phân trang
- JavaScript cho tính năng động (AJAX)

### Routes
```php
// Public routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/theaters-by-city', [SearchController::class, 'getTheatersByCity']);
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete']);
```

## Cách sử dụng

### 1. Truy cập trang tìm kiếm
- Click vào "Tìm kiếm" trên menu navigation
- Hoặc truy cập: `/search`

### 2. Sử dụng Quick Search (Trang chủ)
- Nhập từ khóa vào ô tìm kiếm
- Chọn trạng thái (tùy chọn)
- Click "Tìm kiếm"

### 3. Sử dụng Advanced Search
- Nhập từ khóa (tùy chọn)
- Chọn các bộ lọc: thể loại, trạng thái, thành phố, rạp, ngày
- Click "Tìm kiếm"
- Click "Đặt lại" để xóa tất cả bộ lọc

## Tính năng nâng cao

### Autocomplete
- Gõ ít nhất 2 ký tự trong ô tìm kiếm
- Hệ thống sẽ hiển thị gợi ý phim phù hợp
- Click vào gợi ý để xem chi tiết phim

### Dynamic Theater Filter
- Khi chọn thành phố, danh sách rạp tự động cập nhật
- Chỉ hiển thị các rạp trong thành phố đã chọn

### Pagination
- Hiển thị 12 phim mỗi trang
- Hỗ trợ điều hướng giữa các trang
- Giữ nguyên các bộ lọc khi chuyển trang

## Responsive Design
- Giao diện thân thiện với mobile
- Bootstrap 5 responsive grid
- Hover effects trên desktop

## API Endpoints

### GET /search
**Parameters:**
- `keyword` (string): Từ khóa tìm kiếm
- `genre` (string): Thể loại phim
- `status` (string): Trạng thái phim
- `city` (string): Thành phố
- `theater_id` (integer): ID rạp chiếu
- `date` (date): Ngày chiếu

**Response:** HTML page với kết quả tìm kiếm

### GET /search/theaters-by-city
**Parameters:**
- `city` (string): Tên thành phố

**Response:** JSON array của theaters
```json
[
  {
    "id": 1,
    "name": "CGV Vincom",
    "city": "Hà Nội",
    "address": "191 Bà Triệu"
  }
]
```

### GET /search/autocomplete
**Parameters:**
- `keyword` (string): Từ khóa tìm kiếm

**Response:** JSON array của movies
```json
[
  {
    "id": 1,
    "title": "Avatar 2",
    "genre": "Sci-Fi",
    "poster_url": "..."
  }
]
```

## Tối ưu hóa

### Database Queries
- Sử dụng eager loading với `with(['showtimes.theater'])`
- Pagination để giảm tải database
- Index trên các cột thường xuyên tìm kiếm

### Performance
- AJAX requests với debounce (300ms)
- Lazy loading cho autocomplete
- Caching có thể được thêm vào sau

## Mở rộng trong tương lai
- [ ] Thêm bộ lọc theo khoảng giá vé
- [ ] Lọc theo rating
- [ ] Lưu lịch sử tìm kiếm
- [ ] Gợi ý phim dựa trên sở thích
- [ ] Export kết quả tìm kiếm
- [ ] Chia sẻ link tìm kiếm

## Testing
Để test tính năng:
1. Đảm bảo database có dữ liệu movies, theaters, showtimes
2. Truy cập `/search`
3. Thử các bộ lọc khác nhau
4. Kiểm tra autocomplete
5. Kiểm tra dynamic theater filter khi chọn city

## Troubleshooting

### Autocomplete không hoạt động
- Kiểm tra JavaScript console
- Đảm bảo route `/search/autocomplete` hoạt động
- Kiểm tra network tab trong DevTools

### Theater filter không cập nhật
- Kiểm tra route `/search/theaters-by-city`
- Kiểm tra JavaScript console
- Đảm bảo có dữ liệu theaters trong database

### Không có kết quả
- Kiểm tra dữ liệu trong database
- Kiểm tra relationships giữa Movie, Theater, Showtime
- Xem query log để debug
