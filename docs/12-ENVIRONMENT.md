# Environment & Konfigurasi

## Lokal (developer)
| Komponen | Versi |
|---|---|
| PHP | 8.1–8.2 (samakan dengan pilihan di panel InfinityFree; ekstensi: `pdo_mysql`, `gd`, `mbstring`, `curl`, `fileinfo`, `openssl`) |
| MySQL/MariaDB | MariaDB 10.4+ / MySQL 5.7+ |
| Web server lokal | Salah satu: `php -S localhost:8000 -t htdocs htdocs/router-dev.php` (BE membuat router dev) **atau** Apache (XAMPP/Laragon/FlyEnv) dengan `mod_rewrite` |
| Node/NPM | **Tidak dipakai** |
| Git | 2.30+ (butuh `git worktree`) |
Port & DB lokal: DB `ikurin_shop_local` (dev), `ikurin_test` (tes). Buat manual, import `database/schema.sql` + `seed.sql`.
Catatan: buat satu worktree per AI dan **jalankan server dev dari worktree masing-masing** pada port berbeda (BE `:8001`, FE `:8002` dengan `USE_MOCK=true`). Saat integrasi M3, FE diarahkan ke BE sama-origin lewat satu server (jalankan dari worktree `develop`/gabungan di `:8000`).

## `.env` (BE) — `htdocs/config/.env` (tidak di-commit; contoh: `templates/.env.example`)
| Key | Contoh lokal | Catatan produksi |
|---|---|---|
| `APP_ENV` | `local` | `production` (sembunyikan error) |
| `APP_URL` | `http://localhost:8000` | `https://ikurin.sinata.my.id` |
| `APP_TIMEZONE` | `Asia/Jakarta` | sama |
| `DB_HOST` | `127.0.0.1` | host dari panel (mis. `sqlXXX.infinityfree.com`), **bukan** localhost |
| `DB_NAME` | `ikurin_shop_local` | `if0_XXXXXXXX_ikurin` |
| `DB_USER` / `DB_PASS` | `root` / kosong | dari panel |
| `SESSION_NAME` | `IKURIN_SID` | |
| `SESSION_SECURE` | `0` | `1` (HTTPS) |
| `PUSHER_APP_ID`, `PUSHER_KEY`, `PUSHER_SECRET`, `PUSHER_CLUSTER` | dari dashboard Pusher | `PUSHER_CLUSTER=ap1` (Singapura, terdekat) — `PUSHER_SECRET` jangan pernah ke FE |
| `UPLOAD_MAX_BYTES` | `2097152` | |
Backend membaca `.env` lewat `Env` (parser sendiri). File `.env` harus diblokir `.htaccess` (lihat template) dan **tidak** pernah di-commit.

## Konfigurasi Frontend
`htdocs/assets/js/config.js`:
```js
window.IKURIN_CONFIG = { API_BASE: '/api/v1', USE_MOCK: false };  // dev FE murni: true
```
Kunci Pusher publik & fee % diambil runtime dari `GET /config/public` (tidak dihardcode).

## Batas Hosting yang Mempengaruhi Konfigurasi
Lihat `15-HOSTING-INFINITYFREE.md`: DB tidak bisa `CREATE DATABASE` lewat SQL; tanpa SSH/Composer; `.env` harus diunggah manual via File Manager/FTP.
