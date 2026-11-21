<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$backup_dir = __DIR__ . '/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

$action = $argv[1] ?? 'backup';

if ($action === 'list') {
    $files = glob($backup_dir . '/*.sql');
    echo "Daftar Backup di Desktop Storage:\n";
    if (empty($files)) {
        echo "  (Belum ada file backup)\n";
        exit(0);
    }
    foreach ($files as $file) {
        $size = round(filesize($file) / 1024, 2);
        echo sprintf(" - %s (%s KB)\n", basename($file), $size);
    }
    exit(0);
}

if ($action === 'restore') {
    $file_target = $argv[2] ?? null;
    if (!$file_target) {
        $files = glob($backup_dir . '/*.sql');
        rsort($files);
        $file_target = $files[0] ?? null;
    } else {
        $file_target = $backup_dir . '/' . basename($file_target);
    }

    if (!$file_target || !file_exists($file_target)) {
        echo "Error: File backup tidak ditemukan.\n";
        exit(1);
    }

    echo "Memulai proses restore dari: " . basename($file_target) . "\n";
    try {
        $db = get_db();
        $sql = file_get_contents($file_target);
        
        // Nonaktifkan foreign key checks sementara untuk restore
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $db->exec($sql);
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "Sukses: Database berhasil dipulihkan dari " . basename($file_target) . "\n";
    } catch (Throwable $e) {
        echo "Error restore: " . $e->getMessage() . "\n";
        exit(1);
    }
    exit(0);
}

// Default action: backup
try {
    $db = get_db();
    $timestamp = date('Ymd_His');
    $filename = $backup_dir . "/backup_{$timestamp}.sql";

    echo "Memulai proses backup dari Cloud Database...\n";
    $tables = ['users', 'messages'];
    $out = "-- Backup Desktop Storage: " . date('Y-m-d H:i:s') . "\n";
    $out .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        // Ambil CREATE TABLE
        $stmt = $db->query("SHOW CREATE TABLE `{$table}`");
        $row = $stmt->fetch();
        $create_sql = $row['Create Table'] ?? null;
        if ($create_sql) {
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out .= $create_sql . ";\n\n";
        }

        // Ambil Data
        $data_stmt = $db->query("SELECT * FROM `{$table}`");
        $rows = $data_stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $cols = array_keys($rows[0]);
            $col_names = implode('`, `', $cols);
            
            $out .= "INSERT INTO `{$table}` (`{$col_names}`) VALUES\n";
            $val_lines = [];
            foreach ($rows as $r) {
                $escaped_vals = array_map(function ($val) use ($db) {
                    return $val === null ? 'NULL' : $db->quote((string)$val);
                }, array_values($r));
                $val_lines[] = "(" . implode(', ', $escaped_vals) . ")";
            }
            $out .= implode(",\n", $val_lines) . ";\n\n";
        }
    }

    $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    file_put_contents($filename, $out);

    $size_kb = round(filesize($filename) / 1024, 2);
    echo "Sukses: Backup tersimpan di desktop!\n";
    echo "Lokasi: {$filename} ({$size_kb} KB)\n";
} catch (Throwable $e) {
    echo "Error backup: " . $e->getMessage() . "\n";
    exit(1);
}
