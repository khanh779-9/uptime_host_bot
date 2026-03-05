# Uptime Host Bot (PHP thuần MVC)

## 1) Import database
- Chạy file `uptimebot_db.sql` vào MySQL.
- DB name sử dụng trong code: `uptimebot_db`.

## 2) Cấu hình kết nối
Sửa file `app/Config/config.php`:
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `BASE_URL` (đúng domain của bạn)

## 3) Chạy local nhanh
```bash
php -S localhost:8000
```

Truy cập: `http://localhost:8000/`
