<?php
// Gray: CLI utility — delete an admin by ID or username.
// Usage: php test/delete_admin.php --id 5
//        php test/delete_admin.php --username "admin2"

require_once __DIR__ . '/bootstrap.php';
// get_arg_value() comes from bootstrap.php — removed duplicate definition

$id       = get_arg_value($argv, 'id');
$username = get_arg_value($argv, 'username');

if (!$id && !$username) {
    cli_error("Provide --id or --username.");
}
if ($id && $username) {
    cli_error("Provide only one of --id or --username.");
}

try {
    if ($id) {
        $stmt = $pdo->prepare("SELECT id, username FROM admins WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM admins WHERE username = ?");
        $stmt->execute([$username]);
    }

    $admin = $stmt->fetch();
    if (!$admin) {
        cli_error("Admin not found.");
    }

    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$admin['id']]);

    cli_print_kv([
        'status'   => 'deleted',
        'id'       => $admin['id'],
        'username' => $admin['username'],
    ]);
} catch (Exception $e) {
    cli_error("Failed to delete admin: " . $e->getMessage());
}
