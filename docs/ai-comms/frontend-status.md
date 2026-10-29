# Status Frontend (ditulis hanya oleh FRONTEND AI — OpenCode)

## Ringkasan Saat Ini
- Tugas aktif: FE-006 — Admin: login-guard, daftar & detail verifikasi toko
- Terakhir selesai: FE-005 — Profil + Buka Toko (form, preview, status pengajuan) — status `REVIEW`
- Langkah berikutnya: FE-006

## Pertanyaan Terbuka
(kosong)

## Log Progres
<!-- Gunakan template di docs/14-AI-WORKFLOW.md; entri terbaru di bawah -->
### 2026-10-29 18:30 — FE-001 — REVIEW
- Ringkasan: Design system CSS "Happy & Calm" selesai — token CSS custom properties, base reset/typografi, komponen (tombol, card, form, product grid, toast, modal, skeleton, badge status).
- File diubah: `htdocs/assets/css/tokens.css`, `htdocs/assets/css/base.css`, `htdocs/assets/css/components.css` (baru).
- Cara uji: buka halaman publik setelah FE-003 tersedia; cek token di DevTools; breakpoint 360/768/1280.
- Penyimpangan dari kontrak: tidak ada (FE-001 murni presentasi/tokens).
- Pertanyaan/Keputusan dibutuhkan: konfirmasi warna badge/priority & nama kelas komponen sebelum FE-003.

### 2026-10-29 18:47 — FE-002 — REVIEW
- Ringkasan: API client (`assets/js/api.js`) + mock handler/fixtures (`assets/js/mock/`) + `assets/js/config.js` dibuat — `api.get/post/patch/del/upload`, envelope parsing, `ApiError{code,message,fields,status}`, CSRF token via `/auth/csrf`, 401 → redirect login.
- File diubah: `htdocs/assets/js/config.js`, `htdocs/assets/js/api.js`, `htdocs/assets/js/mock/handler.js`, `htdocs/assets/js/mock/fixtures.js` (baru).
- Cara uji: modul api dengan `USE_MOCK=true` — endpoint `/config/public`, `/auth/csrf`, `/auth/me`, `/products`, `/products/{id}`, `/auth/login`, `/auth/register`.
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: pemilihan named export `IKURIN_CONFIG` + assignment `window.IKURIN_CONFIG` di `config.js` (disetujui).

### 2026-10-29 18:57 — FE-003 — REVIEW
- Ringkasan: Komponen reusable UI (navbar, bottom-nav, footer, product card, pagination, toast, modal) + `format.js` (Rupiah & tanggal Indonesia Intl) selesai.
- File diubah: `htdocs/assets/js/components/*.js`, `htdocs/assets/js/format.js` (baru).
- Cara uji: butuh halaman HTML pembungkus komponen; cek via halaman setelah FE-007.
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: titik mounting komponen perlu kontrak/arsitektur halaman (mis. `index.html` memakai `.product-grid`).

### 2026-10-29 19:05 — FE-004 — REVIEW
- Ringkasan: Halaman `login.html`, `register.html`, page JS (`pages/login.js`, `pages/register.js`) + `guard.js` (`requireRole`) selesai — form login/register via `api`, toast error, anti double-submit, redirect setelah sukses.
- File diubah: `htdocs/login.html`, `htdocs/register.html`, `htdocs/assets/css/pages/auth.css`, `htdocs/assets/js/pages/login.js`, `htdocs/assets/js/pages/register.js`, `htdocs/assets/js/guard.js` (baru).
- Cara uji: butuh backend/mock; cek via browser setelah halaman dilengkapi (FE-007).
- Penyimpangan dari kontrak: tidak ada.
- Pertanyaan/Keputusan dibutuhkan: redirect setelah login ke `?next=` — validasi path dari klien (TODO(question)).

### 2026-10-29 20:10 — FE-005 — REVIEW
- Ringkasan: Halaman Profil (`profile.html` + `pages/profile.js`) & Buka Toko (`open-store.html` + `pages/open-store.js`) selesai — form Buka Toko (field wajib, preview gambar KTP/logo via object URL, submit multipart ke `POST /stores`, redirect ke profil) & halaman Profil (akun via `GET /auth/me`, status pengajuan via `GET /stores/me/application`).
- File diubah: `htdocs/profile.html`, `htdocs/open-store.html`, `htdocs/assets/css/pages/{profile,open-store}.css`, `htdocs/assets/js/pages/{profile,open-store}.js` (baru); `htdocs/assets/js/guard.js` (tambah `requireAuth`).
- Cara uji: `USE_MOCK=true` — login `mock@mail.com` / `mock12345`, buka tokos via `/open-store.html`, status pengajuan muncul di `/profile.html`.
- Penyimpangan dari kontrak: tidak ada (fix: user read memakai `GET /auth/me` sesuai kontrak; `PATCH/POST /users/me` hanya untuk update).
- Pertanyaan/Keputusan dibutuhkan: `POST /stores` mock tidak mensimulasikan 409 nama toko dipakai.

