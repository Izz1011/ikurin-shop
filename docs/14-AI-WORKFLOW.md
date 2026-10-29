# Workflow AI — Komunikasi, Progres, Konflik

## Saluran Komunikasi (semua lewat file di Git, bukan chat langsung)
| File | Penulis | Isi |
|---|---|---|
| `ai-comms/backend-status.md` | BE | log progres, endpoint selesai + contoh curl, pertanyaan |
| `ai-comms/frontend-status.md` | FE | log progres, halaman selesai, pertanyaan |
| `ai-comms/contract-requests.md` | BE & FE (seksi masing-masing) | usulan perubahan kontrak/skema |
| `ai-comms/decisions.md` | Manusia | keputusan final, perubahan dari PRD/SRS awal |
Karena kedua AI berada di worktree berbeda, mereka baru melihat file lawan setelah manusia merge ke `develop` dan AI `git merge origin/develop`. Maka kontrak harus cukup lengkap sehingga **tidak perlu** komunikasi sinkron.

## Laporan Progres (tiap tugas selesai — append di status)
```
### YYYY-MM-DD HH:MM — <ID tugas> — <REVIEW|BLOCKED|DONE>
- Ringkasan:
- File diubah:
- Cara uji:            (curl / langkah browser)
- Penyimpangan dari kontrak: (tidak ada | jelaskan)
- Pertanyaan/Keputusan dibutuhkan:
```

## Contract Change Request (CCR)
```
### CCR-BE-001 | CCR-FE-001 — <judul singkat> — status: OPEN
- Alasan/kebutuhan:
- Usulan perubahan (endpoint/field/tipe):
- Dampak ke sisi lain:
- Workaround sementara (TODO(contract)):
```
Manusia memutuskan → menandai `ACCEPTED`/`REJECTED`, memperbarui kontrak, mencatat di `decisions.md` dan menaikkan versi kontrak.

## Penanganan Konflik & Ambiguitas
1. Dokumen bertentangan → urutan otoritas: `AGENTS.md` > `04-API-CONTRACT.md` > `05-DATABASE.md`/`schema.sql` > `02-SRS.md` > dokumen lain.
2. Ambigu → pilih opsi paling sederhana & aman, tandai `TODO(question)`, tulis di status, lanjut.
3. Butuh file milik AI lain → CCR atau catatan, jangan ubah.
4. Konflik Git kode → berhenti, laporkan.
5. Terblokir > 1 tugas → status `BLOCKED` + alasan; ambil tugas independen lain.

## Disiplin Sesi
- Awal sesi: `git status`, baca `AGENTS.md`, status sendiri, Task Board.
- Akhir sesi: commit + push, update status & Task Board, tuliskan "langkah berikutnya".
- Konteks panjang: ringkas ke status file agar sesi baru bisa lanjut tanpa riwayat chat.
