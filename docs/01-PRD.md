# PRD — Ikurin Shop (v1.1 MVP, dioptimalkan)

| Item | Detail |
|---|---|
| Produk | Ikurin Shop — marketplace kuliner & bakery lokal |
| Domain | `https://ikurin.sinata.my.id` |
| Pasar | Kota Banjarnegara, fokus UMKM bakery (kategori lain disiapkan: snack, makanan berat, minuman) |
| Platform | Web responsif (mobile-first), hosting InfinityFree |
| Pemilik | Admin Ikurin Shop |

## 1. Visi & Tujuan
- **Visi:** marketplace kuliner lokal dengan nuansa *Happy & Calm* (krem + pastel) dan alur akrab seperti Shopee.
- **Tujuan MVP:** UMKM bakery punya toko digital mandiri (produk, stok, pesanan).
- **Monetisasi:** **Biaya Layanan 2%** dari subtotal produk, dibebankan ke pembeli saat checkout; penjual menerima 100% harga produk. Biaya layanan yang terkumpul di penjual disetor ke admin (alur titipan).
- **Logistik:** pengiriman lokal mandiri oleh penjual; ongkir ditentukan toko.

## 2. Peran
| Role | Ringkasan |
|---|---|
| `USER` (Pembeli) | Jelajah katalog, keranjang, checkout, chat, pantau pesanan, ajukan Buka Toko, lapor masalah |
| `TOKO` (Penjual) | Dashboard, CRUD produk/stok, kelola status pesanan, konfirmasi pembayaran, setor biaya layanan, chat |
| `ADMIN` | Analytics, verifikasi toko (KYC), verifikasi setoran, kelola user/toko, tangani laporan, pengaturan platform |
Satu akun = satu role. Saat toko disetujui, role `USER` → `TOKO` (tetap bisa belanja). Satu user maksimal satu toko.

## 3. Alur Utama
**Checkout (per toko, seperti Shopee):** keranjang dikelompokkan per toko → pilih toko → isi alamat + patokan → pilih metode (`CASH` / `ONLINE`) → lihat rincian (Subtotal, Ongkir, Biaya Layanan 2%, Total) → Buat Pesanan.
**Status pesanan:** `PENDING` → `PROCESSING` → `SHIPPING` → `COMPLETED`; atau `CANCELED` (oleh pembeli saat PENDING, atau ditolak toko dengan alasan).
**Metode bayar MVP:**
- `CASH` — bayar tunai ke kurir/penjual saat terima. `payment_status` jadi `PAID` ketika toko menandai `COMPLETED`.
- `ONLINE` — **transfer manual/QRIS milik toko** (info rekening ditampilkan dari profil toko), pembeli unggah bukti, toko konfirmasi. *Payment gateway otomatis = fase 2* (webhook gateway terblokir anti-bot InfinityFree).
**Buka Toko:** Profil → "Buka Toko Gratis" → form (nama unik, kategori, WhatsApp, alamat, foto KTP, logo opsional) → `PENDING` → admin approve/reject (alasan wajib saat reject) → role jadi `TOKO`.
**Setoran Biaya Layanan:** saat pesanan `COMPLETED`, biaya layanan 2% masuk "Saldo Titipan Layanan" toko (status `UNSETTLED`). Jika saldo ≥ **limit (default Rp 50.000, bisa diubah admin)**, toko **otomatis tidak bisa menerima pesanan baru** sampai menyetor. Toko tekan "Setor Layanan" → transfer ke rekening admin → unggah bukti → admin `APPROVED` (pesanan terkait jadi `SETTLED`) atau `REJECTED` (kembali `UNSETTLED`, dengan catatan).

## 4. Desain Visual
- Background Cream Calm `#FDFBF7`; card/nav Pure White `#FFFFFF`; Light Sand `#F5E6CC`.
- CTA Soft Pastel Orange `#FF9F43`, hover Warm Coral `#E8892D`.
- Font rounded: **Nunito** (Google Fonts). Detail: `frontend/DESIGN-SYSTEM.md`.

## 5. KPI
1. 10–15 toko bakery aktif dalam 3 bulan.
2. GMV bulanan Banjarnegara tumbuh.
3. < 5% toko ter-suspend/terblokir karena tunggakan layanan.
4. Halaman < 2 detik di shared hosting gratis; Lighthouse mobile > 85.

## 6. Di Luar Lingkup MVP
Payment gateway otomatis, ulasan/rating, voucher, kurir pihak ketiga/ongkir otomatis, email/SMS (mail() tidak tersedia di InfinityFree), aplikasi native, multi-kota.
