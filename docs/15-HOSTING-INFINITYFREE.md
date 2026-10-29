# Hosting: InfinityFree + Domain `ikurin.sinata.my.id`

> Catatan: detail panel InfinityFree bisa berubah. Verifikasi langkah di panel/Knowledge Base saat mengerjakan. Poin di bawah adalah batasan umum yang sudah diketahui.

## Batasan yang Dipatuhi Desain
| Batasan | Dampak |
|---|---|
| Tanpa SSH, tanpa Composer/Node di server | Backend tanpa dependensi/Composer; FE tanpa build |
| Tanpa WebSocket server | Realtime via Pusher (browser↔Pusher langsung) |
| Limit CPU/proses (error 508) & hits harian | Query ringan, paginasi, cache aset, tanpa polling cepat |
| `mail()` dinonaktifkan | Tanpa email |
| Tanpa cron | Logika waktu *lazy* |
| Proteksi anti-bot (cookie JS) pada request non-browser | **Webhook/payment gateway server-to-server sering terblokir** → MVP tanpa gateway; uji API pakai browser/cookie, `curl` dari luar mungkin dapat halaman tantangan |
| Nama & host DB diberi awalan otomatis; `CREATE DATABASE` tidak diizinkan | `schema.sql` tanpa `CREATE DATABASE/USE`; `DB_HOST` bukan localhost |
| Batas ukuran upload & file PHP | Upload ≤ 2MB, kompres GD |
| Outbound cURL terbatas (HTTPS umumnya boleh) | `PusherClient` memakai HTTPS 443, timeout pendek, kegagalan tidak fatal. **Uji di M0** (`BE-017` spike): jika trigger gagal dari hosting, fallback FE polling ≥ 30 dtk, dan laporkan ke manusia |

## Langkah Setup (manusia, HU-003)
1. Buat akun InfinityFree → buat hosting account.
2. Domain: tambahkan `ikurin.sinata.my.id` sebagai *Addon/Parked domain* di panel hosting, lalu atur DNS di penyedia `sinata.my.id` sesuai instruksi panel (umumnya CNAME/NS untuk subdomain; ikuti petunjuk verifikasi InfinityFree). Tunggu propagasi.
3. Pasang **SSL gratis** dari panel (Free SSL), lalu paksa HTTPS di `.htaccess`.
4. Pilih versi PHP 8.x di panel (*PHP Config/Version*), pastikan ekstensi `gd`, `curl`, `pdo_mysql`, `fileinfo` aktif.
5. *MySQL Databases* → buat DB → catat host/nama/user/password → buka phpMyAdmin → import `schema.sql` lalu `seed.sql` (hash admin sudah diganti).
6. Buat akun Pusher (cluster `ap1`), isi key di `.env` produksi.
7. Upload isi folder `htdocs/` ke `htdocs/` hosting via FTP/File Manager (jangan upload folder `htdocs` itu sendiri). Buat `config/.env` **langsung di server**.
8. Pastikan `storage/` & `uploads/` writable; uji akses langsung `/config/.env`, `/storage/private/x` harus 403/404.
9. Smoke test produksi: halaman utama, register, login, `GET /api/v1/config/public`.

## Deploy Berikutnya
Backup DB → upload hanya file yang berubah (cek `git diff --name-only v0.x..main -- htdocs`) → jangan timpa `config/.env`, `uploads/`, `storage/` → smoke test → bila gagal, unggah ulang dari tag sebelumnya.

## Optimasi Wajib (hits/CPU)
`.htaccess`: kompresi gzip/deflate, `Cache-Control`/`Expires` aset (CSS/JS/gambar 7–30 hari, gunakan query `?v=`), gambar produk thumbnail 320px untuk grid, satu file CSS & satu entry JS per halaman, tidak ada request ke API pada polling cepat.
