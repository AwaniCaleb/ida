<?php
// Gray: CLI utility — list members, optionally filtered by status.
// Usage: php test/list_members.php
//        php test/list_members.php --status approved

require_once __DIR__ . '/bootstrap.php';
// get_arg_value() comes from bootstrap.php — removed duplicate definition

$status = get_arg_value($argv, 'status');
if ($status && !in_array($status, ['pending', 'approved', 'rejected'], true)) {
    cli_error("Invalid status. Use pending, approved, or rejected.");
}

if ($status) {
    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, status, created_at FROM members
         WHERE status = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$status]);
} else {
    $stmt = $pdo->query(
        "SELECT id, full_name, email, status, created_at FROM members
         ORDER BY created_at DESC"
    );
}

$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    cli_print_kv([
        'id'         => $row['id'],
        'name'       => $row['full_name'],
        'email'      => $row['email'],
        'status'     => $row['status'],
        'created_at' => $row['created_at'],
    ]);
    echo "----\n";
}
