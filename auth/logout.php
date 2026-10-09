<?php
/**
 * TastyBites — Logout
 * Destroys the current session, sets a goodbye flash, redirects home.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Clear all session variables.
$_SESSION = [];

// Destroy the session.
session_destroy();

// Start a fresh session for the flash message.
session_start();
setFlash('You have been logged out.', 'info');

redirect('/index.php');
