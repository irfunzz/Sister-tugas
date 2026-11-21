<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$me = require_auth();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $target_user_id = (int)($_GET['target_user_id'] ?? 0);
    $since_id = (int)($_GET['since_id'] ?? 0);

    if ($target_user_id <= 0) {
        json_response(['error' => 'Parameter target_user_id wajib diisi.'], 422);
    }

    try {
        $db = get_db();

        // Verifikasi target user ada
        $check_stmt = $db->prepare('SELECT id, username, full_name FROM users WHERE id = :target LIMIT 1');
        $check_stmt->execute([':target' => $target_user_id]);
        $target_user = $check_stmt->fetch();
        if (!$target_user) {
            json_response(['error' => 'User tujuan tidak ditemukan.'], 404);
        }

        // Query pesan terisolasi (hanya percakapan antara user aktif dan target user)
        $sql = '
            SELECT id, sender_id, receiver_id, content, created_at 
            FROM messages 
            WHERE ((sender_id = :me AND receiver_id = :target) 
                OR (sender_id = :target AND receiver_id = :me))
              AND (:since_id = 0 OR id > :since_id)
            ORDER BY id ASC
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':me' => $me['id'],
            ':target' => $target_user_id,
            ':since_id' => $since_id,
        ]);

        $messages = $stmt->fetchAll();

        json_response([
            'target_user' => $target_user,
            'messages' => $messages,
        ]);
    } catch (Throwable $e) {
        json_response(['error' => 'Gagal mengambil pesan: ' . $e->getMessage()], 500);
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $receiver_id = (int)($input['receiver_id'] ?? 0);
    $content = trim((string)($input['content'] ?? ''));

    if ($receiver_id <= 0 || $content === '') {
        json_response(['error' => 'receiver_id dan content wajib diisi.'], 422);
    }

    if ($receiver_id === $me['id']) {
        json_response(['error' => 'Tidak dapat mengirim pesan ke diri sendiri.'], 422);
    }

    try {
        $db = get_db();

        // Pastikan penerima valid
        $check_stmt = $db->prepare('SELECT id FROM users WHERE id = :rec LIMIT 1');
        $check_stmt->execute([':rec' => $receiver_id]);
        if (!$check_stmt->fetch()) {
            json_response(['error' => 'Penerima tidak ditemukan.'], 404);
        }

        // Simpan pesan dengan waktu acuan cloud database (NOW())
        $insert_sql = '
            INSERT INTO messages (sender_id, receiver_id, content, created_at) 
            VALUES (:me, :rec, :content, NOW())
        ';
        $stmt = $db->prepare($insert_sql);
        $stmt->execute([
            ':me' => $me['id'],
            ':rec' => $receiver_id,
            ':content' => $content,
        ]);

        $new_id = (int)$db->lastInsertId();

        // Ambil kembali detail baris pesan yang baru disimpan beserta timestamp cloud
        $fetch_stmt = $db->prepare('SELECT id, sender_id, receiver_id, content, created_at FROM messages WHERE id = :id');
        $fetch_stmt->execute([':id' => $new_id]);
        $message = $fetch_stmt->fetch();

        json_response([
            'message' => 'Pesan terkirim',
            'data' => $message,
        ], 201);
    } catch (Throwable $e) {
        json_response(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
    }
}

json_response(['error' => 'Method tidak didukung.'], 405);
