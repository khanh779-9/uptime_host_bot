# Uptime Host Bot (Pure PHP MVC)

Uptime Host Bot is a lightweight uptime monitoring web app built with a custom PHP MVC structure (no full framework).
It helps you monitor websites or database health checks, track incidents, and review response time history in a clean dashboard.

## Overview

This project includes:
- User authentication (login/register)
- Monitor management (create/update/delete)
- Incident tracking
- Multi-language support (`en`, `vi`)
- Theme preferences (light/dark)
- Cron-protected checker endpoint with secret token

## Requirements

- PHP 8.2+
- MySQL 5.7+ (or compatible)
- PHP extensions: `pdo`, `pdo_mysql`, `curl`

## 1) Import the database

Import `uptimebot_db.sql` into your MySQL server.

Default database name used by the app:
- `uptimebot_db`

## 2) Configure environment variables

Create `.env` from `.env.example`, then update values:
- `APP_URL` (your full domain/base URL, e.g. Render URL)
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `CRON_SECRET` (long random secret to protect cron endpoint)
- `APP_ASSET_VERSION` (increase when you want to bust CSS cache)

Quick example:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

## 3) Run locally

```bash
php -S localhost:8000
```

Open:
- `http://localhost:8000/`

## 4) Run monitor checks (cron)

Secure cron endpoint:
- `GET /index.php?url=cron/run&token=YOUR_CRON_SECRET`

Or via header:
- `X-Cron-Token: YOUR_CRON_SECRET`

You can schedule this endpoint every 1–5 minutes using your hosting cron service.

## Docker (optional)

This repository includes a simple Dockerfile based on `php:8.2-cli`.

Build:

```bash
docker build -t uptime-host-bot .
```

Run:

```bash
docker run --rm -p 10000:10000 --env PORT=10000 uptime-host-bot
```

## Notes

- Keep `CRON_SECRET` private.
- Make sure your MySQL credentials in `.env` are correct before running checks.
- If styles look stale after deployment, bump `APP_ASSET_VERSION`.
