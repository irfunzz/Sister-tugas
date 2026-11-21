<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$me = require_auth();

try {
    $db = get_db();
    
    // Ambil daftar user lain dan pesan terakhir yang relevan dengan user aktif
    $sql = '
        SELECT 
            u.id, 
            u.username, 
            u.full_name,
            (
                SELECT m.content 
                FROM messages m 
                WHERE (m.sender_id = :me AND m.receiver_id = u.id)
                   OR (m.sender_id = u.id AND m.receiver_id = :me)
                ORDER BY m.id DESC 
                LIMIT 1
            ) AS last_message,
            (
                SELECT m.created_at 
                FROM messages m 
                WHERE (m.sender_id = :me AND m.receiver_id = u.id)
                   OR (m.sender_id = u.id AND m.receiver_id = :me)
                ORDER BY m.id DESC 
                LIMIT 1
            ) AS last_message_time
        FROM users u
        WHERE u.id != :me
        ORDER BY last_message_time DESC, u.full_name ASC
    ';

    $stmt = $db->prepare($sql);
    $stmt->execute([':me' => $me['id']]);
    $users = $stmt->fetchAll();

    json_response(['users' => $users]);
} catch (Throwable $e) {
    json_response(['error' => 'Gagal memuat kontak: ' . $e->getMessage()], 500);
}
