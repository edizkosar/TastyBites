<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';

/** Fetch a message by id or null. */
function fetchMessage(mysqli $db, int $id): ?array
{
    $stmt = $db->prepare(
        "SELECT id, full_name, email, subject, body, is_read, created_at
         FROM messages WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// ============ DELETE ============
if ($action === 'delete' && currentMethod() === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        setFlash('Message deleted.', 'info');
    } else {
        setFlash('Invalid message reference.', 'danger');
    }
    redirect('/admin/messages.php');
}

// ============ MARK UNREAD ============
if ($action === 'mark_unread' && currentMethod() === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("UPDATE messages SET is_read = 0 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        setFlash('Marked as unread.', 'info');
    }
    redirect('/admin/messages.php');
}

// ============ VIEW ============
if ($action === 'view') {
    $id = (int)($_GET['id'] ?? 0);
    $msg = $id > 0 ? fetchMessage($db, $id) : null;
    if (!$msg) {
        setFlash('Message not found.', 'danger');
        redirect('/admin/messages.php');
    }

    // Auto-mark as read (idempotent).
    if (empty($msg['is_read'])) {
        $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $msg['is_read'] = 1;
    }

    $pageTitle = 'Message · ' . $msg['subject'];
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <p>
        <a href="<?= BASE_URL ?>/admin/messages.php" class="btn btn-link p-0">
            ← Back to list
        </a>
    </p>

    <div class="card">
        <div class="card-body">
            <h1 class="h4 mb-3"><?= e($msg['subject']) ?></h1>
            <p class="text-muted mb-3">
                From <strong><?= e($msg['full_name']) ?></strong>
                &lt;<?= e($msg['email']) ?>&gt;
                &middot; <?= e($msg['created_at']) ?>
            </p>
            <hr>
            <p class="mb-4"><?= nl2br(e($msg['body'])) ?></p>

            <div class="d-flex gap-2">
                <form method="post"
                      action="<?= BASE_URL ?>/admin/messages.php?action=mark_unread"
                      class="d-inline">
                    <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                    <button type="submit" class="btn btn-outline-secondary">
                        Mark Unread
                    </button>
                </form>
                <form method="post"
                      action="<?= BASE_URL ?>/admin/messages.php?action=delete"
                      class="d-inline"
                      onsubmit="return confirm('Delete this message?');">
                    <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ============ LIST ============
$filter = $_GET['filter'] ?? '';
$sql = "SELECT id, full_name, email, subject, is_read, created_at
        FROM messages";
if ($filter === 'unread') {
    $sql .= " WHERE is_read = 0";
}
$sql .= " ORDER BY created_at DESC LIMIT 200";
$res = $db->query($sql);
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Messages</h1>
    <div>
        <a href="<?= BASE_URL ?>/admin/messages.php"
           class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline-primary' ?>">
            All
        </a>
        <a href="<?= BASE_URL ?>/admin/messages.php?filter=unread"
           class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' ?>">
            Unread
        </a>
    </div>
</div>

<p class="text-muted">Showing <?= count($rows) ?> message(s).</p>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th></th>
                <th>From</th>
                <th>Subject</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="5" class="text-center text-muted">No messages.</td></tr>
        <?php else: foreach ($rows as $m): ?>
            <tr class="<?= empty($m['is_read']) ? 'table-warning' : '' ?>">
                <td>
                    <?php if (empty($m['is_read'])): ?>
                        <i class="bi bi-envelope-fill text-primary" title="Unread"></i>
                    <?php else: ?>
                        <i class="bi bi-envelope-open text-muted" title="Read"></i>
                    <?php endif; ?>
                </td>
                <td>
                    <?= e($m['full_name']) ?>
                    <small class="d-block text-muted"><?= e($m['email']) ?></small>
                </td>
                <td>
                    <a href="<?= BASE_URL ?>/admin/messages.php?action=view&id=<?= (int)$m['id'] ?>">
                        <?= e($m['subject']) ?>
                    </a>
                </td>
                <td class="small text-muted"><?= e($m['created_at']) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/admin/messages.php?action=view&id=<?= (int)$m['id'] ?>"
                       class="btn btn-sm btn-outline-primary">Open</a>
                    <form method="post"
                          action="<?= BASE_URL ?>/admin/messages.php?action=delete"
                          class="d-inline"
                          onsubmit="return confirm('Delete this message?');">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
