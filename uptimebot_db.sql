CREATE DATABASE IF NOT EXISTS uptimebot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uptimebot_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS monitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    target_type ENUM('host','web','api','database') NOT NULL DEFAULT 'web',
    url VARCHAR(500) NOT NULL,
    check_interval_seconds INT NOT NULL DEFAULT 300,
    expected_status SMALLINT NOT NULL DEFAULT 200,
    last_status SMALLINT NULL,
    last_checked_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_monitors_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    language_code VARCHAR(10) NOT NULL DEFAULT 'vi',
    theme_mode ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS monitor_checks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    monitor_id INT NOT NULL,
    status_code SMALLINT NOT NULL,
    response_time_ms INT NULL,
    checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_monitor_checks_monitor FOREIGN KEY (monitor_id)
        REFERENCES monitors(id) ON DELETE CASCADE,
    INDEX idx_monitor_checks_monitor_checked_at (monitor_id, checked_at),
    INDEX idx_monitor_checks_checked_at (checked_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS monitor_incidents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    monitor_id INT NOT NULL,
    status ENUM('down','resolved') NOT NULL DEFAULT 'down',
    root_cause VARCHAR(255) NOT NULL,
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    duration_seconds INT NULL,
    CONSTRAINT fk_monitor_incidents_monitor FOREIGN KEY (monitor_id)
        REFERENCES monitors(id) ON DELETE CASCADE,
    INDEX idx_monitor_incidents_monitor_started (monitor_id, started_at),
    INDEX idx_monitor_incidents_monitor_status (monitor_id, status)
) ENGINE=InnoDB;
