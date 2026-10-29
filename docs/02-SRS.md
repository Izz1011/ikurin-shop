# SRS — Ikurin Shop (v1.1)

Arsitektur: **Vanilla PHP 8.x REST API (backend)** + **HTML/CSS/JS statis tanpa build step (frontend)** dalam satu repo, dideploy ke InfinityFree. Database MySQL/MariaDB. Realtime: Pusher Channels. Detail: `03-ARCHITECTURE.md`.
Legenda: **[BE]** = Antigravity, **[FE]** = OpenCode. Satu requirement boleh punya sisi BE dan FE; keduanya harus sesuai `04-API-CONTRACT.md`.

## 1. Batasan Sistem
1. Tanpa WebSocket server/Node → realtime hanya via Pusher (browser subscribe; PHP `trigger` via cURL).
2. Tanpa polling AJAX < 10 detik; polling hanya fallback ≥ 30 detik dan hanya saat tab aktif.
3. PHP 8.x tanpa framework besar. Tanpa Composer di server (kode custom ringan; library hanya jika di-upload manual dan disetujui).
4. Semua query: **PDO + prepared statement**.
5. Hemat CPU (error 508) & hits: query ber-index, paginasi wajib, tidak ada loop N+1, aset statis di-cache browser.
6. Tanpa cron → semua logika waktu dihitung *lazy* (saat request).
7. Tanpa `mail()` → tidak ada fitur email.

## 2. Persyaratan Fungsional

### 2.1 Autentikasi & RBAC (FR-AUTH)
| ID | Requirement | Sisi |
|---|---|---|
| FR-AUTH-01 | Register/login; password `password_hash(PASSWORD_BCRYPT)`; email unik | BE |
| FR-AUTH-02 | RBAC `USER`/`TOKO`/`ADMIN` ditegakkan di setiap endpoint | BE |
| FR-AUTH-03 | Session timeout idle (default 30 mnt, `settings`), cookie HttpOnly+Secure+SameSite=Lax, `session_regenerate_id` saat login | BE |
| FR-AUTH-04 | Rate limit login: 5 gagal/15 mnt per email+IP (`login_attempts`) | BE |
| FR-AUTH-05 | Form login/register, route guard per role (redirect), tampilkan pesan timeout | FE |
| FR-AUTH-06 | Akun `is_active=0` tidak bisa login | BE |

### 2.2 Toko & KYC (FR-STORE)
| ID | Requirement | Sisi |
|---|---|---|
| FR-STORE-01 | User ajukan toko; `store_name` unik (case-insensitive), 1 user = 1 toko | BE |
| FR-STORE-02 | Validasi upload KTP/logo: JPG/PNG, ≤ 2MB, cek MIME asli (`finfo`), re-encode via GD, nama `md5(uniqid())` | BE |
| FR-STORE-03 | Admin: `PENDING`→`ACTIVE`/`REJECTED` (alasan wajib); approve → role user jadi `TOKO` (transaksi DB) | BE |
| FR-STORE-04 | Admin: `ACTIVE`↔`SUSPENDED`; toko suspended tidak tampil & tidak bisa menerima pesanan | BE |
| FR-STORE-05 | Foto KTP **privat**: hanya admin via endpoint streaming | BE |
| FR-STORE-06 | Form Buka Toko (preview gambar, validasi klien), halaman status pengajuan, halaman toko publik | FE |
| FR-STORE-07 | Toko ubah profil, ongkir default, info pembayaran ONLINE, buka/tutup toko | BE+FE |

### 2.3 Katalog & Keranjang (FR-PROD)
| ID | Requirement | Sisi |
|---|---|---|
| FR-PROD-01 | CRUD produk oleh pemilik toko (soft-delete via `is_active=0` jika sudah pernah dipesan) | BE |
| FR-PROD-02 | Listing publik: cari (`q`), filter kategori/toko, sort, paginasi; hanya toko `ACTIVE` & produk aktif | BE |
| FR-PROD-03 | Keranjang di database (`cart_items`), grup per toko; qty dibatasi `stock` | BE |
| FR-PROD-04 | Grid katalog gaya Shopee, detail produk, keranjang (qty stepper, hapus), skeleton loading, empty state | FE |
| FR-PROD-05 | Dashboard toko: tabel produk, form tambah/edit + upload gambar, indikator stok menipis | FE |

### 2.4 Checkout & Kalkulasi (FR-CHK)
| ID | Requirement | Sisi |
|---|---|---|
| FR-CHK-01 | `shipping_address` wajib, `address_notes` (patokan) opsional tapi dianjurkan | BE+FE |
| FR-CHK-02 | `service_fee = round(2% × subtotal_products)` dibulatkan ke rupiah; persentase dari `settings` | BE |
| FR-CHK-03 | `total = subtotal_products + shipping_fee + service_fee`, **dihitung server**; klien hanya menampilkan | BE |
| FR-CHK-04 | Buat pesanan dalam 1 transaksi DB: kunci baris produk (`SELECT … FOR UPDATE`), cek stok, kurangi stok, snapshot nama/harga ke `order_items`, kosongkan item toko itu dari keranjang | BE |
| FR-CHK-05 | Tolak pesanan jika toko `SUSPENDED`/tutup/**melebihi limit titipan** | BE |
| FR-CHK-06 | Halaman checkout dengan rincian biaya (preview dari `/checkout/preview`), pilih metode bayar, tombol "Buat Pesanan" anti double-submit | FE |
| FR-CHK-07 | Pembatalan mengembalikan stok (transaksi) | BE |

### 2.5 Pesanan (FR-ORD)
| ID | Requirement | Sisi |
|---|---|---|
| FR-ORD-01 | Transisi status valid saja (lihat `04-API-CONTRACT.md` §Order State Machine); penolakan/pembatalan wajib alasan | BE |
| FR-ORD-02 | Pembeli: daftar & detail pesanan, batalkan saat `PENDING`, unggah bukti bayar (ONLINE) | BE+FE |
| FR-ORD-03 | Toko: daftar (tab per status), terima/tolak, kirim, selesai, konfirmasi pembayaran ONLINE; tombol WhatsApp/chat ke pembeli | BE+FE |
| FR-ORD-04 | Tiap perubahan status → simpan notifikasi + trigger Pusher `private-user-{id}` | BE |

### 2.6 Setoran Layanan (FR-SETTLE)
| ID | Requirement | Sisi |
|---|---|---|
| FR-SETTLE-01 | Saldo titipan = Σ `service_fee` pesanan `COMPLETED` & `admin_fee_status='UNSETTLED'` | BE |
| FR-SETTLE-02 | Toko unggah bukti → buat `settlements`, pesanan terpilih → `PENDING_VERIFICATION` (`settlement_id` terisi) | BE |
| FR-SETTLE-03 | Admin `APPROVED` → pesanan `SETTLED`; `REJECTED` → kembali `UNSETTLED` + catatan | BE |
| FR-SETTLE-04 | Widget saldo + progress bar limit, halaman setor, riwayat; panel admin verifikasi + buku hutang per toko | FE |

### 2.7 Chat & Notifikasi (FR-CHAT)
| ID | Requirement | Sisi |
|---|---|---|
| FR-CHAT-01 | Simpan pesan ke `messages`, lalu trigger Pusher channel `private-chat-{minId}-{maxId}` | BE |
| FR-CHAT-02 | Endpoint `pusher-auth` memvalidasi user adalah salah satu peserta channel | BE |
| FR-CHAT-03 | UI chat (daftar percakapan, bubble, unread badge), subscribe via Pusher JS; fallback polling ≥ 30 dtk bila Pusher gagal | FE |
| FR-CHAT-04 | Pesan teks ≤ 1000 karakter, di-escape saat render (`textContent`) | BE+FE |

### 2.8 Admin & CS (FR-ADMIN)
| ID | Requirement | Sisi |
|---|---|---|
| FR-ADMIN-01 | Dashboard: GMV (Σ `total_amount` COMPLETED), jumlah transaksi, toko aktif, top toko, grafik 30 hari | BE+FE |
| FR-ADMIN-02 | Antrian verifikasi toko, preview KTP, approve/reject/suspend/hapus | BE+FE |
| FR-ADMIN-03 | Kelola user (nonaktifkan/hapus), laporan pengguna (`reports`), pengaturan platform (fee %, limit, rekening admin) | BE+FE |

## 3. Persyaratan Non-Fungsional
**Keamanan:** lihat `13-SECURITY.md` (XSS, CSRF, SQLi, upload, RBAC, header keamanan, file privat).
**Performa:** Lighthouse mobile > 85; gambar produk dikompres GD ≤ 300KB (maks sisi 1024px, juga thumbnail 320px); `Cache-Control` aset statis; paginasi default 12 item; JSON ringkas (tanpa field tak terpakai).
**Usability:** mobile-first (360px+), palet krem/pastel, kontras teks AA, tap target ≥ 44px, semua teks UI Bahasa Indonesia, format Rupiah `Rp 25.000`, waktu `Asia/Jakarta`.
**Kompatibilitas:** Chrome/Edge/Firefox/Safari 2 versi terakhir, Android Chrome.
**Keandalan:** semua mutasi multi-tabel dalam transaksi PDO; error API konsisten (§ kontrak); log error ke `htdocs/storage/logs/` (di-rotate manual/ukuran maks 1MB).
