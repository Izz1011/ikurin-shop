# Keamanan

## Kredensial & Secret
- Semua secret di `htdocs/config/.env` (di-`.gitignore`); hanya `.env.example` di repo. `PUSHER_SECRET` & DB password tidak pernah masuk FE/log/respons.
- Jika secret tak sengaja ter-commit: anggap bocor → rotasi segera, lapor manusia (jangan hanya hapus commit).

## Autentikasi & Sesi
Bcrypt (`password_hash`, `password_verify`); `session_regenerate_id(true)` saat login/logout; cookie `HttpOnly; Secure; SameSite=Lax`; `session.use_strict_mode=1`; timeout idle; rate limit login; pesan error login generik; password ≥ 8 karakter.

## Otorisasi
RBAC di server pada setiap endpoint (jangan percaya UI). **Cek kepemilikan** (anti-IDOR): pesanan milik pembeli/toko terkait, produk milik toko sendiri, chat hanya peserta. Role, status, harga, fee, total **tidak pernah** diambil dari klien.

## Input/Output
- SQLi: PDO prepared statement saja.
- XSS: BE tidak merender HTML; FE `textContent`. Header `Content-Security-Policy` ketat (`default-src 'self'; script-src 'self' https://js.pusher.com; connect-src 'self' https://*.pusher.com wss://*.pusher.com; img-src 'self' data:; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com`), `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`.
- CSRF: token per sesi, header `X-CSRF-Token`, bandingkan `hash_equals`.
- Mass assignment: whitelist field per endpoint.

## Upload
Validasi ukuran, ekstensi **dan** MIME asli (`finfo`), `getimagesize`; re-encode GD (membuang payload tersembunyi); nama `md5(uniqid('',true)).jpg`; folder `uploads/` dengan `php_flag engine off`/`RemoveHandler` + tanpa listing; **KTP, bukti bayar, bukti setoran → `storage/private/`** (diblokir web, disajikan oleh endpoint terotorisasi dengan `Content-Type` tetap).

## Pembatasan Izin & Pemeriksaan Perubahan (untuk kedua AI)
- AI hanya mengubah file miliknya (`07-TASK-SPLIT.md`); AI menjalankan `git diff --stat` sebelum commit dan memastikan hanya file miliknya berubah.
- Manusia mereview diff PR: cari secret, `eval/exec`, `innerHTML` dengan data, query concat, perubahan `.htaccess`, dependensi baru.
- Perintah destruktif butuh persetujuan (lihat `AGENTS.md` & `08-GIT-WORKFLOW.md`). AI tidak boleh mengakses file di luar folder worktree-nya.
- Jangan paste secret ke prompt AI. Gunakan nilai dummy; manusia mengisi `.env` sendiri.

## Hosting
Backup DB berkala (export phpMyAdmin) sebelum tiap deploy; HTTPS dipaksa via `.htaccess`; matikan `display_errors` di produksi; log di `storage/logs` (diblokir web).
## Checklist Audit (BE-022)
[ ] tak ada SQL non-prepared [ ] semua POST/PATCH/DELETE cek CSRF [ ] semua endpoint punya cek role+kepemilikan [ ] folder privat 403 [ ] header keamanan aktif [ ] upload tak bisa menjalankan PHP [ ] rate limit login [ ] error tidak membocorkan stack trace.
