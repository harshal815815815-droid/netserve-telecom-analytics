<?php
/**
 * razorpay-config.php — Razorpay Gateway Configuration
 * ───────────────────────────────────────────────────────
 * All credentials are loaded from the .env file.
 * No keys are hardcoded — safe to commit to GitHub.
 *
 * HOW TO CONFIGURE:
 *  1. Copy .env.example → .env
 *  2. Login at https://dashboard.razorpay.com
 *  3. Settings → API Keys → Generate Key
 *  4. Paste Key ID and Secret into your .env file
 *
 * Use rzp_test_ keys for development, rzp_live_ for production.
 *
 * Include this file AFTER config.php (which loads the .env).
 */

// ── Razorpay API Credentials (loaded from .env) ───────────────
define('RZP_KEY_ID',     env('RZP_KEY_ID',     ''));
define('RZP_KEY_SECRET', env('RZP_KEY_SECRET',  ''));

// ── Transaction Settings ──────────────────────────────────────
define('RZP_CURRENCY', env('RZP_CURRENCY', 'INR'));

// ── Branding (shown in checkout popup) ───────────────────────
define('RZP_COMPANY', env('RZP_COMPANY',  'NetServe Telecom'));
define('RZP_LOGO',    env('RZP_LOGO',     ''));
