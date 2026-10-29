-- Seed Ikurin Shop. Jalankan SETELAH schema.sql.
INSERT INTO settings (setting_key, setting_value) VALUES
 ('service_fee_percent', '2'),
 ('fee_debt_limit', '50000'),
 ('session_timeout_minutes', '30'),
 ('site_name', 'Ikurin Shop'),
 ('admin_bank_info', 'GANTI: Bank/QRIS a.n. Admin Ikurin Shop');

-- Admin awal. GANTI hash! Buat di lokal:
--   php -r "echo password_hash('PasswordKuat123!', PASSWORD_BCRYPT);"
-- lalu tempel di bawah sebelum import. Jangan commit password asli.
INSERT INTO users (username, email, password, role) VALUES
 ('Admin Ikurin', 'admin@ikurin.sinata.my.id', 'GANTI_DENGAN_HASH_BCRYPT', 'ADMIN');
