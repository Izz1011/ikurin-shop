# Pembagian Tugas & Kepemilikan File

| AI | Peran | Worktree / Branch |
|---|---|---|
| **Antigravity CLI** | **BACKEND** — API, DB, keamanan, upload, Pusher server, `.htaccess` | `../ikurin-shop-be` · `feature/be-*` |
| **OpenCode CLI** | **FRONTEND** — semua halaman, CSS, JS, UX, Pusher client, mock | `../ikurin-shop-fe` · `feature/fe-*` |
| **Manusia (integrator)** | Mengubah kontrak/skema/dokumen inti, review & merge, deploy, mengisi secret | `develop` / `main` |

## Matriks Kepemilikan File
| Path | Pemilik | Aturan |
|---|---|---|
| `htdocs/api/**`, `htdocs/app/**`, `htdocs/config/**` (kecuali `.env`) | **BE** | FE dilarang ubah |
| `htdocs/.htaccess`, `htdocs/uploads/.htaccess`, `htdocs/storage/**` | **BE** | FE minta perubahan via `contract-requests.md` |
| `database/**` | **BE** (eksekusi) / **Manusia** (persetujuan skema) | Ubah skema hanya setelah disetujui; buat `migrations/NNN_*.sql` |
| `tests/backend/**` | **BE** | |
| `htdocs/*.html`, `htdocs/seller/**`, `htdocs/admin/**` | **FE** | BE dilarang ubah |
| `htdocs/assets/**` | **FE** | termasuk `js/api.js`, `js/mock/**` |
| `tests/frontend/**` | **FE** | |
| `docs/04-API-CONTRACT.md`, `docs/05-DATABASE.md`, `docs/database/schema.sql`, `docs/01-03`, `docs/06-15`, `AGENTS.md` | **Manusia** | AI hanya membaca |
| `docs/backend/**` | BE boleh menambah catatan di `docs/ai-comms/` saja | |
| `docs/ai-comms/backend-status.md` | **BE** | tulis sendiri |
| `docs/ai-comms/frontend-status.md` | **FE** | tulis sendiri |
| `docs/ai-comms/contract-requests.md` | **BE & FE** (append-only, tiap AI menulis di seksi sendiri) | |
| `docs/09-TASK-BOARD.md` | Masing-masing **hanya mengubah baris tugas dengan prefix miliknya** (`BE-`/`FE-`) | |
| `.gitignore`, `README.md` root | **Manusia** | |

**Zona abu-abu:** file baru yang tidak tercantum → pemilik = AI yang membutuhkannya *dan* berada di folder miliknya. Di luar itu → tanya manusia.

## Lingkup Backend
Auth+session+CSRF+rate limit · RBAC · toko & KYC · produk & upload gambar (GD) · katalog publik · keranjang · checkout transaksional · state machine pesanan · saldo & setoran layanan · chat/notifikasi + Pusher trigger & auth · laporan · admin API & statistik · settings · stream file privat · logging · seed/admin · skrip tes API.

## Lingkup Frontend
Design system CSS · navbar/bottom-nav/komponen · klien API + mock · semua halaman publik, pembeli, seller, admin · route guard per role · form + validasi klien + upload preview · chat UI (Pusher JS) & notifikasi · state loading/empty/error · responsif & aksesibilitas · performa (Lighthouse).

## Titik Sinkronisasi (kontrak)
- FE menulis ke mock sesuai kontrak; BE mengimplementasikan sesuai kontrak. **Keduanya tidak saling menunggu** sampai milestone integrasi (M3).
- Perbedaan antara perilaku BE dan kontrak = bug **BE**. Kebutuhan UI yang tidak ada di kontrak = `contract-request` dari FE.
