<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDA Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); }
        .sidebar .nav-link.active { color: white; background-color: #0d6efd; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <h4 class="mb-4">IDA Admin</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li class="nav-item mb-2"><a href="members.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>"><i class="bi bi-people me-2"></i> Members</a></li>
                <li class="nav-item mb-2"><a href="library.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'library.php' ? 'active' : ''; ?>"><i class="bi bi-journal-bookmark me-2"></i> Library</a></li>
                <li class="nav-item mb-2"><a href="executives.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'executives.php' ? 'active' : ''; ?>"><i class="bi bi-person-badge me-2"></i> Executives</a></li>
                <li class="nav-item mb-2"><a href="audit_logs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'audit_logs.php' ? 'active' : ''; ?>"><i class="bi bi-clipboard-data me-2"></i> Audit Logs</a></li>
                <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-info"><i class="bi bi-house me-2"></i> View Site</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
