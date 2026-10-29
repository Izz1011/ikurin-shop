# Arsitektur — Ikurin Shop

## 1. Gambaran
```
Browser (HTML/CSS/JS statis, ES Modules, tanpa build)
   │  fetch JSON  (same-origin, cookie session + header X-CSRF-Token)
   ▼
InfinityFree (Apache + PHP 8.x)
   ├─ htdocs/.htaccess ─ rewrite /api/v1/* → api/index.php
   ├─ api/index.php  → Router → Middleware → Controller → Service → Repository(PDO) → MySQL
   └─ PusherClient (cURL, server→Pusher)        ┌──────────────┐
Browser ◄──── WebSocket langsung ───────────────┤ Pusher Cloud │
                                                └──────────────┘
```
**Pola:** backend = REST JSON murni (tidak merender HTML). Frontend = multi-page statis yang memanggil API. Pemisahan ini yang memungkinkan 2 AI bekerja paralel; kontraknya `04-API-CONTRACT.md`.
Alasan memilih halaman statis (bukan template PHP): kontrak jelas, frontend bisa dikembangkan dengan **mock API** sebelum backend siap, dan HTML statis nyaris tanpa beban CPU di hosting gratis.

## 2. Struktur Repo (ikurin-shop/)
```
AGENTS.md                      # shared (salinan docs/AGENTS.md)
docs/                          # SHARED — hanya manusia/integrator yang mengubah dokumen inti
database/schema.sql seed.sql   # BE (diubah hanya lewat persetujuan; sumber: docs/database)
tests/backend/  tests/frontend/
htdocs/                        # = isi folder htdocs InfinityFree (deploy)
├─ .htaccess                   # BE
├─ index.html product.html store.html cart.html checkout.html
│  orders.html order.html login.html register.html profile.html
│  open-store.html chat.html report.html            # FE
├─ seller/  dashboard products product-form orders order fees settings (.html)   # FE
├─ admin/   dashboard stores store settlements users reports settings (.html)    # FE
├─ assets/  css/ js/ img/ fonts/                    # FE
│    js/api.js (klien API) js/mock/ (fixture)  js/pages/*.js  js/components/*.js
├─ api/index.php               # BE front controller
├─ app/                        # BE (diblokir via .htaccess)
│    Core/ (Router, Request, Response, Database, Session, Csrf, Validator, Env, Logger)
│    Middleware/ (Auth, Role, Csrf)   Controllers/   Services/   Repositories/
│    Helpers/ (Money, ImageUploader, Pusher)
├─ config/ .env  config.php    # BE (diblokir; .env TIDAK di-commit)
├─ storage/ private/ logs/     # BE (diblokir; KTP, bukti bayar/setoran)
└─ uploads/ products/ logos/   # BE tulis, publik (PHP execution dimatikan)
```

## 3. Backend
- **Front controller** `api/index.php` → bootstrap `app/Core`, set header JSON & keamanan, route berdasarkan `METHOD /api/v1/path`.
- **Layering:** Controller (HTTP: validasi input, panggil service, bentuk response) → Service (aturan bisnis, transaksi) → Repository (SQL PDO). Controller dilarang menulis SQL.
- **Autoload:** PSR-4 sederhana buatan sendiri (tanpa Composer), namespace `App\`.
- **Auth:** PHP session (cookie), `$_SESSION['user_id']`, `last_activity`. CSRF: token di session, dikirim klien via header `X-CSRF-Token` (ambil dari `GET /auth/csrf` atau respons `/auth/me`) untuk semua `POST/PATCH/DELETE`.
- **Response:** envelope seragam (lihat kontrak). Semua error tertangkap handler global → JSON, tanpa stack trace ke klien.
- **Uang:** integer rupiah (`BIGINT`), helper `Money::serviceFee()`; tidak ada float.
- **Pusher:** class `PusherClient` ringan (cURL + HMAC-SHA256 sesuai Pusher REST API); timeout 2 dtk; kegagalan Pusher **tidak boleh** menggagalkan request (log saja).

## 4. Frontend
- HTML5 semantik + CSS vanilla (CSS variables, mobile-first) + JS ES Modules; **tanpa build/Node**.
- `assets/js/api.js` satu-satunya tempat `fetch`; menangani CSRF, envelope, error 401 (redirect login), 403, 422.
- `window.IKURIN_CONFIG.USE_MOCK` memilih adaptor mock (`assets/js/mock/`) vs API asli → FE bisa jalan sebelum BE selesai.
- Komponen reusable (navbar, bottom-nav mobile, product card, toast, modal, skeleton) di `assets/js/components/`.
- Pusher JS dimuat dari CDN `https://js.pusher.com/8.2.0/pusher.min.js` hanya di halaman chat/notifikasi.
- Semua data dari API di-render dengan `textContent`/builder DOM, bukan `innerHTML` mentah.

## 5. Alur Kritis
**Checkout:** `POST /checkout/preview` (hitung) → `POST /orders` (transaksi: lock produk → cek stok → insert order+items → kurangi stok → hapus item keranjang → notifikasi+Pusher ke toko).
**Chat:** FE `POST /chat/messages` → BE simpan → trigger Pusher `private-chat-A-B` event `new-message` → FE lawan menerima. Subscribe private channel memanggil `POST /chat/pusher-auth`.
**Setoran:** `GET /seller/fees` → `POST /seller/settlements` (multipart) → admin `PATCH /admin/settlements/{id}`.

## 6. Environment
`APP_ENV` local|production; DB host/name/user/pass dari `.env`; di InfinityFree nama DB & host berbeda dari lokal (lihat `12-ENVIRONMENT.md`).
