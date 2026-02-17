<?php
require_once 'includes/functions.php';
include 'includes/header.php';

$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Simple contact form logic for now, save to DB later
    $message_sent = true;
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6 mb-5">
            <h1 class="mb-4">Get in Touch</h1>
            <p>If you have any questions about our activities, membership, or upcoming events, please don't hesitate to reach out.</p>

            <div class="mt-4">
                <h5><i class="bi bi-geo-alt"></i> Address</h5>
                <p>Ikwerre Development Association Headquarters,<br>Port Harcourt, Rivers State, Nigeria.</p>

                <h5><i class="bi bi-envelope"></i> Email</h5>
                <p>info@ida.org.ng</p>

                <h5><i class="bi bi-telephone"></i> Phone</h5>
                <p>+234 800 000 0000</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4">
                <h3>Send us a Message</h3>
                <?php if ($message_sent): ?>
                    <div class="alert alert-success">Thank you! Your message has been sent.</div>
                <?php endif; ?>
                <form action="contact.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
