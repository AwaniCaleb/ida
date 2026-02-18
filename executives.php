<?php
// Gray: Public-facing executives page.
// Only change from original: every DB value that touches the page
// now goes through htmlspecialchars() — guards against stored XSS
// if something unexpected ever gets into the database.

require_once 'includes/functions.php';
require_once 'includes/db.php';
include 'includes/header.php';

$present_execs = [];
$past_execs    = [];

if (isset($pdo)) {
    $stmt = $pdo->query("SELECT * FROM executives ORDER BY type DESC, order_weight ASC");
    while ($row = $stmt->fetch()) {
        if ($row['type'] == 'present') {
            $present_execs[] = $row;
        } else {
            $past_execs[] = $row;
        }
    }
}
?>

<div class="container my-5">
    <h1 class="text-center mb-5">IDA Executives</h1>

    <section class="mb-5">
        <h2 class="border-bottom pb-2 mb-4">Present Executives</h2>
        <div class="row">
            <?php if (empty($present_execs)): ?>
                <p>Present executive information is being updated. Please check back soon.</p>
            <?php else: ?>
                <?php foreach ($present_execs as $exec): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center">
                            <img
                                src="<?php echo $exec['photo']
                                    ? 'uploads/executives/' . htmlspecialchars($exec['photo'], ENT_QUOTES, 'UTF-8')
                                    : 'https://via.placeholder.com/300?text=Executive'; ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($exec['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($exec['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h5>
                                <p class="card-text text-primary fw-bold">
                                    <?php echo htmlspecialchars($exec['position'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p class="card-text small">
                                    <?php echo htmlspecialchars($exec['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h2 class="border-bottom pb-2 mb-4">Past Presidents &amp; Executives</h2>
        <div class="row">
            <?php if (empty($past_execs)): ?>
                <p>No past executives available at this time.</p>
            <?php else: ?>
                <?php foreach ($past_execs as $exec): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center">
                            <img
                                src="<?php echo $exec['photo']
                                    ? 'uploads/executives/' . htmlspecialchars($exec['photo'], ENT_QUOTES, 'UTF-8')
                                    : 'https://via.placeholder.com/300?text=Executive'; ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($exec['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($exec['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h5>
                                <p class="card-text text-primary fw-bold">
                                    <?php echo htmlspecialchars($exec['position'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p class="card-text small">
                                    <?php echo htmlspecialchars($exec['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
