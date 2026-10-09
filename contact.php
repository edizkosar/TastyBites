<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_helpers.php';

$errors = [];

// Pre-fill name if logged in (email left blank — only stored in DB)
$old = [
    'full_name' => currentUserName() ?? '',
    'email'     => '',
    'subject'   => '',
    'body'      => '',
];

if (currentMethod() === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['subject']   = trim($_POST['subject'] ?? '');
    $old['body']      = trim($_POST['body'] ?? '');

    if ($old['full_name'] === '' || mb_strlen($old['full_name']) < 2 || mb_strlen($old['full_name']) > 100) {
        $errors[] = 'Full name is required (2–100 chars).';
    }
    if ($old['email'] === '' || !isValidEmail($old['email'])) {
        $errors[] = 'A valid email is required.';
    }
    if ($old['subject'] === '' || mb_strlen($old['subject']) < 3 || mb_strlen($old['subject']) > 150) {
        $errors[] = 'Subject is required (3–150 chars).';
    }
    if ($old['body'] === '' || mb_strlen($old['body']) < 10 || mb_strlen($old['body']) > 2000) {
        $errors[] = 'Message is required (10–2000 chars).';
    }

    if (empty($errors)) {
        $stmt = $db->prepare(
            "INSERT INTO messages (full_name, email, subject, body)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('ssss',
            $old['full_name'], $old['email'], $old['subject'], $old['body']);
        $stmt->execute();
        $stmt->close();

        setFlash('Thanks for reaching out! We will get back to you soon.', 'success');
        redirect('/contact.php');
    }
}

$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Contact Us</h1>

<div class="row g-4">
    <div class="col-md-7">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/contact.php" novalidate>
            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                       value="<?= e($old['full_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= e($old['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="subject">Subject</label>
                <input type="text" id="subject" name="subject" class="form-control"
                       maxlength="150" value="<?= e($old['subject']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="body">Message</label>
                <textarea id="body" name="body" rows="6" class="form-control"
                          maxlength="2000" required><?= e($old['body']) ?></textarea>
                <small class="text-muted">10–2000 characters.</small>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Visit Us</h5>
                <p class="mb-1"><strong>Address</strong></p>
                <p class="mb-3">123 Bistro Lane, Foodtown, FT 12345</p>

                <p class="mb-1"><strong>Phone</strong></p>
                <p class="mb-3">+1 (555) 123-4567</p>

                <p class="mb-1"><strong>Email</strong></p>
                <p class="mb-3">hello@tastybites.local</p>

                <p class="mb-1"><strong>Opening Hours</strong></p>
                <p class="mb-0">Mon–Sun · 11:00 – 22:00</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
