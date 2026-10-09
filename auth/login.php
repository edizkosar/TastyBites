<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$errors = [];
$old = ['email' => ''];

if (currentMethod() === 'POST') {
    $old['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Light validation first
    if ($old['email'] === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    } elseif (!isValidEmail($old['email'])) {
        $errors[] = 'Invalid email or password.';
    } else {
        $stmt = $db->prepare(
            "SELECT id, full_name, password_hash, role
             FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            setFlash('Welcome back, ' . $user['full_name'] . '!', 'success');
            if ($user['role'] === 'admin') {
                redirect('/admin/dashboard.php');
            }
            redirect('/user/dashboard.php');
        }
    }
}

$pageTitle = 'Log In';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="mb-4">Log In</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= e($errors[0]) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/auth/login.php" novalidate>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($old['email']) ?>"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log In</button>
            <p class="mt-3 text-center">
                No account? <a href="<?= BASE_URL ?>/auth/register.php">Register here</a>.
            </p>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>