<?php
/**
 * TastyBites — Authentication Helpers
 * Session-based identity reads and access guards.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

/** True if a user is logged in. */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/** True if the logged-in user has admin role. */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/** Returns the current user's id, or null if not logged in. */
function currentUserId(): ?int
{
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

/** Returns the current user's display name, or null if not logged in. */
function currentUserName(): ?string
{
    return isLoggedIn() ? ($_SESSION['user_name'] ?? null) : null;
}

/** If not logged in, redirect to login.php and exit. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('Please log in to continue.', 'warning');
        redirect('/auth/login.php');
    }
}

/** If not admin, redirect to home with a danger flash and exit. */
function requireAdmin(): void
{
    if (!isAdmin()) {
        setFlash('Admin access required.', 'danger');
        redirect('/index.php');
    }
}
