# Testing Plan

Prinsip: tes dijalankan **lokal** (server tidak punya CLI/Composer). Tidak ada tes yang menyentuh DB produksi.

## 1. Backend (BE — `tests/backend/`)
**Unit (PHP murni, tanpa framework; runner: `php tests/backend/run.php` atau PHPUnit lokal jika diizinkan manusia):**
- `Money::serviceFee`: 50.000→1.000 · 12.345→247 · 0→0 · pembulatan .5 ke atas.
- Total = subtotal + ongkir + fee. Validator (email, whatsapp `^(0|62)8\d{7,12}$`, panjang, enum).
- State machine pesanan: semua transisi valid & invalid.
- `ImageUploader`: tolak file `.php` bermuatan gambar palsu, tolak > 2MB, tolak non-JPG/PNG, hasil ≤ 300KB.
- Perhitungan saldo titipan & blokir limit (`>= limit`).
**Integrasi API (`tests/backend/api-smoke.sh` pakai curl + cookie jar, DB lokal terpisah `ikurin_test`):**
| # | Skenario | Harapan |
|---|---|---|
| 1 | Register → login → `/auth/me` | 201/200, role USER |
| 2 | POST tanpa `X-CSRF-Token` | 403 `CSRF_INVALID` |
| 3 | 6× login salah | 429 |
| 4 | USER akses `/seller/*` & `/admin/*` | 403 |
| 5 | Buka toko nama duplikat | 409 |
| 6 | Admin approve → role jadi TOKO, tambah produk | 201 |
| 7 | Tambah keranjang > stok | 409 `INSUFFICIENT_STOCK` |
| 8 | Checkout CASH → stok berkurang, fee = 2% | total sesuai |
| 9 | Dua checkout bersamaan stok 1 | satu sukses, satu 409 (tidak minus) |
| 10 | Batalkan pesanan → stok kembali | stok semula |
| 11 | Selesaikan pesanan CASH → `PAID`, saldo titipan naik | sesuai |
| 12 | Saldo ≥ limit → pesanan baru ke toko itu | 409 `STORE_LIMIT_REACHED` |
| 13 | Setor → admin APPROVED → saldo 0, REJECTED → kembali | sesuai |
| 14 | Akses pesanan/bukti milik user lain | 403/404 (IDOR) |
| 15 | `/admin/stores/{id}/ktp` oleh non-admin; akses langsung `/storage/...` | 403 |
| 16 | `pusher-auth` channel milik orang lain | 403 |
| 17 | Payload XSS di pesan/nama toko | tersimpan apa adanya, escape di FE |
| 18 | SQLi `' OR 1=1--` di `q`/`email` | tidak berefek |
| 19 | Sesi idle > timeout | 401 `SESSION_EXPIRED` |
Cakupan target: semua endpoint di kontrak punya ≥ 1 tes sukses + 1 tes gagal.

## 2. Frontend (FE — `tests/frontend/CHECKLIST.md`, manual + Lighthouse)
- Per halaman: tampil benar di 360px, 768px, 1280px; loading/empty/error state; tidak ada error console.
- Route guard: tamu ke `/seller/*`/`/admin/*` → login; USER → ditolak; sesi habis → pesan + login.
- Form: validasi klien + tampilkan `error.fields` dari 422; tombol disabled saat submit; upload preview & batas 2MB.
- Alur E2E manual (mock lalu real): daftar → cari produk → keranjang → checkout CASH → lihat pesanan; Buka Toko → (admin approve) → tambah produk → terima & selesaikan pesanan → setor layanan.
- Keamanan UI: payload `<img onerror>` pada nama/pesan tampil sebagai teks.
- Lighthouse mobile ≥ 85 (Performance), ≥ 90 (Accessibility, Best Practices) pada beranda, detail produk, checkout.
- Chat: pesan terkirim muncul di dua browser (dua akun) < 2 dtk; fallback saat Pusher mati.

## 3. Fungsional/UAT (manusia, M3 & M5)
Jalankan alur lengkap tiga role di staging InfinityFree (HP asli, jaringan seluler). Catat bug di GitHub Issues dengan label `be`/`fe`.

## 4. Regresi
Sebelum merge ke `main`: seluruh smoke test + checklist FE hijau.
