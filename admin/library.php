<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $file_path = '';
    $allowed_types = ['document', 'image', 'video'];

    if ($title === '') {
        $message = "Error: Title is required.";
        $message_type = 'danger';
    } elseif (!in_array($type, $allowed_types, true)) {
        $message = "Error: Invalid library type.";
        $message_type = 'danger';
    }

    if (!$message && $type == 'video') {
        $file_path = $_POST['video_url']; // Expected to be an embed URL
        if (trim($file_path) === '') {
            $message = "Error: Embed URL is required for video.";
            $message_type = 'danger';
        }
    } else {
        if (!$message && isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $allowed_extensions = ($type == 'document') ? ['pdf', 'doc', 'docx'] : ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_extensions)) {
                $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                if (move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/library/' . $type . '/' . $filename)) {
                    $file_path = $filename;
                }
            } else {
                $message = "Error: Invalid file extension. Allowed: " . implode(', ', $allowed_extensions);
                $message_type = 'danger';
            }
        }
    }

    if (!$message && $file_path) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO library (title, type, file_path, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $type, $file_path, $description]);
            $item_id = $pdo->lastInsertId();
            log_admin_action($pdo, $_SESSION['admin_id'], 'library_add', 'library', $item_id, json_encode(['title' => $title, 'type' => $type]));
            $pdo->commit();
            $message = "Library item added successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error: " . $e->getMessage();
            $message_type = 'danger';
        }
    } elseif (!$message) {
        $message = "Error: File upload failed or URL missing.";
        $message_type = 'danger';
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $id = (int)$_GET['id'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT title, type, file_path FROM library WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception('Item not found.');
        }

        $stmt = $pdo->prepare("DELETE FROM library WHERE id = ?");
        $stmt->execute([$id]);
        log_admin_action($pdo, $_SESSION['admin_id'], 'library_delete', 'library', $id, json_encode(['title' => $item['title'], 'type' => $item['type']]));
        $pdo->commit();

        if ($item['type'] !== 'video' && $item['file_path']) {
            $safe_name = basename($item['file_path']);
            $file_path = __DIR__ . '/../uploads/library/' . $item['type'] . '/' . $safe_name;
            if (is_file($file_path)) {
                @unlink($file_path);
            }
        }
        $message = "Item deleted.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $message_type = 'danger';
    }
}

$items = $pdo->query("SELECT * FROM library ORDER BY created_at DESC")->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Library Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">Add New Item</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($item['type']); ?></span></td>
                            <td><?php echo date("Y-m-d", strtotime($item['created_at'])); ?></td>
                            <td>
                                <a href="library.php?action=delete&id=<?php echo $item['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="library.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Library Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" id="typeSelect" onchange="toggleInputs()">
                            <option value="document">Document (PDF)</option>
                            <option value="image">Image</option>
                            <option value="video">Video (YouTube Embed)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="fileInput">
                        <label class="form-label">File</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="mb-3 d-none" id="urlInput">
                        <label class="form-label">Embed URL</label>
                        <input type="text" name="video_url" class="form-control" placeholder="https://www.youtube.com/embed/...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_item" class="btn btn-primary">Save Item</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleInputs() {
    const type = document.getElementById('typeSelect').value;
    const fileInput = document.getElementById('fileInput');
    const urlInput = document.getElementById('urlInput');

    if (type === 'video') {
        fileInput.classList.add('d-none');
        urlInput.classList.remove('d-none');
    } else {
        fileInput.classList.remove('d-none');
        urlInput.classList.add('d-none');
    }
}
</script>

<?php include 'footer.php'; ?>
