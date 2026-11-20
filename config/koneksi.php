<?php
/**
 * Bootstrap backend ReFindUHO.
 *
 * Satu-satunya tempat untuk konfigurasi aplikasi, sesi, PDO, konstanta,
 * dan helper bersama yang digunakan oleh controller.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    return $default;
}

$dbHost = env_value('DB_HOST', '127.0.0.1');
$dbPort = env_value('DB_PORT', '3306');
$dbName = env_value('DB_NAME', 'refinduho');
$dbUser = env_value('DB_USER', 'root');
$dbPass = env_value('DB_PASS', '');

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $dbHost,
    $dbPort,
    $dbName
);

$pdo = null;
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('ReFindUHO database connection failed: ' . $exception->getMessage());
}

define('APP_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('UPLOAD_DIR', APP_ROOT . 'uploads' . DIRECTORY_SEPARATOR);
define('ALLOWED_PHOTO_MIME', ['image/jpeg', 'image/png']);
define('ALLOWED_PHOTO_EXTENSION', ['jpg', 'jpeg', 'png']);
define('MAX_PHOTO_SIZE', 2 * 1024 * 1024);

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

function app_base_path(): string
{
    $root = str_replace('\\', '/', rtrim(realpath(dirname(__DIR__)) ?: dirname(__DIR__), '/'));
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $documentRoot = str_replace('\\', '/', rtrim(realpath($documentRoot) ?: $documentRoot, '/'));

    if ($documentRoot !== '' && str_starts_with($root, $documentRoot)) {
        $basePath = substr($root, strlen($documentRoot));
        return $basePath === '' ? '/' : '/' . ltrim(str_replace('//', '/', $basePath), '/');
    }

    return '/';
}

function base_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(app_base_path(), '/');
    $path = ltrim($path, '/');
    return $scheme . '://' . $host . ($base === '' ? '/' : $base . '/') . $path;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => in_array($type, ['success', 'warning', 'danger', 'info'], true) ? $type : 'info',
        'message' => $message,
    ];
}

function get_flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']['id_user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}