<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

echo "Menghubungkan ke database...\n";

try {
    $db = get_db();
    echo "Koneksi berhasil ke: " . $db_config['host'] . " (" . $db_config['database'] . ")\n";

    $schema_file = __DIR__ . '/schema.sql';
    if (!file_exists($schema_file)) {
        throw new RuntimeException("File schema.sql tidak ditemukan di: " . $schema_file);
    }

    $sql = file_get_contents($schema_file);

    // Pada cloud hosting seperti Aiven/TiDB, nama database sudah ditentukan (misal 'defaultdb'),
    // sehingga 'CREATE DATABASE distributed_chat; USE distributed_chat;' di schema.sql bisa dilewati
    // agar tabel dibuat langsung di database yang aktif di config.
    $cleaned_sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
    $cleaned_sql = preg_replace('/USE [^;]+;/i', '', $cleaned_sql);

    echo "Menjalankan import tabel dan data awal...\n";
    $db->exec($cleaned_sql);

    echo "Sukses: Skema tabel dan data awal berhasil diimpor!\n";

    // Verifikasi tabel
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabel yang tersedia: " . implode(', ', $tables) . "\n";

    $user_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Jumlah user terdaftar: " . $user_count . "\n";

} catch (Throwable $e) {
    echo "Error import: " . $e->getMessage() . "\n";
    exit(1);
}
