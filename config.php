<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_config = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'distributed_chat',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
];

if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2) + [null, null];
        if ($key && $value !== null) {
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            
            if ($key === 'DB_URI' || $key === 'DATABASE_URL') {
                $parsed = parse_url($value);
                if ($parsed) {
                    if (!empty($parsed['host'])) $db_config['host'] = $parsed['host'];
                    if (!empty($parsed['port'])) $db_config['port'] = (string)$parsed['port'];
                    if (!empty($parsed['user'])) $db_config['username'] = $parsed['user'];
                    if (!empty($parsed['pass'])) $db_config['password'] = $parsed['pass'];
                    if (!empty($parsed['path'])) $db_config['database'] = ltrim($parsed['path'], '/');
                }
            } else {
                match ($key) {
                    'DB_HOST' => $db_config['host'] = $value,
                    'DB_PORT' => $db_config['port'] = $value,
                    'DB_NAME' => $db_config['database'] = $value,
                    'DB_USER' => $db_config['username'] = $value,
                    'DB_PASS' => $db_config['password'] = $value,
                    default => null,
                };
            }
        }
    }
}

function get_db(): PDO {
    global $db_config;
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db_config['host'],
            $db_config['port'],
            $db_config['database']
        );
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
    }

    return $pdo;
}

function json_response(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_auth(): array {
    if (empty($_SESSION['user_id'])) {
        json_response(['error' => 'Unauthorized. Silakan login terlebih dahulu.'], 401);
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) $_SESSION['username'],
        'full_name' => (string) $_SESSION['full_name'],
    ];
}
