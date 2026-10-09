<?php
/**
 * TastyBites — Database Connection
 * Opens a single mysqli connection and exposes it as $db.
 */

require_once __DIR__ . '/config.php';

$db = new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 3306);

if ($db->connect_errno) {
    die('Database connection failed.');
}

$db->set_charset('utf8mb4');