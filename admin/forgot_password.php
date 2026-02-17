<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$errors = ['username' => ''];
$token = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $username = normalize_identifier($_POST['username'] ?? '');
    if ($username === '') {
        $errors['username'] = "Username is required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $token = create_password_reset_token($pdo, 'admin', $username, $APP_CONFIG['password_reset_seconds']);
            $success = "Password reset token generated. Copy it below.";
        } else {
            $errors['username'] = "No admin account found with that username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset - IDA</title>
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
                    <h3 class="text-center mb-4">Admin Password Reset</h3>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <div class="mb-3">
                            <label class="form-label">Reset Token</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($token); ?>" readonly>
                        </div>
                        <div class="text-center">
                            <a href="reset_password.php" class="btn btn-primary w-100">Continue to Reset</a>
                        </div>
                    <?php else: ?>
                        <form action="forgot_password.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control <?php echo $errors['username'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                <?php if ($errors['username']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['username']); ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Generate Token</button>
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
