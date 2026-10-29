# Design System — "Happy & Calm"

## Token (`assets/css/tokens.css`)
```css
:root{
  --color-bg:#FDFBF7;        /* Cream Calm */
  --color-surface:#FFFFFF;
  --color-sand:#F5E6CC;      /* Light Sand */
  --color-primary:#FF9F43;   /* Soft Pastel Orange (CTA) */
  --color-primary-hover:#E8892D; /* Warm Coral */
  --color-text:#3D3226;      /* cokelat tua, kontras AA di atas krem */
  --color-text-muted:#7A6C5D;
  --color-border:#EADFCB;
  --color-success:#5DA271; --color-warning:#E8A33D; --color-danger:#D95C5C; --color-info:#5B8DB8;
  --radius-sm:8px; --radius-md:14px; --radius-lg:22px; --radius-pill:999px;
  --shadow-card:0 2px 10px rgba(110,80,40,.08);
  --space-1:4px; --space-2:8px; --space-3:12px; --space-4:16px; --space-6:24px; --space-8:32px;
  --font-base:'Nunito',system-ui,sans-serif;
  --fs-sm:.875rem; --fs-md:1rem; --fs-lg:1.25rem; --fs-xl:1.5rem; --fs-2xl:2rem;
}
```
Catatan kontras: teks putih di atas `#FF9F43` kurang kontras → tombol primer memakai teks `--color-text` gelap (atau gunakan `#E8892D` dengan teks putih tebal ≥ 18px). Periksa AA sebelum final.

## Komponen
- **Tombol**: `.btn` (pill, tinggi ≥ 44px), varian `--primary`, `--secondary` (sand), `--ghost`, `--danger`; state hover/focus-visible/disabled/loading.
- **Card**: putih, radius `--radius-md`, `--shadow-card`, border tipis.
- **Form**: label di atas, input radius 12px, border sand, fokus ring oranye, pesan error merah di bawah field.
- **Badge status pesanan**: PENDING kuning, PROCESSING biru, SHIPPING oranye, COMPLETED hijau, CANCELED abu/merah.
- **Navbar** (desktop) sticky putih; **bottom-nav** (mobile): Beranda, Keranjang, Chat, Pesanan, Akun.
- **Grid produk**: `repeat(auto-fill,minmax(150px,1fr))` mobile → `minmax(200px,1fr)` desktop; gap 12–16px.
- **Skeleton**: shimmer lembut warna sand; **Toast**: kanan-bawah (mobile: atas), auto-hide 4 dtk.
Suasana: banyak ruang putih, sudut membulat, ilustrasi/ikon sederhana (emoji atau SVG inline), tanpa warna neon.
Breakpoint: 480, 768, 1024, 1280 (mobile-first `min-width`).
