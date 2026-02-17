<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$message = '';
$message_type = 'success';

if (isset($_GET['action']) && isset($_GET['id'])) {
    if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $id = (int)$_GET['id'];
    $admin_id = $_SESSION['admin_id'];
    try {
        $pdo->beginTransaction();
        if ($_GET['action'] == 'approve') {
            $stmt = $pdo->prepare("UPDATE members SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);

            log_admin_action($pdo, $admin_id, 'member_approve', 'member', $id, json_encode(['status' => 'approved']));

            $message = "Member approved successfully.";
        } elseif ($_GET['action'] == 'reject') {
            $stmt = $pdo->prepare("UPDATE members SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);

            log_admin_action($pdo, $admin_id, 'member_reject', 'member', $id, json_encode(['status' => 'rejected']));

            $message = "Member application rejected.";
        } elseif ($_GET['action'] == 'delete') {
            $stmt = $pdo->prepare("SELECT email, full_name FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch();

            if (!$member) {
                throw new Exception('Member not found.');
            }

            $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
            $stmt->execute([$id]);

            if (isset($member['photo']) && $member['photo']) {
                $safe_name = basename($member['photo']);
                $file_path = __DIR__ . '/../uploads/members/' . $safe_name;
                if (is_file($file_path)) {
                    @unlink($file_path);
                }
            }

            log_admin_action($pdo, $admin_id, 'member_delete', 'member', $id, json_encode(['email' => $member['email'], 'name' => $member['full_name']]));

            $message = "Member deleted successfully.";
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $message_type = 'danger';
        $message = "Action failed: " . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM members ORDER BY created_at DESC");
$members = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Members</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email/Phone</th>
                        <th>Next of Kin</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($member['full_name']); ?></div>
                                <div class="small text-muted">Joined <?php echo date("Y-m-d", strtotime($member['created_at'])); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($member['email']); ?></div>
                                <div class="small"><?php echo htmlspecialchars($member['phone']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($member['next_of_kin']); ?></td>
                            <td>
                                <span class="badge <?php
                                    echo $member['status'] == 'approved' ? 'bg-success' : ($member['status'] == 'pending' ? 'bg-warning text-dark' : 'bg-danger');
                                ?>">
                                    <?php echo ucfirst($member['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php $token = generateCsrfToken(); ?>
                                <?php if ($member['status'] == 'pending'): ?>
                                    <a href="members.php?action=approve&id=<?php echo $member['id']; ?>&csrf_token=<?php echo $token; ?>" class="btn btn-sm btn-outline-success">Approve</a>
                                    <a href="members.php?action=reject&id=<?php echo $member['id']; ?>&csrf_token=<?php echo $token; ?>" class="btn btn-sm btn-outline-danger">Reject</a>
                                <?php endif; ?>
                                <a href="members.php?action=delete&id=<?php echo $member['id']; ?>&csrf_token=<?php echo $token; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
