<?php
/**
 * TastyBites — Global Configuration
 * Single source of truth for environment constants, error reporting,
 * timezone, and session bootstrap.
 */

// ---------- Error Reporting (dev mode) ----------
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---------- Timezone ----------
date_default_timezone_set('Europe/Istanbul');

// ---------- Database ----------
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tastybites');

// ---------- Application ----------
define('APP_NAME', 'TastyBites');
define('BASE_URL', '/tastybites');

// ---------- Reservation Rules ----------
define('RESERVATION_OPEN_HOUR', 11);
define('RESERVATION_CLOSE_HOUR', 22);
define('RESERVATION_SLOT_MINUTES', 30);
define('RESERVATION_MAX_GUESTS', 12);
define('RESERVATION_DAYS_AHEAD', 90);

// ---------- Session ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
