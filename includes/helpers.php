<?php
/**
 * TastyBites — Helper Functions
 * Pure procedural helpers: escaping, redirect, flash, formatting.
 */

require_once __DIR__ . '/config.php';

/** Escapes a string for safe HTML output. */
function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirects to a BASE_URL-relative path and exits. */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/** Stores a one-shot flash message in the session. */
function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/** Returns and clears the current flash message, or null if none. */
function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Formats a numeric price as "$12.50". */
function formatPrice($value): string
{
    return '$' . number_format((float)$value, 2, '.', ',');
}

/** Formats a (date, time) pair as "Mon, 24 Jun 2025 · 19:30". */
function formatReservationDateTime(string $date, string $time): string
{
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
    if (!$dt) {
        $dt = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . substr($time, 0, 5));
    }
    if (!$dt) {
        return $date . ' ' . $time;
    }
    return $dt->format('D, d M Y') . ' · ' . $dt->format('H:i');
}

/** True if the string is a syntactically valid email. */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Returns the current request method in UPPERCASE. */
function currentMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}
