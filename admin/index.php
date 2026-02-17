<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$counts = ['members' => 0, 'pending' => 0, 'library' => 0];

if (isset($pdo)) {
    $counts['members'] = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'approved'")->fetchColumn();
    $counts['pending'] = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'pending'")->fetchColumn();
    $counts['library'] = $pdo->query("SELECT COUNT(*) FROM library")->fetchColumn();
}

include 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Overview</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Approved Members</h5>
                <h2 class="display-4 fw-bold"><?php echo $counts['members']; ?></h2>
                <a href="members.php" class="text-white">View Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-dark h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Pending Applications</h5>
                <h2 class="display-4 fw-bold"><?php echo $counts['pending']; ?></h2>
                <a href="members.php" class="text-dark">Review Applications <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Library Items</h5>
                <h2 class="display-4 fw-bold"><?php echo $counts['library']; ?></h2>
                <a href="library.php" class="text-white">Manage Library <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h3>Quick Actions</h3>
    <div class="list-group shadow-sm mt-3">
        <a href="library.php" class="list-group-item list-group-item-action"><i class="bi bi-plus-circle me-2"></i> Upload New Library Item</a>
        <a href="executives.php" class="list-group-item list-group-item-action"><i class="bi bi-person-plus me-2"></i> Add New Executive</a>
        <a href="members.php" class="list-group-item list-group-item-action"><i class="bi bi-check-circle me-2"></i> Review Pending Members</a>
    </div>
</div>

<?php include 'footer.php'; ?>
