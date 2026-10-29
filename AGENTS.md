# AGENTS.md — Ikurin Shop (berlaku untuk Antigravity CLI & OpenCode CLI)

Marketplace kuliner/bakery lokal Banjarnegara. PHP 8.x vanilla (tanpa framework) + MySQL/MariaDB + Pusher. Hosting **InfinityFree** (gratis), domain `ikurin.sinata.my.id`. Bahasa UI: Indonesia. Bahasa kode/komentar: Inggris (nama identifier), komentar boleh Indonesia.

## 0. Cari tahu kamu siapa
Lihat nama worktree/branch kamu:
- Branch `feature/be-*` / folder `ikurin-shop-be` → kamu **BACKEND AI** (Antigravity).
- Branch `feature/fe-*` / folder `ikurin-shop-fe` → kamu **FRONTEND AI** (OpenCode).
Jika ragu, TANYA pengguna. Jangan menebak.

## 1. Baca dulu (urutan wajib)
1. `docs/README.md` → `docs/ai-comms/decisions.md`
2. `docs/04-API-CONTRACT.md` (sumber kebenaran antar-AI)
3. `docs/07-TASK-SPLIT.md` (file yang boleh kamu ubah)
4. `docs/backend/` ATAU `docs/frontend/` sesuai peranmu
5. `docs/09-TASK-BOARD.md` → ambil tugas milikmu dengan prefix `BE-` / `FE-`
Dokumen lain dibaca saat relevan (DB, keamanan, testing, coding standards).

## 2. Aturan emas
1. **Kontrak API adalah hukum.** Dilarang mengubah `docs/04-API-CONTRACT.md` dan `docs/database/schema.sql` sendiri. Butuh perubahan → tulis di `docs/ai-comms/contract-requests.md` lalu lanjut tugas lain / pakai asumsi sementara yang ditandai `TODO(contract)`.
2. **Hanya sentuh file milikmu** (lihat `07-TASK-SPLIT.md`). File milik AI lain: baca boleh, ubah DILARANG.
3. **Kerja kecil & commit sering** di branch milikmu. Jangan pernah commit langsung ke `main` atau `develop`.
4. **Jangan hardcode** secret, URL absolut, atau ID. Gunakan `.env` (BE) / `window.IKURIN_CONFIG` + `GET /api/v1/config/public` (FE).
5. **Jangan menambah dependensi** (Composer/NPM/CDN) tanpa izin pengguna. Server tidak punya SSH, Composer, maupun Node.
6. **Ingat batasan InfinityFree** (`docs/15-HOSTING-INFINITYFREE.md`): tanpa WebSocket server, tanpa cron, tanpa polling < 10 detik, hemat CPU/hit.
7. **Keamanan non-negosiable** (`docs/13-SECURITY.md`): PDO prepared statement, `htmlspecialchars`/`textContent`, CSRF, validasi upload, RBAC di server.
8. **Jangan menjalankan perintah destruktif** (`rm -rf`, `git reset --hard`, `git push --force`, drop database) tanpa persetujuan eksplisit pengguna.
9. **Jangan mengarang.** Jika spesifikasi ambigu: catat di `ai-comms/*-status.md` bagian "Pertanyaan", pilih opsi paling sederhana yang aman, tandai `TODO(question)`.
10. Selesai ≠ "kode ditulis". Selesai = memenuhi `docs/11-DEFINITION-OF-DONE.md`.

## 3. Siklus kerja per tugas
1. Pilih tugas `TODO` teratas yang dependensinya sudah `DONE` di `09-TASK-BOARD.md`; ubah ke `IN_PROGRESS` (hanya barisnya sendiri).
2. Implementasi → test → lint (`php -l`) / cek browser.
3. Commit (`08-GIT-WORKFLOW.md`): `feat(be): ...` / `feat(fe): ...`.
4. Update `docs/ai-comms/<peran>-status.md` (template ada di dalamnya).
5. Ubah tugas ke `REVIEW`, lalu BERHENTI dan minta pengguna review/merge bila itu akhir milestone.

## 4. Format respons ke pengguna
Singkat: apa yang selesai, file yang diubah, cara mengujinya, hal yang butuh keputusan.
