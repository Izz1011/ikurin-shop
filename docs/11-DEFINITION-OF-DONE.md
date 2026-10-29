# Definition of Done

Tugas boleh `REVIEW` hanya jika **semua** poin relevan terpenuhi.

## Umum
- [ ] Sesuai `04-API-CONTRACT.md` & aturan `06-CODING-STANDARDS.md`; tidak ada file di luar kepemilikan diubah.
- [ ] Commit rapi (Conventional Commits), branch ter-push, PR berisi ringkasan + cara uji.
- [ ] Tidak ada secret/log debug/`TODO` tanpa label di commit.
- [ ] `docs/ai-comms/<peran>-status.md` & baris Task Board diperbarui.

## Backend
- [ ] `php -l` lolos, tanpa warning (`E_ALL`).
- [ ] Semua query PDO prepared; input divalidasi; RBAC + kepemilikan (anti-IDOR) dicek.
- [ ] Mutasi multi-tabel dalam transaksi; stok aman dari race condition.
- [ ] Format respons/error persis kontrak; kode HTTP benar.
- [ ] Tes (unit/smoke) untuk fitur ditulis & lulus; contoh `curl` ditulis di status.
- [ ] Tidak ada query N+1; list berpaginasi; kolom terindeks dipakai.

## Frontend
- [ ] Jalan dengan mock **dan** (setelah M3) dengan API asli.
- [ ] Responsif 360/768/1280, loading/empty/error state, tanpa error console.
- [ ] Data dinamis di-render aman (tanpa XSS); tombol aksi anti double-submit.
- [ ] Teks Bahasa Indonesia, format Rupiah/tanggal benar, aksesibel (label, fokus, kontras).
- [ ] Mengikuti design system (warna, font, spasi); tidak menambah library.

## Milestone
- [ ] Syarat lulus milestone (`09-TASK-BOARD.md`) terpenuhi & dicoba manusia.
