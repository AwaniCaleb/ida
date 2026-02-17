<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$logs = [];
if (isset($pdo)) {
    $stmt = $pdo->query(
        "SELECT l.id, l.action, l.entity_type, l.entity_id, l.details, l.created_at, a.username
         FROM audit_logs l
         JOIN admins a ON a.id = l.admin_id
         ORDER BY l.created_at DESC
         LIMIT 200"
    );
    $logs = $stmt->fetchAll();
}

include 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Audit Logs</h1>
    <div class="text-muted">Showing latest 200 entries</div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>When</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo (int)$log['id']; ?></td>
                            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['entity_type']); ?><?php echo $log['entity_id'] ? ' #' . (int)$log['entity_id'] : ''; ?></td>
                            <td style="max-width: 320px; white-space: pre-wrap;"><?php echo htmlspecialchars($log['details'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$logs): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No audit logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
