<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => ''];

if (currentMethod() === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['phone']     = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $passwordConfirm  = $_POST['password_confirm'] ?? '';

    // ---- Validation ----
    if ($old['full_name'] === '') {
        $errors[] = 'Full name is required.';
    } elseif (mb_strlen($old['full_name']) < 2 || mb_strlen($old['full_name']) > 100) {
        $errors[] = 'Full name must be 2–100 characters.';
    }

    if ($old['email'] === '') {
        $errors[] = 'Email is required.';
    } elseif (!isValidEmail($old['email'])) {
        $errors[] = 'Email is not valid.';
    }

    if ($old['phone'] !== '' && mb_strlen($old['phone']) > 20) {
        $errors[] = 'Phone is too long (max 20 characters).';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (mb_strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($passwordConfirm === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $passwordConfirm) {
        $errors[] = "Passwords don't match.";
    }

    // Unique email check (only if email passed format validation)
    if ($old['email'] !== '' && isValidEmail($old['email'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        }
        $stmt->close();
    }

    // ---- Insert ----
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $phoneToStore = $old['phone'] !== '' ? $old['phone'] : null;
        $stmt = $db->prepare(
            "INSERT INTO users (full_name, email, phone, password_hash, role)
             VALUES (?, ?, ?, ?, 'user')"
        );
        $stmt->bind_param('ssss', $old['full_name'], $old['email'], $phoneToStore, $hash);
        $stmt->execute();
        $stmt->close();

        setFlash('Account created. You can log in now.', 'success');
        redirect('/auth/login.php');
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">Create an Account</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/auth/register.php" novalidate>
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
                <label class="form-label" for="phone">Phone <small class="text-muted">(optional)</small></label>
                <input type="text" id="phone" name="phone" class="form-control"
                       value="<?= e($old['phone']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
            <p class="mt-3 text-center">
                Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Log in</a>.
            </p>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
