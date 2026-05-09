<?php
/**
 * NetServe – Central Configuration
 * ---------------------------------
 * Keep this file ABOVE your web root in production.
 * For XAMPP (local dev), storing here is acceptable.
 *
 * To change your Gmail SMTP password, update SMTP_PASS below.
 */

// ── Database ──────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'airtel_db');

// ── SMTP (Gmail) ──────────────────────────────────────────────
// Use a Gmail App Password (not your main Gmail password).
// Generate one at: https://myaccount.google.com/apppasswords
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',      587);
define('SMTP_USER',     'harshal815815815@gmail.com');   // ← Your Gmail
define('SMTP_PASS',     'ipjr lakk eecm rkrr');           // ← App password
define('SMTP_FROM',     'harshal815815815@gmail.com');
define('SMTP_FROM_NAME','NetServe Service');

// ── App Settings ─────────────────────────────────────────────
define('APP_NAME',   'NetServe');
define('ROWS_PER_PAGE', 10);    // Pagination: rows shown per page
