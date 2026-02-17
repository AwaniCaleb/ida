<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <!-- <img src="img/logo.png" alt="IDA" width="40" height="40" class="me-2" onerror="this.src='https://via.placeholder.com/40?text=IDA'"> -->
            <i class="bi bi-bank me-2"></i>
            <span class="fw-bold">IDA</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown">About</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="about.php">History & Mission</a></li>
                        <li><a class="dropdown-item" href="executives.php">Executives</a></li>
                        <li><a class="dropdown-item" href="branches.php">Branches</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="library.php">Library</a></li>
                <li class="nav-item"><a class="nav-link" href="membership.php">Membership</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <?php if(isset($_SESSION['member_id'])): ?>
                    <li class="nav-item"><a class="btn btn-outline-light ms-lg-3" href="profile.php">My Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-light ms-lg-3" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
