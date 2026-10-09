<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireLogin();

if (currentMethod() !== 'POST') {
    setFlash('Invalid request method.', 'danger');
    redirect('/user/dashboard.php');
}

$reservationId = (int)($_POST['reservation_id'] ?? 0);
if ($reservationId <= 0) {
    setFlash('Invalid reservation reference.', 'danger');
    redirect('/user/dashboard.php');
}

$userId = currentUserId();

// Ownership + status fetch
$stmt = $db->prepare(
    "SELECT id, status, reservation_date
     FROM reservations
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);
$stmt->bind_param('ii', $reservationId, $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    setFlash('Reservation not found.', 'danger');
    redirect('/user/dashboard.php');
}

if (!in_array($row['status'], ['pending', 'confirmed'], true)) {
    setFlash('Only pending or confirmed reservations can be cancelled.', 'warning');
    redirect('/user/dashboard.php');
}

if ($row['reservation_date'] < date('Y-m-d')) {
    setFlash('Past reservations cannot be cancelled.', 'warning');
    redirect('/user/dashboard.php');
}

// Update
$stmt = $db->prepare(
    "UPDATE reservations
     SET status = 'cancelled'
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('ii', $reservationId, $userId);
$stmt->execute();
$stmt->close();

setFlash('Reservation cancelled.', 'info');
redirect('/user/dashboard.php');
