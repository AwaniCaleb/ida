<?php
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="display-4 mb-4">Membership Zone</h1>
            <p class="lead">Join the league of distinguished Ikwerre sons and daughters committed to the advancement of our nation.</p>
            <div class="d-flex gap-2">
                <a href="register.php" class="btn btn-primary btn-lg">Apply Now</a>
                <a href="login.php" class="btn btn-outline-secondary btn-lg">Member Login</a>
            </div>
        </div>
        <div class="col-md-5">
            <img src="img/ida-community.jpg" alt="IDA Community" class="img-fluid rounded shadow">
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 p-4 border-primary">
                <h3>Benefits of Membership</h3>
                <ul class="list-group list-group-flush mt-3">
                    <li class="list-group-item">Network with prominent Ikwerre leaders and professionals.</li>
                    <li class="list-group-item">Support and participate in developmental projects for Ikwerreland.</li>
                    <li class="list-group-item">Access to exclusive memorial lectures and cultural events.</li>
                    <li class="list-group-item">Welfare support during joyous occasions and times of need (as per Constitution).</li>
                    <li class="list-group-item">Eligibility to vote and be voted for in IDA elections.</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 p-4 border-warning">
                <h3>How to Become a Member</h3>
                <p>Membership is open to all Ikwerre sons and daughters who share our vision for progress.</p>
                <ol class="mt-3">
                    <li><strong>Application:</strong> Complete the online membership form.</li>
                    <li><strong>Review:</strong> Your application will be reviewed by the Executive Committee.</li>
                    <li><strong>Documentation:</strong> Provide necessary ID and proof of Ikwerre origin if requested.</li>
                    <li><strong>Registration:</strong> Upon approval, your name will be entered into the Register of Members.</li>
                    <li><strong>Financial Commitment:</strong> Pay the prescribed registration fees and monthly subscriptions.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
