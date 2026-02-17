<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('index.php');
}

$errors = [
    'username' => '',
    'password' => '',
];
$lockout_message = '';
$remaining_attempts = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $username = normalize_identifier($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $errors['username'] = "Username is required.";
    }
    if ($password === '') {
        $errors['password'] = "Password is required.";
    }

    if (!$errors['username'] && !$errors['password']) {
        $lockout = get_lockout_info($pdo, 'admin', $username, $APP_CONFIG['max_login_attempts']);
        if ($lockout['locked']) {
            $remaining = max(0, $lockout['locked_until'] - time());
            $mins = floor($remaining / 60);
            $secs = $remaining % 60;
            $lockout_message = "Too many failed attempts. Try again in {$mins}m {$secs}s.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                clear_login_attempts($pdo, 'admin', $username);
                redirect('index.php');
            } else {
                $new_attempt = record_login_failure(
                    $pdo,
                    'admin',
                    $username,
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
                    $errors['password'] = "Invalid username or password.";
                }
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
    <title>Admin Login - IDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #343a40; }
        .login-card { margin-top: 100px; max-width: 400px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            <div class="card login-card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">IDA Admin</h3>
                    <?php if ($lockout_message): ?>
                        <div class="alert alert-danger small"><?php echo htmlspecialchars($lockout_message); ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control <?php echo $errors['username'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            <?php if ($errors['username']): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['username']); ?></div>
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
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="forgot_password.php">Forgot password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
