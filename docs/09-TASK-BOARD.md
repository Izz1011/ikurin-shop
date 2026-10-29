# Task Board

Status: `TODO` · `IN_PROGRESS` · `REVIEW` · `DONE` · `BLOCKED`. Prioritas: **P0** wajib MVP, P1 penting, P2 bagus-ada.
Aturan: AI hanya mengubah kolom Status pada baris berprefix miliknya. Ambil tugas teratas yang dependensinya `DONE`.

## Milestone
| M | Tujuan | Syarat lulus |
|---|---|---|
| M0 | Fondasi | Struktur, core backend, design system FE, mock API |
| M1 | Auth & Toko | Register/login, buka toko, verifikasi admin |
| M2 | Katalog & Keranjang | Produk CRUD, katalog, keranjang |
| M3 | **Integrasi** | FE `USE_MOCK=false` jalan penuh terhadap BE (auth→checkout) |
| M4 | Pesanan, Setoran, Chat | State machine, saldo & setoran, chat Pusher |
| M5 | Admin, Polish, Deploy | Dashboard admin, keamanan, performa, deploy InfinityFree |

## Backend (Antigravity)
| ID | Tugas | P | Dep | M | Status |
|---|---|---|---|---|---|
| BE-001 | Struktur `htdocs/app`, autoload PSR-4, `Env`, `Database` (PDO), `Response`, `Router`, error handler, logger | P0 | – | M0 | TODO |
| BE-002 | `.htaccess` (rewrite API, blokir folder privat, header keamanan), uploads/.htaccess, `config/.env.example` | P0 | BE-001 | M0 | TODO |
| BE-003 | Session + CSRF + `GET /auth/csrf`, `/config/public`, `/auth/me` | P0 | BE-001 | M0 | TODO |
| BE-004 | Register/login/logout, rate limit, timeout sesi, ubah profil/password | P0 | BE-003 | M1 | TODO |
| BE-005 | Middleware RBAC (Auth, Role, StoreActive) | P0 | BE-004 | M1 | TODO |
| BE-006 | `ImageUploader` (validasi MIME, GD re-encode, ≤300KB, nama acak, private/public) | P0 | BE-001 | M1 | TODO |
| BE-007 | Buka toko + status pengajuan | P0 | BE-005,006 | M1 | TODO |
| BE-008 | Admin: antrian toko, approve/reject/suspend/delete, stream KTP, ubah role | P0 | BE-007 | M1 | TODO |
| BE-009 | Seller: profil toko, CRUD produk + gambar | P0 | BE-005,006 | M2 | TODO |
| BE-010 | Katalog publik: products/stores (cari, filter, sort, paginasi) | P0 | BE-009 | M2 | TODO |
| BE-011 | Keranjang (CRUD, grup per toko, cek stok) | P0 | BE-010 | M2 | TODO |
| BE-012 | `Money`, settings service, `checkout/preview` | P0 | BE-011 | M2 | TODO |
| BE-013 | `POST /orders` transaksional (lock stok, snapshot, limit titipan, notifikasi) | P0 | BE-012 | M4 | TODO |
| BE-014 | Pesanan pembeli: list/detail/cancel/payment-proof | P0 | BE-013 | M4 | TODO |
| BE-015 | Seller pesanan: list/detail/status (state machine), confirm-payment, kembalikan stok | P0 | BE-013 | M4 | TODO |
| BE-016 | Saldo titipan & setoran: `/seller/fees`, `/seller/settlements` | P0 | BE-015 | M4 | TODO |
| BE-017 | `PusherClient` (cURL+HMAC), `chat/*`, `pusher-auth`, notifikasi | P0 | BE-005 | M4 | TODO |
| BE-018 | Admin: settlements verify, fee-ledger, users, reports, settings | P0 | BE-016 | M5 | TODO |
| BE-019 | Dashboard seller & admin (agregasi hemat query) | P1 | BE-015 | M5 | TODO |
| BE-020 | Laporan pengguna `reports` | P1 | BE-005 | M5 | TODO |
| BE-021 | Tes backend + `tests/api-smoke.sh` | P0 | per fitur | M1–M5 | TODO |
| BE-022 | Audit keamanan & performa (lihat `13-SECURITY.md`) | P0 | semua | M5 | TODO |
| BE-023 | Skrip `database/` final, migrasi, panduan import | P1 | BE-022 | M5 | TODO |

## Frontend (OpenCode)
| ID | Tugas | P | Dep | M | Status |
|---|---|---|---|---|---|
| FE-001 | Design system CSS (variabel, tipografi, tombol, form, card, grid, toast, modal, skeleton) | P0 | – | M0 | REVIEW |
| FE-002 | `api.js` (CSRF, envelope, error handling) + adaptor mock + fixture sesuai kontrak | P0 | – | M0 | REVIEW |
| FE-003 | Komponen: navbar, bottom-nav mobile, footer, product card, pagination, format Rupiah | P0 | FE-001 | M0 | REVIEW |
| FE-004 | Halaman login & register + route guard (`guard.js`) | P0 | FE-002 | M1 | TODO |
| FE-005 | Profil + Buka Toko (form, preview, status pengajuan) | P0 | FE-004 | M1 | TODO |
| FE-006 | Admin: login-guard, daftar & detail verifikasi toko | P0 | FE-004 | M1 | TODO |
| FE-007 | Beranda/katalog (cari, filter, sort, paginasi), detail produk, halaman toko | P0 | FE-003 | M2 | TODO |
| FE-008 | Keranjang (grup per toko, stepper qty) | P0 | FE-007 | M2 | TODO |
| FE-009 | Seller: dashboard, daftar produk, form produk + upload | P0 | FE-004 | M2 | TODO |
| FE-010 | **Integrasi M3:** matikan mock, uji alur auth→katalog→keranjang vs BE, perbaiki selisih | P0 | BE-011 | M3 | TODO |
| FE-011 | Checkout (alamat, patokan, metode, rincian biaya, anti double-submit) | P0 | FE-008 | M4 | TODO |
| FE-012 | Pesanan pembeli: daftar, detail, batal, unggah bukti | P0 | FE-011 | M4 | TODO |
| FE-013 | Seller pesanan: tab status, aksi status, konfirmasi bayar | P0 | FE-009 | M4 | TODO |
| FE-014 | Seller: saldo titipan (progress limit), setor layanan, riwayat | P0 | FE-009 | M4 | TODO |
| FE-015 | Chat UI + Pusher JS + badge notifikasi | P0 | FE-004 | M4 | TODO |
| FE-016 | Admin: dashboard, verifikasi setoran, fee-ledger, user, laporan, pengaturan | P0 | FE-006 | M5 | TODO |
| FE-017 | Halaman laporan/CS pengguna | P1 | FE-004 | M5 | TODO |
| FE-018 | Polish: responsif, aksesibilitas, empty/error state, Lighthouse > 85 | P0 | semua | M5 | TODO |
| FE-019 | Tes manual/E2E checklist (`tests/frontend`) | P0 | per fitur | M1–M5 | TODO |

## Integrator (Manusia)
| ID | Tugas | M | Status |
|---|---|---|---|
| HU-001 | Commit docs ke `main`, buat `develop` + worktree | M0 | DONE |
| HU-002 | Buat akun Pusher (app, key, secret, cluster `ap1`) | M0 | TODO |
| HU-003 | Setup InfinityFree: akun hosting, DB, domain `ikurin.sinata.my.id`, SSL | M0 | TODO |
| HU-004 | Review PR, merge ke `develop`, putuskan `contract-requests` | tiap M | TODO |
| HU-005 | Uji M3 integrasi di lokal & staging, deploy per milestone, tag rilis | M3–M5 | TODO |
