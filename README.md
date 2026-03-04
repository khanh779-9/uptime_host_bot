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

## 4) Chạy checker định kỳ (tránh sleep)
### Cách A: gọi endpoint
- Gọi `https://your-domain/index.php?url=cron/run` mỗi 5 phút (cron service ngoài).

### Cách B: cron PHP CLI
```bash
php cron/run_checker.php
```
Đặt cron/task scheduler chạy mỗi 5 phút.

## Ghi chú
- App có 2 bảng chính: `users`, `monitors`.
- Có thêm bảng `user_settings` để lưu `language` + `theme` theo từng user.
- `monitors` hỗ trợ nhiều loại giữ hoạt động: `host`, `web`, `api`, `database`.
- Có combobox chọn chu kỳ keep-alive: 1, 5, 15, 30, 60 phút.
- Cron chỉ check các monitor đã tới hạn theo chu kỳ đã chọn, tránh gọi quá dày.
- File ngôn ngữ JSON nằm trong thư mục `app/Lang` (ví dụ: `vi.json`, `en.json`).
- Trang monitor có dashboard tổng quan + biểu đồ phân bổ theo loại monitor.
- User có thể chỉnh sửa hồ sơ cá nhân trong Settings (username, email, mật khẩu mới).
- Free host có thể vẫn sleep nếu nhà cung cấp chặn idle; giải pháp thực tế là dùng cron/ping từ bên ngoài.

## Nếu bạn đã import DB phiên bản cũ
Chạy thêm SQL sau để nâng cấp:

```sql
ALTER TABLE monitors
ADD COLUMN target_type ENUM('host','web','api','database') NOT NULL DEFAULT 'web' AFTER name,
ADD COLUMN check_interval_seconds INT NOT NULL DEFAULT 300 AFTER url;

CREATE TABLE IF NOT EXISTS user_settings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL UNIQUE,
	language_code VARCHAR(10) NOT NULL DEFAULT 'vi',
	theme_mode ENUM('light', 'dark') NOT NULL DEFAULT 'light',
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id)
		REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```
