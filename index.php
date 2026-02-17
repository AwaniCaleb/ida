<?php
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<header class="hero-section text-center">
    <div class="container">
        <h1 class="display-3 fw-bold mb-4">Unity and Progress for Ikwerre</h1>
        <p class="lead mb-5">Welcome to the official portal of the Ikwerre Development Association (IDA). Join us in our mission to foster growth, culture, and development.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="register.php" class="btn btn-warning btn-lg px-4">Become a Member</a>
            <a href="about.php" class="btn btn-outline-light btn-lg px-4">Learn More</a>
        </div>
    </div>
</header>

<section class="section-padding bg-white">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="p-4">
                    <div class="feature-icon">
                        <i class="bi bi-geo"></i>
                    </div>
                    <h3>Rich History</h3>
                    <p>Established in 1978, IDA has been at the forefront of the Ikwerre struggle for identity and development.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4">
                    <div class="feature-icon">
                        <i class="bi bi-book"></i>
                    </div>
                    <h3>Digital Library</h3>
                    <p>Access memorial lectures, historical documents, and cultural resources in our comprehensive digital library.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3>Membership</h3>
                    <p>Connect with prominent Ikwerre sons and daughters worldwide. Be part of an articulate and vibrant organization.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4">
                <img src="img/ikwerre-culture.jpg" alt="Ikwerre Culture" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h2 class="mb-4">Mission Statement</h2>
                <p class="lead italic text-muted mb-4">"To provide an articulate and vibrant organization for Ikwerre nationality, augmenting the umbrella body and taking decisions on social, economic, and political issues."</p>
                <a href="about.php" class="btn btn-primary">Read Our Story</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
