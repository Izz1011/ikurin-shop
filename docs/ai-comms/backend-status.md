# Status Backend (ditulis hanya oleh BACKEND AI — Antigravity)

## Ringkasan Saat Ini
- Tugas aktif: BE-004 (Selesai, status REVIEW)
- Terakhir selesai: BE-004
- Langkah berikutnya: BE-005 (Middleware RBAC: Auth, Role, StoreActive)

## Pertanyaan Terbuka
(kosong)

## Log Progres
<!-- Gunakan template di docs/14-AI-WORKFLOW.md; entri terbaru di bawah -->

### 2026-10-29 18:33 — BE-001 — REVIEW
- Ringkasan: Mengimplementasikan fondasi arsitektur Core backend: Autoloader PSR-4 manual tanpa Composer, Env parser (.env), Database PDO singleton dengan utf8mb4 & transaksi aman, Response envelope standar kontrak API v1, Request wrapper (JSON & query/route params), Router dengan support URL params & middleware pipeline, Logger berbasis berkas, AppException handler, front controller api/index.php, serta router-dev.php untuk server dev lokal.
- File diubah:
  - `htdocs/app/Core/Autoloader.php`
  - `htdocs/app/Core/AppException.php`
  - `htdocs/app/Core/Env.php`
  - `htdocs/app/Core/Database.php`
  - `htdocs/app/Core/Logger.php`
  - `htdocs/app/Core/Request.php`
  - `htdocs/app/Core/Response.php`
  - `htdocs/app/Core/Router.php`
  - `htdocs/app/bootstrap.php`
  - `htdocs/app/routes.php`
  - `htdocs/api/index.php`
  - `htdocs/router-dev.php`
  - `tests/backend/run.php`
  - `docs/09-TASK-BOARD.md`
  - `docs/ai-comms/backend-status.md`
- Cara uji:
  1. `php -l` pada seluruh berkas PHP: `Get-ChildItem -Recurse htdocs -Filter *.php | ForEach-Object { php -l $_.FullName }` -> 0 errors.
  2. Jalankan test runner unit test Core: `php tests/backend/run.php` -> 19 passed, 0 failed.
  3. Uji dev server: `php -S localhost:8000 -t htdocs htdocs/router-dev.php` lalu request `GET /api/v1/health` -> HTTP 200 `{"success":true,"data":{"status":"healthy","time":"..."}}`.
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: tidak ada.

### 2026-10-29 18:38 — BE-002 — REVIEW
- Ringkasan: Konfigurasi keamanan web server Apache & InfinityFree: rewrite engine untuk API v1, proteksi penolakan akses web langsung ke folder internal (app, config, storage), blokir eksekusi script dan file executable di htdocs/uploads/.htaccess (RemoveHandler & php_flag engine off), header keamanan HTTP lengkap (CSP, X-Content-Type-Options, X-Frame-Options, Referrer-Policy), aturan kompresi deflate & cache expiry aset statis, serta kelengkapan berkas htdocs/config/.env.example.
- File diubah:
  - `htdocs/.htaccess`
  - `htdocs/uploads/.htaccess`
  - `htdocs/config/.env.example`
  - `tests/backend/run.php`
  - `docs/09-TASK-BOARD.md`
  - `docs/ai-comms/backend-status.md`
- Cara uji:
  1. Jalankan `php tests/backend/run.php` -> 32 passed, 0 failed (seluruh assertion konfigurasi .htaccess dan env terverifikasi).
  2. Verifikasi dev-router dan curl blokir akses ke internal folder `/app`, `/config`, `/storage` -> HTTP 403 `FORBIDDEN`.
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: tidak ada.

### 2026-10-29 18:56 — BE-003 — REVIEW (AKHIR MILESTONE M0)
- Ringkasan: Mengimplementasikan sistem Session yang aman (cookie HttpOnly, SameSite=Lax, Secure via env, strict mode, idle timeout check), sistem token CSRF berbasis crypto-random bin2hex 32 bytes, CsrfMiddleware pelindung mutasi POST/PATCH/DELETE (403 CSRF_INVALID), SettingRepository & ConfigService untuk endpoint GET /api/v1/config/public, UserRepository & AuthService untuk endpoint GET /api/v1/auth/csrf dan GET /api/v1/auth/me (mendukung guest dan authenticated user dengan role, store_status, serta hitungan badge unread notifications/messages).
- File diubah:
  - `htdocs/app/Core/Session.php`
  - `htdocs/app/Core/Csrf.php`
  - `htdocs/app/Core/Database.php`
  - `htdocs/app/Middleware/CsrfMiddleware.php`
  - `htdocs/app/Repositories/SettingRepository.php`
  - `htdocs/app/Repositories/UserRepository.php`
  - `htdocs/app/Services/ConfigService.php`
  - `htdocs/app/Services/AuthService.php`
  - `htdocs/app/Controllers/ConfigController.php`
  - `htdocs/app/Controllers/AuthController.php`
  - `htdocs/app/routes.php`
  - `tests/backend/run.php`
  - `tests/backend/setup-local-db.php`
  - `docs/09-TASK-BOARD.md`
  - `docs/ai-comms/backend-status.md`
- Cara uji:
  1. Jalankan unit & integration test runner: `php tests/backend/run.php` -> 45 passed, 0 failed.
  2. Contoh curl:
     - `curl -s http://localhost:8000/api/v1/config/public` -> HTTP 200 `{"success":true,"data":{"site_name":"Ikurin Shop","service_fee_percent":2,"pusher_key":"","pusher_cluster":"ap1","categories":["bakery","snack","makanan_berat","minuman"]}}`
     - `curl -s http://localhost:8000/api/v1/auth/csrf` -> HTTP 200 `{"success":true,"data":{"csrf_token":"..."}}`
     - `curl -s http://localhost:8000/api/v1/auth/me` -> HTTP 200 `{"success":true,"data":{"user":null,"csrf_token":"...","unread_notifications":0,"unread_messages":0}}`
     - `curl -s -X POST http://localhost:8000/api/v1/cart/items` -> HTTP 403 `{"success":false,"error":{"code":"CSRF_INVALID","message":"Token CSRF tidak valid atau telah kedaluwarsa."}}`
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: tidak ada.

### 2026-10-29 19:07 — BE-004 — REVIEW
- Ringkasan: Mengimplementasikan modul validasi input terpusat (Validator: required, email, min, max, int, in, regex, phone format Indonesia) serta fitur autentikasi & profil akun lengkap:
  1. `POST /api/v1/auth/register`: registrasi user baru, hash bcrypt, auto-login dengan session baru, respons 201 User object, validasi email duplikat 422.
  2. `POST /api/v1/auth/login`: autentikasi bcrypt, rate limit berbasis tabel login_attempts (maks 5 percobaan gagal dalam 15 menit -> 429 RATE_LIMITED), regenerasi session ID, kembalikan data user + csrf_token.
  3. `POST /api/v1/auth/logout`: pembersihan sesi aman.
  4. `PATCH /api/v1/users/me`: pembaruan username dan phone profil user aktif.
  5. `POST /api/v1/users/me/password`: pergantian kata sandi dengan verifikasi kata sandi lama & hash bcrypt baru.
- File diubah:
  - `htdocs/app/Core/Validator.php`
  - `htdocs/app/Repositories/UserRepository.php`
  - `htdocs/app/Repositories/LoginAttemptRepository.php`
  - `htdocs/app/Services/AuthService.php`
  - `htdocs/app/Controllers/AuthController.php`
  - `htdocs/app/Controllers/UserController.php`
  - `htdocs/app/routes.php`
  - `tests/backend/run.php`
  - `docs/09-TASK-BOARD.md`
  - `docs/ai-comms/backend-status.md`
- Cara uji:
  1. Jalankan `php tests/backend/run.php` -> 57 passed, 0 failed.
  2. Contoh curl:
     - `curl -s -X POST http://localhost:8000/api/v1/auth/register -H "Content-Type: application/json" -H "X-CSRF-Token: <token>" -d '{"username":"Siti","email":"siti@mail.com","password":"password123","phone":"08123456789"}'` -> HTTP 201 `{"success":true,"data":{"id":2,"username":"Siti",...}}`
     - `curl -s -X POST http://localhost:8000/api/v1/auth/login -H "Content-Type: application/json" -H "X-CSRF-Token: <token>" -d '{"email":"siti@mail.com","password":"password123"}'` -> HTTP 200 `{"success":true,"data":{"user":{...},"csrf_token":"..."}}`
     - `curl -s -X POST http://localhost:8000/api/v1/auth/logout -H "X-CSRF-Token: <token>"` -> HTTP 200 `{"success":true,"data":{}}`
     - `curl -s -X PATCH http://localhost:8000/api/v1/users/me -H "Content-Type: application/json" -H "X-CSRF-Token: <token>" -d '{"username":"Siti Baru"}'` -> HTTP 200
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: tidak ada.
