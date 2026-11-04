<?php
require_once __DIR__ . '/../config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function e($s): string {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

function rupees($n): string {
    return '₹' . rtrim(rtrim(number_format((float)$n, 2), '0'), '.');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

function make_ref(): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no look-alike characters
    $out = '';
    for ($i = 0; $i < 6; $i++) $out .= $chars[random_int(0, strlen($chars) - 1)];
    return 'KK-' . $out;
}

function phone_digits(string $p): string {
    $d = preg_replace('/\D+/', '', $p);
    return substr($d, -10); // compare the last 10 digits
}
