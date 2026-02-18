<?php
// Gray: Public digital library page.

require_once 'includes/functions.php';
require_once 'includes/db.php';
require_once 'includes/validation.php';
include 'includes/header.php';

$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$items = [];

if (isset($pdo)) {
    $sql = "SELECT * FROM library";
    if ($type_filter != 'all') {
        $stmt = $pdo->prepare($sql . " WHERE type = ? ORDER BY created_at DESC");
        $stmt->execute([$type_filter]);
    } else {
        $stmt = $pdo->query($sql . " ORDER BY created_at DESC");
    }
    $items = $stmt->fetchAll();
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Digital Library</h1>
        <div class="btn-group">
            <a href="library.php?type=all" class="btn btn-outline-primary <?php echo $type_filter == 'all' ? 'active' : ''; ?>">All</a>
            <a href="library.php?type=document" class="btn btn-outline-primary <?php echo $type_filter == 'document' ? 'active' : ''; ?>">Documents</a>
            <a href="library.php?type=video" class="btn btn-outline-primary <?php echo $type_filter == 'video' ? 'active' : ''; ?>">Videos</a>
            <a href="library.php?type=image" class="btn btn-outline-primary <?php echo $type_filter == 'image' ? 'active' : ''; ?>">Images</a>
        </div>
    </div>

    <?php if (!isLoggedIn()): ?>
        <div class="alert alert-info">
            <strong>Notice:</strong> Some premium resources are only available to registered and approved IDA members. <a href="login.php">Login</a> or <a href="register.php">Apply for membership</a> to get full access.
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($items)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No items found in this category.</p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($item['type'] == 'image'): ?>
                            <img
                                src="<?php echo 'uploads/library/image/' . htmlspecialchars($item['file_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                onerror="this.src='https://via.placeholder.com/400x300?text=Image+Unavailable'"
                            >

                        <?php elseif ($item['type'] == 'video'): ?>
                            <?php if (is_valid_youtube_embed($item['file_path'])): ?>
                                <!-- Gray: Only render the iframe if the stored URL passes
                                     the YouTube embed check — second line of defence after
                                     the admin-side validation on save. -->
                                <div class="ratio ratio-16x9">
                                    <iframe
                                        src="<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                        title="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <span class="text-muted small">Video unavailable</span>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span class="fs-1">
                                    <i class="bi bi-file-earmark-text"></i>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                            <p class="card-text small text-muted">
                                <?php echo htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <?php if ($item['type'] == 'document'): ?>
                                <?php if (isLoggedIn()): ?>
                                    <a href="uploads/library/document/<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-sm" download>Download PDF</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Login to Download</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
