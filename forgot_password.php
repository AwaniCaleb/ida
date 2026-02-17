<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errors = ['email' => ''];
$token = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $email = normalize_email($_POST['email'] ?? '');
    if (!is_valid_email($email)) {
        $errors['email'] = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $token = create_password_reset_token($pdo, 'member', $email, $APP_CONFIG['password_reset_seconds']);
            $success = "Password reset token generated. Copy it below.";
        } else {
            $errors['email'] = "No account found with that email.";
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
                    <h2 class="text-center mb-4">Reset Password</h2>

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
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control <?php echo $errors['email'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <?php if ($errors['email']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
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

<?php include 'includes/footer.php'; ?>
