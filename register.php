<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/config.php';
require_once 'includes/validation.php';
include 'includes/header.php';

$errors = [
    'full_name' => '',
    'email' => '',
    'password' => '',
    'phone' => '',
    'address' => '',
    'next_of_kin' => '',
    'photo' => '',
];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($pdo)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $full_name = trim($_POST['full_name'] ?? '');
    $email = normalize_email($_POST['email'] ?? '');
    $password_plain = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $next_of_kin = trim($_POST['next_of_kin'] ?? '');
    $photo = '';

    if ($full_name === '' || !is_valid_name($full_name)) {
        $errors['full_name'] = "Please enter a valid full name.";
    }
    if (!is_valid_email($email)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if (strlen($password_plain) < (int)$APP_CONFIG['min_password_length']) {
        $errors['password'] = "Password must be at least {$APP_CONFIG['min_password_length']} characters long.";
    }
    if (!is_valid_phone($phone)) {
        $errors['phone'] = "Please enter a valid phone number.";
    }
    if ($address === '') {
        $errors['address'] = "Address is required.";
    }
    if ($next_of_kin === '' || !is_valid_name($next_of_kin)) {
        $errors['next_of_kin'] = "Please enter a valid next of kin name.";
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== 0 && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['photo'] = "Photo upload failed. Please try again.";
    }

    if (!$errors['photo'] && isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_extensions, true)) {
            $errors['photo'] = "Invalid photo type. Allowed: " . implode(', ', $allowed_extensions);
        } else {
            $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $target = __DIR__ . '/uploads/members/' . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photo = $filename;
            } else {
                $errors['photo'] = "Failed to save uploaded photo.";
            }
        }
    }

    // Check if email already exists
    if (!$errors['full_name'] && !$errors['email'] && !$errors['password'] && !$errors['phone'] && !$errors['address'] && !$errors['next_of_kin'] && !$errors['photo']) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = "An account with this email already exists.";
        } else {
            $password = password_hash($password_plain, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO members (full_name, email, password, phone, address, next_of_kin, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $password, $phone, $address, $next_of_kin, $photo])) {
                $success = "Your application has been submitted successfully! It will be reviewed by the committee.";
            } else {
                $errors['email'] = "There was an error processing your application. Please try again.";
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Membership Application</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php else: ?>
                        <form action="register.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <div class="mb-3">
                                <label class="form-label">Full Name (as per ID)</label>
                                <input type="text" name="full_name" class="form-control <?php echo $errors['full_name'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                <?php if ($errors['full_name']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control <?php echo $errors['email'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <?php if ($errors['email']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control <?php echo $errors['password'] ? 'is-invalid' : ''; ?>" required>
                                <?php if ($errors['password']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control <?php echo $errors['phone'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                <?php if ($errors['phone']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Home Address</label>
                                <textarea name="address" class="form-control <?php echo $errors['address'] ? 'is-invalid' : ''; ?>" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                <?php if ($errors['address']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['address']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Next of Kin</label>
                                <input type="text" name="next_of_kin" class="form-control <?php echo $errors['next_of_kin'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($_POST['next_of_kin'] ?? ''); ?>" required>
                                <?php if ($errors['next_of_kin']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['next_of_kin']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="photo" class="form-control <?php echo $errors['photo'] ? 'is-invalid' : ''; ?>">
                                <?php if ($errors['photo']): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['photo']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" required>
                                    <label class="form-check-label small">
                                        I hereby apply for membership and agree to abide by the Constitution of the Ikwerre Development Association.
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Submit Application</button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
