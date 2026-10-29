# SRS Backend — Antigravity CLI

Peran: bangun **REST API** persis sesuai `../04-API-CONTRACT.md` di atas skema `../database/schema.sql`. Kamu tidak menyentuh HTML/CSS/JS frontend.
Baca juga: `../02-SRS.md` (item bertanda BE), `../03-ARCHITECTURE.md`, `../13-SECURITY.md`, `../15-HOSTING-INFINITYFREE.md`.

## Lingkup & Lokasi Kode (milikmu)
`htdocs/api/index.php` · `htdocs/app/{Core,Middleware,Controllers,Services,Repositories,Helpers}` · `htdocs/config/` · `htdocs/.htaccess` · `htdocs/uploads/.htaccess` · `htdocs/storage/` · `database/` · `tests/backend/`.

## Komponen Inti (BE-001..003)
- **Env**: parse `config/.env`; **Database**: PDO singleton (utf8mb4, exception mode, emulate prepares off, `SET time_zone='+07:00'`).
- **Router**: `METHOD /api/v1/path/{param}` → `[Controller, method]` + daftar middleware. Route table di `app/routes.php`.
- **Request/Response**: parse JSON & multipart, `Response::ok($data,$meta)`, `Response::error($code,$msg,$status,$fields)`; header JSON + keamanan di bootstrap.
- **Session & CSRF**: sesuai `13-SECURITY.md`; timeout dari `settings.session_timeout_minutes`; `SESSION_EXPIRED` bila idle.
- **Handler global**: tangkap `Throwable` → 500 generik + log.
- **Validator**: rule `required|email|min|max|int|in|regex|phone`.

## Aturan Bisnis yang Harus Tepat
1. `service_fee = intdiv(subtotal * pct + 50, 100)`; `pct` dari `settings.service_fee_percent`; total dihitung server.
2. **Checkout (satu transaksi)**: ambil item keranjang toko X → `SELECT … FOR UPDATE` produk → cek aktif & stok & toko ACTIVE & `is_open` & saldo titipan < limit → insert `orders` (kode `IKR-YYYYMMDD-NNNN`, NNNN acak/urut aman unik) + `order_items` (snapshot) → kurangi stok → hapus item keranjang toko itu → notifikasi + Pusher ke pemilik toko. `shipping_fee` = `stores.shipping_fee_default` (toko boleh ubah saat menerima, hanya PENDING→PROCESSING; total & fee dihitung ulang dengan `service_fee` tetap).
3. **State machine** sesuai kontrak; transisi ilegal → 409 `INVALID_TRANSITION`. `CANCELED` → stok dikembalikan dalam transaksi yang sama. `COMPLETED` → `completed_at`, CASH → `PAID`.
4. **Saldo titipan** = Σ `service_fee` dari pesanan `COMPLETED` dengan `admin_fee_status='UNSETTLED'` per toko. `blocked = saldo >= fee_debt_limit`. Dihitung on-demand (tanpa cron).
5. **Setoran**: `POST /seller/settlements` mengunci pesanan UNSETTLED toko itu → `PENDING_VERIFICATION` + `settlement_id`, `amount_paid`=jumlah server. Maks 1 setoran `PENDING` per toko. Admin APPROVED → `SETTLED`; REJECTED → kembali `UNSETTLED`, `settlement_id` NULL.
6. **Approve toko** (transaksi): `stores.status='ACTIVE'`, `users.role='TOKO'`, notifikasi. REJECTED/SUSPENDED wajib `reason`. Suspend → role tetap TOKO, tapi `StoreActive` middleware memblokir akses seller.
7. **Produk**: harga ≥ 0 integer, stok ≥ 0; hapus produk yang pernah dipesan → `is_active=0`.
8. **Katalog publik** hanya toko `ACTIVE`; `q` = `LIKE` pada nama produk/toko (di-escape `%`/`_`); `per_page` ≤ 50.
9. **Chat**: `receiver_id` harus user ada & bukan diri sendiri; panjang ≤ 1000; setelah simpan → `PusherClient::trigger()`.
10. **Notifikasi**: simpan di `notifications` untuk: pesanan baru (toko), perubahan status (pembeli), hasil verifikasi toko, hasil setoran.

## Helper
`ImageUploader`: `savePublic($file,'products'|'logos',$maxSide,$maxBytes)`, `savePrivate($file,'ktp'|'payments'|'settlements')`; hasilkan JPG; produk juga thumbnail `*_t.jpg` (320px).
`PusherClient`: `trigger($channel,$event,$data)`, `authenticate($socketId,$channel)`; cURL timeout 2 dtk; HMAC-SHA256 sesuai dokumentasi REST Pusher; gagal → log, jangan lempar.

## Deliverables
Seluruh endpoint kontrak · `database/` final (tanpa mengubah tabel tanpa CCR) · `tests/backend/*` · `tests/backend/api-smoke.sh` · `htdocs/router-dev.php` (router untuk `php -S`) · contoh `curl` per endpoint di `ai-comms/backend-status.md`.

## Urutan Kerja
Ikuti `../09-TASK-BOARD.md` BE-001 → BE-023. Setelah tiap milestone BERHENTI dan minta manusia review.
