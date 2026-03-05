# Uptime Host Bot (PHP thuần MVC)

## 1) Import database
- Chạy file `uptimebot_db.sql` vào MySQL.
- DB name sử dụng trong code: `uptimebot_db`.

## 2) Cấu hình kết nối
Tạo file `.env` từ `.env.example`, sau đó chỉnh:
- `APP_URL` (đúng domain của bạn, ví dụ trên Render)
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `CRON_SECRET` (chuỗi bí mật dài để bảo vệ endpoint cron)
- `APP_ASSET_VERSION` (tăng version khi cần clear cache CSS)

Ví dụ nhanh:
```bash
cp .env.example .env
```

Windows PowerShell:
```powershell
Copy-Item .env.example .env
```

## 3) Chạy local nhanh
```bash
php -S localhost:8000
```

Truy cập: `http://localhost:8000/`

## 4) Chạy cron endpoint an toàn
- Endpoint: `GET /index.php?url=cron/run&token=YOUR_CRON_SECRET`
- Hoặc gửi header: `X-Cron-Token: YOUR_CRON_SECRET`
