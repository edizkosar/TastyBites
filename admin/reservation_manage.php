<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireAdmin();

$allowedStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];

// ============ STATUS UPDATE ============
if (($_GET['action'] ?? '') === 'update_status' && currentMethod() === 'POST') {
    $id        = (int)($_POST['id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';

    if ($id <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
        setFlash('Invalid status update.', 'danger');
    } else {
        $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $newStatus, $id);
        $stmt->execute();
        $stmt->close();
        setFlash('Reservation status updated.', 'success');
    }

    // Preserve filter query string in the redirect.
    $qs = $_POST;
    unset($qs['action'], $qs['id'], $qs['new_status']);
    $tail = !empty($qs) ? '?' . http_build_query($qs) : '';
    redirect('/admin/reservation_manage.php' . $tail);
}

// ============ LIST WITH FILTERS ============
$statusFilter = $_GET['status']    ?? '';
$dateFrom     = $_GET['date_from'] ?? '';
$dateTo       = $_GET['date_to']   ?? '';

// Sanity-check status filter
if ($statusFilter !== '' && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$conditions = [];
$params     = [];
$types      = '';
if ($statusFilter !== '') { $conditions[] = 'r.status = ?';            $params[] = $statusFilter; $types .= 's'; }
if ($dateFrom !== '')     { $conditions[] = 'r.reservation_date >= ?'; $params[] = $dateFrom;     $types .= 's'; }
if ($dateTo !== '')       { $conditions[] = 'r.reservation_date <= ?'; $params[] = $dateTo;       $types .= 's'; }

$sql = "SELECT r.id, r.reservation_date, r.reservation_time, r.party_size, r.status,
               r.special_request, r.created_at,
               u.full_name, u.email, u.phone,
               t.table_number
        FROM reservations r
        JOIN users u  ON u.id = r.user_id
        JOIN tables t ON t.id = r.table_id";
if (!empty($conditions)) { $sql .= ' WHERE ' . implode(' AND ', $conditions); }
$sql .= ' ORDER BY r.reservation_date DESC, r.reservation_time DESC LIMIT 200';

$stmt = $db->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$filterQuery = array_filter([
    'status'    => $statusFilter,
    'date_from' => $dateFrom,
    'date_to'   => $dateTo,
], fn($v) => $v !== '');

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

$pageTitle = 'Manage Reservations';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Manage Reservations</h1>

<form method="get" action="<?= BASE_URL ?>/admin/reservation_manage.php" class="row g-2 mb-4">
    <div class="col-md-3">
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="">All</option>
            <?php foreach ($allowedStatuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>>
                    <?= e(ucfirst($s)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="date_from" class="form-label">Date From</label>
        <input type="date" id="date_from" name="date_from" class="form-control"
               value="<?= e($dateFrom) ?>">
    </div>
    <div class="col-md-3">
        <label for="date_to" class="form-label">Date To</label>
        <input type="date" id="date_to" name="date_to" class="form-control"
               value="<?= e($dateTo) ?>">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">Filter</button>
        <a href="<?= BASE_URL ?>/admin/reservation_manage.php" class="btn btn-link">Reset</a>
    </div>
</form>

<p class="text-muted">Showing <?= count($rows) ?> reservation(s).</p>

<div class="table-responsive">
    <table class="table align-middle table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>Created</th>
                <th>Customer</th>
                <th>Table</th>
                <th>Date &amp; Time</th>
                <th>Party</th>
                <th>Status</th>
                <th>Request</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="text-center text-muted">No reservations match the filters.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td class="small text-muted"><?= e($r['created_at']) ?></td>
                <td>
                    <?= e($r['full_name']) ?>
                    <small class="d-block text-muted"><?= e($r['email']) ?></small>
                    <?php if (!empty($r['phone'])): ?>
                        <small class="d-block text-muted"><?= e($r['phone']) ?></small>
                    <?php endif; ?>
                </td>
                <td>Table #<?= (int)$r['table_number'] ?></td>
                <td><?= e(formatReservationDateTime($r['reservation_date'], $r['reservation_time'])) ?></td>
                <td><?= (int)$r['party_size'] ?></td>
                <td>
                    <span class="badge <?= statusBadgeClass($r['status']) ?> mb-1 d-inline-block">
                        <?= e(ucfirst($r['status'])) ?>
                    </span>
                    <form method="post"
                          action="<?= BASE_URL ?>/admin/reservation_manage.php?action=update_status<?= !empty($filterQuery) ? '&' . http_build_query($filterQuery) : '' ?>"
                          class="d-inline">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <?php foreach ($filterQuery as $k => $v): ?>
                            <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                        <?php endforeach; ?>
                        <select name="new_status" class="form-select form-select-sm"
                                onchange="this.form.submit()">
                            <?php foreach ($allowedStatuses as $s): ?>
                                <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>>
                                    <?= e(ucfirst($s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td class="small text-muted"><?= e($r['special_request'] ?? '') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
