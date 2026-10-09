<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_helpers.php';

requireLogin();

/** Returns the list of valid 30-minute time slots between open and close hours. */
function generateTimeSlots(): array
{
    $slots = [];
    for ($h = RESERVATION_OPEN_HOUR; $h < RESERVATION_CLOSE_HOUR; $h++) {
        for ($m = 0; $m < 60; $m += RESERVATION_SLOT_MINUTES) {
            $slots[] = sprintf('%02d:%02d', $h, $m);
        }
    }
    return $slots;
}

/** True if a (table, date, time) triple has no conflicting reservation. */
function isTableAvailable(mysqli $db, int $tableId, string $date, string $time): bool
{
    $stmt = $db->prepare(
        "SELECT id FROM reservations
         WHERE table_id = ? AND reservation_date = ? AND reservation_time = ?
           AND status IN ('pending','confirmed')
         LIMIT 1"
    );
    $stmt->bind_param('iss', $tableId, $date, $time);
    $stmt->execute();
    $stmt->store_result();
    $available = $stmt->num_rows === 0;
    $stmt->close();
    return $available;
}

// -------- Page logic --------

$slots  = generateTimeSlots();
$errors = [];
$old = [
    'reservation_date' => '',
    'reservation_time' => '',
    'party_size'       => '',
    'table_id'         => '',
    'special_request'  => '',
];

// Fetch active tables for the dropdown.
$tStmt = $db->prepare(
    "SELECT id, table_number, capacity, location_note
     FROM tables WHERE is_active = 1
     ORDER BY table_number ASC"
);
$tStmt->execute();
$activeTables = $tStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$tStmt->close();

if (currentMethod() === 'POST') {
    $old['reservation_date'] = trim($_POST['reservation_date'] ?? '');
    $old['reservation_time'] = trim($_POST['reservation_time'] ?? '');
    $old['party_size']       = trim($_POST['party_size'] ?? '');
    $old['table_id']         = trim($_POST['table_id'] ?? '');
    $old['special_request']  = trim($_POST['special_request'] ?? '');

    // 1. Required
    if ($old['reservation_date'] === '') { $errors[] = 'Please pick a date.'; }
    if ($old['reservation_time'] === '') { $errors[] = 'Please pick a time slot.'; }
    if ($old['party_size'] === '')       { $errors[] = 'Please enter the party size.'; }
    if ($old['table_id'] === '')         { $errors[] = 'Please pick a table.'; }

    // 2. Date validity
    $dateValid = false;
    $dt = DateTime::createFromFormat('Y-m-d', $old['reservation_date']);
    if ($dt && $dt->format('Y-m-d') === $old['reservation_date']) {
        $today   = new DateTime('today');
        $maxDate = (clone $today)->modify('+' . RESERVATION_DAYS_AHEAD . ' days');
        if ($dt < $today) {
            $errors[] = 'Reservation date cannot be in the past.';
        } elseif ($dt > $maxDate) {
            $errors[] = 'Reservation date is too far in the future (max ' . RESERVATION_DAYS_AHEAD . ' days).';
        } else {
            $dateValid = true;
        }
    } elseif ($old['reservation_date'] !== '') {
        $errors[] = 'Invalid date format.';
    }

    // 3. Time slot must be in the allowed list
    if ($old['reservation_time'] !== '' && !in_array($old['reservation_time'], $slots, true)) {
        $errors[] = 'Invalid time slot.';
    }

    // 4. Party size
    $partySize = (int)$old['party_size'];
    if ($old['party_size'] !== '' && ($partySize < 1 || $partySize > RESERVATION_MAX_GUESTS)) {
        $errors[] = 'Party size must be between 1 and ' . RESERVATION_MAX_GUESTS . '.';
    }

    // 5. Table existence + capacity
    $tableId     = (int)$old['table_id'];
    $chosenTable = null;
    if ($tableId > 0) {
        foreach ($activeTables as $t) {
            if ((int)$t['id'] === $tableId) {
                $chosenTable = $t;
                break;
            }
        }
        if ($chosenTable === null) {
            $errors[] = 'Selected table is not available.';
        } elseif ($partySize > 0 && $partySize > (int)$chosenTable['capacity']) {
            $errors[] = 'Selected table cannot seat ' . $partySize . ' guests.';
        }
    }

    // 6. Special request length
    if (mb_strlen($old['special_request']) > 500) {
        $errors[] = 'Special request is too long (max 500 characters).';
    }

    // 7. Conflict check (only if everything else is clean)
    if (empty($errors) && $dateValid && $chosenTable) {
        if (!isTableAvailable($db, $tableId, $old['reservation_date'], $old['reservation_time'])) {
            $errors[] = 'This table is already booked at that time. Please pick another slot or table.';
        }
    }

    // INSERT
    if (empty($errors)) {
        $userId         = currentUserId();
        $specialRequest = $old['special_request'] !== '' ? $old['special_request'] : null;
        $stmt = $db->prepare(
            "INSERT INTO reservations
                (user_id, table_id, reservation_date, reservation_time,
                 party_size, special_request, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param(
            'iissis',
            $userId, $tableId,
            $old['reservation_date'], $old['reservation_time'],
            $partySize, $specialRequest
        );
        $stmt->execute();
        $stmt->close();

        setFlash('Reservation requested! We will confirm it shortly.', 'success');
        redirect('/user/dashboard.php');
    }
}

$pageTitle = 'Reserve a Table';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <h1 class="mb-4">Reserve a Table</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/reservation.php" novalidate>
            <div class="mb-3">
                <label class="form-label" for="reservation_date">Date</label>
                <input type="date" id="reservation_date" name="reservation_date"
                       class="form-control" data-min-today
                       value="<?= e($old['reservation_date']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="reservation_time">Time</label>
                <select id="reservation_time" name="reservation_time" class="form-select" required>
                    <option value="">-- Select a time --</option>
                    <?php foreach ($slots as $slot): ?>
                        <option value="<?= e($slot) ?>"
                            <?= $old['reservation_time'] === $slot ? 'selected' : '' ?>>
                            <?= e($slot) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Open 11:00 – 22:00. Last slot 21:30.</small>
            </div>

            <div class="mb-3">
                <label class="form-label" for="party_size">Party Size</label>
                <input type="number" id="party_size" name="party_size"
                       min="1" max="<?= RESERVATION_MAX_GUESTS ?>"
                       class="form-control"
                       value="<?= e($old['party_size']) ?>" required>
                <small class="text-muted">Max <?= RESERVATION_MAX_GUESTS ?> guests. For larger groups, please contact us.</small>
            </div>

            <div class="mb-3">
                <label class="form-label" for="table_id">Table</label>
                <select id="table_id" name="table_id" class="form-select" required>
                    <option value="">-- Select a table --</option>
                    <?php foreach ($activeTables as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"
                            <?= (string)$old['table_id'] === (string)$t['id'] ? 'selected' : '' ?>>
                            Table #<?= (int)$t['table_number'] ?>
                            — seats <?= (int)$t['capacity'] ?>
                            <?php if (!empty($t['location_note'])): ?>
                                — <?= e($t['location_note']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="special_request">
                    Special Request <small class="text-muted">(optional, max 500 chars)</small>
                </label>
                <textarea id="special_request" name="special_request"
                          class="form-control" rows="3"
                          maxlength="500"><?= e($old['special_request']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Request Reservation</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
