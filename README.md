<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Hướng dẫn Setup cho Team Members

### Yêu cầu hệ thống:
- PHP >= 8.0.2
- Composer
- Node.js & NPM (nếu cần build frontend assets)
- MySQL/PostgreSQL/SQLite

### Các bước setup sau khi clone/pull code:

1. **Cài đặt PHP dependencies:**
   ```bash
   composer install
   ```
   > **Lưu ý:** Lệnh này sẽ tự động tạo symlink `storage` (không cần chạy `php artisan storage:link` riêng)

2. **Cài đặt Node dependencies (nếu cần build frontend):**
   ```bash
   npm install
   ```

3. **Tạo file .env và cấu hình:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   Sau đó, chỉnh sửa file `.env` và cấu hình database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ten_database
   DB_USERNAME=ten_user
   DB_PASSWORD=mat_khau
   ```

4. **Chạy migrations:**
   ```bash
   php artisan migrate
   ```

5. **Seed dữ liệu mẫu (tùy chọn):**
   ```bash
   php artisan db:seed
   ```

6. **Build frontend assets (nếu cần):**
   ```bash
   npm run build
   # hoặc chạy dev server:
   npm run dev
   ```

### Tóm tắt nhanh (cho lần đầu setup):
```bash
composer install
cp .env.example .env
php artisan key:generate
# Chỉnh sửa .env với thông tin database
php artisan migrate
php artisan db:seed  # (tùy chọn)
```

### Sau khi pull code mới (không phải lần đầu):
```bash
composer install  # Tự động tạo symlink storage
php artisan migrate  # Nếu có migration mới
```

### Vấn đề về ảnh bị mất:

- **Nguyên nhân:** Các file ảnh trong `storage/app/public/movies` không được commit vào git (đúng như vậy, vì chúng là files được upload).
- **Giải pháp:**
  - Đảm bảo đã chạy `php artisan storage:link` để tạo symlink
  - Nếu cần ảnh để test, bạn có thể:
    - Upload ảnh mới qua admin panel
    - Hoặc sử dụng URL ảnh từ internet khi tạo phim mới
    - Hoặc tải ảnh từ server production (nếu có quyền truy cập)

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
