<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$member_id = $_SESSION['member_id'];
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <img src="<?php echo $member['photo'] ? 'uploads/members/'.$member['photo'] : 'https://via.placeholder.com/150?text=Profile'; ?>" class="rounded-circle mb-3" width="150" height="150" alt="Profile">
                    <h4><?php echo $member['full_name']; ?></h4>
                    <span class="badge bg-success mb-3">Verified Member</span>
                    <hr>
                    <p class="text-start"><strong>Email:</strong> <?php echo $member['email']; ?></p>
                    <p class="text-start"><strong>Phone:</strong> <?php echo $member['phone']; ?></p>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-4">Member Information</h3>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Address</div>
                        <div class="col-sm-9"><?php echo $member['address']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Next of Kin</div>
                        <div class="col-sm-9"><?php echo $member['next_of_kin']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Joined Date</div>
                        <div class="col-sm-9"><?php echo date("F j, Y", strtotime($member['created_at'])); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Resources for You</h3>
                    <p>As a verified member, you have full access to our digital library and archives.</p>
                    <a href="library.php" class="btn btn-primary">Go to Library</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
