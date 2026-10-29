# Git & Version Control

Repo: `ikurin-shop` (remote `origin` di GitHub). Cabang awal: `main`.

## Cabang
| Branch | Fungsi | Siapa yang menulis |
|---|---|---|
| `main` | Stabil, siap deploy. Hanya menerima merge dari `develop` pada milestone lulus | Manusia |
| `develop` | Integrasi BE+FE | Manusia (merge PR) |
| `feature/be-<topik>` | Kerja backend, mis. `feature/be-auth`, `feature/be-checkout` | Antigravity |
| `feature/fe-<topik>` | Kerja frontend, mis. `feature/fe-catalog` | OpenCode |
| `hotfix/<topik>` | Perbaikan urgen dari `main` | Manusia |
AI **dilarang** commit/push langsung ke `main` atau `develop`.

## Worktree (wajib agar 2 AI tidak bentrok di satu folder)
```bash
git worktree add ../ikurin-shop-be -b feature/be-foundation develop
git worktree add ../ikurin-shop-fe -b feature/fe-foundation develop
git worktree list
```
Tiap AI hanya bekerja di folder worktree-nya. Saat memulai topik baru: dari worktree, `git fetch && git switch -c feature/be-<topik> origin/develop`.
Sinkron dengan perubahan lawan: `git fetch origin && git merge origin/develop` (atau rebase bila disetujui) — **hanya** setelah manusia merge ke `develop`.

## Commit — Conventional Commits
`<type>(<scope>): <ringkasan imperatif, ≤ 72 char>` · type: `feat fix refactor style docs test chore perf` · scope: `be`, `fe`, `db`, `docs`.
Contoh: `feat(be): add checkout transaction with stock locking` · `fix(fe): prevent double submit on checkout`.
Aturan: 1 commit = 1 perubahan logis; commit kecil & sering; jangan commit `.env`, `vendor/`, file upload, `storage/logs`, file besar.

## Alur Per Topik
1. Kerjakan di `feature/*` → commit → `git push -u origin <branch>`.
2. Buka Pull Request ke `develop` (judul = commit utama; isi: ringkasan, cara uji, centang DoD).
3. Manusia review (+ jalankan smoke test) → **Squash/Merge** ke `develop`.
4. Di akhir milestone: PR `develop` → `main`, beri tag `v0.<milestone>` (mis. `v0.3.0`).
Perlu menunggu merge? AI melanjutkan tugas lain di branch yang sama/berikutnya, tidak menunggu diam.

## Konflik
- Karena kepemilikan file terpisah, konflik seharusnya hanya di `docs/09-TASK-BOARD.md` dan `ai-comms/` → selesaikan dengan menyimpan **kedua** baris/seksi.
- Jika konflik di kode: **berhenti**, jangan menimpa; laporkan ke manusia dengan daftar file.

## Pemulihan (aman → keras)
| Kebutuhan | Perintah |
|---|---|
| Lihat perubahan | `git status`, `git diff`, `git log --oneline -20` |
| Batalkan perubahan file belum commit | `git restore <file>` |
| Unstage | `git restore --staged <file>` |
| Batalkan commit yang sudah di-push | `git revert <sha>` (aman, membuat commit baru) |
| Kembali ke commit tertentu sementara | `git switch -c rescue <sha>` |
| Commit "hilang" | `git reflog` → `git switch -c rescue <sha>` |
| Simpan sementara | `git stash push -m "wip"` / `git stash pop` |
| Rilis bermasalah | deploy ulang dari tag sebelumnya: `git checkout v0.2.0 -- htdocs` |
**Dilarang tanpa izin manusia:** `git reset --hard`, `git push --force`, `git clean -fd`, `git branch -D`, rewriting history.

## Tag & Rilis
Tag di `main` per milestone. Deploy = upload isi `htdocs/` ke InfinityFree (lihat `15-HOSTING-INFINITYFREE.md`), **tanpa** `.env` lokal.
