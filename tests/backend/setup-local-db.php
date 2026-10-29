<?php

declare(strict_types=1);

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = 'izaaz2009';

$pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$databases = ['ikurin_shop_local', 'ikurin_test'];

$schemaSql = file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql');
$seedSql = file_get_contents(dirname(__DIR__, 2) . '/database/seed.sql');

foreach ($databases as $db) {
    echo "Creating database {$db}...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$db}`");
    
    // Execute schema
    $pdo->exec($schemaSql);
    echo "Schema imported for {$db}.\n";

    // Hash admin password for seed
    $adminHash = password_hash('Admin12345!', PASSWORD_BCRYPT);
    $preparedSeed = str_replace('GANTI_DENGAN_HASH_BCRYPT', $adminHash, $seedSql);

    // Execute seed
    $pdo->exec($preparedSeed);
    echo "Seed imported for {$db}.\n";
}

echo "Database setup completed successfully.\n";
