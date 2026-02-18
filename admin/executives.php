<?php
// Gray: Admin — manage executives (add / delete).

require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/validation.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$message      = '';
$message_type = 'info';

// ── Handle ADD ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exec'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $name         = trim($_POST['name']          ?? '');
    $position     = trim($_POST['position']      ?? '');
    $type         = trim($_POST['type']          ?? '');
    $bio          = trim($_POST['bio']           ?? '');
    $order_weight = (int) ($_POST['order_weight'] ?? 0);
    $photo        = '';
    $allowed_types = ['present', 'past'];

    if ($name === '' || !is_valid_name($name)) {
        $message      = "Error: Please enter a valid name.";
        $message_type = 'danger';
    } elseif ($position === '') {
        $message      = "Error: Position is required.";
        $message_type = 'danger';
    } elseif (!in_array($type, $allowed_types, true)) {
        $message      = "Error: Invalid executive type.";
        $message_type = 'danger';
    }

    // Gray: Photo upload — MIME check
    if (!$message && isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $upload_error = '';
        if (is_valid_image_upload($_FILES['photo'], $upload_error)) {
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/executives/' . $filename)) {
                $photo = $filename;
            } else {
                $message      = "Error: Failed to save uploaded photo.";
                $message_type = 'danger';
            }
        } else {
            $message      = "Error: " . $upload_error;
            $message_type = 'danger';
        }
    }

    if (!$message) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO executives (name, position, type, bio, photo, order_weight)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $position, $type, $bio, $photo, $order_weight]);
            $exec_id = $pdo->lastInsertId();
            log_admin_action(
                $pdo,
                $_SESSION['admin_id'],
                'executive_add',
                'executive',
                $exec_id,
                json_encode(['name' => $name, 'position' => $position])
            );
            $pdo->commit();
            $message      = "Executive added successfully.";
            $message_type = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message      = "Error: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// ── Handle DELETE ────────────────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $id = (int) $_GET['id'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT name, position, photo FROM executives WHERE id = ?");
        $stmt->execute([$id]);
        $exec = $stmt->fetch();
        if (!$exec) {
            throw new Exception('Executive not found.');
        }
        $stmt = $pdo->prepare("DELETE FROM executives WHERE id = ?");
        $stmt->execute([$id]);
        log_admin_action(
            $pdo,
            $_SESSION['admin_id'],
            'executive_delete',
            'executive',
            $id,
            json_encode(['name' => $exec['name'], 'position' => $exec['position']])
        );
        $pdo->commit();

        // Gray: Unlink after commit so we never orphan a DB row
        if ($exec['photo']) {
            $safe_name = basename($exec['photo']);
            $file_path = __DIR__ . '/../uploads/executives/' . $safe_name;
            if (is_file($file_path)) {
                @unlink($file_path);
            }
        }

        $message      = "Executive removed.";
        $message_type = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message      = "Error: " . $e->getMessage();
        $message_type = 'danger';
    }
}

$executives = $pdo->query(
    "SELECT * FROM executives ORDER BY type DESC, order_weight ASC"
)->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Executives</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExecModal">Add Executive</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($executives as $exec): ?>
                        <tr>
                            <td>
                                <img
                                    src="<?php echo $exec['photo']
                                        ? '../uploads/executives/' . htmlspecialchars($exec['photo'], ENT_QUOTES, 'UTF-8')
                                        : 'https://via.placeholder.com/50'; ?>"
                                    width="40" height="40" class="rounded-circle"
                                >
                            </td>
                            <td><?php echo htmlspecialchars($exec['name'],     ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($exec['position'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?php echo $exec['type'] == 'present' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($exec['type'], ENT_QUOTES, 'UTF-8')); ?>
                                </span>
                            </td>
                            <td>
                                <a
                                    href="executives.php?action=delete&id=<?php echo (int) $exec['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Remove this executive?')"
                                >
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal — unchanged from original -->
<div class="modal fade" id="addExecModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="executives.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Executive</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="present">Present Executive</option>
                            <option value="past">Past Executive / President</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio (Brief introduction)</label>
                        <textarea name="bio" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order (Lower numbers first)</label>
                        <input type="number" name="order_weight" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_exec" class="btn btn-primary">Save Executive</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
