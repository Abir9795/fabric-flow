<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function db(): mysqli
{
    static $connection;
    if (!$connection) {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('DB_PORT') ?: 3306);
        $name = getenv('DB_NAME') ?: 'fabricflowwsystem';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $connection = new mysqli($host, $user, $pass, $name, $port);
        $connection->set_charset('utf8mb4');
    }
    return $connection;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path): string
{
    return $path;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function require_auth(): void
{
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Please sign in to continue.');
        redirect('login.php');
    }
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
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Your session token expired. Go back, refresh the page, and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function post_string(string $key, int $max = 255): string
{
    return mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
}

function post_int(string $key): int
{
    return filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT) ?: 0;
}

function valid_date(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function friendly_db_error(Throwable $error): string
{
    if ($error instanceof mysqli_sql_exception && $error->getCode() === 1062) {
        return 'That value is already in use.';
    }
    if ($error instanceof mysqli_sql_exception && $error->getCode() === 1451) {
        return 'This record is in use and cannot be deleted.';
    }
    error_log((string) $error);
    return 'The request could not be completed. Please try again.';
}

set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    $message = 'Something went wrong while processing your request.';
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message . PHP_EOL);
        return;
    }
    echo '<!doctype html><meta charset="utf-8"><title>Error</title>';
    echo '<link rel="stylesheet" href="assets/app.css"><main class="auth-shell"><section class="auth-card">';
    echo '<h1>Unable to complete request</h1><p>' . e($message) . '</p>';
    echo '<a class="btn" href="admin_dashboard.php">Return to dashboard</a></section></main>';
});
