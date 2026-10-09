<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireAdmin();

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

/** Returns the integer count from a single-row, single-column query. */
function fetchCount(mysqli $db, string $sql): int
{
    $res = $db->query($sql);
    if (!$res) { return 0; }
    $row = $res->fetch_row();
    return (int)($row[0] ?? 0);
}

$totalUsers     = fetchCount($db, "SELECT COUNT(*) FROM users");
$totalMenuItems = fetchCount($db, "SELECT COUNT(*) FROM menu_items");
$activeTables   = fetchCount($db, "SELECT COUNT(*) FROM tables WHERE is_active = 1");
$pendingResv    = fetchCount($db, "SELECT COUNT(*) FROM reservations WHERE status = 'pending'");
$unreadMessages = fetchCount($db, "SELECT COUNT(*) FROM messages WHERE is_read = 0");

$res = $db->query(
    "SELECT r.id, r.reservation_date, r.reservation_time, r.party_size, r.status,
            r.created_at, u.full_name, t.table_number
     FROM reservations r
     JOIN users u  ON u.id = r.user_id
     JOIN tables t ON t.id = r.table_id
     ORDER BY r.created_at DESC
     LIMIT 10"
);
$recent = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Admin Dashboard</h1>

<div class="row g-3 mb-5">
    <?php
    $stats = [
        ['label' => 'Users',           'value' => $totalUsers,     'icon' => 'people'],
        ['label' => 'Menu Items',      'value' => $totalMenuItems, 'icon' => 'card-list'],
        ['label' => 'Active Tables',   'value' => $activeTables,   'icon' => 'table'],
        ['label' => 'Pending Resv.',   'value' => $pendingResv,    'icon' => 'hourglass-split'],
        ['label' => 'Unread Messages', 'value' => $unreadMessages, 'icon' => 'envelope'],
    ];
    foreach ($stats as $s): ?>
        <div class="col-md">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-<?= e($s['icon']) ?> fs-2 text-secondary"></i>
                    <h2 class="display-6 mb-1"><?= (int)$s['value'] ?></h2>
                    <p class="text-muted mb-0"><?= e($s['label']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<h2 class="mb-3">Recent Reservations</h2>
<?php if (empty($recent)): ?>
    <p class="text-muted">No reservations yet.</p>
<?php else: ?>
    <div class="table-responsive mb-5">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Table</th>
                    <th>Date &amp; Time</th>
                    <th>Party</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td>Table #<?= (int)$r['table_number'] ?></td>
                    <td><?= e(formatReservationDateTime($r['reservation_date'], $r['reservation_time'])) ?></td>
                    <td><?= (int)$r['party_size'] ?></td>
                    <td>
                        <span class="badge <?= statusBadgeClass($r['status']) ?>">
                            <?= e(ucfirst($r['status'])) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= e($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2 class="mb-3">Quick Links</h2>
<div class="row g-3">
    <?php
    $links = [
        ['url' => '/admin/menu_manage.php',        'label' => 'Manage Menu',         'icon' => 'card-list'],
        ['url' => '/admin/table_manage.php',       'label' => 'Manage Tables',       'icon' => 'grid-3x3'],
        ['url' => '/admin/reservation_manage.php', 'label' => 'Manage Reservations', 'icon' => 'calendar-check'],
        ['url' => '/admin/messages.php',           'label' => 'Messages',            'icon' => 'envelope'],
    ];
    foreach ($links as $lk): ?>
        <div class="col-md-3">
            <a href="<?= BASE_URL . e($lk['url']) ?>"
               class="card text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-<?= e($lk['icon']) ?> fs-1 text-primary"></i>
                    <h5 class="mt-2 mb-0"><?= e($lk['label']) ?></h5>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
