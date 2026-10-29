# SRS Frontend — OpenCode CLI

Peran: bangun seluruh UI (HTML/CSS/JS statis, tanpa build) yang mengonsumsi `../04-API-CONTRACT.md`. Kamu tidak menyentuh PHP/SQL. Baca juga `../02-SRS.md` (item FE), `DESIGN-SYSTEM.md`, `../06-CODING-STANDARDS.md`.

## Lokasi (milikmu)
`htdocs/*.html` · `htdocs/seller/*` · `htdocs/admin/*` · `htdocs/assets/{css,js,img,fonts}` · `tests/frontend/`.

## Fondasi (FE-001..003)
- `assets/js/config.js` (`API_BASE`, `USE_MOCK`), `assets/js/api.js`: `api.get/post/patch/del/upload(path, …)`; menyisipkan `X-CSRF-Token` (ambil dari `/auth/csrf`, cache di memori, refresh saat `CSRF_INVALID`); parsing envelope; error → `ApiError{code,message,fields,status}`; 401 → redirect `login.html?next=…`.
- `assets/js/mock/` : fixture + handler mengikuti persis bentuk kontrak (termasuk 422, 409). **Gunakan mock sepanjang M0–M2 supaya tidak menunggu BE.** Simulasikan latensi 200–500ms.
- `assets/js/guard.js`: `requireRole('USER'|'TOKO'|'ADMIN')` memakai `GET /auth/me`; `store_status` menentukan menu (Buka Toko / Status Pengajuan / Dashboard Toko).
- Komponen (`assets/js/components/`): `navbar`, `bottom-nav` (mobile), `product-card`, `pagination`, `toast`, `modal`, `skeleton`, `empty-state`, `format.js` (Rupiah, tanggal Indonesia `Intl`), `dom.js` (helper `el()` tanpa innerHTML data).

## Daftar Halaman → Endpoint
| Halaman | Peran | Endpoint utama |
|---|---|---|
| `index.html` beranda+katalog | 🌐 | `/products`, `/stores`, `/config/public` |
| `product.html?id=` | 🌐 | `/products/{id}`, `/cart/items` |
| `store.html?id=` | 🌐 | `/stores/{id}`, `/stores/{id}/products` |
| `login.html`, `register.html` | 🌐 | `/auth/*` |
| `cart.html` | USER+ | `/cart*` |
| `checkout.html?store=` | USER+ | `/checkout/preview`, `/orders` |
| `orders.html`, `order.html?id=` | USER+ | `/orders*`, `/orders/{id}/payment-proof` |
| `profile.html` | USER+ | `/users/me`, `/users/me/password`, `/stores/me/application` |
| `open-store.html` | USER | `POST /stores` (multipart) |
| `chat.html` | 🔑 | `/chat/*`, Pusher JS |
| `report.html` | 🔑 | `/reports*` |
| `seller/dashboard,products,product-form,orders,order,fees,settings` | TOKO | `/seller/*` |
| `admin/dashboard,stores,store,settlements,users,reports,settings` | ADMIN | `/admin/*` |

## Perilaku UI Kunci
- **Katalog gaya Shopee**: grid 2 kolom mobile / 4–5 desktop, kartu (gambar rasio 1:1, nama 2 baris, harga oranye, nama toko), search bar sticky, chip kategori, sort, "Muat lebih banyak" / pagination.
- **Keranjang**: dikelompokkan per toko (checkbox/tombol "Checkout toko ini"), stepper qty (+/-) dengan debounce, peringatan stok.
- **Checkout**: alamat (textarea) + patokan, pilih CASH/ONLINE (ONLINE menampilkan `payment_info` dan instruksi unggah bukti setelah pesanan dibuat), rincian **Subtotal Produk / Ongkir / Biaya Layanan (2%) / Total** dari `checkout/preview` (jangan hitung sendiri), tombol "Buat Pesanan" dengan spinner & disable.
- **Pesanan**: badge status berwarna, timeline sederhana, tombol aksi sesuai status (batal, unggah bukti), tombol WhatsApp (`https://wa.me/<nomor>`) & Chat.
- **Seller**: tab pesanan per status dengan penghitung, modal tolak (alasan wajib), input ongkir saat menerima, widget **Saldo Titipan Layanan** + progress bar menuju limit (warna berubah mendekati limit; banner merah saat `blocked`), form setor dengan preview bukti.
- **Admin**: kartu statistik, grafik 30 hari (SVG/CSS buatan sendiri, tanpa library), viewer KTP/bukti dalam modal (gambar dari endpoint stream), konfirmasi sebelum aksi destruktif.
- **Chat**: daftar percakapan + panel pesan; subscribe `private-chat-{min}-{max}` (auth ke `/chat/pusher-auth`); append pesan masuk realtime; badge unread; bila Pusher gagal → polling 30 dtk hanya saat tab terlihat.
- **Umum**: skeleton saat loading, empty state ramah, toast sukses/gagal, tampilkan `error.fields` per input, pesan Indonesia, "sesi habis" → login.

## Performa
Tanpa framework/library; satu CSS global + CSS per halaman kecil; JS per halaman dengan `type="module"`, `defer`; font Nunito `display=swap` (subset 400/600/700); gambar lazy + `width/height`; hindari request berulang (cache `config/public` di sessionStorage).

## Urutan Kerja
Ikuti `../09-TASK-BOARD.md` FE-001 → FE-019. FE-010 (integrasi M3) dilakukan setelah manusia memberi tahu BE sudah merge sampai BE-011; catat selisih BE vs kontrak sebagai bug BE di status.
