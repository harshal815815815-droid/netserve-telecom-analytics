<?php
/**
 * config.php — NetServe Central Configuration
 * ─────────────────────────────────────────────
 * Loads all settings from the .env file in the project root.
 * No secrets are hardcoded here — safe to commit to GitHub.
 *
 * For production deployment:
 *   - Upload your .env file to the server (above web root if possible)
 *   - OR set environment variables directly via your hosting panel / .htaccess
 */

// ── Load .env file ────────────────────────────────────────────
$_envFile = __DIR__ . '/.env';

if (file_exists($_envFile)) {
    $lines = file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and blank lines
        if ($line === '' || str_starts_with($line, '#')) continue;
        // Parse KEY=VALUE
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]    = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Helper: safely read an env var with an optional fallback
function env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── Database ──────────────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'airtel_db'));

// ── SMTP (Gmail) ──────────────────────────────────────────────
define('SMTP_HOST',      env('SMTP_HOST',      'smtp.gmail.com'));
define('SMTP_PORT', (int) env('SMTP_PORT',      '587'));
define('SMTP_USER',      env('SMTP_USER',       ''));
define('SMTP_PASS',      env('SMTP_PASS',       ''));
define('SMTP_FROM',      env('SMTP_FROM',       ''));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME',  'NetServe Service'));

// ── App Settings ─────────────────────────────────────────────
define('APP_NAME',      env('APP_NAME', 'NetServe'));
define('ROWS_PER_PAGE', 10);    // Pagination: rows shown per page
