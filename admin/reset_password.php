<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$errors = [
    'username' => '',
    'token' => '',
    'password' => '',
    'confirm' => '',
];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $username = normalize_identifier($_POST['username'] ?? '');
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($username === '') {
        $errors['username'] = "Username is required.";
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

    if (!$errors['username'] && !$errors['token'] && !$errors['password'] && !$errors['confirm']) {
        $reset = find_valid_password_reset($pdo, 'admin', $username, $token);
        if (!$reset) {
            $errors['token'] = "Invalid or expired reset token.";
        } else {
            try {
                $pdo->beginTransaction();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
                $stmt->execute([$hash, $username]);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Set New Password - IDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #343a40; }
        .login-card { margin-top: 100px; max-width: 420px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            <div class="card login-card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Set New Password</h3>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary w-100">Back to login</a>
                        </div>
                    <?php else: ?>
                        <form action="reset_password.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control <?php echo $errors['username'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                <?php if ($errors['username']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['username']); ?></div>
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
</body>
</html>
