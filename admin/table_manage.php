<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';

/** Returns row or null. */
function fetchTable(mysqli $db, int $id): ?array
{
    $stmt = $db->prepare(
        "SELECT id, table_number, capacity, location_note, is_active
         FROM tables WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/** True if another row (id != $excludeId) has the same table_number. */
function tableNumberExists(mysqli $db, int $tableNumber, int $excludeId = 0): bool
{
    $stmt = $db->prepare(
        "SELECT id FROM tables WHERE table_number = ? AND id <> ? LIMIT 1"
    );
    $stmt->bind_param('ii', $tableNumber, $excludeId);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

/** True if the table has any active (pending/confirmed AND future) reservation. */
function tableHasActiveReservations(mysqli $db, int $tableId): bool
{
    $stmt = $db->prepare(
        "SELECT id FROM reservations
         WHERE table_id = ?
           AND status IN ('pending','confirmed')
           AND reservation_date >= CURDATE()
         LIMIT 1"
    );
    $stmt->bind_param('i', $tableId);
    $stmt->execute();
    $stmt->store_result();
    $has = $stmt->num_rows > 0;
    $stmt->close();
    return $has;
}

// ============ SAVE ============
if ($action === 'save' && currentMethod() === 'POST') {
    $id           = (int)($_POST['id'] ?? 0);
    $tableNumber  = (int)($_POST['table_number'] ?? 0);
    $capacity     = (int)($_POST['capacity'] ?? 0);
    $locationNote = trim($_POST['location_note'] ?? '');
    $isActive     = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];
    if ($tableNumber < 1 || $tableNumber > 999) {
        $errors[] = 'Table number must be 1–999.';
    }
    if ($capacity < 1 || $capacity > 30) {
        $errors[] = 'Capacity must be 1–30.';
    }
    if (mb_strlen($locationNote) > 80) {
        $errors[] = 'Location note too long (max 80 chars).';
    }
    if ($tableNumber > 0 && tableNumberExists($db, $tableNumber, $id)) {
        $errors[] = 'Another table already uses this number.';
    }

    if (empty($errors)) {
        $noteToStore = $locationNote !== '' ? $locationNote : null;
        if ($id > 0) {
            $stmt = $db->prepare(
                "UPDATE tables
                 SET table_number = ?, capacity = ?, location_note = ?, is_active = ?
                 WHERE id = ?"
            );
            $stmt->bind_param('iisii', $tableNumber, $capacity, $noteToStore, $isActive, $id);
            $stmt->execute();
            $stmt->close();
            setFlash('Table updated.', 'success');
        } else {
            $stmt = $db->prepare(
                "INSERT INTO tables (table_number, capacity, location_note, is_active)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('iisi', $tableNumber, $capacity, $noteToStore, $isActive);
            $stmt->execute();
            $stmt->close();
            setFlash('Table created.', 'success');
        }
        redirect('/admin/table_manage.php');
    }

    $table = [
        'id'            => $id,
        'table_number'  => $tableNumber,
        'capacity'      => $capacity,
        'location_note' => $locationNote,
        'is_active'     => $isActive,
    ];
    $action = $id > 0 ? 'edit' : 'new';
}

// ============ DELETE ============
if ($action === 'delete' && currentMethod() === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        setFlash('Invalid table reference.', 'danger');
        redirect('/admin/table_manage.php');
    }
    if (tableHasActiveReservations($db, $id)) {
        setFlash(
            'This table has active reservations and cannot be deleted. Deactivate it instead.',
            'warning'
        );
        redirect('/admin/table_manage.php');
    }
    $stmt = $db->prepare("DELETE FROM tables WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    setFlash('Table deleted.', 'info');
    redirect('/admin/table_manage.php');
}

// ============ EDIT (load) ============
if ($action === 'edit' && !isset($table)) {
    $id    = (int)($_GET['id'] ?? 0);
    $table = $id > 0 ? fetchTable($db, $id) : null;
    if (!$table) {
        setFlash('Table not found.', 'danger');
        redirect('/admin/table_manage.php');
    }
}

// ============ NEW ============
if ($action === 'new' && !isset($table)) {
    $table = [
        'id' => 0, 'table_number' => '', 'capacity' => '',
        'location_note' => '', 'is_active' => 1,
    ];
}

$pageTitle = 'Manage Tables';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'new' || $action === 'edit'): ?>
    <h1 class="mb-4"><?= $table['id'] > 0 ? 'Edit Table' : 'New Table' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/admin/table_manage.php?action=save" novalidate>
        <input type="hidden" name="id" value="<?= (int)$table['id'] ?>">

        <div class="mb-3">
            <label class="form-label" for="table_number">Table Number</label>
            <input type="number" min="1" max="999" id="table_number" name="table_number"
                   class="form-control" value="<?= e($table['table_number']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="capacity">Capacity</label>
            <input type="number" min="1" max="30" id="capacity" name="capacity"
                   class="form-control" value="<?= e($table['capacity']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="location_note">Location Note</label>
            <input type="text" id="location_note" name="location_note"
                   class="form-control" maxlength="80"
                   value="<?= e($table['location_note']) ?>">
            <small class="text-muted">Optional. e.g. "Window side", "Terrace".</small>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" id="is_active" name="is_active"
                   class="form-check-input" <?= !empty($table['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?= BASE_URL ?>/admin/table_manage.php" class="btn btn-link">Cancel</a>
    </form>

<?php else: /* list */ ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Manage Tables</h1>
        <a href="<?= BASE_URL ?>/admin/table_manage.php?action=new" class="btn btn-primary">
            + New Table
        </a>
    </div>

    <?php
    $res  = $db->query(
        "SELECT id, table_number, capacity, location_note, is_active
         FROM tables ORDER BY table_number ASC"
    );
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    ?>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Number</th>
                    <th>Capacity</th>
                    <th>Location</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-center text-muted">No tables yet.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td>Table #<?= (int)$r['table_number'] ?></td>
                    <td><?= (int)$r['capacity'] ?></td>
                    <td><?= e($r['location_note'] ?? '') ?></td>
                    <td>
                        <?php if (!empty($r['is_active'])): ?>
                            <span class="badge bg-success">Yes</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/admin/table_manage.php?action=edit&id=<?= (int)$r['id'] ?>"
                           class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post"
                              action="<?= BASE_URL ?>/admin/table_manage.php?action=delete"
                              class="d-inline"
                              onsubmit="return confirm('Delete this table?');">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
