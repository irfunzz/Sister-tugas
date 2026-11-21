<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $identifier = trim((string)($input['username_or_email'] ?? ''));
    $password = (string)($input['password'] ?? '');

    if ($identifier === '' || $password === '') {
        json_response(['error' => 'Username/email dan password wajib diisi.'], 422);
    }

    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT id, username, email, password_hash, full_name FROM users WHERE username = :u OR email = :e LIMIT 1');
        $stmt->execute([':u' => $identifier, ':e' => $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            json_response(['error' => 'Kombinasi kredensial tidak valid.'], 401);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];

        json_response([
            'message' => 'Login berhasil',
            'user' => [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
            ]
        ]);
    } catch (Throwable $e) {
        json_response(['error' => 'Gagal menghubungkan ke database: ' . $e->getMessage()], 500);
    }
}

if ($action === 'register' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $username = trim((string)($input['username'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $password = (string)($input['password'] ?? '');
    $full_name = trim((string)($input['full_name'] ?? ''));

    if ($username === '' || $email === '' || $password === '' || $full_name === '') {
        json_response(['error' => 'Semua kolom wajib diisi.'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Format email tidak valid.'], 422);
    }

    try {
        $db = get_db();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, full_name) VALUES (:u, :e, :p, :f)');
        $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':p' => $hash,
            ':f' => $full_name,
        ]);

        $new_id = (int)$db->lastInsertId();
        $_SESSION['user_id'] = $new_id;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $full_name;

        json_response([
            'message' => 'Registrasi berhasil',
            'user' => [
                'id' => $new_id,
                'username' => $username,
                'email' => $email,
                'full_name' => $full_name,
            ]
        ], 201);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            json_response(['error' => 'Username atau email sudah terdaftar.'], 409);
        }
        json_response(['error' => 'Gagal mendaftarkan user: ' . $e->getMessage()], 500);
    }
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    json_response(['message' => 'Logout berhasil']);
}

if ($action === 'me') {
    $me = require_auth();
    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT id, username, email, full_name, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $me['id']]);
        $user = $stmt->fetch();
        if (!$user) {
            json_response(['error' => 'User tidak ditemukan'], 404);
        }
        json_response(['user' => $user]);
    } catch (Throwable $e) {
        json_response(['error' => $e->getMessage()], 500);
    }
}

json_response(['error' => 'Endpoint aksi tidak dikenal.'], 400);
