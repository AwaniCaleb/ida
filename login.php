<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$errors = [
    'email' => '',
    'password' => '',
];
$lockout_message = '';
$remaining_attempts = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $email = normalize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_valid_email($email)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if ($password === '') {
        $errors['password'] = "Password is required.";
    }

    if (!$errors['email'] && !$errors['password']) {
        $lockout = get_lockout_info($pdo, 'member', $email, $APP_CONFIG['max_login_attempts']);
        if ($lockout['locked']) {
            $remaining = max(0, $lockout['locked_until'] - time());
            $mins = floor($remaining / 60);
            $secs = $remaining % 60;
            $lockout_message = "Too many failed attempts. Try again in {$mins}m {$secs}s.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] == 'approved') {
                    session_regenerate_id(true);
                    $_SESSION['member_id'] = $user['id'];
                    $_SESSION['member_name'] = $user['full_name'];
                    clear_login_attempts($pdo, 'member', $email);
                    redirect('profile.php');
                } elseif ($user['status'] == 'pending') {
                    $errors['password'] = "Your application is still pending review.";
                } else {
                    $errors['password'] = "Your application has been rejected. Please contact IDA for more information.";
                }
            } else {
                $new_attempt = record_login_failure(
                    $pdo,
                    'member',
                    $email,
                    $APP_CONFIG['max_login_attempts'],
                    $APP_CONFIG['login_lockout_seconds']
                );
                $remaining_attempts = max(0, $APP_CONFIG['max_login_attempts'] - (int)$new_attempt['failed_count']);
                if ($new_attempt['locked_until'] > time()) {
                    $remaining = max(0, $new_attempt['locked_until'] - time());
                    $mins = floor($remaining / 60);
                    $secs = $remaining % 60;
                    $lockout_message = "Too many failed attempts. Try again in {$mins}m {$secs}s.";
                } else {
                    $errors['password'] = "Invalid email or password.";
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6 col-lg-4 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Member Login</h2>

                    <?php if ($lockout_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($lockout_message); ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control <?php echo $errors['email'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            <?php if ($errors['email']): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?php echo $errors['password'] ? 'is-invalid' : ''; ?>" required>
                            <?php if ($errors['password']): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                            <?php endif; ?>
                            <?php if ($remaining_attempts !== null && !$lockout_message): ?>
                                <div class="form-text">Remaining attempts: <?php echo (int)$remaining_attempts; ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">New member? <a href="register.php">Apply here</a></p>
                        <p class="mb-0 mt-2"><a href="forgot_password.php">Forgot password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
