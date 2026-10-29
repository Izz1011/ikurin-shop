# Aturan Coding — Ikurin Shop

## Umum
- Indentasi 4 spasi (PHP) / 2 spasi (HTML, CSS, JS). UTF-8, LF, newline di akhir file.
- Satu tanggung jawab per file/fungsi. Fungsi ≤ ~40 baris. Tidak ada kode mati/komentar kode lama.
- Komentar menjelaskan **kenapa**, bukan **apa**. Identifier berbahasa Inggris; teks UI Indonesia.
- Tidak ada magic number: pakai konstanta / `settings`.

## Backend (PHP 8.x)
- `declare(strict_types=1);` di setiap file. Namespace `App\...`, PSR-4 manual, PSR-12 style.
- Nama: Class `PascalCase`, method/variabel `camelCase`, konstanta `UPPER_SNAKE`, file = nama class. Kolom DB `snake_case`.
- Controller: `XxxController` (tipis). Service: `XxxService` (bisnis + transaksi). Repository: `XxxRepository` (SQL).
- **Semua SQL**: PDO prepared statement, named parameter. Dilarang string-concat input ke SQL. `PDO::ATTR_EMULATE_PREPARES=false`, `ERRMODE_EXCEPTION`, `utf8mb4`.
- `LIMIT/OFFSET` di-cast int sebelum bind. Dilarang `SELECT *` pada query list; sebutkan kolom.
- Validasi input di satu tempat (`Validator`), kembalikan 422 sesuai kontrak. Jangan percaya field dari klien (harga, total, fee, role, status).
- Transaksi (`beginTransaction/commit/rollBack`) untuk operasi multi-tabel; gunakan `SELECT … FOR UPDATE` untuk stok.
- Tidak ada `echo`/`var_dump`/`die` di kode produksi; semua output lewat `Response::json()`.
- Error: lempar `AppException(code, message, httpStatus, fields)`; handler global yang mengubahnya jadi JSON. Detail teknis hanya ke log.
- Tidak ada `eval`, `exec`, `shell_exec`, `system`, `unserialize` data user.
- Timezone: `date_default_timezone_set('Asia/Jakarta')`; DB `SET time_zone = '+07:00'`.

## Frontend (HTML/CSS/JS)
- HTML semantik (`header, nav, main, section, article, footer`), satu `<h1>` per halaman, `lang="id"`, `alt` pada gambar, label pada input.
- CSS: **vanilla**, mobile-first, CSS variables untuk warna/spasi/radius (lihat `frontend/DESIGN-SYSTEM.md`). Penamaan **BEM** (`.product-card__title--sale`). Tanpa `!important`, tanpa inline style kecuali nilai dinamis.
- JS: ES Modules, `const/let` (tanpa `var`), `async/await`, tanpa library tambahan. Nama: variabel/fungsi `camelCase`, konstanta `UPPER_SNAKE`, file `kebab-case.js`.
- Semua panggilan jaringan lewat `assets/js/api.js`. Dilarang `fetch` langsung di halaman.
- Render data user dengan `textContent` / `createElement`. `innerHTML` hanya untuk template statis tanpa data dinamis.
- Semua aksi async punya state: loading, sukses, error (toast), empty state. Cegah double-submit (disable tombol).
- Format uang: `Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0})`.
- Aksesibilitas: fokus terlihat, kontras AA, tap target ≥ 44px, navigasi keyboard.
- Gambar: `loading="lazy"`, `width/height` eksplisit, tanpa layout shift.

## Struktur & Penamaan File
- Halaman: `htdocs/<nama>.html` + `assets/js/pages/<nama>.js` + (opsional) `assets/css/pages/<nama>.css`.
- Seller/admin: subfolder `seller/`, `admin/` dengan pola sama.

## Kualitas
- `php -l` lolos pada semua file PHP. Tidak ada warning/notice (`error_reporting(E_ALL)` di lokal).
- Tidak ada secret di repo. Tidak ada `console.log` tertinggal di commit akhir.
- Jangan refactor file di luar tugas. Perubahan minimal & fokus.
