<?php
// Shared CLI helpers for test scripts.
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI only.\n";
    exit(1);
}

require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo)) {
    fwrite(STDERR, "Database connection is not available. Check includes/db.php.\n");
    exit(1);
}

function cli_error($message, $code = 1) {
    fwrite(STDERR, $message . "\n");
    exit($code);
}

function cli_print_kv(array $data) {
    foreach ($data as $key => $value) {
        echo $key . ": " . $value . "\n";
    }
}
?>
