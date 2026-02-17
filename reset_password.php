<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errors = [
    'email' => '',
    'token' => '',
    'password' => '',
    'confirm' => '',
];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $email = normalize_email($_POST['email'] ?? '');
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!is_valid_email($email)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if ($token === '') {
        $errors['token'] = "Reset token is required.";
    }
    if (strlen($password) < (int)$APP_CONFIG['min_password_length']) {
        $errors['password'] = "Password must be at least {$APP_CONFIG['min_password_length']} characters.";
    }
    if ($password !== $confirm) {
        $errors['confirm'] = "Passwords do not match.";
    }

    if (!$errors['email'] && !$errors['token'] && !$errors['password'] && !$errors['confirm']) {
        $reset = find_valid_password_reset($pdo, 'member', $email, $token);
        if (!$reset) {
            $errors['token'] = "Invalid or expired reset token.";
        } else {
            try {
                $pdo->beginTransaction();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE email = ?");
                $stmt->execute([$hash, $email]);
                mark_password_reset_used($pdo, $reset['id']);
                $pdo->commit();
                $success = "Password reset successful. You can now log in.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors['token'] = "Reset failed: " . $e->getMessage();
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
                    <h2 class="text-center mb-4">Set New Password</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary w-100">Back to login</a>
                        </div>
                    <?php else: ?>
                        <form action="reset_password.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control <?php echo $errors['email'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <?php if ($errors['email']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reset Token</label>
                                <input type="text" name="token" class="form-control <?php echo $errors['token'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['token'] ?? ''); ?>" required>
                                <?php if ($errors['token']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['token']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control <?php echo $errors['password'] ? 'is-invalid' : ''; ?>" required>
                                <?php if ($errors['password']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo $errors['confirm'] ? 'is-invalid' : ''; ?>" required>
                                <?php if ($errors['confirm']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm']); ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="login.php">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
