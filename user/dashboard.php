<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireLogin();

/** Returns the Bootstrap badge class for a given reservation status. */
function statusBadgeClass(string $status): string
{
    return match ($status) {
        'pending'   => 'bg-warning text-dark',
        'confirmed' => 'bg-success',
        'cancelled' => 'bg-secondary',
        'completed' => 'bg-info text-dark',
        default     => 'bg-light text-dark',
    };
}

$userId = currentUserId();
$today  = date('Y-m-d');

// Upcoming reservations
$stmt = $db->prepare(
    "SELECT r.id, r.reservation_date, r.reservation_time, r.party_size,
            r.special_request, r.status,
            t.table_number, t.capacity, t.location_note
     FROM reservations r
     JOIN tables t ON t.id = r.table_id
     WHERE r.user_id = ? AND r.reservation_date >= ?
     ORDER BY r.reservation_date ASC, r.reservation_time ASC"
);
$stmt->bind_param('is', $userId, $today);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Past reservations
$stmt = $db->prepare(
    "SELECT r.id, r.reservation_date, r.reservation_time, r.party_size,
            r.special_request, r.status,
            t.table_number, t.capacity, t.location_note
     FROM reservations r
     JOIN tables t ON t.id = r.table_id
     WHERE r.user_id = ? AND r.reservation_date < ?
     ORDER BY r.reservation_date DESC, r.reservation_time DESC
     LIMIT 20"
);
$stmt->bind_param('is', $userId, $today);
$stmt->execute();
$past = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Active count = pending OR confirmed AND in the future
$activeCount = 0;
foreach ($upcoming as $r) {
    if (in_array($r['status'], ['pending', 'confirmed'], true)) {
        $activeCount++;
    }
}

$pageTitle = 'My Reservations';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">My Reservations</h1>

<div class="row g-3 mb-5">
    <div class="col-md-4">
        <div class="card text-center"><div class="card-body">
            <h2 class="display-6 mb-1"><?= count($upcoming) ?></h2>
            <p class="text-muted mb-0">Upcoming</p>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-center"><div class="card-body">
            <h2 class="display-6 mb-1"><?= count($past) ?></h2>
            <p class="text-muted mb-0">Past (last 20)</p>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-center"><div class="card-body">
            <h2 class="display-6 mb-1"><?= $activeCount ?></h2>
            <p class="text-muted mb-0">Active</p>
        </div></div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Upcoming Reservations</h2>
    <a href="<?= BASE_URL ?>/reservation.php" class="btn btn-primary btn-sm">
        + New Reservation
    </a>
</div>

<?php if (empty($upcoming)): ?>
    <p class="text-muted">
        You have no upcoming reservations.
        <a href="<?= BASE_URL ?>/reservation.php">Book a table</a>.
    </p>
<?php else: ?>
    <div class="table-responsive mb-5">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>Table</th>
                    <th>Party</th>
                    <th>Status</th>
                    <th>Request</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($upcoming as $r): ?>
                <tr>
                    <td><?= e(formatReservationDateTime($r['reservation_date'], $r['reservation_time'])) ?></td>
                    <td>
                        Table #<?= (int)$r['table_number'] ?>
                        <small class="text-muted d-block">
                            seats <?= (int)$r['capacity'] ?>
                            <?= !empty($r['location_note']) ? '· ' . e($r['location_note']) : '' ?>
                        </small>
                    </td>
                    <td><?= (int)$r['party_size'] ?></td>
                    <td>
                        <span class="badge <?= statusBadgeClass($r['status']) ?>">
                            <?= e(ucfirst($r['status'])) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= e($r['special_request'] ?? '') ?></td>
                    <td>
                        <?php
                        $canCancel = in_array($r['status'], ['pending', 'confirmed'], true)
                                  && $r['reservation_date'] >= $today;
                        ?>
                        <?php if ($canCancel): ?>
                            <form method="post"
                                  action="<?= BASE_URL ?>/user/cancel_reservation.php"
                                  class="d-inline"
                                  onsubmit="return confirm('Cancel this reservation?');">
                                <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2 class="mb-3">Past Reservations</h2>
<?php if (empty($past)): ?>
    <p class="text-muted">No past reservations yet.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>Table</th>
                    <th>Party</th>
                    <th>Status</th>
                    <th>Request</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($past as $r): ?>
                <tr>
                    <td><?= e(formatReservationDateTime($r['reservation_date'], $r['reservation_time'])) ?></td>
                    <td>Table #<?= (int)$r['table_number'] ?></td>
                    <td><?= (int)$r['party_size'] ?></td>
                    <td>
                        <span class="badge <?= statusBadgeClass($r['status']) ?>">
                            <?= e(ucfirst($r['status'])) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= e($r['special_request'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
