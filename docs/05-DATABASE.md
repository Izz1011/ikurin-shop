# Database — Ikurin Shop

File SQL: `database/schema.sql` (struktur) & `seed.sql` (pengaturan awal + admin). **Tanpa `CREATE DATABASE`/`USE`** — di InfinityFree DB dibuat lewat panel (nama berawalan `if0_XXXXXXX_`), lalu *Import* via phpMyAdmin.

## ERD (ringkas)
```
users 1─1 stores 1─* products *─* orders (via order_items)
users 1─* orders *─1 stores        orders *─1 settlements *─1 stores
users 1─* cart_items *─1 products  users *─* messages   users 1─* notifications
users 1─* reports  stores 0..1─* reports     settings (key-value)   login_attempts
```

## Tabel & Catatan
| Tabel | Kunci & catatan |
|---|---|
| `users` | PK `id`; `email` UNIQUE; `role` ENUM; `is_active` |
| `stores` | PK `id`; FK `user_id`→users (UNIQUE: 1 user 1 toko); `store_name` & `slug` UNIQUE; `status` ENUM(PENDING, ACTIVE, REJECTED, SUSPENDED); `ktp_image` path privat |
| `products` | FK `store_id`; `price` BIGINT rupiah; `stock`; `is_active` |
| `cart_items` | UNIQUE(`user_id`,`product_id`) |
| `orders` | `order_code` UNIQUE (`IKR-YYYYMMDD-XXXX`); FK user, store, `settlement_id` (NULL); uang BIGINT; `admin_fee_status` ENUM(UNSETTLED, PENDING_VERIFICATION, SETTLED) |
| `order_items` | snapshot `product_name`, `price`; FK order (CASCADE), product |
| `settlements` | FK store; status PENDING/APPROVED/REJECTED; `admin_note` |
| `messages` | sender/receiver FK users; `is_read`; index (sender,receiver,id) |
| `notifications` | FK user; `is_read`; `type`, `data` JSON(text) |
| `reports` | FK user, store NULL; status OPEN/RESOLVED |
| `settings` | key-value: `service_fee_percent`, `fee_debt_limit`, `session_timeout_minutes`, `admin_bank_info`, `site_name` |
| `login_attempts` | rate limiting login |

## Aturan Data
1. Uang = integer rupiah. `service_fee = intdiv(subtotal*pct + 50, 100)` (pct=2).
2. Snapshot harga & nama di `order_items` (harga produk boleh berubah kemudian).
3. Perubahan skema setelah v1 → file migrasi `database/migrations/NNN_deskripsi.sql` (disetujui integrator, diimpor manual via phpMyAdmin).
4. Index sudah disiapkan untuk query utama (status toko, produk per toko, pesanan per user/toko/status, chat).
5. Penghapusan user/toko: gunakan `is_active`/`SUSPENDED` dulu; `DELETE` hanya admin dan hanya jika tidak punya pesanan (FK `RESTRICT` pada orders).
