# Keputusan & Perubahan dari PRD/SRS Awal (ditulis manusia/integrator)

Tanggal: 2026-10-28 · Kontrak API: v1.0 · Skema: v1.1

## Perubahan pada dokumen awal (dan alasannya)
| # | Awal | Sekarang | Alasan |
|---|---|---|---|
| D1 | Satu aplikasi PHP MVC dengan view PHP | **API JSON (BE) + halaman statis (FE)** | Agar 2 AI paralel tanpa konflik; kontrak jelas; ringan di hosting gratis |
| D2 | `CREATE DATABASE`/`USE` di SQL | Dihapus | InfinityFree tidak mengizinkan, nama DB berawalan otomatis |
| D3 | Uang `DECIMAL(10,2)` | `BIGINT UNSIGNED` rupiah | Rupiah tanpa desimal, hindari float, pembulatan fee pasti |
| D4 | Status toko tanpa `REJECTED` di ENUM padahal dipakai FR-STORE-03 | `PENDING, ACTIVE, REJECTED, SUSPENDED` + `rejection_reason` | Inkonsistensi SRS |
| D5 | Hutang fee dicatat "saat checkout" (SRS) vs "saat transaksi selesai" (PRD) | **Saat pesanan `COMPLETED`** | Pesanan batal tidak berutang; sesuai PRD |
| D6 | Hutang fee hanya pesanan CASH | **CASH dan ONLINE** (MVP tanpa gateway, uang ONLINE masuk rekening toko) | Biaya layanan tetap harus sampai ke admin |
| D7 | Metode `ONLINE` tanpa gateway/rincian | **ONLINE = transfer/QRIS manual ke toko + unggah bukti + konfirmasi toko**; gateway otomatis fase 2 | Webhook server-to-server terblokir anti-bot InfinityFree |
| D8 | `admin_fee_status` UNSETTLED/SETTLED | + `PENDING_VERIFICATION`, `orders.settlement_id` | Setoran harus terhubung ke pesanan; mencegah setor ganda |
| D9 | Limit titipan "misal Rp 50.000" | Default 50.000, disimpan di `settings`, **dihitung lazy** saat checkout | Tanpa cron di hosting gratis |
| D10 | Foto KTP di folder upload biasa | **Folder privat** + stream admin | Data pribadi (KTP) tidak boleh publik |
| D11 | Cart "session atau DB" | **Database** (`cart_items`) | Konsisten lintas perangkat |
| D12 | Checkout campur semua toko | **Satu pesanan per toko** | Ongkir & penjual berbeda; seperti Shopee |
| D13 | `messages` saja | + `is_read`, tabel `notifications` | Badge unread & notifikasi realtime |
| D14 | Tidak ada rate limit login | `login_attempts` | Keamanan dasar |
| D15 | Tidak ada tabel pengaturan | `settings` | Fee %, limit, rekening admin dapat diubah admin |
| D16 | Satu user bisa banyak toko (tidak diatur) | 1 user = 1 toko | Menyederhanakan MVP |

## Pertanyaan untuk Manusia (jawab sebelum M4 bila ingin diubah)
1. Nomor rekening/QRIS admin untuk setoran → isi di `settings.admin_bank_info`.
2. Akun Pusher: cluster `ap1` dipakai; buat app & isi `.env`.
3. Apakah ONLINE manual (D7) cukup untuk MVP? (Default: ya.)
4. Konfirmasi limit titipan 50.000 & fee 2% sebagai default.

## Log Perubahan Kontrak
- v1.0 (2026-10-28): kontrak awal.
