# Kontrak API — Ikurin Shop v1

> **Sumber kebenaran antar-AI.** Hanya manusia/integrator yang mengubah file ini. Butuh perubahan → `ai-comms/contract-requests.md`.
> Base URL: `/api/v1` (same-origin). Semua body JSON (`Content-Type: application/json`) kecuali yang bertanda **multipart**.

## 1. Konvensi
**Auth:** cookie session `IKURIN_SID` (HttpOnly, Secure, SameSite=Lax). **CSRF:** semua `POST/PATCH/DELETE` wajib header `X-CSRF-Token` (token dari `GET /auth/csrf`). Tanpa token → `403 CSRF_INVALID`.
**Sukses:** `{ "success": true, "data": <object|array>, "meta": { ... } }` (`meta` opsional).
**Gagal:** `{ "success": false, "error": { "code": "STRING", "message": "Pesan Indonesia", "fields": { "email": "Email sudah dipakai" } } }` (`fields` hanya untuk 422).
**Paginasi:** query `page` (default 1), `per_page` (default 12, maks 50) → `meta: { page, per_page, total, total_pages }`.
**Uang:** integer rupiah (mis. `25000`). **Waktu:** string `"2026-10-28 14:30:00"` zona `Asia/Jakarta`. **Boolean:** `true/false` di JSON. **Gambar:** URL relatif (`/uploads/products/ab12.jpg`) atau `null`.
**Status HTTP:** 200, 201 (dibuat), 400 `BAD_REQUEST`, 401 `UNAUTHENTICATED` / `SESSION_EXPIRED`, 403 `FORBIDDEN` / `CSRF_INVALID` / `STORE_NOT_ACTIVE`, 404 `NOT_FOUND`, 409 `CONFLICT` (mis. stok, nama toko), 413 `FILE_TOO_LARGE`, 422 `VALIDATION_ERROR`, 429 `RATE_LIMITED`, 500 `SERVER_ERROR`.
**Akses:** 🌐 publik · 🔑 login · 🛍 TOKO aktif · 👑 ADMIN.

## 2. Objek Umum
```jsonc
// User
{ "id": 1, "username": "Siti", "email": "siti@mail.com", "phone": "0812...", "role": "USER|TOKO|ADMIN", "store_status": null|"PENDING"|"ACTIVE"|"REJECTED"|"SUSPENDED" }
// StoreCard
{ "id": 3, "store_name": "Roti Ibu Ani", "slug": "roti-ibu-ani", "category": "bakery", "logo_image": "/uploads/logos/x.jpg"|null, "is_open": true, "address_short": "Kec. Banjarnegara", "product_count": 12 }
// ProductCard
{ "id": 10, "product_name": "Roti Sobek", "price": 25000, "stock": 8, "image_url": "/uploads/products/a.jpg"|null, "store": { "id": 3, "store_name": "Roti Ibu Ani" } }
// OrderSummary
{ "id": 55, "order_code": "IKR-20261028-0007", "store": { "id": 3, "store_name": "..." }, "buyer": { "id": 1, "username": "..." }, "total_amount": 52500, "payment_method": "CASH", "payment_status": "UNPAID", "order_status": "PENDING", "item_count": 3, "created_at": "..." }
```

## 3. Endpoint

### 3.1 Config & Auth
| Method Path | Akses | Request | Response `data` |
|---|---|---|---|
| `GET /config/public` | 🌐 | – | `{ site_name, service_fee_percent, pusher_key, pusher_cluster, fee_debt_limit?(tidak), categories:["bakery","snack","makanan_berat","minuman"] }` |
| `GET /auth/csrf` | 🌐 | – | `{ csrf_token }` |
| `POST /auth/register` | 🌐 | `{ username, email, password(≥8), phone? }` | 201 `User` (otomatis login) |
| `POST /auth/login` | 🌐 | `{ email, password }` | `{ user: User, csrf_token }` · 401 `INVALID_CREDENTIALS` · 429 |
| `POST /auth/logout` | 🔑 | – | `{}` |
| `GET /auth/me` | 🌐 | – | `{ user: User\|null, csrf_token, unread_notifications, unread_messages }` |
| `PATCH /users/me` | 🔑 | `{ username?, phone? }` | `User` |
| `POST /users/me/password` | 🔑 | `{ current_password, new_password }` | `{}` |

### 3.2 Katalog publik
| Method Path | Akses | Query / Body | Response |
|---|---|---|---|
| `GET /products` | 🌐 | `q, category, store_id, sort(newest\|price_asc\|price_desc), page, per_page` | `ProductCard[]` + meta |
| `GET /products/{id}` | 🌐 | – | `ProductCard` + `{ description, store: StoreCard+{whatsapp,is_open,shipping_fee_default,delivery_notes} }` |
| `GET /stores` | 🌐 | `q, category, page` | `StoreCard[]` + meta |
| `GET /stores/{id}` | 🌐 | – | `StoreCard + { description, address, whatsapp, shipping_fee_default, delivery_notes, owner_user_id }` |
| `GET /stores/{id}/products` | 🌐 | `page, per_page` | `ProductCard[]` + meta |

Hanya toko `ACTIVE` & produk `is_active` yang muncul; selain itu 404.

### 3.3 Buka Toko
| Method Path | Akses | Request | Response |
|---|---|---|---|
| `POST /stores` | 🔑 (role USER) | **multipart** `store_name, category, description?, whatsapp, address, ktp_image(file), logo_image?(file)` | 201 `{ id, status:"PENDING" }` · 409 nama dipakai / sudah punya toko |
| `GET /stores/me/application` | 🔑 | – | `{ status, rejection_reason, store_name, created_at }` atau `null` |

### 3.4 Keranjang
| Method Path | Akses | Request | Response |
|---|---|---|---|
| `GET /cart` | 🔑 | – | `{ groups: [ { store: {id, store_name, is_open, shipping_fee_default}, items: [ { id, product_id, product_name, price, quantity, stock, image_url, subtotal } ], subtotal } ], total_items }` |
| `POST /cart/items` | 🔑 | `{ product_id, quantity }` (menambah ke qty ada) | `{ total_items }` · 409 `INSUFFICIENT_STOCK` |
| `PATCH /cart/items/{id}` | 🔑 | `{ quantity }` (≥1) | `{ total_items }` |
| `DELETE /cart/items/{id}` | 🔑 | – | `{ total_items }` |

### 3.5 Checkout & Pesanan Pembeli
| Method Path | Akses | Request | Response |
|---|---|---|---|
| `POST /checkout/preview` | 🔑 | `{ store_id }` | `{ store, items[], subtotal_products, shipping_fee, service_fee_percent, service_fee, total_amount, payment_methods:["CASH","ONLINE"], payment_info }` |
| `POST /orders` | 🔑 | `{ store_id, shipping_address, address_notes?, payment_method }` | 201 `OrderDetail` · 409 `INSUFFICIENT_STOCK` / `STORE_LIMIT_REACHED` / `STORE_CLOSED` |
| `GET /orders` | 🔑 | `status?, page` | `OrderSummary[]` + meta |
| `GET /orders/{id}` | 🔑 (pemilik) | – | `OrderDetail` |
| `POST /orders/{id}/cancel` | 🔑 | `{ reason? }` hanya saat `PENDING` | `OrderDetail` |
| `POST /orders/{id}/payment-proof` | 🔑 | **multipart** `proof_image` (ONLINE) | `OrderDetail` |
| `GET /orders/{id}/payment-proof` | 🔑 pemilik / 🛍 toko terkait / 👑 | – | stream gambar |

```jsonc
// OrderDetail = OrderSummary + 
{ "shipping_address": "...", "address_notes": "...", "subtotal_products": 50000, "shipping_fee": 5000, "service_fee": 1000,
  "cancel_reason": null, "has_payment_proof": false, "payment_info": "BCA 123 a.n. ...", "store_whatsapp": "62812...",
  "items": [ { "product_id": 10, "product_name": "Roti Sobek", "price": 25000, "quantity": 2, "subtotal": 50000 } ],
  "timeline": [ { "status": "PENDING", "at": "..." } ] }   // timeline opsional (boleh hanya created_at/completed_at)
```

### Order State Machine
```
PENDING ──(toko terima)──► PROCESSING ──(toko kirim)──► SHIPPING ──(toko selesai)──► COMPLETED
  │  └─(toko tolak, reason wajib)──► CANCELED
  └─(pembeli batal)──► CANCELED          PROCESSING ──(toko batal, reason wajib)──► CANCELED
```
Efek: `CANCELED` → stok dikembalikan. `COMPLETED` → `completed_at` diisi; jika `CASH` maka `payment_status=PAID`; biaya layanan masuk saldo titipan. ONLINE: toko `confirm-payment` → `PAID` (boleh sebelum PROCESSING).

### 3.6 Seller 🛍 (role TOKO, toko ACTIVE)
| Method Path | Request | Response |
|---|---|---|
| `GET /seller/dashboard` | – | `{ orders_by_status:{PENDING:n,...}, today_revenue, month_revenue, low_stock_products:[ProductCard], fee_balance:{ unsettled_amount, limit, percent, blocked }, recent_orders:[OrderSummary] }` |
| `GET /seller/store` | – | profil toko lengkap |
| `PATCH /seller/store` | `{ description?, whatsapp?, address?, shipping_fee_default?, delivery_notes?, payment_info?, is_open? }` | profil |
| `POST /seller/store/logo` | **multipart** `logo_image` | `{ logo_image }` |
| `GET /seller/products` | `q, page` | `Product[]` (termasuk nonaktif, `is_active`) + meta |
| `POST /seller/products` | **multipart** `product_name, description?, price, stock, image?` | 201 `Product` |
| `GET /seller/products/{id}` | – | `Product` |
| `PATCH /seller/products/{id}` | JSON `{ product_name?, description?, price?, stock?, is_active? }` | `Product` |
| `POST /seller/products/{id}/image` | **multipart** `image` | `{ image_url }` |
| `DELETE /seller/products/{id}` | – | `{}` (hard delete jika belum pernah dipesan, selain itu `is_active=0`) |
| `GET /seller/orders` | `status?, page` | `OrderSummary[]` + meta |
| `GET /seller/orders/{id}` | – | `OrderDetail` (+ `buyer: {username, phone}`) |
| `PATCH /seller/orders/{id}/status` | `{ status, reason?, shipping_fee? }` (`shipping_fee` hanya saat PENDING→PROCESSING) | `OrderDetail` · 409 `INVALID_TRANSITION` |
| `POST /seller/orders/{id}/confirm-payment` | – | `OrderDetail` |
| `GET /seller/fees` | – | `{ unsettled_amount, limit, percent, blocked, admin_bank_info, orders:[{id,order_code,service_fee,completed_at}] }` |
| `POST /seller/settlements` | **multipart** `proof_image` (jumlah = seluruh `unsettled_amount` saat ini; server yang menghitung) | 201 `Settlement` · 409 `NOTHING_TO_SETTLE` / `SETTLEMENT_PENDING` |
| `GET /seller/settlements` | `page` | `Settlement[]` `{id, amount_paid, status, admin_note, created_at}` + meta |

### 3.7 Chat & Notifikasi 🔑
| Method Path | Request | Response |
|---|---|---|
| `GET /chat/conversations` | – | `[ { user:{id,username,store_name?}, last_message, last_at, unread } ]` |
| `GET /chat/messages` | `with(user_id), before_id?, limit(≤50)` | `[ { id, sender_id, receiver_id, message, is_read, created_at } ]` urut naik |
| `POST /chat/messages` | `{ receiver_id, message(≤1000) }` | 201 `Message` (+ trigger Pusher) |
| `POST /chat/read` | `{ with }` | `{}` |
| `POST /chat/pusher-auth` | form-urlencoded `socket_id, channel_name` | `{ auth: "key:signature" }` (bukan envelope — sesuai Pusher) · 403 bila bukan peserta |
| `GET /notifications` | `page` | `[ {id,type,title,body,link,is_read,created_at} ]` + meta |
| `POST /notifications/read` | `{ ids?: [] }` (kosong = semua) | `{}` |

**Pusher:** channel chat `private-chat-{minUserId}-{maxUserId}`, event `new-message` (payload = `Message`). Channel notifikasi `private-user-{userId}`, event `notification` (payload = notifikasi). `chat/pusher-auth` mengizinkan `private-user-{self}` dan chat yang melibatkan `self`.

### 3.8 Laporan 🔑
| `POST /reports` | `{ subject, message, store_id? }` | 201 `{ id }` |
|---|---|---|
| `GET /reports/mine` | – | `Report[]` |

### 3.9 Admin 👑
| Method Path | Request | Response |
|---|---|---|
| `GET /admin/dashboard` | `days?(30)` | `{ gmv, total_orders, active_stores, pending_stores, pending_settlements, open_reports, top_stores:[{store_name, gmv, orders}], daily:[{date, orders, gmv}] }` |
| `GET /admin/stores` | `status?, q?, page` | `StoreAdmin[]` + meta |
| `GET /admin/stores/{id}` | – | `StoreAdmin` (lengkap + owner) |
| `GET /admin/stores/{id}/ktp` | – | stream gambar KTP (privat) |
| `PATCH /admin/stores/{id}/status` | `{ status: "ACTIVE"\|"REJECTED"\|"SUSPENDED", reason? }` (reason wajib untuk REJECTED/SUSPENDED) | `StoreAdmin` |
| `DELETE /admin/stores/{id}` | – | `{}` · 409 bila punya pesanan |
| `GET /admin/users` | `q?, role?, page` | `User[]`+`is_active` + meta |
| `PATCH /admin/users/{id}` | `{ is_active }` | `User` |
| `DELETE /admin/users/{id}` | – | `{}` · 409 bila punya pesanan; tidak bisa hapus diri sendiri |
| `GET /admin/settlements` | `status?, page` | `[ {id, store:{id,store_name}, amount_paid, status, created_at, order_count} ]` + meta |
| `GET /admin/settlements/{id}/proof` | – | stream gambar |
| `PATCH /admin/settlements/{id}` | `{ status:"APPROVED"\|"REJECTED", note? }` | `Settlement` |
| `GET /admin/fee-ledger` | `page` | `[ {store_id, store_name, unsettled_amount, pending_verification_amount, blocked} ]` |
| `GET /admin/reports` | `status?, page` | `Report[]` (+ user, store) |
| `PATCH /admin/reports/{id}` | `{ status:"RESOLVED", admin_note? }` | `Report` |
| `GET /admin/settings` · `PATCH /admin/settings` | `{ service_fee_percent?, fee_debt_limit?, session_timeout_minutes?, admin_bank_info? }` | settings |

## 4. Ketentuan Upload
`multipart/form-data`; JPG/PNG saja; ≤ 2MB (413 bila lebih); server memvalidasi MIME asli, re-encode via GD, produk ≤ 300KB. Gambar privat (KTP, bukti) hanya via endpoint stream bertanda di atas.

## 5. Contoh Error 422
```json
{ "success": false, "error": { "code": "VALIDATION_ERROR", "message": "Periksa kembali isian Anda.", "fields": { "whatsapp": "Format nomor tidak valid" } } }
```

## 6. Versi Kontrak
v1.0 — awal. Setiap perubahan dicatat di bawah dengan tanggal, dan diberi tahu kedua AI lewat `ai-comms/decisions.md`.
