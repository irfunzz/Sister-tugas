<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

try {
    $db = get_db();
    $stmt = $db->query('
        SELECT 
            NOW() AS cloud_now, 
            UTC_TIMESTAMP() AS cloud_utc,
            @@global.time_zone AS cloud_tz_global,
            @@session.time_zone AS cloud_tz_session,
            VERSION() AS db_version
    ');
    $time_info = $stmt->fetch();

    json_response([
        'status' => 'connected',
        'cloud_database' => [
            'server_time_now' => $time_info['cloud_now'],
            'utc_timestamp' => $time_info['cloud_utc'],
            'timezone_global' => $time_info['cloud_tz_global'],
            'timezone_session' => $time_info['cloud_tz_session'],
            'version' => $time_info['db_version'],
        ],
        'local_app_server' => [
            'php_time_now' => date('Y-m-d H:i:s'),
            'php_timezone' => date_default_timezone_get(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Web Server',
            'host' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        ],
        'note' => 'Waktu sistem chat terpusat pada server_time_now (Cloud MySQL NOW()).'
    ]);
} catch (Throwable $e) {
    json_response([
        'status' => 'disconnected',
        'error' => 'Koneksi ke database gagal: ' . $e->getMessage(),
        'local_app_server' => [
            'php_time_now' => date('Y-m-d H:i:s'),
        ]
    ], 500);
}
