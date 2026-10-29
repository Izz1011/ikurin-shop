# Ikurin Shop — Paket Dokumentasi untuk 2 AI CLI

Paket ini membuat **Antigravity CLI (Backend)** dan **OpenCode CLI (Frontend)** bisa bekerja paralel tanpa saling menimpa.
Kuncinya: **kontrak API dulu** (`04-API-CONTRACT.md`), **kepemilikan file yang jelas** (`07-TASK-SPLIT.md`), dan **Git worktree terpisah** (`08-GIT-WORKFLOW.md`).

## Isi
| File | Fungsi |
|---|---|
| `AGENTS.md` | Aturan wajib kedua AI (salin ke root repo) |
| `01-PRD.md` | PRD yang sudah dioptimalkan |
| `02-SRS.md` | SRS, dipisah Backend / Frontend |
| `03-ARCHITECTURE.md` | Arsitektur, struktur folder, alur request |
| `04-API-CONTRACT.md` | **Sumber kebenaran** endpoint & JSON |
| `05-DATABASE.md` + `database/schema.sql` | Skema, ERD, aturan data |
| `06-CODING-STANDARDS.md` | Penamaan, struktur, kualitas kode |
| `07-TASK-SPLIT.md` | Pembagian tugas + file yang boleh diubah |
| `08-GIT-WORKFLOW.md` | Branch, worktree, commit, merge, recovery |
| `09-TASK-BOARD.md` | Daftar tugas, prioritas, dependensi, status |
| `10-TESTING-PLAN.md` | Unit, integrasi API, fungsional |
| `11-DEFINITION-OF-DONE.md` | Kriteria selesai |
| `12-ENVIRONMENT.md` | Versi, `.env`, konfigurasi lokal |
| `13-SECURITY.md` | Keamanan |
| `14-AI-WORKFLOW.md` | Komunikasi, laporan progres, konflik |
| `15-HOSTING-INFINITYFREE.md` | Batasan & cara deploy ke InfinityFree |
| `backend/`, `frontend/` | SRS spesifik + **START_PROMPT** untuk tiap AI |
| `ai-comms/` | File status & permintaan antar-AI |
| `templates/` | `.gitignore`, `.env.example`, `.htaccess` siap pakai |

## Langkah Setup (sekali saja, di folder `ikurin-shop`, branch `main`; pakai Git Bash/terminal bash)
```bash
# 1. Ekstrak docs.zip di root repo -> terbentuk ikurin-shop/docs/
cp docs/AGENTS.md ./AGENTS.md
cp docs/templates/.gitignore ./.gitignore
mkdir -p htdocs/api htdocs/app htdocs/config htdocs/storage/private htdocs/storage/logs htdocs/uploads htdocs/assets/css htdocs/assets/js htdocs/assets/img database tests/backend tests/frontend
cp docs/templates/.env.example htdocs/config/.env.example
cp docs/templates/htaccess.root htdocs/.htaccess
cp docs/templates/htaccess.private htdocs/app/.htaccess
cp docs/templates/htaccess.private htdocs/config/.htaccess
cp docs/templates/htaccess.private htdocs/storage/.htaccess
cp docs/templates/htaccess.uploads htdocs/uploads/.htaccess
cp docs/database/schema.sql docs/database/seed.sql database/
touch htdocs/storage/private/.gitkeep htdocs/storage/logs/.gitkeep htdocs/uploads/.gitkeep
git add -A && git commit -m "docs: add project documentation and scaffolding" && git push origin main

# 2. Buat branch integrasi + 2 worktree (folder terpisah untuk tiap AI)
git branch develop && git push -u origin develop
git worktree add ../ikurin-shop-be -b feature/be-foundation develop
git worktree add ../ikurin-shop-fe -b feature/fe-foundation develop
```
3. Buka terminal 1 di `../ikurin-shop-be` → jalankan **Antigravity CLI** → tempel isi `docs/backend/START_PROMPT.md`.
4. Buka terminal 2 di `../ikurin-shop-fe` → jalankan **OpenCode CLI** → tempel isi `docs/frontend/START_PROMPT.md`.
5. Anda (manusia) = *integrator*: review PR ke `develop`, putuskan perubahan kontrak API, merge ke `main` saat milestone lulus.

> Perubahan penting dari PRD/SRS awal (dan alasannya) ada di `ai-comms/decisions.md`. **Baca dulu sebelum mulai.**
